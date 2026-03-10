<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <!-- DOVVSU Alert Banner -->
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h5><i class="icon fas fa-shield-alt"></i> DOVVSU Case - Special Handling Required</h5>
            <ul class="mb-0">
                <li><strong>Victim Protection:</strong> All victim information is confidential</li>
                <li><strong>Privacy:</strong> Limited access to authorized DOVVSU personnel only</li>
                <li><strong>Support Services:</strong> Victim support and counseling must be offered</li>
                <li><strong>Multi-Agency:</strong> May require coordination with Social Welfare, Health Services</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <!-- DOVVSU Workflow Steps -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> DOVVSU Case Workflow</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!-- Step 1: Initial Report -->
                    <div class="time-label">
                        <span class="bg-danger">Step 1: Initial Report & Victim Support</span>
                    </div>
                    <div>
                        <i class="fas fa-user-shield bg-danger"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Victim Reception & Safety Assessment</h3>
                            <div class="timeline-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step1_1">
                                    <label class="form-check-label" for="step1_1">
                                        Victim received in private, safe environment
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step1_2">
                                    <label class="form-check-label" for="step1_2">
                                        Immediate safety assessment completed
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step1_3">
                                    <label class="form-check-label" for="step1_3">
                                        Medical attention arranged (if needed)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step1_4">
                                    <label class="form-check-label" for="step1_4">
                                        Victim informed of rights and support services
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Statement Taking -->
                    <div class="time-label">
                        <span class="bg-warning">Step 2: Statement & Evidence Collection</span>
                    </div>
                    <div>
                        <i class="fas fa-file-alt bg-warning"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Detailed Statement Recording</h3>
                            <div class="timeline-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step2_1">
                                    <label class="form-check-label" for="step2_1">
                                        Statement taken by trained DOVVSU officer
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step2_2">
                                    <label class="form-check-label" for="step2_2">
                                        Medical examination form (Form 3) issued
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step2_3">
                                    <label class="form-check-label" for="step2_3">
                                        Evidence collected and documented
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step2_4">
                                    <label class="form-check-label" for="step2_4">
                                        Photographs taken (with consent)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Investigation -->
                    <div class="time-label">
                        <span class="bg-info">Step 3: Investigation & Suspect Management</span>
                    </div>
                    <div>
                        <i class="fas fa-search bg-info"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Investigation Process</h3>
                            <div class="timeline-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step3_1">
                                    <label class="form-check-label" for="step3_1">
                                        Suspect identified and located
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step3_2">
                                    <label class="form-check-label" for="step3_2">
                                        Suspect arrested (if applicable)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step3_3">
                                    <label class="form-check-label" for="step3_3">
                                        Suspect statement recorded
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step3_4">
                                    <label class="form-check-label" for="step3_4">
                                        Witnesses interviewed
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Multi-Agency Coordination -->
                    <div class="time-label">
                        <span class="bg-primary">Step 4: Multi-Agency Support</span>
                    </div>
                    <div>
                        <i class="fas fa-users bg-primary"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Coordination with Support Services</h3>
                            <div class="timeline-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step4_1">
                                    <label class="form-check-label" for="step4_1">
                                        Social Welfare notified (for children)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step4_2">
                                    <label class="form-check-label" for="step4_2">
                                        Counseling services arranged
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step4_3">
                                    <label class="form-check-label" for="step4_3">
                                        Safe house/shelter arranged (if needed)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step4_4">
                                    <label class="form-check-label" for="step4_4">
                                        Legal aid contacted (if requested)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Prosecution -->
                    <div class="time-label">
                        <span class="bg-success">Step 5: Case Preparation & Prosecution</span>
                    </div>
                    <div>
                        <i class="fas fa-gavel bg-success"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Prosecution Readiness</h3>
                            <div class="timeline-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step5_1">
                                    <label class="form-check-label" for="step5_1">
                                        Case docket prepared
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step5_2">
                                    <label class="form-check-label" for="step5_2">
                                        Attorney General advice obtained
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step5_3">
                                    <label class="form-check-label" for="step5_3">
                                        Victim prepared for court (if applicable)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step5_4">
                                    <label class="form-check-label" for="step5_4">
                                        Case sent to court
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Follow-up -->
                    <div class="time-label">
                        <span class="bg-secondary">Step 6: Victim Follow-up & Support</span>
                    </div>
                    <div>
                        <i class="fas fa-heart bg-secondary"></i>
                        <div class="timeline-item">
                            <h3 class="timeline-header">Ongoing Support</h3>
                            <div class="timeline-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step6_1">
                                    <label class="form-check-label" for="step6_1">
                                        Regular victim welfare checks
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step6_2">
                                    <label class="form-check-label" for="step6_2">
                                        Court date notifications sent
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="step6_3">
                                    <label class="form-check-label" for="step6_3">
                                        Post-case counseling offered
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-ambulance"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Emergency Medical</span>
                <span class="info-box-number">Call 193</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-home"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Safe House</span>
                <span class="info-box-number">Arrange Now</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-user-md"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Counseling</span>
                <span class="info-box-number">Book Session</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Legal Aid</span>
                <span class="info-box-number">Contact Now</span>
            </div>
        </div>
    </div>
</div>

<!-- Important Contacts -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-phone"></i> Important DOVVSU Contacts</h3>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Contact</th>
                            <th>Hours</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>DOVVSU Hotline</strong></td>
                            <td>0800-111-222</td>
                            <td>24/7</td>
                            <td>Emergency domestic violence support</td>
                        </tr>
                        <tr>
                            <td><strong>Department of Social Welfare</strong></td>
                            <td>0302-123-456</td>
                            <td>Mon-Fri 8am-5pm</td>
                            <td>Child protection cases</td>
                        </tr>
                        <tr>
                            <td><strong>Ark Foundation</strong></td>
                            <td>0244-123-456</td>
                            <td>24/7</td>
                            <td>Safe house for victims</td>
                        </tr>
                        <tr>
                            <td><strong>Legal Aid Ghana</strong></td>
                            <td>0302-987-654</td>
                            <td>Mon-Fri 9am-5pm</td>
                            <td>Free legal representation</td>
                        </tr>
                        <tr>
                            <td><strong>Counseling Services</strong></td>
                            <td>0244-987-654</td>
                            <td>By appointment</td>
                            <td>Trauma counseling</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
';

$scripts = '
<script>
$(document).ready(function() {
    // Save workflow progress
    $(".form-check-input").on("change", function() {
        const stepId = $(this).attr("id");
        const isChecked = $(this).is(":checked");
        
        // Save to localStorage for persistence
        localStorage.setItem("dovvsu_" + stepId, isChecked ? "1" : "0");
        
        // Calculate progress
        const totalSteps = $(".form-check-input").length;
        const completedSteps = $(".form-check-input:checked").length;
        const progress = Math.round((completedSteps / totalSteps) * 100);
        
        console.log("DOVVSU Workflow Progress: " + progress + "%");
    });
    
    // Load saved progress
    $(".form-check-input").each(function() {
        const stepId = $(this).attr("id");
        const savedValue = localStorage.getItem("dovvsu_" + stepId);
        if (savedValue === "1") {
            $(this).prop("checked", true);
        }
    });
});
</script>
';

include __DIR__ . '/../layouts/main.php';
?>
