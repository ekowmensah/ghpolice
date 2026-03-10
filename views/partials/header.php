<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-light gp-header">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?= url('/dashboard') ?>" class="nav-link gp-home-link">Home</a>
        </li>
        <li class="nav-item d-none d-md-inline-block gp-header-brand">
            <img src="<?= url('/static/img/ghana-police-logo.jpg') ?>" alt="Ghana Police Emblem">
            <span>Ghana Police Integrated Management System</span>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Search -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
            <div class="navbar-search-block">
                <form class="form-inline" action="<?= url('/persons/search') ?>" method="GET">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" name="q" placeholder="Search persons..." aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li>

        <!-- Notifications -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge navbar-badge gp-badge-count">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">3 Notifications</span>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-exclamation-triangle mr-2"></i> High priority case
                    <span class="float-right text-muted text-sm">5 mins</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>

        <!-- User Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-user"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <div class="dropdown-item">
                    <strong><?= sanitize(auth()['username'] ?? 'User') ?></strong><br>
                    <small><?= sanitize(auth()['role_name'] ?? 'Role') ?></small>
                </div>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Profile
                </a>
                <a href="#" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="<?= url('/logout') ?>" class="dropdown-item">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>

<style>
    .gp-header {
        border-bottom: 3px solid var(--gp-gold);
        background: linear-gradient(100deg, var(--gp-navy), var(--gp-navy-2));
        color: #fff;
    }

    .gp-header .nav-link,
    .gp-header .navbar-nav .nav-link {
        color: rgba(255, 255, 255, 0.92) !important;
    }

    .gp-header .nav-link:hover {
        color: #fff !important;
    }

    .gp-home-link {
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 999px;
        padding: 5px 12px !important;
        margin-left: 8px;
    }

    .gp-header-brand {
        margin-left: 14px;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: rgba(255, 255, 255, 0.86);
        font-size: 0.89rem;
        letter-spacing: 0.3px;
    }

    .gp-header-brand img {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #fff;
        padding: 1px;
    }

    .gp-badge-count {
        background: linear-gradient(135deg, var(--gp-red), #be3428);
        color: #fff;
        font-weight: 700;
    }

    .gp-header .dropdown-menu {
        border: 1px solid #d5deea;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(13, 35, 62, 0.15);
    }

    .gp-header .navbar-search-block .form-control-navbar,
    .gp-header .navbar-search-block .btn-navbar {
        background: rgba(255, 255, 255, 0.18);
        border-color: rgba(255, 255, 255, 0.3);
        color: #fff;
    }
</style>
