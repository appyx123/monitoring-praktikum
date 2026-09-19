<?php

class Laporan_model {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getRekapKehadiran($id_tahun = null) {
        $query = "SELECT f.*, m.nama_matkul, t.tahun_ajaran, k.kelas, r.nama_ruangan, 
                         j.jurusan as prodi, d.nama_dosen, 
                         a1.nama_asisten as asisten1, a2.nama_asisten as asisten2,
                         
                         (SELECT COUNT(*) FROM trs_mentoring WHERE id_frekuensi = f.id_frekuensi) as total_pertemuan,
                         
                         (SELECT COUNT(*) FROM trs_mentoring WHERE id_frekuensi = f.id_frekuensi AND status_dosen = 'Hadir') as hadir_dosen,
                         
                         (SELECT COUNT(*) FROM trs_mentoring WHERE id_frekuensi = f.id_frekuensi AND status_asisten1 = 'Hadir') as hadir_asisten1,
                         
                         (SELECT COUNT(*) FROM trs_mentoring WHERE id_frekuensi = f.id_frekuensi AND status_asisten2 = 'Hadir') as hadir_asisten2

                  FROM trs_frekuensi f
                  JOIN mst_matakuliah m ON f.id_matkul = m.id_matkul
                  JOIN mst_tahun_ajaran t ON f.id_tahun = t.id_tahun
                  JOIN mst_kelas k ON f.id_kelas = k.id_kelas
                  JOIN mst_ruangan r ON f.id_ruangan = r.id_ruangan
                  JOIN mst_jurusan j ON m.id_jurusan = j.id_jurusan
                  JOIN mst_dosen d ON f.id_dosen = d.id_dosen
                  LEFT JOIN mst_asisten a1 ON f.id_asisten1 = a1.id_asisten
                  LEFT JOIN mst_asisten a2 ON f.id_asisten2 = a2.id_asisten";

        if ($id_tahun) {
            $query .= " WHERE f.id_tahun = :id_tahun";
        }

        $this->db->query($query);
        
        if ($id_tahun) {
            $this->db->bind('id_tahun', $id_tahun);
        }

        return $this->db->resultSet();
    }
}  