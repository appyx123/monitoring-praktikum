<?php
require_once __DIR__ . '/Restore_model.php';

class Frekuensi_model{
    private $db;
    public function __construct(){
        $this->db = new Database;
    }
    
    public function tambah($data) {
        $query = "INSERT INTO trs_frekuensi
                    (frekuensi, id_matkul, id_tahun, id_kelas, 
                    hari, jam_mulai, jam_selesai, id_ruangan, 
                    id_dosen, id_asisten1, id_asisten2) 
                VALUES 
                    (:frekuensi, :id_matkul, :id_tahun, :id_kelas, 
                    :hari, :jam_mulai, :jam_selesai, :id_ruangan, 
                    :id_dosen, :id_asisten1, :id_asisten2)";
        
        $this->db->query($query);

        $this->db->bind('frekuensi', $data['frekuensi']);
        $this->db->bind('id_matkul', $data['id_matkul']);
        $this->db->bind('id_tahun', $data['id_tahun']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('jam_mulai', $data['jam_mulai']);
        $this->db->bind('jam_selesai', $data['jam_selesai']);
        $this->db->bind('id_ruangan', $data['id_ruangan']);
        $this->db->bind('id_dosen', $data['id_dosen']);
        $asisten1 = !empty($data['id_asisten1']) ? $data['id_asisten1'] : null;
        $asisten2 = !empty($data['id_asisten2']) ? $data['id_asisten2'] : null;
        
        $this->db->bind('id_asisten1', $asisten1);
        $this->db->bind('id_asisten2', $asisten2);

        try {
            $this->db->execute();
            return $this->db->rowCount();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function prosesUbah($data){   
        $this->db->query("UPDATE trs_frekuensi 
                            SET 
                              frekuensi = :frekuensi,
                              id_matkul = :id_matkul, 
                              id_tahun = :id_tahun, 
                              id_kelas = :id_kelas, 
                              hari = :hari, 
                              jam_mulai = :jam_mulai, 
                              jam_selesai = :jam_selesai, 
                              id_ruangan = :id_ruangan, 
                              id_dosen = :id_dosen, 
                              id_asisten1 = :id_asisten1, 
                              id_asisten2 = :id_asisten2
                            WHERE 
                              id_frekuensi = :id_frekuensi;");
    
        $this->db->bind('frekuensi', $data['frekuensi']);
        $this->db->bind('id_matkul', $data['id_matkul']);
        $this->db->bind('id_tahun', $data['id_tahun']);
        $this->db->bind('id_kelas', $data['id_kelas']);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('jam_mulai', $data['jam_mulai']);
        $this->db->bind('jam_selesai', $data['jam_selesai']);
        $this->db->bind('id_ruangan', $data['id_ruangan']);
        $this->db->bind('id_dosen', $data['id_dosen']);
        $this->db->bind('id_asisten1', $data['id_asisten1']);
        $this->db->bind('id_asisten2', $data['id_asisten2']);
        $this->db->bind('id_frekuensi', $data['id_frekuensi']);
        
        try {
            $this->db->execute();
            return $this->db->rowCount();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function tampil(){
        $this->db->query("SELECT
                            trs_frekuensi.id_frekuensi,
                            trs_frekuensi.frekuensi, 
                            -- mst_jurusan.jurusan, 
                            mst_matakuliah.kode_matkul, 
                            mst_matakuliah.nama_matkul,  
                            mst_tahun_ajaran.tahun_ajaran, 
                            mst_kelas.kelas, 
                            trs_frekuensi.hari, 
                            trs_frekuensi.jam_mulai, 
                            trs_frekuensi.jam_selesai, 
                            mst_ruangan.nama_ruangan, 
                            mst_dosen.nama_dosen, 
                            a1.nama_asisten AS asisten_1, 
                            a2.nama_asisten AS asisten_2
                        FROM
                            trs_frekuensi
                        JOIN
                            mst_dosen ON trs_frekuensi.id_dosen = mst_dosen.id_dosen
                        LEFT JOIN
                            mst_asisten a1 ON trs_frekuensi.id_asisten1 = a1.id_asisten
                        LEFT JOIN
                            mst_asisten a2 ON trs_frekuensi.id_asisten2 = a2.id_asisten
                        JOIN
                            mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul
                        JOIN
                            mst_jurusan ON mst_matakuliah.id_jurusan = mst_jurusan.id_jurusan
                        JOIN
                            mst_tahun_ajaran ON trs_frekuensi.id_tahun = mst_tahun_ajaran.id_tahun
                        JOIN
                            mst_kelas ON trs_frekuensi.id_kelas = mst_kelas.id_kelas
                        JOIN
                            mst_ruangan ON trs_frekuensi.id_ruangan = mst_ruangan.id_ruangan;");
        return $this->db->resultSet();
    }
    

    public function tampilBerdasarkanTahun($id_tahun) {
        $this->db->query("SELECT trs_frekuensi.*, mst_matakuliah.kode_matkul, mst_matakuliah.nama_matkul, 
                          mst_tahun_ajaran.tahun_ajaran, mst_kelas.kelas, mst_ruangan.nama_ruangan, 
                          mst_dosen.nama_dosen, a1.nama_asisten AS asisten_1, a2.nama_asisten AS asisten_2 
                          FROM trs_frekuensi 
                          JOIN mst_dosen ON trs_frekuensi.id_dosen = mst_dosen.id_dosen 
                          LEFT JOIN mst_asisten a1 ON trs_frekuensi.id_asisten1 = a1.id_asisten 
                          LEFT JOIN mst_asisten a2 ON trs_frekuensi.id_asisten2 = a2.id_asisten 
                          JOIN mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul 
                          JOIN mst_tahun_ajaran ON trs_frekuensi.id_tahun = mst_tahun_ajaran.id_tahun 
                          JOIN mst_kelas ON trs_frekuensi.id_kelas = mst_kelas.id_kelas 
                          JOIN mst_ruangan ON trs_frekuensi.id_ruangan = mst_ruangan.id_ruangan 
                          WHERE trs_frekuensi.id_tahun = :id_tahun");
        
        $this->db->bind('id_tahun', $id_tahun); 
        return $this->db->resultSet(); 
    }

        public function tampilDosen(){
        $this->db->query("SELECT id_dosen, nama_dosen FROM mst_dosen");

        return $this->db->resultSet();
    }

    public function tampilAsisten(){
        $this->db->query("SELECT id_asisten, nama_asisten FROM mst_asisten");

        return $this->db->resultSet();
    }

    public function tampilMatakuliah(){
        $this->db->query("SELECT id_matkul, nama_matkul, kode_matkul, semester, singkatan FROM mst_matakuliah");

        return $this->db->resultSet();
    }

    public function tampilFrekuensi(){
        $this->db->query("SELECT id_kelas, kelas, angkatan FROM mst_kelas");

        return $this->db->resultSet();
    }

    public function tampilJurusan(){
        $this->db->query("SELECT id_jurusan, jurusan, singkatan_jurusan FROM mst_jurusan");

        return $this->db->resultSet();
    }

    public function tampilAjaran(){
        $this->db->query("SELECT id_tahun, tahun_ajaran FROM mst_tahun_ajaran");

        return $this->db->resultSet();
    }

    public function tampilRuangan(){
        $this->db->query("SELECT id_ruangan, nama_ruangan FROM mst_ruangan");

        return $this->db->resultSet();
    }

    public function ubah($id){
        $this->db->query("SELECT trs_frekuensi.*, mst_matakuliah.id_jurusan 
                          FROM trs_frekuensi 
                          JOIN mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul 
                          WHERE trs_frekuensi.id_frekuensi = :id");
        $this->db->bind("id", $id);

        return $this->db->single(); 
    }

    public function prosesHapus($id){
        $frekuensi = $this->ubah($id);
        if (!$frekuensi) {
            return 0;
        }

        $this->db->beginTransaction();
        try {
            $deletedBy = $_SESSION['id_user'] ?? 0;
            $restoreModel = new Restore_model();
            $restoreModel->saveToRestore('trs_frekuensi', $frekuensi, $deletedBy);

            // Hapus data mentoring terkait
            $this->db->query("DELETE FROM trs_mentoring WHERE id_frekuensi = :id");
            $this->db->bind("id", $id);
            $this->db->execute();

            // Hapus frekuensi
            $this->db->query("DELETE FROM trs_frekuensi WHERE id_frekuensi = :id");
            $this->db->bind("id", $id);
            $this->db->execute();

            $this->db->commit();
            return 1;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Gagal menghapus frekuensi ID $id: " . $e->getMessage());
            return 0;
        }
    }

    public function detailFrekuensi($id) {
        $this->db->query("SELECT
                            trs_frekuensi.id_frekuensi,
                            trs_frekuensi.frekuensi,
                            mst_matakuliah.kode_matkul,
                            mst_matakuliah.nama_matkul,
                            mst_matakuliah.semester,
                            mst_tahun_ajaran.tahun_ajaran,
                            trs_frekuensi.hari,
                            trs_frekuensi.jam_mulai,
                            trs_frekuensi.jam_selesai,
                            mst_ruangan.nama_ruangan,
                            mst_dosen.nama_dosen,
                            mst_dosen.photo_path,
                            a1.nama_asisten AS asisten_1, 
                            a1.photo_path AS photo_path_asisten1,
                            a2.nama_asisten AS asisten_2,
                            a2.photo_path AS photo_path_asisten2
                          FROM trs_frekuensi
                          JOIN mst_tahun_ajaran ON trs_frekuensi.id_tahun = mst_tahun_ajaran.id_tahun
                          JOIN mst_dosen ON trs_frekuensi.id_dosen = mst_dosen.id_dosen
                          LEFT JOIN mst_asisten a1 ON trs_frekuensi.id_asisten1 = a1.id_asisten
                          LEFT JOIN mst_asisten a2 ON trs_frekuensi.id_asisten2 = a2.id_asisten
                          JOIN mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul
                          JOIN mst_ruangan ON trs_frekuensi.id_ruangan = mst_ruangan.id_ruangan
                          WHERE trs_frekuensi.id_frekuensi = :id");
        $this->db->bind("id", $id);
        return $this->db->single();
    }

    public function getMentoringByFrekuensiId($id_frekuensi) {
        $this->db->query("SELECT
                            trs_mentoring.id_mentoring,
                            trs_mentoring.tanggal,
                            trs_mentoring.uraian_materi,
                            trs_mentoring.uraian_tugas,
                            trs_mentoring.hadir,
                            trs_mentoring.alpa,
                            trs_mentoring.status_dosen,
                            trs_mentoring.status_asisten1,
                            trs_mentoring.status_asisten2,
                            IFNULL(a1.nama_asisten, '-') as nama_asisten_pengganti
                          FROM
                            trs_mentoring
                          LEFT JOIN
                            mst_asisten a1 ON trs_mentoring.id_asisten_pengganti = a1.id_asisten
                          WHERE
                            trs_mentoring.id_frekuensi = :id_frekuensi");
        $this->db->bind('id_frekuensi', $id_frekuensi);
        return $this->db->resultSet();
    }

    public function jumlahDataFrekuensi() {
        $this->db->query("SELECT COUNT(*) as jumlah FROM trs_frekuensi");
        $result = $this->db->single();
        return $result['jumlah'];
    }

    public function getFrekuensiCount($singkatan) {
        $this->db->query("SELECT COUNT(*) as count FROM trs_frekuensi WHERE frekuensi LIKE :singkatan");
        $this->db->bind('singkatan', $singkatan . '%');
        $result = $this->db->single();
        return $result['count'];
    }
    
    public function getAsistenIdByUserId($id_user) {
        $this->db->query("SELECT id_asisten FROM mst_asisten WHERE id_user = :id_user");
        $this->db->bind('id_user', $id_user);
        return $this->db->single();
    }
    
    public function getFrekuensiByAsistenId($id_asisten) {
        $this->db->query("SELECT
                            trs_frekuensi.id_frekuensi,
                            trs_frekuensi.frekuensi, 
                            mst_matakuliah.kode_matkul, 
                            mst_matakuliah.nama_matkul,  
                            mst_tahun_ajaran.tahun_ajaran, 
                            mst_kelas.kelas, 
                            trs_frekuensi.hari, 
                            trs_frekuensi.jam_mulai, 
                            trs_frekuensi.jam_selesai, 
                            mst_ruangan.nama_ruangan, 
                            mst_dosen.nama_dosen, 
                            a1.nama_asisten AS asisten_1, 
                            a2.nama_asisten AS asisten_2
                        FROM
                            trs_frekuensi
                        JOIN
                            mst_dosen ON trs_frekuensi.id_dosen = mst_dosen.id_dosen
                        LEFT JOIN
                            mst_asisten a1 ON trs_frekuensi.id_asisten1 = a1.id_asisten
                        LEFT JOIN
                            mst_asisten a2 ON trs_frekuensi.id_asisten2 = a2.id_asisten
                        JOIN
                            mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul
                        JOIN
                            mst_jurusan ON mst_matakuliah.id_jurusan = mst_jurusan.id_jurusan
                        JOIN
                            mst_tahun_ajaran ON trs_frekuensi.id_tahun = mst_tahun_ajaran.id_tahun
                        JOIN
                            mst_kelas ON trs_frekuensi.id_kelas = mst_kelas.id_kelas
                        JOIN
                            mst_ruangan ON trs_frekuensi.id_ruangan = mst_ruangan.id_ruangan
                        WHERE
                            trs_frekuensi.id_asisten1 = :id_asisten OR trs_frekuensi.id_asisten2 = :id_asisten");
        $this->db->bind('id_asisten', $id_asisten);
        // return $this->db->resultSet();
        $result = $this->db->resultSet();
        return $result;
    }

    public function getJadwalHariIniAsisten($id_asisten)
    {
        date_default_timezone_set('Asia/Jakarta');

        $hariIniIndex = date('N'); // 1–7
        $hariMap = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        $hariIni = $hariMap[$hariIniIndex];

        $this->db->query("
            SELECT
                f.id_frekuensi,
                f.frekuensi,
                f.jam_mulai,
                f.jam_selesai,
                mk.nama_matkul,
                r.nama_ruangan AS ruangan,
                k.kelas,
                MIN(m.tanggal) AS first_date
            FROM trs_frekuensi f
            JOIN mst_matakuliah mk ON f.id_matkul = mk.id_matkul
            JOIN mst_ruangan r ON f.id_ruangan = r.id_ruangan
            JOIN mst_kelas k ON f.id_kelas = k.id_kelas
            JOIN trs_mentoring m ON m.id_frekuensi = f.id_frekuensi
            WHERE
                (f.id_asisten1 = :id_asisten OR f.id_asisten2 = :id_asisten)
                AND f.hari = :hari_ini
            GROUP BY f.id_frekuensi
            HAVING
                CURDATE() BETWEEN
                    MIN(m.tanggal)
                    AND DATE_ADD(MIN(m.tanggal), INTERVAL 11 WEEK)
            ORDER BY f.jam_mulai ASC
        ");

        $this->db->bind('id_asisten', $id_asisten);
        $this->db->bind('hari_ini', $hariIni);

        return $this->db->resultSet();
    }


    public function getAllFrekuensi() {
        $this->db->query("SELECT
                            trs_frekuensi.id_frekuensi,
                            trs_frekuensi.frekuensi, 
                            mst_matakuliah.kode_matkul, 
                            mst_matakuliah.nama_matkul,  
                            mst_tahun_ajaran.tahun_ajaran, 
                            mst_kelas.kelas, 
                            trs_frekuensi.hari, 
                            trs_frekuensi.jam_mulai, 
                            trs_frekuensi.jam_selesai, 
                            mst_ruangan.nama_ruangan, 
                            mst_dosen.nama_dosen, 
                            a1.nama_asisten AS asisten_1, 
                            a2.nama_asisten AS asisten_2
                        FROM
                            trs_frekuensi
                        JOIN
                            mst_dosen ON trs_frekuensi.id_dosen = mst_dosen.id_dosen
                        LEFT JOIN
                            mst_asisten a1 ON trs_frekuensi.id_asisten1 = a1.id_asisten
                        LEFT JOIN
                            mst_asisten a2 ON trs_frekuensi.id_asisten2 = a2.id_asisten
                        JOIN
                            mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul
                        JOIN
                            mst_jurusan ON mst_matakuliah.id_jurusan = mst_jurusan.id_jurusan
                        JOIN
                            mst_tahun_ajaran ON trs_frekuensi.id_tahun = mst_tahun_ajaran.id_tahun
                        JOIN
                            mst_kelas ON trs_frekuensi.id_kelas = mst_kelas.id_kelas
                        JOIN
                            mst_ruangan ON trs_frekuensi.id_ruangan = mst_ruangan.id_ruangan");
        return $this->db->resultSet();
    }

    public function getFrekuensiByTahun($id_tahun) {
        $this->db->query("SELECT
                            trs_frekuensi.id_frekuensi,
                            trs_frekuensi.frekuensi, 
                            mst_matakuliah.kode_matkul, 
                            mst_matakuliah.nama_matkul,  
                            mst_tahun_ajaran.tahun_ajaran, 
                            mst_kelas.kelas, 
                            trs_frekuensi.hari, 
                            trs_frekuensi.jam_mulai, 
                            trs_frekuensi.jam_selesai, 
                            mst_ruangan.nama_ruangan, 
                            mst_dosen.nama_dosen, 
                            a1.nama_asisten AS asisten_1, 
                            a2.nama_asisten AS asisten_2
                        FROM
                            trs_frekuensi
                        JOIN
                            mst_dosen ON trs_frekuensi.id_dosen = mst_dosen.id_dosen
                        LEFT JOIN
                            mst_asisten a1 ON trs_frekuensi.id_asisten1 = a1.id_asisten
                        LEFT JOIN
                            mst_asisten a2 ON trs_frekuensi.id_asisten2 = a2.id_asisten
                        JOIN
                            mst_matakuliah ON trs_frekuensi.id_matkul = mst_matakuliah.id_matkul
                        JOIN
                            mst_jurusan ON mst_matakuliah.id_jurusan = mst_jurusan.id_jurusan
                        JOIN
                            mst_tahun_ajaran ON trs_frekuensi.id_tahun = mst_tahun_ajaran.id_tahun
                        JOIN
                            mst_kelas ON trs_frekuensi.id_kelas = mst_kelas.id_kelas
                        JOIN
                            mst_ruangan ON trs_frekuensi.id_ruangan = mst_ruangan.id_ruangan
                        WHERE trs_frekuensi.id_tahun = :id_tahun");
        $this->db->bind('id_tahun', $id_tahun);
        return $this->db->resultSet();
    }

    public function getDetailFrekuensi($id) {
        $this->db->query("SELECT f.*, m.nama_matkul, m.kode_matkul, r.nama_ruangan, 
                                d.nama_dosen, d.photo_path,
                                a1.nama_asisten as asisten1, a1.photo_path as photo_path_asisten1,
                                a2.nama_asisten as asisten2, a2.photo_path as photo_path_asisten2,
                                t.tahun_ajaran
                        FROM trs_frekuensi f
                        JOIN mst_matakuliah m ON f.id_matkul = m.id_matkul
                        JOIN mst_ruangan r ON f.id_ruangan = r.id_ruangan
                        JOIN mst_dosen d ON f.id_dosen = d.id_dosen
                        LEFT JOIN mst_asisten a1 ON f.id_asisten1 = a1.id_asisten
                        LEFT JOIN mst_asisten a2 ON f.id_asisten2 = a2.id_asisten
                        JOIN mst_tahun_ajaran t ON f.id_tahun = t.id_tahun
                        WHERE f.id_frekuensi = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }
}
