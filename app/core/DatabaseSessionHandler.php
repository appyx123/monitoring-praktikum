<?php

class DatabaseSessionHandler implements SessionHandlerInterface {
    private $db;
    private $maxLifetime;

    public function __construct() {
        $this->maxLifetime = (int)ini_get('session.gc_maxlifetime') ?: 86400;
    }

    public function open($savePath, $sessionName): bool {
        $this->db = new Database();
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string|false {
        try {
            $currentTime = time();
            $expiryThreshold = $currentTime - $this->maxLifetime;

            $this->db->query("SELECT data FROM sessions WHERE id = :id AND last_activity > :expiry");
            $this->db->bind('id', $id);
            $this->db->bind('expiry', $expiryThreshold);
            $row = $this->db->single();

            if ($row && isset($row['data'])) {
                return (string)$row['data'];
            }
            return "";
        } catch (Exception $e) {
            error_log("Session Read Error: " . $e->getMessage());
            return "";
        }
    }

    public function write($id, $data): bool {
        try {
            $currentTime = time();
            // SQLite UPSERT syntax (didukung penuh oleh Turso / libSQL)
            $this->db->query("INSERT INTO sessions (id, data, last_activity) 
                             VALUES (:id, :data, :activity)
                             ON CONFLICT(id) DO UPDATE SET 
                                 data = excluded.data, 
                                 last_activity = excluded.last_activity");
            $this->db->bind('id', $id);
            $this->db->bind('data', $data);
            $this->db->bind('activity', $currentTime);

            return (bool)$this->db->execute();
        } catch (Exception $e) {
            error_log("Session Write Error: " . $e->getMessage());
            return false;
        }
    }

    public function destroy($id): bool {
        try {
            $this->db->query("DELETE FROM sessions WHERE id = :id");
            $this->db->bind('id', $id);
            return (bool)$this->db->execute();
        } catch (Exception $e) {
            error_log("Session Destroy Error: " . $e->getMessage());
            return false;
        }
    }

    public function gc($maxLifetime): int|false {
        try {
            $expiryThreshold = time() - $maxLifetime;
            $this->db->query("DELETE FROM sessions WHERE last_activity < :expiry");
            $this->db->bind('expiry', $expiryThreshold);
            $this->db->execute();
            return $this->db->rowCount();
        } catch (Exception $e) {
            error_log("Session GC Error: " . $e->getMessage());
            return false;
        }
    }
}
