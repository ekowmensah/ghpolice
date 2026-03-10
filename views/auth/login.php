<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - GHPIMS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Semi+Condensed:wght@400;500;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= url('AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= url('AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= url('AdminLTE/dist/css/adminlte.min.css') ?>">

    <style>
        :root {
            --gp-navy: #112c4d;
            --gp-navy-2: #1a406d;
            --gp-gold: #c7a13f;
            --gp-red: #d94a3a;
            --gp-green: #1f7a3d;
            --gp-bg: #eef3f9;
            --gp-text: #1c2630;
            --gp-muted: #607086;
        }

        body.gp-login-page {
            margin: 0;
            min-height: 100vh;
            font-family: "Barlow Semi Condensed", "Segoe UI", sans-serif;
            color: var(--gp-text);
            background:
                radial-gradient(circle at 10% 15%, rgba(17, 44, 77, 0.09), transparent 30%),
                radial-gradient(circle at 90% 80%, rgba(199, 161, 63, 0.16), transparent 32%),
                linear-gradient(180deg, #f7f9fc 0%, var(--gp-bg) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
        }

        .gp-wrapper {
            width: 100%;
            max-width: 1080px;
            border-radius: 16px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 20px 50px rgba(10, 27, 49, 0.18);
        }

        .gp-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            background: linear-gradient(100deg, var(--gp-navy), var(--gp-navy-2));
            color: #fff;
            padding: 14px 22px;
            border-bottom: 4px solid var(--gp-gold);
        }

        .gp-topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .gp-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            background: #fff;
            padding: 2px;
        }

        .gp-title {
            margin: 0;
            font-family: "Merriweather", serif;
            font-size: 1.1rem;
            line-height: 1.2;
        }

        .gp-subtitle {
            margin: 2px 0 0;
            font-size: 0.86rem;
            color: rgba(255, 255, 255, 0.86);
            letter-spacing: 0.35px;
        }

        .gp-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            padding: 6px 11px;
            font-size: 0.8rem;
            letter-spacing: 0.45px;
            text-transform: uppercase;
        }

        .gp-badge img {
            width: 20px;
            height: 14px;
            object-fit: cover;
            border-radius: 2px;
        }

        .gp-grid {
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
        }

        .gp-info {
            padding: 34px 30px;
            background:
                linear-gradient(0deg, rgba(17, 44, 77, 0.86), rgba(17, 44, 77, 0.86)),
                url("<?= url('/static/img/ghana-police-officer.jpg') ?>") center/cover no-repeat;
            color: #fff;
        }

        .gp-info h2 {
            margin: 0 0 10px;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.12;
            max-width: 420px;
        }

        .gp-info p {
            margin: 0;
            font-size: 1rem;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.87);
            max-width: 500px;
        }

        .gp-points {
            list-style: none;
            margin: 22px 0 0;
            padding: 0;
            display: grid;
            gap: 10px;
        }

        .gp-points li {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
        }

        .gp-points i {
            color: var(--gp-gold);
            width: 18px;
            text-align: center;
        }

        .gp-form-pane {
            padding: 34px 30px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gp-form {
            width: 100%;
            max-width: 375px;
        }

        .gp-form h3 {
            margin: 0;
            color: var(--gp-navy);
            font-size: 1.55rem;
            font-weight: 700;
        }

        .gp-form p {
            margin: 4px 0 18px;
            color: var(--gp-muted);
            font-size: 0.95rem;
        }

        .gp-input .form-control {
            height: 46px;
            border-radius: 9px;
            border-color: #d4deea;
            font-size: 1rem;
        }

        .gp-input .form-control:focus {
            border-color: #8aa2c0;
            box-shadow: 0 0 0 0.15rem rgba(40, 89, 145, 0.2);
        }

        .gp-input .input-group-text {
            border-radius: 0 9px 9px 0;
            border-color: #d4deea;
            background: #f5f8fc;
            color: #48658c;
            width: 44px;
            justify-content: center;
        }

        .gp-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 2px 0 14px;
        }

        .gp-actions a {
            color: #2f588f;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .gp-btn {
            height: 44px;
            border-radius: 9px;
            border: 0;
            background: linear-gradient(140deg, var(--gp-navy), var(--gp-navy-2));
            font-weight: 600;
            letter-spacing: 0.35px;
        }

        .gp-security {
            margin-top: 14px;
            text-align: center;
            color: #74849a;
            font-size: 0.84rem;
        }

        .gp-security strong {
            color: #405470;
            font-weight: 600;
        }

        @media (max-width: 991px) {
            .gp-grid {
                grid-template-columns: 1fr;
            }

            .gp-info {
                min-height: 280px;
            }

            .gp-topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body class="hold-transition gp-login-page">
<div class="gp-wrapper">
    <div class="gp-topbar">
        <div class="gp-topbar-left">
            <img class="gp-logo" src="<?= url('/static/img/ghana-police-logo.jpg') ?>" alt="Ghana Police Service Emblem">
            <div>
                <h1 class="gp-title">Ghana Police Integrated Management System</h1>
                <p class="gp-subtitle">Official Operations and Records Platform</p>
            </div>
        </div>
        <div class="gp-badge">
            <img src="<?= url('/static/img/ghana-flag.svg') ?>" alt="Flag of Ghana">
            Republic of Ghana
        </div>
    </div>

    <div class="gp-grid">
        <section class="gp-info">
            <h2>Professional, Secure, and Accountable Police Operations</h2>
            <p>
                Access case intelligence, evidence workflows, duty operations, and national records through a unified,
                auditable system built for Ghana Police Service personnel.
            </p>
            <ul class="gp-points">
                <li><i class="fas fa-user-shield"></i> Role-based access control for authorized officers</li>
                <li><i class="fas fa-history"></i> Audited actions for operational accountability</li>
                <li><i class="fas fa-network-wired"></i> Coordinated workflows across all commands</li>
            </ul>
        </section>

        <section class="gp-form-pane">
            <div class="gp-form">
                <h3>Officer Login</h3>
                <p>Enter your service credentials to continue.</p>

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

                    <div class="gp-actions">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                        <a href="<?= url('/forgot-password') ?>">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block gp-btn">Sign In</button>
                </form>

                <div class="gp-security">
                    Authorized Personnel Only. <strong>Unauthorized access is prohibited.</strong>
                </div>
            </div>
        </section>
    </div>
</div>

<?php unset($_SESSION['errors']); unset($_SESSION['old']); ?>

<script src="<?= url('AdminLTE/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= url('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= url('AdminLTE/dist/js/adminlte.min.js') ?>"></script>
</body>
</html>
