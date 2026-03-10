<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GHPIMS</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= url('AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?= url('AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= url('AdminLTE/dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="<?= url('/') ?>" class="h1"><b>GHP</b>IMS</a>
            <p class="text-muted">Ghana Police Integrated Management System</p>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Sign in to start your session</p>

            <?php if (isset($_SESSION['flash']['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= sanitize($_SESSION['flash']['error']) ?>
                    <?php unset($_SESSION['flash']['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash']['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= sanitize($_SESSION['flash']['success']) ?>
                    <?php unset($_SESSION['flash']['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    Your session has expired. Please login again.
                </div>
            <?php endif; ?>

            <form action="<?= url('/login') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="input-group mb-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" 
                           value="<?= sanitize(old('username')) ?>" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['errors']['username'])): ?>
                    <p class="text-danger small"><?= sanitize($_SESSION['errors']['username']) ?></p>
                <?php endif; ?>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['errors']['password'])): ?>
                    <p class="text-danger small"><?= sanitize($_SESSION['errors']['password']) ?></p>
                <?php endif; ?>

                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                    </div>
                </div>
            </form>

            <p class="mb-1">
                <a href="<?= url('/forgot-password') ?>">I forgot my password</a>
            </p>
        </div>
    </div>
</div>

<?php unset($_SESSION['errors']); unset($_SESSION['old']); ?>

<!-- jQuery -->
<script src="<?= url('AdminLTE/plugins/jquery/jquery.min.js') ?>"></script>
<!-- Bootstrap 4 -->
<script src="<?= url('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?= url('AdminLTE/dist/js/adminlte.min.js') ?>"></script>
</body>
</html>
