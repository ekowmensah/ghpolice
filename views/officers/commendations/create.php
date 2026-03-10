<?php
$title = 'Record Officer Commendation';

$content = '
<style>
.achievement-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 2rem;
    color: white;
    margin-bottom: 2rem;
}

.achievement-form h1 {
    color: white;
    margin-bottom: 0.5rem;
}

.achievement-form .lead {
    opacity: 0.9;
    margin-bottom: 0;
}

.commendation-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

.commendation-card .card-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border: none;
    padding: 1.5rem;
}

.form-control:focus {
    border-color: #f5576c;
    box-shadow: 0 0 0 0.2rem rgba(245, 87, 108, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    border: none;
    border-radius: 25px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: transform 0.2s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(245, 87, 108, 0.3);
}

.btn-secondary {
    border-radius: 25px;
    padding: 0.75rem 2rem;
    font-weight: 600;
}

.award-type-preview {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    border-left: 4px solid #f5576c;
}

.award-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #b8860b;
}

.officer-preview {
    background: #e3f2fd;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 1rem;
    display: none;
}

.officer-preview.show {
    display: block;
}

.officer-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.text-danger {
    color: #f5576c !important;
}

.info-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 1rem;
    color: white;
    border: none;
}

.character-count {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: right;
    margin-top: 0.25rem;
}

.steps-indicator {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    position: relative;
}

.step {
    flex: 1;
    text-align: center;
    position: relative;
}

.step::before {
    content: "";
    position: absolute;
    top: 15px;
    left: 50%;
    right: -50%;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.step:last-child::before {
    display: none;
}

.step.active::before {
    background: #f5576c;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.step.active .step-number {
    background: #f5576c;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.step.active .step-label {
    color: #f5576c;
    font-weight: 600;
}
</style>

<section class="content">
    <div class="container-fluid">
        <!-- Achievement Header -->
        <div class="achievement-form text-center">
            <div class="award-icon mb-3">
                <i class="fas fa-trophy"></i>
            </div>
            <h1><i class="fas fa-award"></i> Record Officer Commendation</h1>
            <p class="lead">Recognize outstanding service and achievements</p>
        </div>

        <!-- Progress Steps -->
        <div class="steps-indicator">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <div class="step-label">Officer Details</div>
            </div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-label">Award Information</div>
            </div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-label">Description</div>
            </div>
            <div class="step" id="step4">
                <div class="step-number">4</div>
                <div class="step-label">Review & Submit</div>
            </div>
        </div>

        <!-- Main Form Card -->
        <div class="card commendation-card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-star"></i> Commendation Details</h3>
            </div>
            <form id="commendationForm">
                ' . csrf_field() . '
                <div class="card-body p-4">
                    <!-- Step 1: Officer Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="officer_id" class="form-label">
                                    <i class="fas fa-user-shield"></i> Select Officer <span class="text-danger">*</span>
                                </label>
                                <select class="form-control select2" id="officer_id" name="officer_id" required>
                                    <option value="">Choose an officer to commend</option>';

foreach ($officers as $off) {
    $selected = ($officer && $officer['id'] == $off['id']) ? 'selected' : '';
    $content .= '
                                    <option value="' . $off['id'] . '" data-name="' . sanitize($off['first_name'] . ' ' . $off['last_name']) . '" data-rank="' . sanitize($off['rank_name'] ?? '') . '" data-service="' . sanitize($off['service_number'] ?? '') . '" ' . $selected . '>
                                        ' . sanitize($off['service_number'] ?? '') . ' - ' . sanitize($off['first_name'] . ' ' . $off['last_name']) . '
                                    </option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commendation_type" class="form-label">
                                    <i class="fas fa-award"></i> Commendation Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-control" id="commendation_type" name="commendation_type" required>
                                    <option value="">Select award type</option>
                                    <option value="Bravery Award">🏅 Bravery Award</option>
                                    <option value="Meritorious Service">⭐ Meritorious Service</option>
                                    <option value="Excellence Award">🎖️ Excellence Award</option>
                                    <option value="Long Service Award">🏆 Long Service Award</option>
                                    <option value="Presidential Award">🌟 Presidential Award</option>
                                    <option value="Commendation Letter">📜 Commendation Letter</option>
                                    <option value="Certificate of Recognition">🎓 Certificate of Recognition</option>
                                    <option value="Other">🎯 Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Officer Preview -->
                    <div class="officer-preview" id="officerPreview">
                        <div class="d-flex align-items-center">
                            <div class="officer-avatar mr-3" id="officerAvatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h6 class="mb-0" id="officerName">Officer Name</h6>
                                <small class="text-muted" id="officerDetails">Rank • Service Number</small>
                            </div>
                        </div>
                    </div>

                    <!-- Award Type Preview -->
                    <div class="award-type-preview" id="awardPreview" style="display: none;">
                        <div class="text-center">
                            <div class="award-icon mb-2">
                                <i class="fas fa-trophy" id="awardIcon"></i>
                            </div>
                            <h6 class="mb-0" id="awardTitle">Award Type</h6>
                            <small class="text-muted" id="awardDescription">Award description will appear here</small>
                        </div>
                    </div>

                    <!-- Step 2: Basic Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commendation_title" class="form-label">
                                    <i class="fas fa-heading"></i> Commendation Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="commendation_title" name="commendation_title" 
                                       placeholder="e.g., Outstanding Performance in Crime Prevention" required>
                                <small class="text-muted">Provide a clear, descriptive title for the commendation</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="commendation_date" class="form-label">
                                    <i class="fas fa-calendar"></i> Date Awarded <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="commendation_date" name="commendation_date" 
                                       value="' . date('Y-m-d') . '" required>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Additional Details -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="awarded_by" class="form-label">
                                    <i class="fas fa-user-tie"></i> Awarded By
                                </label>
                                <input type="text" class="form-control" id="awarded_by" name="awarded_by" 
                                       placeholder="e.g., Inspector General of Police">
                                <small class="text-muted">Name or title of the awarding authority</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="certificate_number" class="form-label">
                                    <i class="fas fa-certificate"></i> Certificate Number
                                </label>
                                <input type="text" class="form-control" id="certificate_number" name="certificate_number" 
                                       placeholder="e.g., CERT-2024-001">
                                <small class="text-muted">Official certificate reference (if applicable)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Description -->
                    <div class="form-group mb-4">
                        <label for="description" class="form-label">
                            <i class="fas fa-pen"></i> Description / Reason for Commendation
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="5" 
                                  placeholder="Describe the specific actions, achievements, or contributions that led to this commendation..." 
                                  maxlength="1000"></textarea>
                        <div class="character-count">
                            <span id="charCount">0</span> / 1000 characters
                        </div>
                    </div>

                    <!-- Information Banner -->
                    <div class="alert info-banner mb-4">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Important:</strong> This commendation will be permanently recorded in the officer\'s service record and may be used for promotion considerations and career advancement.
                    </div>

                    <!-- Review Section -->
                    <div class="text-center mb-4">
                        <h5><i class="fas fa-check-circle"></i> Review Before Submission</h5>
                        <p class="text-muted">Please verify all information before recording this commendation</p>
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between">
                        <a href="' . url('/officers/commendations') . '" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Commendations
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Record Commendation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>';

$scripts = '
<script>
$(document).ready(function() {
    // Initialize Select2
    $(".select2").select2({
        theme: "bootstrap4",
        placeholder: "Select Officer",
        allowClear: true
    });

    // Officer selection handler
    $("#officer_id").on("change", function() {
        const selectedOption = $(this).find("option:selected");
        const officerName = selectedOption.data("name");
        const officerRank = selectedOption.data("rank");
        const officerService = selectedOption.data("service");
        
        if (officerName) {
            $("#officerPreview").addClass("show");
            $("#officerAvatar").text(officerName.charAt(0));
            $("#officerName").text(officerName);
            $("#officerDetails").text(officerRank + " • " + officerService);
            updateStepProgress(2);
        } else {
            $("#officerPreview").removeClass("show");
            updateStepProgress(1);
        }
    });

    // Award type handler
    $("#commendation_type").on("change", function() {
        const awardType = $(this).val();
        const descriptions = {
            "Bravery Award": "Awarded for acts of courage and bravery in the line of duty",
            "Meritorious Service": "Recognizes exceptional service and dedication",
            "Excellence Award": "For outstanding performance and achievements",
            "Long Service Award": "Honors years of loyal service",
            "Presidential Award": "Highest honor for exceptional contributions",
            "Commendation Letter": "Official recognition of good performance",
            "Certificate of Recognition": "Formal acknowledgment of achievements",
            "Other": "Custom recognition for specific achievements"
        };
        
        if (awardType) {
            $("#awardPreview").show();
            $("#awardTitle").text(awardType);
            $("#awardDescription").text(descriptions[awardType] || "Custom award description");
            updateStepProgress(3);
        } else {
            $("#awardPreview").hide();
        }
    });

    // Character counter
    $("#description").on("input", function() {
        const length = $(this).val().length;
        $("#charCount").text(length);
        if (length > 900) {
            $("#charCount").css("color", "#dc3545");
        } else {
            $("#charCount").css("color", "#6c757d");
        }
    });

    // Update step progress
    function updateStepProgress(step) {
        $(".step").removeClass("active");
        for (let i = 1; i <= step; i++) {
            $("#step" + i).addClass("active");
        }
    }

    // Form submission
    $("#commendationForm").on("submit", function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return false;
        }
        
        const submitBtn = $(this).find("button[type=submit]");
        const originalText = submitBtn.html();
        submitBtn.prop("disabled", true).html("<i class=\"fas fa-spinner fa-spin\"></i> Recording...");
        
        $.ajax({
            url: "' . url('/officers/commendations/store') . '",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Commendation Recorded!",
                        text: "The officer commendation has been successfully recorded.",
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = "' . url('/officers/commendations') . '";
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message || "Failed to record commendation"
                    });
                    submitBtn.prop("disabled", false).html(originalText);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || "An error occurred. Please try again.";
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: message
                });
                submitBtn.prop("disabled", false).html(originalText);
            }
        });
    });

    // Form validation
    function validateForm() {
        const officerId = $("#officer_id").val();
        const awardType = $("#commendation_type").val();
        const title = $("#commendation_title").val();
        
        if (!officerId) {
            Swal.fire({
                icon: "warning",
                title: "Missing Information",
                text: "Please select an officer to commend."
            });
            return false;
        }
        
        if (!awardType) {
            Swal.fire({
                icon: "warning",
                title: "Missing Information",
                text: "Please select a commendation type."
            });
            return false;
        }
        
        if (!title.trim()) {
            Swal.fire({
                icon: "warning",
                title: "Missing Information",
                text: "Please provide a commendation title."
            });
            return false;
        }
        
        return true;
    }
});
</script>';

include __DIR__ . '/../../layouts/main.php';
?>
