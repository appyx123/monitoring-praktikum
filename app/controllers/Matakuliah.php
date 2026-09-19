<?php

class Matakuliah extends Controller {
    public function index(){
        $this->isAdmin();
        $data['title'] = 'Data Matakuliah';
        $data['matakuliah'] = $this->model('Matakuliah_model')->tampil();
        
        $this->view('templates/header', $data);
        $this->view('templates/topbar');
        $this->view('templates/sidebar');
        $this->view('matakuliah/index', $data);
        $this->view('templates/footer');
    }

    public function modalTambah(){
        $this->isAdmin();
        $data['jurusanOptions'] = $this->model('Matakuliah_model')->tampilJurusan();
        $this->view('matakuliah/tambah_matakuliah', $data);
    }

    // PENAMBAHAN FLASHER (rafli)

    public function tambah(){
        $this->isAdmin();
        $data['jurusanOptions'] = $this->model('Matakuliah_model')->tampilJurusan();
    
        $result = $this->model('Matakuliah_model')->tambah($_POST);
    
        if($result > 0){
            Flasher::setFlash('Matakuliah berhasil ditambahkan', '', 'success');
        } elseif($result == -2) {
            Flasher::setFlash('Kode matakuliah sudah ada! Gunakan kode lain', '', 'danger');
        } else {
            Flasher::setFlash('Matakuliah tidak berhasil ditambahkan', '', 'danger');
        }
    
        header('Location: '.BASEURL. '/matakuliah');
        exit;
    }

    public function ubahModal(){
        $this->isAdmin();
        $data['jurusanOptions'] = $this->model('Matakuliah_model')->tampilJurusan();
        $id = $_POST['id'];
        $data['ubahdata'] = $this->model('Matakuliah_model')->ubah($id);

        $this->view('matakuliah/ubah_matakuliah', $data);
    }

    public function prosesUbah(){
        $this->isAdmin();
        if($this->model('Matakuliah_model')->prosesUbah($_POST) > 0){
            Flasher::setFlash(' berhasil diubah', '', 'success');
        }else{
            Flasher::setFlash(' tidak berhasil diubah', '', 'danger');
        }
        header('Location: '.BASEURL. '/matakuliah');
        exit;
    }

    public function hapus($id){
        $this->verifyCsrfToken();
        $this->isAdmin();
        if($this->model('Matakuliah_model')->prosesHapus($id)){
            Flasher::setFlash(' berhasil dihapus', '', 'success');
        }else{
            Flasher::setFlash(' tidak berhasil dihapus', '', 'danger');
        }
        header('Location: '.BASEURL. '/matakuliah');
        exit;
    }
    public function importExcel() {
        $this->isAdmin();
        
        if (isset($_FILES['file_excel']['name']) && $_FILES['file_excel']['name'] != '') {
            if ($_FILES['file_excel']['error'] !== UPLOAD_ERR_OK) {
                Flasher::setFlash('Gagal mengupload file. Error kode: ' . $_FILES['file_excel']['error'], 'Error', 'danger');
                header('Location: ' . BASEURL . '/matakuliah');
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
                        'kode_matkul' => trim(isset($row[0]) ? $row[0] : ''),
                        'nama_matkul' => trim(isset($row[1]) ? $row[1] : ''),
                        'singkatan'   => trim(isset($row[2]) ? $row[2] : ''),
                        'id_jurusan'  => trim(isset($row[3]) ? $row[3] : ''),
                        'semester'    => trim(isset($row[4]) ? $row[4] : ''),
                        'sks'         => trim(isset($row[5]) ? $row[5] : '')
                    ];
                    
                    if (empty($data['kode_matkul']) || empty($data['nama_matkul'])) {
                        $failCount++;
                        continue;
                    }
                    
                    $result = $this->model('Matakuliah_model')->tambah($data);
                    if ($result > 0) {
                        $successCount++;
                    } else {
                        $failCount++;
                    }
                }
                fclose($file);
                
                Flasher::setFlash("Import Selesai. $successCount sukses, $failCount gagal/duplikat.", 'Info', 'info');
            } else {
                Flasher::setFlash('Format file harus .csv (Comma Delimited)', 'Error', 'danger');
            }
        } else {
            Flasher::setFlash('Pilih file terlebih dahulu!', 'Error', 'warning');
        }
        
        header('Location: ' . BASEURL . '/matakuliah');
        exit;
    }

    public function downloadTemplate() {
        $this->isAdmin();
        $filename = "Template_Matakuliah_" . date('Ymd') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Kode Matkul', 'Matakuliah (Nama Matkul)', 'Singkatan', 'ID Jurusan', 'Semester (GANJIL / GENAP)', 'SKS'], ";");
        fputcsv($file, ['MK001', 'Pemrograman Web', 'PW', '1', 'GANJIL', '3'], ";");
        fclose($file);
        exit;
    }

    public function getMatakuliahByJurusan(){
        $id_jurusan = $_POST['id_jurusan'];
        $data['matakuliahOptions'] = $this->model('Matakuliah_model')->getMatakuliahByJurusan($id_jurusan);
        echo json_encode($data['matakuliahOptions']);
    }    
}
