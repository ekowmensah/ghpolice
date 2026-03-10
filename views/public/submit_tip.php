<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit Anonymous Tip - Ghana Police Service</title>
    <link rel="stylesheet" href="<?= url('/AdminLTE/plugins/fontawesome-free/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/AdminLTE/dist/css/adminlte.min.css') ?>">
</head>
<body class="hold-transition">
<div class="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container">
                <div class="row mb-2">
                    <div class="col-sm-12 text-center">
                        <h1><i class="fas fa-shield-alt"></i> Ghana Police Service</h1>
                        <h3>Submit Anonymous Tip</h3>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-info">
                            <h5><i class="icon fas fa-info"></i> Your Safety is Our Priority</h5>
                            You can submit tips anonymously. We do not track IP addresses or personal information unless you choose to provide contact details.
                        </div>

                        <?php if (isset($_SESSION['flash'])): ?>
                            <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
                            </div>
                            <?php unset($_SESSION['flash']); ?>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Submit Your Tip</h3>
                            </div>
                            <form action="/submit-tip" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>How did you learn about this information?</label>
                                        <select name="tip_source" class="form-control">
                                            <option value="Web Form">Web Form</option>
                                            <option value="Phone">Phone Call</option>
                                            <option value="SMS">SMS</option>
                                            <option value="Email">Email</option>
                                            <option value="Walk-in">Walk-in</option>
                                            <option value="Social Media">Social Media</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="tip_category" class="form-control">
                                            <option value="General">General Crime</option>
                                            <option value="Drug Trafficking">Drug Trafficking</option>
                                            <option value="Armed Robbery">Armed Robbery</option>
                                            <option value="Cybercrime">Cybercrime</option>
                                            <option value="Fraud">Fraud</option>
                                            <option value="Missing Person">Missing Person</option>
                                            <option value="Wanted Person">Wanted Person</option>
                                            <option value="Suspicious Activity">Suspicious Activity</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Your Tip <span class="text-danger">*</span></label>
                                        <textarea name="tip_content" class="form-control" rows="6" required 
                                                  placeholder="Please provide as much detail as possible: What happened? When? Where? Who was involved?"></textarea>
                                        <small class="form-text text-muted">Minimum 20 characters</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Location (Optional)</label>
                                        <input type="text" name="location" class="form-control" 
                                               placeholder="Where did this occur? (City, neighborhood, landmark)">
                                    </div>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="anonymous" name="is_anonymous" value="1" checked>
                                            <label class="custom-control-label" for="anonymous">
                                                Submit Anonymously (Recommended)
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group" id="contact-info" style="display: none;">
                                        <label>Contact Information (Optional)</label>
                                        <input type="text" name="contact_information" class="form-control" 
                                               placeholder="Phone number or email if you want us to contact you">
                                        <small class="form-text text-muted">Only provide if you want follow-up contact</small>
                                    </div>

                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <strong>Important:</strong> Do not submit false information. False reports waste police resources and may be prosecuted.
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-paper-plane"></i> Submit Tip
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-phone"></i> Other Ways to Report</h3>
                            </div>
                            <div class="card-body">
                                <p><strong>Emergency Hotline:</strong> 191 or 18555</p>
                                <p><strong>Anti-Corruption Hotline:</strong> 0800-100-250</p>
                                <p><strong>SMS:</strong> Send to 1919</p>
                                <p><strong>Email:</strong> tips@police.gov.gh</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="<?= url('/AdminLTE/plugins/jquery/jquery.min.js') ?>"></script>
<script src="<?= url('/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script>
$(document).ready(function() {
    $('#anonymous').change(function() {
        if ($(this).is(':checked')) {
            $('#contact-info').hide();
        } else {
            $('#contact-info').show();
        }
    });
});
</script>
</body>
</html>
