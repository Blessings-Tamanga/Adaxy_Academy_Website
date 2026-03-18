    </div><!-- main content -->
</div><!-- dashboard wrapper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Shared JS (sidebar toggle, etc.)
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => e.preventDefault());
    });
</script>
</body>
</html>
<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}
?>

