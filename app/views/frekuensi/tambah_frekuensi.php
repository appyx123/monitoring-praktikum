<form id="formTambahDataFrekuensi" action="<?= BASEURL ?>/Frekuensi/tambah" method="post" autocomplete="off">
    <div class="row">
        <div class="col-12">
            <div class="form-group mb-1">
                <label for="id_jurusan" class="form-label">Jurusan</label>
                <select id="id_jurusan" name="id_jurusan" class="form-control" required>
                    <option value="">Pilih Jurusan</option>
                    <?php foreach ($data['jurusanOptions'] as $jurusan) : ?>
                        <option value="<?= e($jurusan['id_jurusan']) ?>" data-singkatan="<?= e($jurusan['singkatan_jurusan']) ?>">
                            <?= e($jurusan['jurusan']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-1">
                <label for="id_matkul" class="form-label">Matakuliah</label>
                <select id="id_matkul" name="id_matkul" class="form-control" required>
                    <option value="">Pilih Matakuliah</option>
                    <?php foreach ($data['matakuliahOptions'] as $matkul) : ?>
                        <option value="<?= e($matkul['id_matkul']) ?>" data-singkatan="<?= e($matkul['singkatan']) ?>">
                            <?= e($matkul['nama_matkul']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Frekuensi Input -->
            <div class="form-group mb-3">
                <label for="frekuensi" class="form-label">Frekuensi</label>
                <input id="frekuensi" type="text" name="frekuensi" class="form-control" placeholder="Masukkan Frekuensi" required>
            </div>
            <div class="form-group mb-1">
                <label for="id_tahun" class="form-label">Tahun Ajaran</label>
                <select name="id_tahun" class="form-control" required>
                    <option value="">Pilih Tahun Ajaran</option>
                    <?php foreach ($data['ajaranOptions'] as $ajaran) : ?>
                        <option value="<?= e($ajaran['id_tahun']) ?>"><?= e($ajaran['tahun_ajaran']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-1">
                <label for="id_kelas" class="form-label">Kelas</label>
                <select name="id_kelas" class="form-control" required>
                    <option value="">Pilih Kelas</option>
                    <?php foreach ($data['frekuensiOptions'] as $kelas) : ?>
                        <option value="<?= e($kelas['id_kelas']) ?>"><?= e($kelas['kelas']) ?> - <?= e($kelas['angkatan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group mb-3 col-md-4">
                    <label for="hari" class="form-label">Jadwal</label>
                    <select name="hari" id="hari" class="form-control" required>
                        <option value="">Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="jam_mulai">Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-control">
                </div>
                <div class="form-group col-md-4">
                    <label for="jam_selesai">Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-control">
                </div>
            </div>
            <div class="form-group mb-1">
                <label for="id_ruangan" class="form-label">Laboratorium</label>
                <select name="id_ruangan" class="form-control" required>
                    <option value="">Pilih Laboratorium</option>
                    <?php foreach ($data['ruanganOptions'] as $dosen) : ?>
                        <option value="<?= e($dosen['id_ruangan']) ?>"><?= e($dosen['nama_ruangan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-1">
                <label for="id_dosen" class="form-label">Dosen</label>
                <select name="id_dosen" class="form-control" required>
                    <option value="">Pilih Nama Dosen</option>
                    <?php foreach ($data['dosenOptions'] as $dosen) : ?>
                        <option value="<?= e($dosen['id_dosen']) ?>"><?= e($dosen['nama_dosen']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-1">
                <label for="id_asisten1" class="form-label">Asisten 1</label>
                <select name="id_asisten1" class="form-control">
                    <option value="">Pilih Nama Asisten 1</option>
                    <?php foreach ($data['asistenOptions'] as $asisten) : ?>
                        <option value="<?= e($asisten['id_asisten']) ?>"><?= e($asisten['nama_asisten']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-1">
                <label for="id_asisten2" class="form-label">Asisten 2</label>
                <select name="id_asisten2" class="form-control">
                    <option value="">Pilih Nama Asisten 2</option>
                    <?php foreach ($data['asistenOptions'] as $asisten) : ?>
                        <option value="<?= e($asisten['id_asisten']) ?>"><?= e($asisten['nama_asisten']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <br>
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-primary">Tambah Data</button>
                <button type="button" class="btn btn-secondary ml-2" data-bs-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</form>
<script>
document.getElementById('id_jurusan').addEventListener('change', function() {
    updateFrekuensi();
});

document.getElementById('id_matkul').addEventListener('change', function() {
    updateFrekuensi();
});

function updateFrekuensi() {
    var jurusanSelect = document.getElementById('id_jurusan');
    var matkulSelect = document.getElementById('id_matkul');

    var jurusanOption = jurusanSelect.options[jurusanSelect.selectedIndex];
    var matkulOption = matkulSelect.options[matkulSelect.selectedIndex];

    var jurusanSingkatan = jurusanOption.getAttribute('data-singkatan') || '';
    var matkulSingkatan = matkulOption.getAttribute('data-singkatan') || '';

    console.log('Jurusan Singkatan:', jurusanSingkatan);
    console.log('Matkul Singkatan:', matkulSingkatan);

    if (jurusanSingkatan && matkulSingkatan) {
        var singkatan = jurusanSingkatan + '_' + matkulSingkatan;

        fetch('<?= BASEURL ?>/Frekuensi/getFrekuensiCount', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ singkatan: singkatan })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Frekuensi Count:', data);
            var count = data.count;
            document.getElementById('frekuensi').value = singkatan + '-' + (count + 1);
        })
        .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('frekuensi').value = '';
    }
}
</script>
