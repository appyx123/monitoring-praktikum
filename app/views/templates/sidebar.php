<?php
  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  $nama_user = isset($_SESSION['nama_user']) ? $_SESSION['nama_user'] : '';
  $current_page = $_SERVER['REQUEST_URI'];
?>

<aside class="main-sidebar sidebar-light-primary elevation-4">
    <div class="brand-link d-flex align-items-center" style="padding: 10px 14px; border-bottom: 1px solid rgba(0,0,0,0.1);">
      <img src="<?= BASEURL ?>/public/img/ICLabs.webp" alt="ICLabs" class="brand-image img-circle elevation-3" style="opacity: .9; width: 36px; height: 36px; object-fit: cover; flex-shrink: 0;">
      <div class="ml-2" style="line-height: 1.3; min-width: 0;">
        <span class="d-block font-weight-bold" style="font-size: 0.78rem; color: #333;">Monitoring Praktikum</span>
        <span class="d-block" style="font-size: 0.65rem; color: #888;">ICLabs - FIKOM UMI</span>
      </div>
    </div>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="info">
          <?php if ($_SESSION['role'] == 'Admin') : ?>
            <a href="<?= BASEURL ?>/user/profil" style="text-decoration:none;"><?= e($nama_user) ?></a>
          <?php elseif ($_SESSION['role'] == 'Asisten') : ?>
            <a href="<?= BASEURL ?>/asisten" style="text-decoration:none;"><?= e($nama_user) ?></a>
          <?php endif; ?>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <li class="nav-item">
            <a href="<?= BASEURL ?>/home" class="nav-link <?= strpos($current_page, '/home') !== false ? 'active' : '' ?>">
                <i class="nav-icon fas fa-home"></i>
                <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-header">MENU UTAMA</li>
          
          <li class="nav-item">
            <a href="<?= BASEURL ?>/frekuensi" class="nav-link <?= strpos($current_page, '/frekuensi') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-clipboard"></i>
              <p>Monitoring</p>
            </a>
          </li>

          <?php if ($_SESSION['role'] == 'Admin') : ?>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/laporan" class="nav-link <?= strpos($current_page, '/laporan') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-file-excel"></i>
              <p>Laporan</p>
            </a>
          </li>

          <li class="nav-header">DATA MASTER</li>            
          <li class="nav-item">
            <a href="<?= BASEURL ?>/dosen" class="nav-link <?= strpos($current_page, '/dosen') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-user"></i>
              <p>Data Dosen</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/asisten" class="nav-link <?= strpos($current_page, '/asisten') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>Data Asisten</p>
            </a>
          </li>
          <li class="nav-item">
              <a href="<?= BASEURL ?>/user" class="nav-link <?= (strpos($current_page, '/user') !== false && strpos($current_page, '/profil') === false) ? 'active' : '' ?>">
                  <i class="nav-icon fas fa-user-plus"></i>
                  <p>Data User</p>
              </a>
          </li>

          <li class="nav-header">MENU LAINNYA</li>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/matakuliah" class="nav-link <?= strpos($current_page, '/matakuliah') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-book"></i>
              <p>Matakuliah</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/ruangan" class="nav-link <?= strpos($current_page, '/ruangan') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-building"></i>
              <p>Laboratorium</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/kelas" class="nav-link <?= strpos($current_page, '/kelas') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-chalkboard-teacher"></i>
              <p>Kelas</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/jurusan" class="nav-link <?= strpos($current_page, '/jurusan') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-graduation-cap"></i>
              <p>Jurusan</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= BASEURL ?>/ajaran" class="nav-link <?= strpos($current_page, '/ajaran') !== false ? 'active' : '' ?>">
              <i class="nav-icon fas fa-calendar-alt"></i>
              <p>Tahun Ajaran</p>
            </a>
          </li>

          <li class="nav-header mt-3">UTILITY</li>
            <li class="nav-item">
              <a href="<?= BASEURL ?>/restore"
                class="nav-link <?= strpos($current_page, '/restore') !== false ? 'active' : '' ?>">
                <i class="nav-icon fas fa-undo"></i>
                <p>Restore Data</p>
              </a>
            </li>
        <?php endif; ?>
        </ul>
      </nav>
    </div>
</aside>