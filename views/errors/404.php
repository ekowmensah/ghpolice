<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Page Not Found | GHPIMS</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="<?= url('/AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/AdminLTE/dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content">
            <div class="error-page" style="margin-top: 100px;">
                <h2 class="headline text-warning"> 404</h2>

                <div class="error-content">
                    <h3><i class="fas fa-exclamation-triangle text-warning"></i> Oops! Page not found.</h3>

                    <p>
                        We could not find the page you were looking for.
                        Meanwhile, you may <a href="<?= url('/dashboard') ?>">return to dashboard</a> or try using the search form.
                    </p>

                    <div class="input-group" style="max-width: 400px;">
                        <input type="text" class="form-control" placeholder="Search">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-warning"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="<?= url('/AdminLTE/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= url('/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= url('/AdminLTE/dist/js/adminlte.min.js') ?>"></script>
</body>
</html>
