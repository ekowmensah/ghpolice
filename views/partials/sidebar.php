<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?= url('/dashboard') ?>" class="brand-link">
        <img src="<?= url('/AdminLTE/dist/img/AdminLTELogo.png') ?>" alt="GHPIMS Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">GHPIMS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= url('/AdminLTE/dist/img/user2-160x160.jpg') ?>" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= sanitize(auth()['username'] ?? 'User') ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= url('/dashboard') ?>" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Person Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Person Registry
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/persons') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Persons</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/persons/create') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Register Person</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/persons/crime-check') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Crime Check</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Case Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-folder-open"></i>
                        <p>
                            Cases
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/cases') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Cases</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/cases/create') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Register Case</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Evidence & Exhibits -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-box"></i>
                        <p>
                            Evidence & Exhibits
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/evidence') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Evidence</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/exhibits') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Exhibits</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/custody-chain') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Custody Chain</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Investigation Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-search"></i>
                        <p>
                            Investigations
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/investigations') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Active Investigations</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Court & Legal -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-gavel"></i>
                        <p>
                            Court & Legal
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/court-calendar') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Court Calendar</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/arrests') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Arrests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/charges') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Charges</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/bail') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Bail Records</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/custody') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Custody Records</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/warrants') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Warrants</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Officer Management -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>
                            Officers
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/officers') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>All Officers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/create') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Register Officer</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/postings') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Postings & Transfers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/promotions') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Promotions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/training') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Training</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/leave') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Leave Management</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/commendations') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Commendations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/disciplinary') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Disciplinary Actions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/officers/biometrics') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Biometrics</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Operations -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>
                            Operations
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/operations') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Police Operations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/duty-roster') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Duty Roster</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/patrol-logs') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Patrol Logs</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Administration -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Administration
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/regions') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Regions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/divisions') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Divisions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/districts') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Districts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/stations') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Stations</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/units') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Units</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Intelligence -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-brain"></i>
                        <p>
                            Intelligence
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/intelligence') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/intelligence/bulletins') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Bulletins</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/intelligence/reports') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Intel Reports</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/surveillance') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Surveillance Ops</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/informants') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Informants</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/tips/admin') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Public Tips</p>
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- Registries -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-database"></i>
                        <p>
                            Registries
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/missing-persons') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Missing Persons</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/vehicles') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Vehicles</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/firearms') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Firearms</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/ammunition') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Ammunition Stock</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/assets') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Assets</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/incidents') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Incident Reports</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/public-complaints') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Public Complaints</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/documents') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Documents</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>
                            Reports
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= url('/reports') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/reports/cases') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Case Reports</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/reports/statistics') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Statistics</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('/export') ?>" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Export Data</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>
