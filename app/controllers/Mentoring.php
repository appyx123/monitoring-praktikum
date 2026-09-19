<?php

class Mentoring extends Controller {
    public function __construct() {
        parent::__construct();
        $this->isLogin();
    }

    public function index(){
        $data['title'] = 'Data Mentoring';
        $data['mentoring'] = $this->model('Mentoring_model')->tampil();
        
        $this->view('templates/header', $data);
        $this->view('templates/topbar');
        $this->view('templates/sidebar');
        $this->view('mentoring/index', $data);
        $this->view('templates/footer');
    }
    public function modalTambah($id_frekuensi){
        $data['dosenOptions'] = $this->model('Mentoring_model')->tampilDosen();
        $data['asistenOptions'] = $this->model('Mentoring_model')->tampilAsisten();
        $data['matakuliahOptions'] = $this->model('Mentoring_model')->tampilMatakuliah();
        $data['kelasOptions'] = $this->model('Mentoring_model')->tampilKelas();
        $data['ruanganOptions'] = $this->model('Mentoring_model')->tampilRuangan();
        $data['frekuensiOptions'] = $this->model('Frekuensi_model')->detailFrekuensi($id_frekuensi);

        $this->view('mentoring/tambah_mentoring', $data);
    }
    public function tambah(){
        $data['dosenOptions'] = $this->model('Mentoring_model')->tampilDosen();
        $data['asistenOptions'] = $this->model('Mentoring_model')->tampilAsisten();
        $data['matakuliahOptions'] = $this->model('Mentoring_model')->tampilMatakuliah();
        $data['kelasOptions'] = $this->model('Mentoring_model')->tampilKelas();
        $data['ruanganOptions'] = $this->model('Mentoring_model')->tampilRuangan();
        $data['frekuensiOptions'] = $this->model('Mentoring_model')->tampilFrekuensi();

        $id_frekuensi = $this->model('Mentoring_model')->tambah($_POST);

        if($id_frekuensi){
            Flasher::setFlash('Data berhasil ditambahkan', '', 'success');
            header('Location: '.BASEURL. '/frekuensi/detail/' . $id_frekuensi);
            exit;
        } else {
            Flasher::setFlash('Data gagal ditambahkan', '', 'danger');
            header('Location: '.BASEURL. '/frekuensi/detail/' . $id_frekuensi);
            exit;
        }
    }

    public function ubahModal(){
        $id = $_POST['id'];
        $data['dosenOptions'] = $this->model('Mentoring_model')->tampilDosen();
        $data['asistenOptions'] = $this->model('Mentoring_model')->tampilAsisten();
        $data['matakuliahOptions'] = $this->model('Mentoring_model')->tampilMatakuliah();
        $data['kelasOptions'] = $this->model('Mentoring_model')->tampilKelas();
        $data['ruanganOptions'] = $this->model('Mentoring_model')->tampilRuangan();
        $data['ubahdata'] = $this->model('Mentoring_model')->ubah($id);

        $this->view('mentoring/ubah_mentoring', $data);
    }

    public function prosesUbah(){
        // Cek apakah ada baris yang berubah
        if($this->model('Mentoring_model')->prosesUbah($_POST) > 0){
            Flasher::setFlash('berhasil diubah', '', 'success');
        } else {
            // Jika data sama persis (tidak ada yang diedit), tetap dianggap sukses
            Flasher::setFlash('berhasil diubah', '', 'success');
        }
        
        // AMBIL ID FREKUENSI DARI FORM UNTUK REDIRECT
        $id_frekuensi = $_POST['id_frekuensi'];
        
        // KEMBALIKAN KE HALAMAN DETAIL, BUKAN KE HALAMAN INDEX MENTORING
        header('Location: '.BASEURL. '/frekuensi/detail/' . $id_frekuensi);
        exit;
    }

    public function hapus($id){
        $this->verifyCsrfToken();
        if($this->model('Mentoring_model')->prosesHapus($id)){
            Flasher::setFlash(' berhasil dihapus', '', 'success');
        }else{
            Flasher::setFlash(' tidak berhasil dihapus', '', 'danger');
        }
        header('Location: '.BASEURL. '/mentoring');
        exit;
    }

    public function prosesHapus($id, $id_frekuensi){
        $this->verifyCsrfToken();
        if($this->model('Mentoring_model')->prosesHapus($id)){
            Flasher::setFlash('berhasil dihapus', '', 'success');
        } else {
            Flasher::setFlash('tidak berhasil dihapus', '', 'danger');
        }
        header('Location: '.BASEURL. '/frekuensi/detail/' . $id_frekuensi);
        exit;
    }

    // public function export_excel($id_frekuensi) {
    //     $data['detail'] = $this->model('Frekuensi_model')->getDetailFrekuensi($id_frekuensi);
    //     $data['mentoring'] = $this->model('Frekuensi_model')->getMentoringByFrekuensiId($id_frekuensi);

    //     $filename = "Monitoring_" . str_replace(' ', '_', $data['detail']['nama_matkul']) . ".xls";

    //     header("Content-Type: application/vnd.ms-excel");
    //     header("Content-Disposition: attachment; filename=\"$filename\"");
    //     header("Pragma: no-cache");
    //     header("Expires: 0");

    //     $this->view('mentoring/export_excel', $data);
    // }

    public function export_pdf($id_frekuensi) {
        $data['detail'] = $this->model('Frekuensi_model')->getDetailFrekuensi($id_frekuensi);
        $data['mentoring'] = $this->model('Frekuensi_model')->getMentoringByFrekuensiId($id_frekuensi);
        $data['title'] = 'Laporan Monitoring Praktikum';

        // Memanggil template standar aplikasi kamu
        $this->view('templates/header', $data);
        $this->view('mentoring/export_pdf', $data);
        $this->view('templates/footer', $data);
    }

    public function importExcel() {
        if (isset($_FILES['file_excel']['name']) && $_FILES['file_excel']['name'] != '') {
            if ($_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal mengupload file. Error kode: ' . $_FILES['file_excel']['error'], 'Error', 'danger');
                $id_frekuensi = isset($_POST['id_frekuensi']) ? $_POST['id_frekuensi'] : (isset($data['id_frekuensi']) ? $data['id_frekuensi'] : null);
                if ($id_frekuensi) {
                    header('Location: ' . BASEURL . '/frekuensi/detail/' . $id_frekuensi);
                } else {
                    header('Location: ' . BASEURL . '/frekuensi');
                }
                exit;
            }

            $allowed_ext = ['csv'];
            $ext = pathinfo($_FILES['file_excel']['name'], PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed_ext)) {
                
                ini_set('auto_detect_line_endings', TRUE);
                $file = fopen($_FILES['file_excel']['tmp_name'], 'r');
                $successCount = 0;
                $failCount = 0;
                $isFirstRow = true;
                
                while (($row = fgetcsv($file, 1000, ";")) !== FALSE) {
                    if ($isFirstRow) {
                        $isFirstRow = false;
                        continue; 
                    }
                    
                    $data = [
                        'id_frekuensi'        => trim(isset($row[0]) ? $row[0] : ''),
                        'tanggal'             => trim(isset($row[1]) ? $row[1] : ''),
                        'uraian_materi'       => trim(isset($row[2]) ? $row[2] : ''),
                        'uraian_tugas'        => trim(isset($row[3]) ? $row[3] : ''),
                        'hadir'               => trim(isset($row[4]) ? $row[4] : ''),
                        'alpa'                => trim(isset($row[5]) ? $row[5] : ''),
                        'status_dosen'        => trim(isset($row[6]) ? $row[6] : ''),
                        'status_asisten1'     => trim(isset($row[7]) ? $row[7] : ''),
                        'status_asisten2'     => trim(isset($row[8]) ? $row[8] : ''),
                        'id_asisten_pengganti'=> trim(isset($row[9]) ? $row[9] : '')
                    ];
                    
                    if (empty($data['id_frekuensi']) || empty($data['tanggal'])) {
                        $failCount++;
                        continue;
                    }
                    
                    $result = $this->model('Mentoring_model')->tambah($data);
                    if ($result) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                fclose($file);
                
                Flasher::setFlash("Import Selesai. $successCount sukses, $failCount gagal.", 'Info', 'info');
                
            } else {
                Flasher::setFlash('Format file harus .csv (Comma Delimited)', 'Error', 'danger');
            }
        } else {
            Flasher::setFlash('Pilih file terlebih dahulu!', 'Error', 'warning');
        }
        
        $id_frekuensi = isset($_POST['id_frekuensi']) ? $_POST['id_frekuensi'] : (isset($data['id_frekuensi']) ? $data['id_frekuensi'] : null);
        if ($id_frekuensi) {
            header('Location: ' . BASEURL . '/frekuensi/detail/' . $id_frekuensi);
        } else {
            header('Location: ' . BASEURL . '/frekuensi');
        }
        exit;
    }

    public function downloadTemplate() {
        $this->isAdmin();
        $filename = "Template_Monitoring_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        
        $file = fopen('php://output', 'w');
        fputcsv($file, ['ID Frekuensi', 'Tanggal (YYYY-MM-DD)', 'Uraian Materi', 'Uraian Tugas', 'Hadir', 'Alpa', 'Dosen (Hadir/kosong)', 'Asisten 1 (Hadir/kosong)', 'Asisten 2 (Hadir/kosong)', 'ID Asisten Pengganti'], ";");
        fputcsv($file, ['1', '2026-08-01', 'Pengenalan HTML', 'Tugas Form', '30', '2', 'Hadir', 'Hadir', 'Hadir', ''], ";");
        fclose($file);
        exit;
    }
}