<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Database {
    private static ?Database $instance = null;
    private static ?Client $sharedClient = null;

    private $httpClient;
    private $dbUrl;
    private $authToken;

    private $rawSql = '';
    private $bindings = [];
    
    private $rows = [];
    private $affectedRowCount = 0;
    private $lastInsertId = null;

    // Transaction Management via Turso Batons
    private $baton = null;
    private $inTransaction = false;

    public function __construct() {
        $this->dbUrl = defined('TURSO_DB_URL') ? TURSO_DB_URL : '';
        $this->authToken = defined('TURSO_AUTH_TOKEN') ? TURSO_AUTH_TOKEN : '';

        // Normalisasi format URL jika diawali libsql:// atau turso://
        if (str_starts_with($this->dbUrl, 'libsql://')) {
            $this->dbUrl = 'https://' . substr($this->dbUrl, 9);
        } elseif (str_starts_with($this->dbUrl, 'turso://')) {
            $this->dbUrl = 'https://' . substr($this->dbUrl, 8);
        }

        if (self::$sharedClient === null) {
            self::$sharedClient = new Client([
                'base_uri' => $this->dbUrl,
                'timeout'  => 5.0,
                'headers'  => [
                    'Authorization' => 'Bearer ' . $this->authToken,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ]
            ]);
        }
        $this->httpClient = self::$sharedClient;
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($query) {
        $this->rawSql = $query;
        $this->bindings = [];
        $this->rows = [];
        $this->affectedRowCount = 0;
        $this->lastInsertId = null;
    }

    public function bind($param, $value, $type = null) {
        // Normalisasi parameter (mendukung :param maupun param tanpa titik dua)
        $key = str_starts_with($param, ':') ? substr($param, 1) : $param;
        $this->bindings[$key] = $value;
    }

    public function execute() {
        if (empty($this->rawSql)) {
            return false;
        }

        // 1. Konversi PDO Named Parameter (:param) menjadi Positional Argument (?)
        $convertedSql = $this->rawSql;
        $positionalArgs = [];

        if (!empty($this->bindings)) {
            $convertedSql = preg_replace_callback('/:([a-zA-Z0-9_]+)/', function($matches) use (&$positionalArgs) {
                $paramName = $matches[1];
                if (array_key_exists($paramName, $this->bindings)) {
                    $val = $this->bindings[$paramName];
                    $positionalArgs[] = $this->formatArgument($val);
                    return '?';
                }
                return $matches[0];
            }, $this->rawSql);
        }

        // 2. Susun Payload HTTP Pipeline v2 Turso
        $requestPayload = [
            'type' => 'execute',
            'stmt' => [
                'sql'  => $convertedSql,
                'args' => $positionalArgs
            ]
        ];

        $pipelineRequests = [$requestPayload];

        // Jika tidak dalam transaksi, segera tutup stream koneksi
        if (!$this->inTransaction) {
            $pipelineRequests[] = ['type' => 'close'];
        }

        $body = ['requests' => $pipelineRequests];
        if ($this->baton !== null) {
            $body['baton'] = $this->baton;
        }

        try {
            $response = $this->httpClient->post('/v2/pipeline', [
                'json' => $body
            ]);

            $resultData = json_decode($response->getBody()->getContents(), true);

            if (isset($resultData['baton'])) {
                $this->baton = $resultData['baton'];
            }

            if (!empty($resultData['results'][0])) {
                $execResult = $resultData['results'][0];

                if ($execResult['type'] === 'error') {
                    $errorMessage = $execResult['error']['message'] ?? 'Unknown Turso error';
                    error_log('Turso Execution Error: ' . $errorMessage);
                    throw new Exception($errorMessage);
                }

                $stmtResult = $execResult['response']['result'] ?? [];
                $cols = $stmtResult['cols'] ?? [];
                $rows = $stmtResult['rows'] ?? [];

                $this->affectedRowCount = (int)($stmtResult['affected_row_count'] ?? 0);
                $this->lastInsertId = $stmtResult['last_insert_rowid'] ?? null;

                // 3. Mapping hasil rows ke Array Asosiatif (identik dengan PDO::FETCH_ASSOC)
                $this->rows = [];
                foreach ($rows as $row) {
                    $record = [];
                    foreach ($cols as $colIdx => $colMeta) {
                        $colName = $colMeta['name'];
                        $cell = $row[$colIdx] ?? ['type' => 'null'];
                        $record[$colName] = $this->extractCellValue($cell);
                    }
                    $this->rows[] = $record;
                }
            }

            return true;
        } catch (GuzzleException $e) {
            error_log('Turso HTTP Connection Error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log('Turso Query Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function resultSet() {
        $this->execute();
        return $this->rows;
    }

    public function single() {
        $this->execute();
        return !empty($this->rows) ? $this->rows[0] : false;
    }

    public function rowCount() {
        if ($this->affectedRowCount > 0) {
            return $this->affectedRowCount;
        }
        return count($this->rows);
    }

    public function lastInsertId() {
        return $this->lastInsertId;
    }

    public function beginTransaction() {
        $this->inTransaction = true;
        $this->rawSql = 'BEGIN TRANSACTION;';
        $this->bindings = [];
        return $this->execute();
    }

    public function commit() {
        $this->rawSql = 'COMMIT;';
        $this->bindings = [];
        $res = $this->execute();
        $this->inTransaction = false;
        $this->baton = null;
        return $res;
    }

    public function rollBack() {
        $this->rawSql = 'ROLLBACK;';
        $this->bindings = [];
        $res = $this->execute();
        $this->inTransaction = false;
        $this->baton = null;
        return $res;
    }

    private function formatArgument($value) {
        if ($value === null) {
            return ['type' => 'null'];
        } elseif (is_int($value)) {
            return ['type' => 'integer', 'value' => (string)$value];
        } elseif (is_float($value)) {
            return ['type' => 'float', 'value' => $value];
        } elseif (is_bool($value)) {
            return ['type' => 'integer', 'value' => $value ? '1' : '0'];
        } else {
            return ['type' => 'text', 'value' => (string)$value];
        }
    }

    private function extractCellValue($cell) {
        $type = $cell['type'] ?? 'null';
        if ($type === 'null') {
            return null;
        } elseif ($type === 'integer') {
            return is_numeric($cell['value']) ? (int)$cell['value'] : $cell['value'];
        } elseif ($type === 'float') {
            return (float)$cell['value'];
        } elseif ($type === 'blob') {
            return isset($cell['base64']) ? base64_decode($cell['base64']) : '';
        }
        return $cell['value'] ?? null;
    }
}