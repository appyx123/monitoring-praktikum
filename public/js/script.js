    function ubahdata(x){
        $('#modal-size').addClass('modal-lg');
        $('.modal-title').html('Ubah Data');
        let url = BASEURL + '/Asisten/ubahModal';
        $.post(url, {
          id : x
        }, function(data, success){
          $('.modal-body').html(data);
        });
        $('.tombol').html('<button type="submit" form="formUbahDataAsisten" class="btn btn-primary" style="background: #06253A; color: #FFFFFF;">Simpan Perubahan</button>');
        $('#close').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>');
    }
    function hapus(a){
      $('#modal-size').removeClass('modal-lg');
      $('.modal-title').html('Hapus Data');
      $('.modal-body').html('<p class="text-center">Apakah Anda yakin ingin menghapus data ini?</p>');       
      
      let csrfMeta = document.querySelector('meta[name="csrf-token"]');
      let csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
      let url = BASEURL + '/Asisten/hapus/' + a;
      
      $('.tombol').html(`
        <form action="${url}" method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="${csrf}">
            <button type="submit" class="btn btn-primary" style="background: #06253A; color: #FFFFFF;">Hapus</button>
        </form>
      `);
      $('#close').html('Batal');
    }

    // BAGIAN SIDEBAR
    $(".sidebar ul li").on('click', function () {
        $(".sidebar ul li.active").removeClass('active');
        $(this).addClass('active');
    });

    $('.open-btn').on('click', function () {
        $('.sidebar').addClass('active');

    });

    $('.close-btn').on('click', function () {
        $('.sidebar').removeClass('active');

    })

    //JavaScript Show / Hide Password
    $(document).on('click', '#togglePassword', function () {
        const passwordInput = $('#passwordInput');
        const icon = $(this).find('i');

        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    $(document).on('submit', '#formTambahDataAsisten', function (e) {
        const email = $('#usernameInput').val().trim();

        const allowedDomains = [
            '@iclabs@umi.ac.id',
            '@student.umi.ac.id',
            '@umi.ac.id',
            '@gmail.com'
        ];

        const valid = allowedDomains.some(domain => email.endsWith(domain));

        if (!valid) {
            e.preventDefault();
            alert(
                'Email tidak valid!\n\n' +
                'Gunakan domain berikut:\n' +
                '- iclabs@umi.ac.id\n' +
                '- student.umi.ac.id\n' +
                '- umi.ac.id\n' +
                '- gmail.com'
            );
            $('#usernameInput').focus().addClass('is-invalid');
            return false;
        }
    });



    document.addEventListener('DOMContentLoaded', function() {
        const tahunFilter = document.getElementById('tahunAjaranFilter');
        if (tahunFilter) {
            tahunFilter.addEventListener('change', function() {
                const tahunId = this.value;
                fetch(BASEURL + '/Frekuensi/filterAjax', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ id_tahun: tahunId })
                })
                .then(response => response.json())
                .then(data => {
                    updateFrekuensiTable(data);
                })
                .catch(error => {
                    alert('Gagal mengambil data!');
                });
            });
        }

    function updateFrekuensiTable(data) {
        const tbody = document.querySelector('#myTable tbody');
        if (!tbody) return;
        tbody.innerHTML = '';
        let no = 1;

        data.forEach(frekuensi => {
            const aksi = `
                <a class="btn btn-success btn-sm button-style text-center me-1 mb-1" 
                   onclick="change('Frekuensi', '${frekuensi.id_frekuensi}')" 
                   role="button" 
                   data-bs-toggle="modal" 
                   data-bs-target="#myModal"
                   title="Edit">
                    <i class="fa fa-edit"></i>
                </a>
                <a class="btn btn-danger btn-sm button-style text-center me-1 mb-1" 
                   onclick="deleteData('Frekuensi', '${frekuensi.id_frekuensi}')" 
                   role="button" 
                   data-bs-toggle="modal" 
                   data-bs-target="#myModal"
                   title="Hapus">
                    <i class="fa fa-trash"></i>
                </a>
                <a class="btn btn-info btn-sm button-style text-center me-1 mb-1" 
                   href="${BASEURL}/frekuensi/detail/${frekuensi.id_frekuensi}" 
                   role="button"
                   title="Detail">
                    <i class="fa fa-list"></i>
                </a>
            `;

            const jamMulai = frekuensi.jam_mulai ? frekuensi.jam_mulai.substring(0, 5) : '';
            const jamSelesai = frekuensi.jam_selesai ? frekuensi.jam_selesai.substring(0, 5) : '';

            const row = `<tr>
                <td class="text-center">${no++}</td>
                <td class="text-center">${frekuensi.frekuensi}</td>
                <td class="text-center">${frekuensi.kode_matkul}</td>
                <td>${frekuensi.nama_matkul}</td>
                <td class="text-center">${frekuensi.tahun_ajaran}</td>
                <td class="text-center">${frekuensi.kelas}</td>
                <td>${frekuensi.hari}/${jamMulai}-${jamSelesai}</td>
                <td>${frekuensi.nama_ruangan}</td>
                <td>${frekuensi.nama_dosen}</td>
                <td>${frekuensi.asisten_1 || '-'}</td>
                <td>${frekuensi.asisten_2 || '-'}</td>
                <td align="center" style="white-space: nowrap;">${aksi}</td>
            </tr>`;
            
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }
});

    $(document).ready(function() {
        // --- 1. INISIALISASI DATATABLES ---
        if ($('#example2').length) {
            $('#example2').DataTable();
        }

        if ($('#myTable').length) {
            $('#myTable').DataTable();
        }

        // Perbaiki bug DataTables di dalam Tab Bootstrap (Jadwal Keseluruhan)
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e){
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });

        // Buka tab secara otomatis jika ada hash di URL (misal: #jadwal-frekuensi2)
        let hash = window.location.hash;
        if (hash) {
            $('.nav-tabs a[href="' + hash + '"]').tab('show');
        }

        if ($('#example').length) {
            $('#example').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 
                    {
                        extend: 'pdfHtml5',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        text: 'PDF',
                        titleAttr: 'Export PDF',
                        customize: function (doc) {
                            let objLayout = {
                                hLineWidth: (i) => .5,
                                vLineWidth: (i) => .5,
                                hLineColor: (i) => '#000000',
                                vLineColor: (i) => '#000000',
                                paddingLeft: (i) => 4,
                                paddingRight: (i) => 4,
                                paddingTop: (i) => 4,
                                paddingBottom: (i) => 4,
                                fillColor: (i) => null
                            };
                            doc.content[1].layout = objLayout;
                        }
                    }, 
                    'print'
                ]
            });
        }

        // --- 2. LOGOUT HANDLER (Event Delegation) ---
        $(document).on('click', '#logoutLink', function(e) {
            e.preventDefault(); 
            $('.modal-title').html('Konfirmasi Keluar');
            $('.modal-body').html(`
                <div class="text-center mb-3">Apakah anda yakin ingin keluar?</div>
                <div class="text-center">
                    <a href="${BASEURL}/Login/logout" class="btn btn-primary">Keluar</a>
                    <button type="button" class="btn btn-secondary ml-2" data-bs-dismiss="modal">Batal</button>
                </div>
            `);
            $('#myModal').modal('show');
        });

        // // --- 3. DARK MODE TOGGLE ---
        // const toggleButton = $('#dark-mode-toggle');
        // const body = $('body');
        // const icon = toggleButton.find('i');

        // // Cek tema saat load
        // if (localStorage.getItem('theme') === 'dark') {
        //     body.addClass('dark-mode');
        //     icon.removeClass('fa-moon').addClass('fa-sun');
        //     $('.main-header').addClass('navbar-dark').removeClass('navbar-white navbar-light');
        // }

        // toggleButton.on('click', function(e) {
        //     e.preventDefault();
        //     body.toggleClass('dark-mode');
            
        //     const isDark = body.hasClass('dark-mode');
        //     localStorage.setItem('theme', isDark ? 'dark' : 'light');
            
        //     // Update Ikon & Navbar
        //     icon.toggleClass('fa-moon fa-sun');
        //     if (isDark) {
        //         $('.main-header').addClass('navbar-dark').removeClass('navbar-white navbar-light');
        //     } else {
        //         $('.main-header').addClass('navbar-white navbar-light').removeClass('navbar-dark');
        //     }
        // });

        // --- 4. FULL CALENDAR ---
        const calendarEl = document.getElementById('calendar-monitoring');
        if (calendarEl) {
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                height: 420,
                firstDay: 1,
                events: `${BASEURL}/dashboard/calendarAsisten`,

                headerToolbar: {
                    left: 'datepicker',
                    center: 'title',
                    right: 'today prev,next'
                },

                customButtons: {
                    datepicker: {
                        text: 'Pilih Tanggal',
                        click: function () {
                            document.getElementById('fc-date-picker').showPicker();
                        }
                    }
                },

                eventClick(info) {
                    if (!info.event.extendedProps.clickable) return;

                    const dateObj = info.event.start;
                    const hari = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                    const tanggal = dateObj.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });

                    $('#md-matkul').text(info.event.title);
                    $('#md-ruangan').text(info.event.extendedProps.ruangan);
                    $('#md-status').text(info.event.extendedProps.status);
                    $('#md-tanggal').text(`${hari}, ${tanggal}`);
                    $('#md-link').attr(
                        'href',
                        `${BASEURL}/frekuensi/detail/${info.event.extendedProps.id_frekuensi}`
                    );

                    $('#eventDetailModal').modal('show');
                }
            });

            calendar.render();

            const toolbar = calendarEl.querySelector('.fc-toolbar-chunk:first-child');
            const dateInput = document.createElement('input');
            dateInput.type = 'date';
            dateInput.id = 'fc-date-picker';
            dateInput.style.position = 'absolute';
            dateInput.style.opacity = '0';
            dateInput.style.pointerEvents = 'none';

            dateInput.addEventListener('change', function () {
                calendar.gotoDate(this.value);
            });
            toolbar.appendChild(dateInput);
        }
    });

// --- 5. FUNGSI GLOBAL (Bisa dipanggil dari atribut onclick di HTML) ---
    function add(jenis, id = null) {
        if (jenis === 'Asisten' || jenis === 'User') {
            $('#modal-size').addClass('modal-lg');
        } else {
            $('#modal-size').removeClass('modal-lg');
        }
        
        $('.modal-title').html('Tambah Data');
        let url = `${BASEURL}/${jenis}/modalTambah${id ? '/' + id : ''}`;

        $.get(url, function(data) {
            $('.modal-body').html(data);
            const formID = `#formTambahData${jenis}`;
            const formObj = $(formID);
            
            // Cerdas: Hanya tambahkan tombol jika form belum memiliki tombol submit
            if (formObj.length > 0 && formObj.find('button[type="submit"]').length === 0) {
                formObj.append(`
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary">Tambah</button>
                        <button type="button" class="btn btn-secondary ml-2" data-bs-dismiss="modal">Batal</button>
                    </div>
                `);
            }
            
            // clear footer
            $('.tombol').html('');
            $('#close').html('');
        }).fail(() => console.error("Gagal memuat modal tambah"));
    }

    function change(jenis, id) {
        if (jenis === 'Asisten' || jenis === 'User') {
            $('#modal-size').addClass('modal-lg');
        } else {
            $('#modal-size').removeClass('modal-lg');
        }
        
        $('.modal-title').html('Ubah Data');
        $.post(`${BASEURL}/${jenis}/ubahModal`, { id: id }, function(data) {
            $('.modal-body').html(data);
            
            // clear footer
            $('.tombol').html('');
            $('#close').html('');
        }).fail(() => console.error("Gagal memuat modal ubah"));
    }

    function deleteData(jenis, id) {
        let csrfMeta = document.querySelector('meta[name="csrf-token"]');
        let csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
        
        $('.modal-title').html('Hapus Data');
        $('.modal-body').html(`
        <div class="text-center mb-3">Hapus Data?</div>
        <div class="text-center">
            <form action="${BASEURL}/${jenis}/hapus/${id}" method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="${csrf}">
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
        `);
    }

    function hapusMentoring(idMentoring, idFrekuensi) {
        let csrfMeta = document.querySelector('meta[name="csrf-token"]');
        let csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';

        $('.modal-title').html('Hapus Data Mentoring');
        $('.modal-body').html(`
            <div class="text-center mb-3"><p>Hapus data mentoring ini?</p></div>
            <div class="text-center">
                <form action="${BASEURL}/Mentoring/prosesHapus/${idMentoring}/${idFrekuensi}" method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="${csrf}">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        `);
    }

    // --- 6. FITUR LAPORAN ---
    $(document).ready(function() {
        // Hanya jalankan jika elemen-elemen ini ada di halaman
        if ($('#tahun_filter').length && $('#laporan_body').length) {
            
            $('#tahun_filter').on('change', function() {
                const idTahun = $(this).val() || 'null';
                
                $('#laporan_body').html('<tr><td colspan="20" class="text-center">Sedang memuat data...</td></tr>');

                $.ajax({
                    url: BASEURL + '/Laporan/getTableByTahun/' + idTahun,
                    type: 'GET',
                    success: function(data) {
                        $('#laporan_body').html(data);
                    },
                    error: function() {
                        $('#laporan_body').html('<tr><td colspan="20" class="text-center text-danger">Gagal memuat data.</td></tr>');
                        alert('Gagal mengambil data laporan.');
                    }
                });
            });
        }

        if ($('#btn_export').length) {
            $('#btn_export').on('click', function() {
                const idTahun = $('#tahun_filter').val() || 'null';
                // Arahkan ke metode exportExcel dengan parameter tahun
                window.location.href = BASEURL + '/Laporan/exportExcel/' + idTahun;
            });
        }
    });
