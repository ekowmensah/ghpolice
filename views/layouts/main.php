<?php
require_once __DIR__ . '/../../app/Helpers/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'GHPIMS' ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= url('/AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= url('/AdminLTE/dist/css/adminlte.min.css') ?>">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('/static/css/custom.css') ?>">
    <style>
        :root {
            --gp-navy: #112c4d;
            --gp-navy-2: #1a406d;
            --gp-gold: #c7a13f;
            --gp-red: #d94a3a;
            --gp-green: #1f7a3d;
            --gp-page: #eef3f9;
            --gp-card: #ffffff;
            --gp-text: #1c2630;
            --gp-muted: #607086;
            --gp-border: #d6dfeb;
        }

        body {
            font-family: "Barlow Semi Condensed", "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #f7f9fc 0%, var(--gp-page) 100%);
            color: var(--gp-text);
        }

        .content-wrapper {
            background: transparent;
        }

        .content-header h1,
        h1, h2, h3, h4, h5 {
            color: #827c7cff;
           
        }

        .card {
            border: 1px solid var(--gp-border);
            box-shadow: 0 10px 24px rgba(16, 40, 70, 0.08);
            border-radius: 12px;
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #e8edf5;
            border-radius: 12px 12px 0 0;
        }

        .main-footer.gp-footer {
            background: #fff;
            border-top: 1px solid #dce5f0;
            color: #586a7f;
        }

        .alert {
            border-radius: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
            border: 0;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(135deg, #16355d, #204c7d);
        }

        .text-muted {
            color: var(--gp-muted) !important;
        }
    </style>
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

                <!-- CSRF Token for AJAX calls -->
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

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
