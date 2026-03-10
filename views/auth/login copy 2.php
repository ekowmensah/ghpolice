<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GHPIMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= url('AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="<?= url('AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= url('AdminLTE/dist/css/adminlte.min.css') ?>">
    <style>
        :root {
            --gp-navy: #0f2746;
            --gp-navy-soft: #15345e;
            --gp-gold: #c8a24a;
            --gp-red: #d94a3a;
            --gp-green: #1f7a3d;
            --gp-ink: #1c2630;
            --gp-mist: #f4f6f9;
        }

        body.gp-login-page {
            margin: 0;
            min-height: 100vh;
            font-family: "Barlow Semi Condensed", "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at 12% 10%, rgba(201, 162, 74, 0.2), transparent 42%),
                radial-gradient(circle at 85% 15%, rgba(217, 74, 58, 0.12), transparent 35%),
                linear-gradient(160deg, #f5f7fb 0%, #e8eef7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: var(--gp-ink);
        }

        .gp-shell {
            width: 100%;
            max-width: 1120px;
            min-height: 640px;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            border-radius: 18px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 24px 60px rgba(12, 29, 54, 0.2);
        }

        .gp-brand {
            position: relative;
            padding: 36px 36px 28px;
            color: #fff;
            background:
                linear-gradient(0deg, rgba(10, 26, 48, 0.92), rgba(10, 26, 48, 0.92)),
                url("<?= url('/static/img/ghana-police-officer.jpg') ?>") center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .gp-flag-stripe {
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--gp-red) 0 33.33%, #f4cc3a 33.33% 66.66%, var(--gp-green) 66.66% 100%);
            margin-bottom: 20px;
        }

        .gp-head {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .gp-head img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 50%;
            background: #fff;
            border: 2px solid rgba(255, 255, 255, 0.35);
            padding: 4px;
        }

        .gp-head h1 {
            margin: 0;
            font-family: "Merriweather", serif;
            font-size: 1.35rem;
            line-height: 1.2;
            letter-spacing: 0.2px;
        }

        .gp-head p {
            margin: 3px 0 0;
            color: rgba(255, 255, 255, 0.84);
            font-size: 0.94rem;
            letter-spacing: 0.5px;
        }

        .gp-copy h2 {
            margin: 0 0 10px;
            font-size: 2rem;
            line-height: 1.1;
            font-weight: 700;
        }

        .gp-copy p {
            margin: 0;
            max-width: 500px;
            color: rgba(255, 255, 255, 0.84);
            font-size: 1rem;
            line-height: 1.5;
        }

        .gp-meta {
            margin-top: 20px;
            display: grid;
            gap: 9px;
            font-size: 0.95rem;
        }

        .gp-meta div {
            display: flex;
            gap: 10px;
            align-items: center;
            color: rgba(255, 255, 255, 0.93);
        }

        .gp-meta i {
            color: var(--gp-gold);
            width: 18px;
            text-align: center;
        }

        .gp-flag {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 0.83rem;
            letter-spacing: 0.55px;
            text-transform: uppercase;
        }

        .gp-flag img {
            width: 24px;
            height: 16px;
            object-fit: cover;
            border-radius: 2px;
        }

        .gp-form-wrap {
            padding: 36px 34px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gp-form {
            width: 100%;
            max-width: 390px;
        }

        .gp-form-title {
            margin-bottom: 16px;
        }

        .gp-form-title h3 {
            margin: 0 0 4px;
            font-size: 1.62rem;
            font-weight: 700;
            color: var(--gp-navy);
        }

        .gp-form-title p {
            margin: 0;
            color: #6a7686;
            font-size: 0.95rem;
        }

        .gp-input .form-control {
            height: 46px;
            border-radius: 10px;
            border-color: #d3dce8;
            font-size: 1rem;
        }

        .gp-input .form-control:focus {
            border-color: #7f9ac1;
            box-shadow: 0 0 0 0.15rem rgba(63, 103, 145, 0.2);
        }

        .gp-input .input-group-text {
            border-radius: 0 10px 10px 0;
            border-color: #d3dce8;
            background: #f6f9fe;
            color: #49638c;
            width: 44px;
            justify-content: center;
        }

        .gp-btn {
            height: 44px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--gp-navy) 0%, var(--gp-navy-soft) 100%);
            font-weight: 600;
            letter-spacing: 0.4px;
        }

        .gp-btn:hover {
            background: linear-gradient(135deg, #12305a 0%, #1a4478 100%);
        }

        .gp-foot {
            margin-top: 18px;
            text-align: center;
            font-size: 0.86rem;
            color: #7b8798;
        }

        .gp-foot a {
            color: #2f5588;
            font-weight: 600;
        }

        @media (max-width: 991px) {
            .gp-shell {
                grid-template-columns: 1fr;
                max-width: 520px;
            }

            .gp-brand {
                min-height: 320px;
            }
        }
    </style>
</head>
<body class="hold-transition gp-login-page">
<div class="gp-shell">
    <section class="gp-brand">
        <div>
            <div class="gp-flag-stripe"></div>
            <div class="gp-head">
                <img src="<?= url('/static/img/ghana-police-logo.jpg') ?>" alt="Ghana Police Service Emblem">
                <div>
                    <h1>Ghana Police Service</h1>
                    <p>Integrated Operations Platform</p>
                </div>
            </div>
            <div class="gp-copy">
                <h2>GHPIMS Secure Access</h2>
                <p>
                    Centralized digital operations for investigation, records, evidence, and intelligence workflows
                    across the Ghana Police Service.
                </p>
            </div>
        </div>

        <div>
            <div class="gp-meta">
                <div><i class="fas fa-shield-alt"></i> Protected operational records</div>
                <div><i class="fas fa-user-shield"></i> Role-based access and audit trails</div>
                <div><i class="fas fa-network-wired"></i> National, regional, division, and district coordination</div>
            </div>
            <div class="gp-flag">
                <img src="<?= url('/static/img/ghana-flag.svg') ?>" alt="Flag of Ghana">
                Republic of Ghana
            </div>
        </div>
    </section>

    <section class="gp-form-wrap">
        <div class="gp-form">
            <div class="gp-form-title">
                <h3>Officer Login</h3>
                <p>Sign in with your authorized service credentials.</p>
            </div>

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

                <div class="input-group mb-2 gp-input">
                    <input type="text" name="username" class="form-control" placeholder="Username"
                           value="<?= sanitize(old('username')) ?>" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['errors']['username'])): ?>
                    <p class="text-danger small mb-2"><?= sanitize($_SESSION['errors']['username']) ?></p>
                <?php endif; ?>

                <div class="input-group mb-2 gp-input">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <?php if (isset($_SESSION['errors']['password'])): ?>
                    <p class="text-danger small mb-2"><?= sanitize($_SESSION['errors']['password']) ?></p>
                <?php endif; ?>

                <div class="d-flex align-items-center justify-content-between mt-2 mb-3">
                    <div class="icheck-primary">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="<?= url('/forgot-password') ?>" class="small">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block gp-btn">Sign In</button>
            </form>

            <div class="gp-foot">
                Authorized Personnel Only • Ghana Police Integrated Management System
            </div>
        </div>
    </section>
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
