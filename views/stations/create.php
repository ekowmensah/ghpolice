<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Register New Station</h3>
            </div>
            <form method="POST" action="' . url('/stations') . '">
                ' . csrf_field() . '
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="station_name">Station Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="station_name" name="station_name" value="' . old('station_name') . '" required>
                                ' . (isset($_SESSION['errors']['station_name']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['station_name']) . '</small>' : '') . '
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="station_code">Station Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="station_code" name="station_code" value="' . old('station_code') . '" required>
                                ' . (isset($_SESSION['errors']['station_code']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['station_code']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="region_id">Region <span class="text-danger">*</span></label>
                                <select class="form-control" id="region_id" name="region_id" required>
                                    <option value="">Select Region</option>';

foreach ($regions as $region) {
    $selected = old('region_id') == $region['id'] ? 'selected' : '';
    $content .= '<option value="' . $region['id'] . '" ' . $selected . '>' . sanitize($region['region_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="division_id">Division <span class="text-danger">*</span></label>
                                <select class="form-control" id="division_id" name="division_id" required disabled>
                                    <option value="">Select Division</option>';

foreach ($divisions as $division) {
    $content .= '<option value="' . $division['id'] . '" data-region="' . $division['region_id'] . '">' . sanitize($division['division_name']) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="district_id">District <span class="text-danger">*</span></label>
                                <select class="form-control" id="district_id" name="district_id" required disabled>
                                    <option value="">Select District</option>';

foreach ($districts as $district) {
    $content .= '<option value="' . $district['id'] . '" data-division="' . $district['division_id'] . '">' . sanitize($district['district_name']) . '</option>';
}

$content .= '
                                </select>
                                ' . (isset($_SESSION['errors']['district_id']) ? '<small class="text-danger">' . sanitize($_SESSION['errors']['district_id']) . '</small>' : '') . '
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2">' . old('address') . '</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" value="' . old('contact_number') . '">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Register Station
                    </button>
                    <a href="' . url('/stations') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Stations', 'url' => '/stations'],
    ['title' => 'Register']
];

$scripts = '
<script>
$(document).ready(function() {
    const regionSelect = $("#region_id");
    const divisionSelect = $("#division_id");
    const districtSelect = $("#district_id");
    
    // Store all options at page load
    const allDivisions = [];
    const allDistricts = [];
    
    divisionSelect.find("option").each(function() {
        if ($(this).val() !== "") {
            allDivisions.push({
                value: $(this).val(),
                text: $(this).text(),
                regionId: $(this).data("region")
            });
        }
    });
    
    districtSelect.find("option").each(function() {
        if ($(this).val() !== "") {
            allDistricts.push({
                value: $(this).val(),
                text: $(this).text(),
                divisionId: $(this).data("division")
            });
        }
    });
    
    // Region change handler
    regionSelect.on("change", function() {
        const regionId = $(this).val();
        
        // Clear and reset division and district
        divisionSelect.html("<option value=\"\">Select Division</option>");
        districtSelect.html("<option value=\"\">Select District</option>");
        districtSelect.prop("disabled", true);
        
        if (regionId) {
            // Add filtered divisions
            let divisionCount = 0;
            allDivisions.forEach(function(division) {
                if (division.regionId == regionId) {
                    divisionSelect.append(
                        $("<option></option>")
                            .attr("value", division.value)
                            .attr("data-region", division.regionId)
                            .text(division.text)
                    );
                    divisionCount++;
                }
            });
            
            // Enable division dropdown if divisions found
            if (divisionCount > 0) {
                divisionSelect.prop("disabled", false);
            } else {
                divisionSelect.prop("disabled", true);
            }
        } else {
            divisionSelect.prop("disabled", true);
        }
    });
    
    // Division change handler
    divisionSelect.on("change", function() {
        const divisionId = $(this).val();
        
        // Clear district
        districtSelect.html("<option value=\"\">Select District</option>");
        
        if (divisionId) {
            // Add filtered districts
            let districtCount = 0;
            allDistricts.forEach(function(district) {
                if (district.divisionId == divisionId) {
                    districtSelect.append(
                        $("<option></option>")
                            .attr("value", district.value)
                            .attr("data-division", district.divisionId)
                            .text(district.text)
                    );
                    districtCount++;
                }
            });
            
            // Enable district dropdown if districts found
            if (districtCount > 0) {
                districtSelect.prop("disabled", false);
            } else {
                districtSelect.prop("disabled", true);
            }
        } else {
            districtSelect.prop("disabled", true);
        }
    });
    
    // Initialize if there are old values
    const oldRegion = "' . old('region_id') . '";
    const oldDivision = "' . old('division_id') . '";
    const oldDistrict = "' . old('district_id') . '";
    
    if (oldRegion) {
        regionSelect.val(oldRegion).trigger("change");
        setTimeout(function() {
            if (oldDivision) {
                divisionSelect.val(oldDivision).trigger("change");
                setTimeout(function() {
                    if (oldDistrict) {
                        districtSelect.val(oldDistrict);
                    }
                }, 100);
            }
        }, 100);
    }
});
</script>
';

unset($_SESSION['errors']);
unset($_SESSION['old']);

include __DIR__ . '/../layouts/main.php';
?>
