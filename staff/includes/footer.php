<?php
// staff/includes/footer.php
?>
        </div> <!-- /.container-fluid -->
    </div> <!-- /#page-content-wrapper -->
</div> <!-- /#wrapper -->

<!-- Bootstrap Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (optional) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom Admin JS (can be shared) -->
<script src="<?php echo BASE_URL; ?>/admin/js/admin.js"></script>

<!-- Additional scripts can be injected here -->
<?php if (isset($extraScripts)) echo $extraScripts; ?>

</body>
</html>