<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/core/Database.php';

$db = new Database();

$tables = [
    'mst_user' => "CREATE TABLE IF NOT EXISTS mst_user (
        id_user INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_user TEXT DEFAULT NULL,
        username TEXT UNIQUE DEFAULT NULL,
        password TEXT DEFAULT NULL,
        photo_profil TEXT DEFAULT NULL,
        photo_path TEXT DEFAULT NULL,
        role TEXT DEFAULT NULL
    );",

    'mst_asisten' => "CREATE TABLE IF NOT EXISTS mst_asisten (
        id_asisten INTEGER PRIMARY KEY AUTOINCREMENT,
        stambuk TEXT DEFAULT NULL,
        nama_asisten TEXT DEFAULT NULL,
        angkatan TEXT DEFAULT NULL,
        status TEXT DEFAULT NULL,
        jenis_kelamin TEXT DEFAULT NULL,
        id_user INTEGER DEFAULT NULL,
        photo_profil TEXT DEFAULT NULL,
        photo_path TEXT DEFAULT NULL
    );",

    'mst_dosen' => "CREATE TABLE IF NOT EXISTS mst_dosen (
        id_dosen INTEGER PRIMARY KEY AUTOINCREMENT,
        nip TEXT DEFAULT NULL,
        nama_dosen TEXT DEFAULT NULL,
        photo_path TEXT DEFAULT NULL
    );",

    'mst_jurusan' => "CREATE TABLE IF NOT EXISTS mst_jurusan (
        id_jurusan INTEGER PRIMARY KEY AUTOINCREMENT,
        jurusan TEXT DEFAULT NULL,
        singkatan_jurusan TEXT DEFAULT NULL
    );",

    'mst_kelas' => "CREATE TABLE IF NOT EXISTS mst_kelas (
        id_kelas INTEGER PRIMARY KEY AUTOINCREMENT,
        id_jurusan INTEGER DEFAULT NULL,
        kelas TEXT DEFAULT NULL,
        frekuensi TEXT DEFAULT NULL,
        angkatan TEXT NOT NULL
    );",

    'mst_matakuliah' => "CREATE TABLE IF NOT EXISTS mst_matakuliah (
        id_matkul INTEGER PRIMARY KEY AUTOINCREMENT,
        kode_matkul TEXT DEFAULT NULL,
        nama_matkul TEXT DEFAULT NULL,
        singkatan TEXT DEFAULT NULL,
        semester TEXT DEFAULT NULL,
        sks INTEGER DEFAULT NULL,
        id_jurusan INTEGER DEFAULT NULL
    );",

    'mst_ruangan' => "CREATE TABLE IF NOT EXISTS mst_ruangan (
        id_ruangan INTEGER PRIMARY KEY AUTOINCREMENT,
        nama_ruangan TEXT DEFAULT NULL
    );",

    'mst_tahun_ajaran' => "CREATE TABLE IF NOT EXISTS mst_tahun_ajaran (
        id_tahun INTEGER PRIMARY KEY AUTOINCREMENT,
        tahun_ajaran TEXT DEFAULT NULL
    );",

    'trs_frekuensi' => "CREATE TABLE IF NOT EXISTS trs_frekuensi (
        id_frekuensi INTEGER PRIMARY KEY AUTOINCREMENT,
        id_jurusan INTEGER DEFAULT NULL,
        id_matkul INTEGER DEFAULT NULL,
        frekuensi TEXT DEFAULT NULL,
        id_tahun INTEGER DEFAULT NULL,
        id_kelas INTEGER DEFAULT NULL,
        hari TEXT DEFAULT NULL,
        jam_mulai TEXT DEFAULT NULL,
        jam_selesai TEXT DEFAULT NULL,
        id_ruangan INTEGER DEFAULT NULL,
        id_dosen INTEGER DEFAULT NULL,
        id_asisten1 INTEGER DEFAULT NULL,
        id_asisten2 INTEGER DEFAULT NULL
    );",

    'trs_mentoring' => "CREATE TABLE IF NOT EXISTS trs_mentoring (
        id_mentoring INTEGER PRIMARY KEY AUTOINCREMENT,
        id_frekuensi INTEGER DEFAULT NULL,
        tanggal TEXT DEFAULT NULL,
        uraian_materi TEXT DEFAULT NULL,
        uraian_tugas TEXT DEFAULT NULL,
        hadir INTEGER DEFAULT NULL,
        alpa INTEGER DEFAULT NULL,
        status_dosen TEXT DEFAULT NULL,
        status_asisten1 TEXT DEFAULT NULL,
        status_asisten2 TEXT DEFAULT NULL,
        id_asisten_pengganti INTEGER DEFAULT NULL
    );",

    'trs_restore' => "CREATE TABLE IF NOT EXISTS trs_restore (
        id_restore INTEGER PRIMARY KEY AUTOINCREMENT,
        jenis_data TEXT DEFAULT NULL,
        data_json TEXT DEFAULT NULL,
        deleted_by INTEGER DEFAULT NULL,
        deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );",

    'restore' => "CREATE TABLE IF NOT EXISTS restore (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        jenis_data TEXT NOT NULL,
        data_json TEXT NOT NULL,
        deleted_by INTEGER DEFAULT NULL,
        deleted_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );"
];

echo "Migrating tables to Turso libSQL...\n";

foreach ($tables as $name => $sql) {
    echo "Creating table $name... ";
    $db->query($sql);
    if ($db->execute()) {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }
}

echo "Migration finished.\n";
