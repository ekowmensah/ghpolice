<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'GHPIMS' ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= url('/AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= url('/AdminLTE/dist/css/adminlte.min.css') ?>">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('/static/css/custom.css') ?>">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include __DIR__ . '/../partials/header.php'; ?>
    
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <?php include __DIR__ . '/../partials/breadcrumb.php'; ?>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <?php if (isset($_SESSION['flash'])): ?>
                    <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?= sanitize($message) ?>
                        </div>
                        <?php unset($_SESSION['flash'][$type]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </section>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>
</div>

<!-- jQuery -->
<script src="<?= url('/AdminLTE/plugins/jquery/jquery.min.js') ?>"></script>
<!-- Bootstrap 4 -->
<script src="<?= url('/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?= url('/AdminLTE/dist/js/adminlte.min.js') ?>"></script>
<!-- Custom JS -->
<script src="<?= url('/static/js/custom.js') ?>"></script>

<!-- Page-specific scripts -->
<?= $scripts ?? '' ?>

</body>
</html>
