<?php
require_once __DIR__ . '/../../app/Helpers/functions.php';

$title = 'Edit Warrant';

$content = '
<style>
:root {
    --gp-navy: #112c4d;
    --gp-navy-2: #1a406d;
    --gp-navy-3: #243a5a;
    --gp-gold: #c7a13f;
    --gp-gold-2: #d4af37;
    --gp-red: #d94a3a;
    --gp-green: #1f7a3d;
    --gp-teal: #17a2b8;
    --gp-purple: #6f42c1;
    --gp-pink: #f093fb;
    --gp-bg: #eef3f9;
    --gp-text: #1c2630;
    --gp-muted: #607086;
    --gp-border: #d4deea;
    --gp-light: #f5f8fc;
}

.gp-warrant-header {
    background: linear-gradient(135deg, var(--gp-navy) 0%, var(--gp-navy-2) 50%, var(--gp-gold) 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(17, 44, 77, 0.3);
}

.gp-warrant-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Cpath fill=\'rgba(255,255,255,0.03)\' d=\'M0 50L50 0L100 50L50 100Z\'/%3E%3C/svg%3E");
    background-size: 30px 30px;
}

.gp-warrant-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    text-align: center;
}

.gp-warrant-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    text-align: center;
}

.gp-section-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid var(--gp-border);
    margin-bottom: 2rem;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gp-section-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.gp-section-header {
    padding: 1.5rem 2rem;
    color: white;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
}

.gp-section-header::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gp-gold);
}

.gp-section-edit { background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2)); }
.gp-section-details { background: linear-gradient(135deg, var(--gp-teal), #20c997); }

.gp-section-body {
    padding: 2rem;
}

.gp-form {
    background: var(--gp-light);
    border-radius: 15px;
    padding: 2rem;
    border-left: 4px solid var(--gp-navy);
}

.gp-form-group {
    margin-bottom: 1.5rem;
}

.gp-form-group label {
    font-weight: 600;
    color: var(--gp-navy);
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
    display: block;
}

.gp-form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--gp-border);
    border-radius: 10px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    background: white;
}

.gp-form-control:focus {
    outline: none;
    border-color: var(--gp-navy);
    box-shadow: 0 0 0 3px rgba(17, 44, 77, 0.1);
}

.gp-btn {
    background: linear-gradient(135deg, var(--gp-navy), var(--gp-navy-2));
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
    color: white;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(17, 44, 77, 0.2);
    cursor: pointer;
}

.gp-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(17, 44, 77, 0.3);
}

.gp-btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.gp-btn-success:hover {
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
}

.gp-btn-secondary {
    background: linear-gradient(135deg, #6c757d, #545b62);
}

.gp-btn-secondary:hover {
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
}

.gp-row {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.gp-col {
    flex: 1;
}

.gp-alert {
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    border: 1px solid #bee5eb;
    border-radius: 15px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    color: #0c5460;
}

.gp-alert i {
    font-size: 1.2rem;
    margin-right: 0.5rem;
}

@media (max-width: 768px) {
    .gp-warrant-header {
        padding: 2rem 1rem;
    }
    
    .gp-warrant-title {
        font-size: 2rem;
    }
    
    .gp-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .gp-section-body {
        padding: 1.5rem;
    }
}
</style>

<!-- Ghana Police Warrant Edit Header -->
<div class="gp-warrant-header text-center">
    <div class="position-relative" style="z-index: 2;">
        <h1 class="gp-warrant-title">
            <i class="fas fa-edit"></i> Edit Warrant
        </h1>
        <div class="gp-warrant-subtitle">
            Warrant Number: ' . htmlspecialchars($warrant['warrant_number']) . '
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Warrant Edit Form -->
    <div class="gp-section-card">
        <div class="gp-section-header gp-section-edit">
            <div>
                <i class="fas fa-gavel"></i> Warrant Details
            </div>
        </div>
        <div class="gp-section-body">
            <form method="POST" action="' . url('/warrants/' . $warrant['id'] . '/update') . '" class="gp-form">
                ' . csrf_field() . '
                
                <!-- Basic Information -->
                <div class="gp-row">
                    <div class="gp-col">
                        <div class="gp-form-group">
                            <label for="warrant_type">
                                <i class="fas fa-tag"></i> Warrant Type
                            </label>
                            <select name="warrant_type" id="warrant_type" class="gp-form-control" required>
                                <option value="Arrest" ' . ($warrant['warrant_type'] === 'Arrest' ? 'selected' : '') . '>Arrest Warrant</option>
                                <option value="Search" ' . ($warrant['warrant_type'] === 'Search' ? 'selected' : '') . '>Search Warrant</option>
                                <option value="Bench" ' . ($warrant['warrant_type'] === 'Bench' ? 'selected' : '') . '>Bench Warrant</option>
                                <option value="Detention" ' . ($warrant['warrant_type'] === 'Detention' ? 'selected' : '') . '>Detention Warrant</option>
                                <option value="Other" ' . ($warrant['warrant_type'] === 'Other' ? 'selected' : '') . '>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="gp-col">
                        <div class="gp-form-group">
                            <label for="case_id">
                                <i class="fas fa-folder"></i> Case Number
                            </label>
                            <select name="case_id" id="case_id" class="gp-form-control" required>
                                <option value="">Select Case</option>';

foreach ($cases as $case) {
    $content .= '
                                <option value="' . $case['id'] . '" ' . ($case['id'] == $warrant['case_id'] ? 'selected' : '') . '>' . htmlspecialchars($case['case_number']) . ' - ' . htmlspecialchars(substr($case['description'] ?? '', 0, 50)) . '...</option>';
}

$content .= '
                            </select>
                        </div>
                    </div>
                </div>

                <div class="gp-row">
                    <div class="gp-col">
                        <div class="gp-form-group">
                            <label for="suspect_id">
                                <i class="fas fa-user"></i> Suspect (Optional)
                            </label>
                            <select name="suspect_id" id="suspect_id" class="gp-form-control">
                                <option value="">Select a suspect (optional)</option>';

foreach ($suspects as $suspect) {
    $content .= '
                                <option value="' . $suspect['id'] . '" ' . ($suspect['id'] == $warrant['suspect_id'] ? 'selected' : '') . '>' . htmlspecialchars($suspect['name']) . '</option>';
}

$content .= '
                            </select>
                        </div>
                    </div>
                    <div class="gp-col">
                        <div class="gp-form-group">
                            <label for="issue_date">
                                <i class="fas fa-calendar"></i> Issue Date
                            </label>
                            <input type="date" name="issue_date" id="issue_date" class="gp-form-control" value="' . $warrant['issue_date'] . '" required>
                        </div>
                    </div>
                </div>

                <div class="gp-row">
                    <div class="gp-col">
                        <div class="gp-form-group">
                            <label for="expiry_date">
                                <i class="fas fa-calendar-times"></i> Expiry Date (Optional)
                            </label>
                            <input type="date" name="expiry_date" id="expiry_date" class="gp-form-control" value="' . ($warrant['expiry_date'] ?? '') . '">
                        </div>
                    </div>
                    <div class="gp-col">
                        <div class="gp-form-group">
                            <label for="issuing_court">
                                <i class="fas fa-balance-scale"></i> Issuing Court
                            </label>
                            <input type="text" name="issuing_court" id="issuing_court" class="gp-form-control" value="' . htmlspecialchars($warrant['issuing_court'] ?? '') . '" placeholder="e.g., Accra High Court">
                        </div>
                    </div>
                </div>

                <!-- Warrant Details -->
                <div class="gp-section-card">
                    <div class="gp-section-header gp-section-details">
                        <div>
                            <i class="fas fa-file-alt"></i> Warrant Information
                        </div>
                    </div>
                    <div class="gp-section-body">
                        <div class="gp-form-group">
                            <label for="warrant_details">
                                <i class="fas fa-info-circle"></i> Warrant Details
                            </label>
                            <textarea name="warrant_details" id="warrant_details" class="gp-form-control" rows="4" placeholder="Enter detailed description of the warrant..." required>' . htmlspecialchars($warrant['warrant_details'] ?? '') . '</textarea>
                        </div>

                        <div class="gp-form-group">
                            <label for="execution_instructions">
                                <i class="fas fa-list-ol"></i> Execution Instructions
                            </label>
                            <textarea name="execution_instructions" id="execution_instructions" class="gp-form-control" rows="3" placeholder="Specific instructions for warrant execution...">' . htmlspecialchars($warrant['execution_instructions'] ?? '') . '</textarea>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="text-center">
                    <button type="submit" class="gp-btn gp-btn-success">
                        <i class="fas fa-save"></i> Update Warrant
                    </button>
                    <a href="' . url('/warrants/view/' . $warrant['id']) . '" class="gp-btn gp-btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Warrants', 'link' => url('/warrants')],
    ['title' => 'Warrant Details', 'link' => url('/warrants/view/' . $warrant['id'])],
    ['title' => 'Edit Warrant']
];

include __DIR__ . '/../layouts/main.php';
?>

<!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Handle form submission
    $('form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        confirmButtonColor: '#112c4d'
                    }).then((result) => {
                        if (result.isConfirmed && response.redirect) {
                            window.location.href = response.redirect;
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message,
                        confirmButtonColor: '#d94a3a'
                    });
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while updating the warrant.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message,
                    confirmButtonColor: '#d94a3a'
                });
            }
        });
    });
    
    // Character counter for textareas
    $('textarea').each(function() {
        const textarea = $(this);
        const maxLength = 1000;
        const counter = $('<div class="text-muted small mt-1">0/' + maxLength + ' characters</div>');
        
        textarea.after(counter);
        
        textarea.on('input', function() {
            const length = $(this).val().length;
            counter.text(length + '/' + maxLength + ' characters');
            
            if (length > maxLength) {
                counter.addClass('text-danger');
            } else {
                counter.removeClass('text-danger');
            }
        });
    });
});
</script>
