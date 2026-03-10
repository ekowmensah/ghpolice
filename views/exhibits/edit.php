<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Exhibit Information</h3>
            </div>
            <form id="editExhibitForm">
                ' . csrf_field() . '
                <input type="hidden" name="id" value="' . $exhibit['id'] . '">
                <div class="card-body">
                        <div class="form-group">
                            <label>Exhibit Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="exhibit_type" required>
                                <option value="Weapon" ' . ($exhibit['exhibit_type'] == 'Weapon' ? 'selected' : '') . '>Weapon</option>
                                <option value="Drug" ' . ($exhibit['exhibit_type'] == 'Drug' ? 'selected' : '') . '>Drug</option>
                                <option value="Document" ' . ($exhibit['exhibit_type'] == 'Document' ? 'selected' : '') . '>Document</option>
                                <option value="Electronic Device" ' . ($exhibit['exhibit_type'] == 'Electronic Device' ? 'selected' : '') . '>Electronic Device</option>
                                <option value="Clothing" ' . ($exhibit['exhibit_type'] == 'Clothing' ? 'selected' : '') . '>Clothing</option>
                                <option value="Vehicle" ' . ($exhibit['exhibit_type'] == 'Vehicle' ? 'selected' : '') . '>Vehicle</option>
                                <option value="Money" ' . ($exhibit['exhibit_type'] == 'Money' ? 'selected' : '') . '>Money</option>
                                <option value="Other" ' . ($exhibit['exhibit_type'] == 'Other' ? 'selected' : '') . '>Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="description" rows="4" required>' . sanitize($exhibit['description']) . '</textarea>
                        </div>

                        <div class="form-group">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" min="1" 
                                   value="' . $exhibit['quantity'] . '" required>
                        </div>

                        <div class="form-group">
                            <label>Current Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="current_location" 
                                   value="' . sanitize($exhibit['current_location']) . '" required>
                        </div>

                        <div class="form-group">
                            <label>Exhibit Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="exhibit_status" required>
                                <option value="In Custody" ' . ($exhibit['exhibit_status'] == 'In Custody' ? 'selected' : '') . '>In Custody</option>
                                <option value="In Court" ' . ($exhibit['exhibit_status'] == 'In Court' ? 'selected' : '') . '>In Court</option>
                                <option value="Released" ' . ($exhibit['exhibit_status'] == 'Released' ? 'selected' : '') . '>Released</option>
                                <option value="Destroyed" ' . ($exhibit['exhibit_status'] == 'Destroyed' ? 'selected' : '') . '>Destroyed</option>
                                <option value="Missing" ' . ($exhibit['exhibit_status'] == 'Missing' ? 'selected' : '') . '>Missing</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2">' . sanitize($exhibit['remarks'] ?? '') . '</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Exhibit
                        </button>
                        <a href="' . url('/exhibits/view/' . $exhibit['id']) . '" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
            </form>
        </div>
    </div>
</div>';

$scripts = '
<script>
$(document).ready(function() {
    $('#editExhibitForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "' . url('/exhibits/update') . '",
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = "' . url('/exhibits/view/' . $exhibit['id']) . '";
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
    ['title' => 'Edit']
];

include __DIR__ . '/../layouts/main.php';
?>
