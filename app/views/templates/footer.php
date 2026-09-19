<div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div id="modal-size" class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"> 
        <h5 class="modal-title fs-5"></h5>
        <div data-bs-theme="dark">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <span class="tombol"></span>
        <span class="batal"></span>
      </div>
    </div>
  </div>
</div>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark"></aside>

<!-- Main Footer -->
<footer class="main-footer">
    <strong>Copyright &copy; 2024</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>ICLabs</b> Integrated Computer Laboratories
    </div>
</footer>

</div>
<!-- ./wrapper -->

<!-- 1. jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- 2. Bootstrap Bundle (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 3. AdminLTE Core Plugins -->
<script src="<?= BASEURL?>/public/template/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<script src="<?= BASEURL?>/public/template/dist/js/adminlte.js"></script>

<!-- 4. DataTables (Core only) -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- 5. Conditional Libraries (Loaded only when needed) -->
<?php if (!empty($data['use_export'])): ?>
  <script src="https://cdn.datatables.net/buttons/3.1.0/js/dataTables.buttons.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/3.1.0/js/buttons.html5.min.js"></script>
<?php endif; ?>

<?php if (!empty($data['use_chart'])): ?>
  <script src="<?= BASEURL?>/public/template/plugins/chart.js/Chart.min.js"></script>
<?php endif; ?>

<?php if (!empty($data['use_calendar']) || (isset($data['active_menu']) && $data['active_menu'] === 'home')): ?>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<?php endif; ?>

<!-- 6. Custom Global Scripts & CSRF Auto-Injection -->
<script>
  const BASEURL = "<?= BASEURL ?>";
  $(document).ready(function() {
      let csrf = $('meta[name="csrf-token"]').attr('content');
      if (csrf) {
          $.ajaxSetup({
              headers: { 'X-CSRF-TOKEN': csrf }
          });
          $(document).on('submit', 'form', function() {
              if (!$(this).find('input[name="csrf_token"]').length) {
                  $(this).append('<input type="hidden" name="csrf_token" value="' + csrf + '">');
              }
          });
      }
  });
</script>
<script src="<?= BASEURL?>/public/js/script.js"></script>

</body>
</html>