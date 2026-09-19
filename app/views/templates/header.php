<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
  <title><?= $data['title'] ?? 'Restore Data'; ?></title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="<?= BASEURL?>/public/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= BASEURL?>/public/template/plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= BASEURL?>/public/template/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= BASEURL?>/public/template/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <?php if (!empty($data['use_calendar']) || (isset($data['active_menu']) && $data['active_menu'] === 'home')) : ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css">
  <?php endif; ?>
  <!-- Custom Responsive Style (load last to override) -->
  <link rel="stylesheet" href="<?= BASEURL?>/public/css/style.css">
</head>