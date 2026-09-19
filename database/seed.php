<?php

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();

echo "====================================================\n";
echo " SEEDING USER ACCOUNTS (1 AKUN PER ROLE)\n";
echo "====================================================\n\n";

// 1. Akun Admin
$adminData = [
    'nama_user' => 'Administrator',
    'username'  => 'admin@gmail.com',
    'password'  => password_hash('admin123', PASSWORD_DEFAULT),
    'role'      => 'Admin'
];

// Cek apakah akun Admin sudah ada
$db->query("SELECT * FROM mst_user WHERE role = 'Admin' OR username = :username LIMIT 1");
$db->bind(':username', $adminData['username']);
$existingAdmin = $db->single();

if ($existingAdmin) {
    echo "[!] Akun Admin sudah ada:\n";
    echo "    - ID: {$existingAdmin['id_user']}\n";
    echo "    - Nama: {$existingAdmin['nama_user']}\n";
    echo "    - Username: {$existingAdmin['username']}\n";
    echo "    - Role: {$existingAdmin['role']}\n\n";
} else {
    $db->query("INSERT INTO mst_user (nama_user, username, password, role) VALUES (:nama_user, :username, :password, :role)");
    $db->bind(':nama_user', $adminData['nama_user']);
    $db->bind(':username', $adminData['username']);
    $db->bind(':password', $adminData['password']);
    $db->bind(':role', $adminData['role']);
    $db->execute();

    echo "[+] Berhasil membuat akun Admin:\n";
    echo "    - Nama: {$adminData['nama_user']}\n";
    echo "    - Username: {$adminData['username']}\n";
    echo "    - Password: admin123\n";
    echo "    - Role: Admin\n\n";
}

// 2. Akun Asisten
$asistenData = [
    'nama_user' => 'Asisten Laboratorium',
    'username'  => 'asisten@student.umi.ac.id',
    'password'  => password_hash('asisten123', PASSWORD_DEFAULT),
    'role'      => 'Asisten'
];

$db->query("SELECT * FROM mst_user WHERE role = 'Asisten' OR username = :username LIMIT 1");
$db->bind(':username', $asistenData['username']);
$existingAsisten = $db->single();

$asistenUserId = null;

if ($existingAsisten) {
    $asistenUserId = $existingAsisten['id_user'];
    echo "[!] Akun Asisten sudah ada:\n";
    echo "    - ID: {$existingAsisten['id_user']}\n";
    echo "    - Nama: {$existingAsisten['nama_user']}\n";
    echo "    - Username: {$existingAsisten['username']}\n";
    echo "    - Role: {$existingAsisten['role']}\n\n";
} else {
    $db->query("INSERT INTO mst_user (nama_user, username, password, role) VALUES (:nama_user, :username, :password, :role)");
    $db->bind(':nama_user', $asistenData['nama_user']);
    $db->bind(':username', $asistenData['username']);
    $db->bind(':password', $asistenData['password']);
    $db->bind(':role', $asistenData['role']);
    $db->execute();

    // Dapatkan id_user yang baru dibuat
    $db->query("SELECT id_user FROM mst_user WHERE username = :username");
    $db->bind(':username', $asistenData['username']);
    $newUser = $db->single();
    $asistenUserId = $newUser ? $newUser['id_user'] : null;

    echo "[+] Berhasil membuat akun Asisten:\n";
    echo "    - Nama: {$asistenData['nama_user']}\n";
    echo "    - Username: {$asistenData['username']}\n";
    echo "    - Password: asisten123\n";
    echo "    - Role: Asisten\n\n";
}

// Sinkronkan data profil Asisten di mst_asisten
if ($asistenUserId) {
    $db->query("SELECT * FROM mst_asisten WHERE id_user = :id_user LIMIT 1");
    $db->bind(':id_user', $asistenUserId);
    $existingMstAsisten = $db->single();

    if ($existingMstAsisten) {
        echo "[!] Data profil asisten di mst_asisten sudah terhubung (ID Asisten: {$existingMstAsisten['id_asisten']}).\n\n";
    } else {
        $db->query("INSERT INTO mst_asisten (stambuk, nama_asisten, angkatan, status, jenis_kelamin, id_user) 
                    VALUES (:stambuk, :nama_asisten, :angkatan, :status, :jenis_kelamin, :id_user)");
        $db->bind(':stambuk', '13020210001');
        $db->bind(':nama_asisten', $asistenData['nama_user']);
        $db->bind(':angkatan', '2021');
        $db->bind(':status', 'Asisten');
        $db->bind(':jenis_kelamin', 'Pria');
        $db->bind(':id_user', $asistenUserId);
        $db->execute();

        echo "[+] Berhasil menghubungkan data profil ke mst_asisten (Stambuk: 13020210001).\n\n";
    }
}

echo "====================================================\n";
echo " SEEDING SELESAI!\n";
echo " Akun Admin:\n";
echo "   Username: admin@gmail.com\n";
echo "   Password: admin123\n";
echo " Akun Asisten:\n";
echo "   Username: asisten@student.umi.ac.id\n";
echo "   Password: asisten123\n";
echo "====================================================\n";
