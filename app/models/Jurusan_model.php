<?php
require_once __DIR__ . '/Restore_model.php';
class Jurusan_model{
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function tambah($data){
        $this->db->query("INSERT INTO mst_jurusan (jurusan, singkatan_jurusan) 
                        VALUES (:jurusan, :singkatan_jurusan)");
        $this->db->bind('jurusan', $data['jurusan']);
        $this->db->bind('singkatan_jurusan', $data['singkatan_jurusan']);

        $this->db->execute();

        return $this->db->rowCount();
    }

    public function prosesUbah($data){
        $this->db->query("UPDATE mst_jurusan 
                        SET 
                            jurusan = :jurusan, 
                            singkatan_jurusan = :singkatan_jurusan 
                        WHERE 
                            id_jurusan = :id_jurusan;");

        $this->db->bind('jurusan', $data['jurusan']);
        $this->db->bind('id_jurusan', $data['id_jurusan']);
        $this->db->bind('singkatan_jurusan', $data['singkatan_jurusan']);
    
        $this->db->execute();
    
        return $this->db->rowCount();
    }

    public function tampil(){
        $this->db->query("SELECT * FROM mst_jurusan ORDER BY id_jurusan ASC");
        return $this->db->resultSet();
    }

    public function ubah($id){
        $this->db->query("SELECT * FROM mst_jurusan WHERE id_jurusan = :id");
        $this->db->bind("id", $id);

        return $this->db->single(); 
    }

    public function prosesHapus($id){
        $jurusan = $this->ubah($id);
        if (!$jurusan) {
            return 0;
        }

        $this->db->beginTransaction();
        try {
            $deletedBy = $_SESSION['id_user'] ?? 0;
            $restoreModel = new Restore_model();
            $restoreModel->saveToRestore('mst_jurusan', $jurusan, $deletedBy);

            // 1. Hapus Mentoring terkait frekuensi di jurusan ini
            $this->db->query("DELETE FROM trs_mentoring WHERE id_frekuensi IN (SELECT id_frekuensi FROM trs_frekuensi WHERE id_matkul IN (SELECT id_matkul FROM mst_matakuliah WHERE id_jurusan = :id))");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // 2. Hapus Frekuensi di jurusan ini
            $this->db->query("DELETE FROM trs_frekuensi WHERE id_matkul IN (SELECT id_matkul FROM mst_matakuliah WHERE id_jurusan = :id)");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // 3. Hapus Kelas di jurusan ini
            $this->db->query("DELETE FROM mst_kelas WHERE id_jurusan = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // 4. Hapus Matakuliah di jurusan ini
            $this->db->query("DELETE FROM mst_matakuliah WHERE id_jurusan = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            // 5. Hapus Jurusan
            $this->db->query("DELETE FROM mst_jurusan WHERE id_jurusan = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();
            return 1;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Gagal menghapus jurusan ID $id: " . $e->getMessage());
            return 0;
        }
    }

    public function jumlahDataJurusan() {
        $this->db->query("SELECT COUNT(*) as jumlah FROM mst_jurusan");
        $result = $this->db->single();
        return $result['jumlah'];
    }
    
    public function getJurusanIdByID($jurusan) {
        $this->db->query("SELECT id_jurusan FROM mst_jurusan WHERE jurusan = :jurusan");
        $this->db->bind('jurusan', $jurusan);
        return $this->db->single();
    }
}