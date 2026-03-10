<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - GHPIMS</title>
    <link rel="stylesheet" href="<?= url('/AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/AdminLTE/dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <b>GHPIMS</b>
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Enter your email to reset your password</p>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= sanitize($_SESSION['flash']['message']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form action="<?= url('/forgot-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="input-group mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['errors']['email'])): ?>
                    <small class="text-danger"><?= sanitize($_SESSION['errors']['email']) ?></small>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                    </div>
                </div>
            </form>

            <p class="mt-3 mb-1">
                <a href="<?= url('/login') ?>">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<script src="<?= url('/AdminLTE/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= url('/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= url('/AdminLTE/dist/js/adminlte.min.js') ?>"></script>
</body>
</html>
<?php unset($_SESSION['errors']); ?>
