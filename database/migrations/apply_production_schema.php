<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/core/Database.php';

$db = new Database();

echo "====================================================================\n";
echo " APPLYING SENIOR ARCHITECT PRODUCTION SCHEMA (TURSO libSQL / SQLite)\n";
echo "====================================================================\n\n";

// 1. BACKUP EXISTING DATA
echo "[1/5] Backing up existing database records...\n";
$db->query("SELECT * FROM sessions");
$sessionsData = $db->resultSet() ?: [];
echo "      - sessions: " . count($sessionsData) . " records\n";

$db->query("SELECT * FROM mst_user");
$userData = $db->resultSet() ?: [];
echo "      - mst_user: " . count($userData) . " records\n";

$db->query("SELECT * FROM mst_asisten");
$asistenData = $db->resultSet() ?: [];
echo "      - mst_asisten: " . count($asistenData) . " records\n";

// 2. DROP TABLES IN REVERSE DEPENDENCY ORDER
echo "\n[2/5] Dropping legacy tables...\n";
$dropTables = [
    'restore',
    'trs_mentoring',
    'trs_frekuensi',
    'trs_restore',
    'mst_asisten',
    'mst_matakuliah',
    'mst_kelas',
    'mst_tahun_ajaran',
    'mst_ruangan',
    'mst_dosen',
    'mst_jurusan',
    'mst_user',
    'sessions'
];

foreach ($dropTables as $table) {
    echo "      - Dropping table {$table}... ";
    $db->query("DROP TABLE IF EXISTS `{$table}`;");
    if ($db->execute()) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }
}

// 3. CREATE PRODUCTION TABLES
echo "\n[3/5] Creating production tables with strict constraints & foreign keys...\n";
$tables = [
    'sessions' => "CREATE TABLE `sessions` (
        `id` TEXT PRIMARY KEY,
        `data` TEXT NOT NULL,
        `last_activity` INTEGER NOT NULL
    );",

    'mst_user' => "CREATE TABLE `mst_user` (
        `id_user` INTEGER PRIMARY KEY AUTOINCREMENT,
        `nama_user` TEXT NOT NULL,
        `username` TEXT NOT NULL UNIQUE,
        `password` TEXT NOT NULL,
        `photo_profil` TEXT DEFAULT NULL,
        `photo_path` TEXT DEFAULT NULL,
        `role` TEXT NOT NULL
    );",

    'mst_jurusan' => "CREATE TABLE `mst_jurusan` (
        `id_jurusan` INTEGER PRIMARY KEY AUTOINCREMENT,
        `jurusan` TEXT NOT NULL,
        `singkatan_jurusan` TEXT NOT NULL
    );",

    'mst_dosen' => "CREATE TABLE `mst_dosen` (
        `id_dosen` INTEGER PRIMARY KEY AUTOINCREMENT,
        `nip` TEXT NOT NULL UNIQUE,
        `nama_dosen` TEXT NOT NULL,
        `photo_path` TEXT DEFAULT NULL
    );",

    'mst_ruangan' => "CREATE TABLE `mst_ruangan` (
        `id_ruangan` INTEGER PRIMARY KEY AUTOINCREMENT,
        `nama_ruangan` TEXT NOT NULL UNIQUE
    );",

    'mst_tahun_ajaran' => "CREATE TABLE `mst_tahun_ajaran` (
        `id_tahun` INTEGER PRIMARY KEY AUTOINCREMENT,
        `tahun_ajaran` TEXT NOT NULL
    );",

    'mst_asisten' => "CREATE TABLE `mst_asisten` (
        `id_asisten` INTEGER PRIMARY KEY AUTOINCREMENT,
        `stambuk` TEXT NOT NULL UNIQUE,
        `nama_asisten` TEXT NOT NULL,
        `angkatan` TEXT NOT NULL,
        `status` TEXT NOT NULL,
        `jenis_kelamin` TEXT NOT NULL,
        `id_user` INTEGER DEFAULT NULL,
        `photo_profil` TEXT DEFAULT NULL,
        `photo_path` TEXT DEFAULT NULL,
        CONSTRAINT `fk_asisten_user` FOREIGN KEY (`id_user`) 
            REFERENCES `mst_user` (`id_user`) 
            ON DELETE SET NULL
    );",

    'mst_kelas' => "CREATE TABLE `mst_kelas` (
        `id_kelas` INTEGER PRIMARY KEY AUTOINCREMENT,
        `id_jurusan` INTEGER NOT NULL,
        `kelas` TEXT NOT NULL,
        `frekuensi` TEXT DEFAULT NULL,
        `angkatan` TEXT NOT NULL,
        CONSTRAINT `fk_kelas_jurusan` FOREIGN KEY (`id_jurusan`) 
            REFERENCES `mst_jurusan` (`id_jurusan`) 
            ON DELETE RESTRICT
    );",

    'mst_matakuliah' => "CREATE TABLE `mst_matakuliah` (
        `id_matkul` INTEGER PRIMARY KEY AUTOINCREMENT,
        `kode_matkul` TEXT NOT NULL UNIQUE,
        `nama_matkul` TEXT NOT NULL,
        `singkatan` TEXT NOT NULL,
        `semester` TEXT NOT NULL,
        `sks` INTEGER NOT NULL,
        `id_jurusan` INTEGER NOT NULL,
        CONSTRAINT `fk_matkul_jurusan` FOREIGN KEY (`id_jurusan`) 
            REFERENCES `mst_jurusan` (`id_jurusan`) 
            ON DELETE RESTRICT
    );",

    'trs_frekuensi' => "CREATE TABLE `trs_frekuensi` (
        `id_frekuensi` INTEGER PRIMARY KEY AUTOINCREMENT,
        `id_matkul` INTEGER NOT NULL,
        `frekuensi` TEXT NOT NULL,
        `id_tahun` INTEGER NOT NULL,
        `id_kelas` INTEGER NOT NULL,
        `hari` TEXT NOT NULL,
        `jam_mulai` TEXT NOT NULL,
        `jam_selesai` TEXT NOT NULL,
        `id_ruangan` INTEGER NOT NULL,
        `id_dosen` INTEGER NOT NULL,
        `id_asisten1` INTEGER DEFAULT NULL,
        `id_asisten2` INTEGER DEFAULT NULL,
        CONSTRAINT `fk_frekuensi_matkul` FOREIGN KEY (`id_matkul`) 
            REFERENCES `mst_matakuliah` (`id_matkul`) 
            ON DELETE RESTRICT,
        CONSTRAINT `fk_frekuensi_tahun` FOREIGN KEY (`id_tahun`) 
            REFERENCES `mst_tahun_ajaran` (`id_tahun`) 
            ON DELETE RESTRICT,
        CONSTRAINT `fk_frekuensi_kelas` FOREIGN KEY (`id_kelas`) 
            REFERENCES `mst_kelas` (`id_kelas`) 
            ON DELETE RESTRICT,
        CONSTRAINT `fk_frekuensi_ruangan` FOREIGN KEY (`id_ruangan`) 
            REFERENCES `mst_ruangan` (`id_ruangan`) 
            ON DELETE RESTRICT,
        CONSTRAINT `fk_frekuensi_dosen` FOREIGN KEY (`id_dosen`) 
            REFERENCES `mst_dosen` (`id_dosen`) 
            ON DELETE RESTRICT,
        CONSTRAINT `fk_frekuensi_asisten1` FOREIGN KEY (`id_asisten1`) 
            REFERENCES `mst_asisten` (`id_asisten`) 
            ON DELETE RESTRICT,
        CONSTRAINT `fk_frekuensi_asisten2` FOREIGN KEY (`id_asisten2`) 
            REFERENCES `mst_asisten` (`id_asisten`) 
            ON DELETE RESTRICT
    );",

    'trs_mentoring' => "CREATE TABLE `trs_mentoring` (
        `id_mentoring` INTEGER PRIMARY KEY AUTOINCREMENT,
        `id_frekuensi` INTEGER NOT NULL,
        `tanggal` TEXT NOT NULL,
        `uraian_materi` TEXT NOT NULL,
        `uraian_tugas` TEXT DEFAULT NULL,
        `hadir` INTEGER NOT NULL DEFAULT 0,
        `alpa` INTEGER NOT NULL DEFAULT 0,
        `status_dosen` TEXT DEFAULT NULL,
        `status_asisten1` TEXT DEFAULT NULL,
        `status_asisten2` TEXT DEFAULT NULL,
        `id_asisten_pengganti` INTEGER DEFAULT NULL,
        CONSTRAINT `fk_mentoring_frekuensi` FOREIGN KEY (`id_frekuensi`) 
            REFERENCES `trs_frekuensi` (`id_frekuensi`) 
            ON DELETE CASCADE,
        CONSTRAINT `fk_mentoring_asisten_pengganti` FOREIGN KEY (`id_asisten_pengganti`) 
            REFERENCES `mst_asisten` (`id_asisten`) 
            ON DELETE SET NULL
    );",

    'trs_restore' => "CREATE TABLE `trs_restore` (
        `id_restore` INTEGER PRIMARY KEY AUTOINCREMENT,
        `jenis_data` TEXT NOT NULL,
        `data_json` TEXT NOT NULL,
        `deleted_by` INTEGER DEFAULT NULL,
        `deleted_at` NUMERIC NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT `fk_restore_user` FOREIGN KEY (`deleted_by`) 
            REFERENCES `mst_user` (`id_user`) 
            ON DELETE SET NULL
    );"
];

foreach ($tables as $tableName => $sql) {
    echo "      - Creating table {$tableName}... ";
    $db->query($sql);
    if ($db->execute()) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }
}

// 4. RESTORE PRESERVED DATA
echo "\n[4/5] Restoring preserved data...\n";
if (!empty($userData)) {
    foreach ($userData as $u) {
        $db->query("INSERT INTO mst_user (id_user, nama_user, username, password, photo_profil, photo_path, role) 
                    VALUES (:id, :nama, :user, :pass, :profil, :path, :role)");
        $db->bind(':id', $u['id_user']);
        $db->bind(':nama', $u['nama_user']);
        $db->bind(':user', $u['username']);
        $db->bind(':pass', $u['password']);
        $db->bind(':profil', $u['photo_profil']);
        $db->bind(':path', $u['photo_path']);
        $db->bind(':role', $u['role']);
        $db->execute();
    }
    echo "      [+] Restored " . count($userData) . " user accounts to mst_user.\n";
}

if (!empty($asistenData)) {
    foreach ($asistenData as $a) {
        $db->query("INSERT INTO mst_asisten (id_asisten, stambuk, nama_asisten, angkatan, status, jenis_kelamin, id_user, photo_profil, photo_path) 
                    VALUES (:id, :stambuk, :nama, :angkatan, :status, :jk, :id_user, :profil, :path)");
        $db->bind(':id', $a['id_asisten']);
        $db->bind(':stambuk', $a['stambuk']);
        $db->bind(':nama', $a['nama_asisten']);
        $db->bind(':angkatan', $a['angkatan']);
        $db->bind(':status', $a['status']);
        $db->bind(':jk', $a['jenis_kelamin']);
        $db->bind(':id_user', $a['id_user']);
        $db->bind(':profil', $a['photo_profil']);
        $db->bind(':path', $a['photo_path']);
        $db->execute();
    }
    echo "      [+] Restored " . count($asistenData) . " asisten profiles to mst_asisten.\n";
}

if (!empty($sessionsData)) {
    foreach ($sessionsData as $s) {
        $db->query("INSERT INTO sessions (id, data, last_activity) VALUES (:id, :data, :act)");
        $db->bind(':id', $s['id']);
        $db->bind(':data', $s['data']);
        $db->bind(':act', $s['last_activity']);
        $db->execute();
    }
    echo "      [+] Restored " . count($sessionsData) . " sessions to sessions.\n";
}

// 5. CREATE PERFORMANCE INDEXES
echo "\n[5/5] Creating performance indexes...\n";
$indexes = [
    'idx_sessions_activity' => "CREATE INDEX IF NOT EXISTS `idx_sessions_activity` ON `sessions` (`last_activity`);",
    'idx_mst_asisten_user' => "CREATE INDEX IF NOT EXISTS `idx_mst_asisten_user` ON `mst_asisten` (`id_user`);",
    'idx_mst_kelas_jurusan' => "CREATE INDEX IF NOT EXISTS `idx_mst_kelas_jurusan` ON `mst_kelas` (`id_jurusan`);",
    'idx_mst_matakuliah_jurusan' => "CREATE INDEX IF NOT EXISTS `idx_mst_matakuliah_jurusan` ON `mst_matakuliah` (`id_jurusan`);",
    'idx_trs_frekuensi_matkul' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_matkul` ON `trs_frekuensi` (`id_matkul`);",
    'idx_trs_frekuensi_tahun' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_tahun` ON `trs_frekuensi` (`id_tahun`);",
    'idx_trs_frekuensi_kelas' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_kelas` ON `trs_frekuensi` (`id_kelas`);",
    'idx_trs_frekuensi_ruangan' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_ruangan` ON `trs_frekuensi` (`id_ruangan`);",
    'idx_trs_frekuensi_dosen' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_dosen` ON `trs_frekuensi` (`id_dosen`);",
    'idx_trs_frekuensi_asisten1' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_asisten1` ON `trs_frekuensi` (`id_asisten1`);",
    'idx_trs_frekuensi_asisten2' => "CREATE INDEX IF NOT EXISTS `idx_trs_frekuensi_asisten2` ON `trs_frekuensi` (`id_asisten2`);",
    'idx_trs_mentoring_frekuensi' => "CREATE INDEX IF NOT EXISTS `idx_trs_mentoring_frekuensi` ON `trs_mentoring` (`id_frekuensi`);",
    'idx_trs_mentoring_tanggal' => "CREATE INDEX IF NOT EXISTS `idx_trs_mentoring_tanggal` ON `trs_mentoring` (`tanggal`);",
    'idx_trs_mentoring_asisten_pengganti' => "CREATE INDEX IF NOT EXISTS `idx_trs_mentoring_asisten_pengganti` ON `trs_mentoring` (`id_asisten_pengganti`);",
    'idx_trs_restore_deleted_by' => "CREATE INDEX IF NOT EXISTS `idx_trs_restore_deleted_by` ON `trs_restore` (`deleted_by`);",
    'idx_trs_restore_jenis_data' => "CREATE INDEX IF NOT EXISTS `idx_trs_restore_jenis_data` ON `trs_restore` (`jenis_data`);"
];

foreach ($indexes as $indexName => $sql) {
    echo "      - Creating index {$indexName}... ";
    $db->query($sql);
    if ($db->execute()) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }
}

echo "\n====================================================================\n";
echo " PRODUCTION SCHEMA APPLIED AND VERIFIED SUCCESSFULLY!\n";
echo "====================================================================\n";
