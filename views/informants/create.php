<?php require_once __DIR__ . '/../layouts/main.php'; ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Register Informant</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= url('/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('/informants') ?>">Informants</a></li>
                        <li class="breadcrumb-item active">Register</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Confidential:</strong> Do not include real names or identifying information in this system.
            </div>

            <div class="card">
                <form action="<?= url('/informants/store') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Contact Method</label>
                                    <select name="contact_method" class="form-control">
                                        <option value="Phone">Phone</option>
                                        <option value="In Person">In Person</option>
                                        <option value="Email">Email</option>
                                        <option value="Secure App">Secure App</option>
                                        <option value="Dead Drop">Dead Drop</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reliability Rating</label>
                                    <select name="reliability_rating" class="form-control">
                                        <option value="Unproven">Unproven</option>
                                        <option value="Fairly Reliable">Fairly Reliable</option>
                                        <option value="Usually Reliable">Usually Reliable</option>
                                        <option value="Reliable">Reliable</option>
                                        <option value="Unreliable">Unreliable</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Recruitment Date</label>
                                    <input type="date" name="recruitment_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Area of Operation <span class="text-danger">*</span></label>
                                    <input type="text" name="area_of_operation" class="form-control" required placeholder="e.g., Accra Central, Kumasi">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Specialization</label>
                            <input type="text" name="specialization" class="form-control" placeholder="e.g., Drug trafficking, Armed robbery, Cybercrime">
                        </div>

                        <div class="form-group">
                            <label>Confidential Notes</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Handler notes (highly confidential)"></textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Register Informant
                        </button>
                        <a href="<?= url('/informants') ?>" class="btn btn-default">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
