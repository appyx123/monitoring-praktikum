# Sistem Monitoring Praktikum Laboratorium FIKOM UMI

#### Kelompok 4
#### - Zaki Falihin Ayyubi
#### - Muhammad Rafli
#### - Rizqi Ananda Jalil

## Deskripsi Aplikasi
Aplikasi berbasis web untuk memonitoring kegiatan praktikum, pengelolaan asisten, dosen, serta jadwal mentoring di laboratorium. Dibangun menggunakan **PHP Native** dengan konsep **MVC (Model-View-Controller)**.

## Fitur Website
**ADMIN:**
- Profil : Edit
- Dashboard : Read
- Monitoring : CRUD
- Laporan : Read
- Data Dosen : CRUD
- Data Asisten : CRUD
- Data User : CRUD (Terhubung ke Data Asisten dan Dosen)
- Data Matakuliah : CRUD
- Data Laboratorium : CRUD
- Data Kelas : CRUD
- Data Jurusan : CRUD
- Data Tahun Ajaran : CRUD

**ASISTEN:**
- Profil : Edit
- Dashboard : Read
- Monitoring : CRUD

## Struktur Direktori dan File Utama
```
monitoring-praktikum/
├── app/                  # Folder utama aplikasi (MVC)
│   ├── config/           # Konfigurasi aplikasi & database
│   ├── controllers/      # Logika aplikasi (Controller)
│   ├── core/             # Core MVC framework
│   ├── models/           # Interaksi dengan database (Model)
│   └── views/            # Tampilan antarmuka (View)
├── database/             # File terkait database & migrasi
│   └── migrations/
├── docs/                 # Dokumentasi proyek
│   ├── LAPORAN_AUDIT_DAN_PERBAIKAN.md
│   └── testing.md
├── public/               # Asset statis yang dapat diakses publik
│   ├── css/
│   ├── img/
│   ├── js/
│   └── template/
├── .htaccess             # Konfigurasi rewrite rule Apache
├── index.php             # Entry point utama aplikasi
└── README.md             # Informasi proyek
```

## Penjelasan Mengenai MVC
<img width="3999" height="1999" alt="image" src="https://github.com/user-attachments/assets/5f539f9e-14ff-4b3b-aac2-4e770fb1e54b" />

### 1. Model (Data)
- **Definisi**: Komponen yang berhubungan langsung dengan database.
- **Peran Utama**: Mengelola data (CRUD: Create, Read, Update, Delete). Model tidak peduli bagaimana data ditampilkan, ia hanya tahu cara mengambil, menyimpan, dan memproses data berdasarkan kueri SQL.
  
### 2. View (Tampilan)
- **Definisi**: Komponen yang berisi apa yang dilihat oleh pengguna (User Interface).
- **Peran Utama**: Menampilkan data yang dikirim oleh Model melalui Controller ke dalam format HTML/CSS. View tidak boleh memiliki logika yang berat; tugasnya hanya mencetak variabel data.
  
### 3. Controller (Otak)
- **Definisi**: Jembatan atau penghubung antara Model dan View.
- **Peran Utama**: Menerima permintaan (request) dari pengguna (misal: klik tombol Filter), meminta data ke Model, lalu mengirimkan hasilnya ke View untuk ditampilkan. Controller mengatur "lalu lintas" logika aplikasi.


## Role & Akses Login

Aplikasi ini memiliki 2 jenis user dengan hak akses berbeda:

| Role | Akses |
|------|-------|
| Admin | Full access (kelola semua data) |
| Asisten | Edit Profil & Input, Edit kegiatan monitoring |

**LINK WIREFRAME, UI/UX** : https://www.figma.com/design/4NRVbxZMbC3GH0vDGgKXw1/Tubes-Monitoring-Praktikum?node-id=0-1&p=f&t=wp3gnwP24i3hwVg0-0

**LINK FLOWCHART, USE CASE, ACTIVITY DIAGRAM, ERD** : https://app.diagrams.net/?src=about#G1STQ8m5rozE-dUxZWm64ATBabYVF9YJAM#%7B%22pageId%22%3A%22Sj3gRpXGsk_He88CJsHY%22%7D
