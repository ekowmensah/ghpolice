<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <form id="exhibitForm" enctype="multipart/form-data">
            ' . csrf_field() . '
            <input type="hidden" name="case_id" value="' . $case['id'] . '">
            <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Exhibit Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Exhibit Type <span class="text-danger">*</span></label>
                                    <select class="form-control" name="exhibit_type" required>
                                        <option value="">Select Type</option>
                                        <option value="Weapon">Weapon</option>
                                        <option value="Drug">Drug</option>
                                        <option value="Document">Document</option>
                                        <option value="Electronic Device">Electronic Device</option>
                                        <option value="Clothing">Clothing</option>
                                        <option value="Vehicle">Vehicle</option>
                                        <option value="Money">Money</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="description" rows="4" required placeholder="Detailed description of the exhibit"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="quantity" min="1" value="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Seized From</label>
                                            <input type="text" class="form-control" name="seized_from" placeholder="Person or location">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Seized Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="seized_date" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Seized By <span class="text-danger">*</span></label>
                                            <select class="form-control select2" name="seized_by" required>
                                                <option value="">Select Officer</option>
';

foreach ($officers as $officer) {
    $content .= '
                                                <option value="' . $officer['id'] . '">' . sanitize($officer['officer_name']) . '</option>';
}

$content .= '
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Current Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="current_location" required placeholder="Storage location">
                                </div>

                                <div class="form-group">
                                    <label>Photo</label>
                                    <input type="file" class="form-control-file" name="photo" accept="image/*">
                                </div>

                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="2" placeholder="Additional remarks"></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Register Exhibit
                                </button>
                                <a href="' . url('/cases/view/' . $case['id']) . '" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Case Information</h3>
                            </div>
                            <div class="card-body">
                                <dl>
                                    <dt>Case Number:</dt>
                                    <dd>' . sanitize($case['case_number']) . '</dd>
                                    <dt>Description:</dt>
                                    <dd>' . sanitize($case['description']) . '</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
        </form>
    </div>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $('.select2').select2();

    $('#exhibitForm').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        
        $.ajax({
            url: "' . url('/exhibits/store') . '",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "' . url('/exhibits/view/') . '" + response.exhibit_id;
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Exhibits', 'url' => '/exhibits'],
    ['title' => 'Register']
];

include __DIR__ . '/../layouts/main.php';
?>
