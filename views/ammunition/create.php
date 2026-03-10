<?php
$canCreatePool = in_array($access_level, ['National', 'Region', 'Division', 'District']);
$showCascadingFilters = in_array($access_level, ['National', 'Region', 'Division']);

$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes"></i> Add Ammunition Stock</h3>
                <div class="card-tools">
                    <a href="' . url('/ammunition') . '" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All
                    </a>
                </div>
            </div>
            <form action="' . url('/ammunition/store') . '" method="POST" id="ammoForm">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                
                <div class="card-body">';

if (isset($_SESSION['success'])) {
    $content .= '
                    <div class="alert alert-success alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success']) . '
                    </div>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $content .= '
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['error']) . '
                    </div>';
    unset($_SESSION['error']);
}

// Show organizational level info
$levelInfo = '';
switch ($access_level) {
    case 'National':
        $levelInfo = '<i class="fas fa-flag"></i> You can add ammunition to <strong>National Pool</strong> or to any location nationwide';
        break;
    case 'Region':
        $levelInfo = '<i class="fas fa-globe-africa"></i> You can add ammunition to your <strong>Region Pool</strong> or to any station in your region';
        break;
    case 'Division':
        $levelInfo = '<i class="fas fa-layer-group"></i> You can add ammunition to your <strong>Division Pool</strong> or to any station in your division';
        break;
    case 'District':
        $levelInfo = '<i class="fas fa-map-marked-alt"></i> You can add ammunition to your <strong>District Pool</strong> or to any station in your district';
        break;
    case 'Station':
    case 'Unit':
    case 'Own':
        $levelInfo = '<i class="fas fa-building"></i> You can add ammunition to your station only';
        break;
}

if ($levelInfo) {
    $content .= '
                    <div class="alert alert-info">
                        ' . $levelInfo . '
                    </div>';
}

if ($canCreatePool) {
    $content .= '
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="addToPool" name="add_to_pool" value="1">
                            <label class="custom-control-label" for="addToPool">
                                <strong>Add to ' . htmlspecialchars($access_level) . ' Pool</strong> 
                                <small class="text-muted">(Unallocated stock that can be distributed to lower levels)</small>
                            </label>
                        </div>
                    </div>';
}

$content .= '
                    <h5 class="mb-3">Location Selection</h5>
                    <div id="locationFilters">';

// Cascading filters for National/Region/Division users
if ($showCascadingFilters) {
    $content .= '
                    <div class="row">';
    
    // Region filter (National users only)
    if ($access_level === 'National' && !empty($regions)) {
        $content .= '
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Filter by Region</label>
                                <select id="regionFilter" class="form-control select2">
                                    <option value="">All Regions</option>';
        foreach ($regions as $region) {
            $content .= '<option value="' . $region['id'] . '">' . htmlspecialchars($region['region_name']) . '</option>';
        }
        $content .= '
                                </select>
                            </div>
                        </div>';
    }
    
    // Division filter (National and Region users)
    if (in_array($access_level, ['National', 'Region'])) {
        $content .= '
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Filter by Division</label>
                                <select id="divisionFilter" class="form-control select2">
                                    <option value="">All Divisions</option>';
        if ($access_level === 'Region' && !empty($divisions)) {
            foreach ($divisions as $division) {
                $content .= '<option value="' . $division['id'] . '">' . htmlspecialchars($division['division_name']) . '</option>';
            }
        }
        $content .= '
                                </select>
                            </div>
                        </div>';
    }
    
    // District filter (National, Region, and Division users)
    if (in_array($access_level, ['National', 'Region', 'Division'])) {
        $content .= '
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Filter by District</label>
                                <select id="districtFilter" class="form-control select2">
                                    <option value="">All Districts</option>';
        if ($access_level === 'Division' && !empty($districts)) {
            foreach ($districts as $district) {
                $content .= '<option value="' . $district['id'] . '">' . htmlspecialchars($district['district_name']) . '</option>';
            }
        }
        $content .= '
                                </select>
                            </div>
                        </div>';
    }
    
    $content .= '
                    </div>';
}

$content .= '
                    <div class="row">
                        <div class="col-md-12" id="stationSelectDiv">';

if (!empty($stations)) {
    $content .= '
                            <div class="form-group">
                                <label>Station <span class="text-danger" id="stationRequired">*</span></label>
                                <select name="station_id" id="stationSelect" class="form-control select2" required>
                                    <option value="">Select Station</option>';
    
    foreach ($stations as $station) {
        $selected = old('station_id') == $station['id'] ? 'selected' : '';
        $content .= '<option value="' . $station['id'] . '" 
                            data-region="' . ($station['region_id'] ?? '') . '" 
                            data-division="' . ($station['division_id'] ?? '') . '" 
                            data-district="' . ($station['district_id'] ?? '') . '" 
                            ' . $selected . '>' . htmlspecialchars($station['station_name']) . '</option>';
    }
    
    $content .= '
                                </select>
                                <small class="form-text text-muted">Search by station name or use filters above to narrow down options</small>
                            </div>';
} else {
    $content .= '
                            <div class="alert alert-warning">
                                No stations available for your access level
                            </div>';
}

$content .= '
                        </div>
                    </div>
                    </div>

                    <h5 class="mb-3 mt-3">Stock Information</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Ammunition Type <span class="text-danger">*</span></label>
                                <select name="ammunition_type_id" class="form-control select2" required style="width: 100%;">
                                    <option value="">Select Ammunition Type</option>';

foreach ($ammunition_types as $ammoType) {
    $selected = old('ammunition_type_id') == $ammoType['id'] ? 'selected' : '';
    $content .= '<option value="' . $ammoType['id'] . '" ' . $selected . '>' 
              . htmlspecialchars($ammoType['type']) . ' - ' 
              . htmlspecialchars($ammoType['caliber']) 
              . ($ammoType['description'] ? ' (' . htmlspecialchars($ammoType['description']) . ')' : '')
              . '</option>';
}

$content .= '
                                </select>
                                <small class="form-text text-muted">Select the ammunition type and caliber combination</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" class="form-control" required min="1" value="' . old('quantity') . '" placeholder="Number of rounds">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Minimum Threshold</label>
                                <input type="number" name="minimum_threshold" class="form-control" min="1" value="' . old('minimum_threshold', '100') . '">
                                <small class="form-text text-muted">Alert when below this level</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> <strong>Multi-Level Flow:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Pool Stock:</strong> Unallocated ammunition that can be distributed down the hierarchy</li>
                            <li><strong>Station Stock:</strong> Operational ammunition available for issuing to officers</li>
                            <li><strong>Hierarchy:</strong> National → Region → Division → District → Station</li>
                            <li><strong>Automatic Tracking:</strong> Stock is automatically deducted when firearms are issued</li>
                        </ul>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Stock
                    </button>
                    <a href="' . url('/ammunition') . '" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>';

$scripts = '
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $(".select2").select2({
        theme: "bootstrap4",
        width: "100%"
    });
    
    // Initialize filter states - disable dependent filters initially
    function initializeFilters() {
        // Disable division filter if region not selected
        if (!$("#regionFilter").val()) {
            $("#divisionFilter").prop("disabled", true).val("").trigger("change");
        }
        
        // Disable district filter if division not selected
        if (!$("#divisionFilter").val()) {
            $("#districtFilter").prop("disabled", true).val("").trigger("change");
        }
        
        // Disable station if district not selected (for users who have district filter)
        if ($("#districtFilter").length && !$("#districtFilter").val()) {
            $("#stationSelect").prop("disabled", true).val("").trigger("change");
        }
    }
    
    // Call on page load
    initializeFilters();
    
    // Handle pool checkbox toggle
    $("#addToPool").change(function() {
        if ($(this).is(":checked")) {
            // Hide all location filters and station selection
            $("#locationFilters").hide();
            $("#stationSelect").prop("required", false).val("").trigger("change");
            $("#stationRequired").hide();
        } else {
            // Show all location filters and station selection
            $("#locationFilters").show();
            $("#stationSelect").prop("required", true);
            $("#stationRequired").show();
            initializeFilters();
        }
    });
    
    // Region filter change
    $("#regionFilter").change(function() {
        var regionId = $(this).val();
        
        // Reset and disable dependent filters
        $("#divisionFilter").html("<option value=\"\">Loading...</option>").prop("disabled", true).val("").trigger("change");
        $("#districtFilter").html("<option value=\"\">All Districts</option>").prop("disabled", true).val("").trigger("change");
        $("#stationSelect").prop("disabled", true).val("").trigger("change");
        
        if (regionId) {
            // Load divisions for selected region
            $.ajax({
                url: "' . url('/ammunition/get-divisions') . '",
                method: "GET",
                data: { region_id: regionId },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var options = "<option value=\"\">Select Division</option>";
                        response.divisions.forEach(function(division) {
                            options += "<option value=\"" + division.id + "\">" + division.division_name + "</option>";
                        });
                        $("#divisionFilter").html(options).prop("disabled", false);
                    }
                },
                error: function() {
                    $("#divisionFilter").html("<option value=\"\">Error loading divisions</option>");
                }
            });
        } else {
            $("#divisionFilter").html("<option value=\"\">All Divisions</option>").prop("disabled", true);
        }
        
        filterStations();
    });
    
    // Division filter change
    $("#divisionFilter").change(function() {
        var divisionId = $(this).val();
        
        // Reset and disable dependent filters
        $("#districtFilter").html("<option value=\"\">Loading...</option>").prop("disabled", true).val("").trigger("change");
        $("#stationSelect").prop("disabled", true).val("").trigger("change");
        
        if (divisionId) {
            // Load districts for selected division
            $.ajax({
                url: "' . url('/ammunition/get-districts') . '",
                method: "GET",
                data: { division_id: divisionId },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var options = "<option value=\"\">Select District</option>";
                        response.districts.forEach(function(district) {
                            options += "<option value=\"" + district.id + "\">" + district.district_name + "</option>";
                        });
                        $("#districtFilter").html(options).prop("disabled", false);
                    }
                },
                error: function() {
                    $("#districtFilter").html("<option value=\"\">Error loading districts</option>");
                }
            });
        } else {
            $("#districtFilter").html("<option value=\"\">All Districts</option>").prop("disabled", true);
        }
        
        filterStations();
    });
    
    // District filter change
    $("#districtFilter").change(function() {
        var districtId = $(this).val();
        
        if (districtId) {
            // Enable station select
            $("#stationSelect").prop("disabled", false);
        } else {
            // Disable and reset station
            $("#stationSelect").prop("disabled", true).val("").trigger("change");
        }
        
        filterStations();
    });
    
    // Cascading filter functionality
    function filterStations() {
        var regionId = $("#regionFilter").val();
        var divisionId = $("#divisionFilter").val();
        var districtId = $("#districtFilter").val();
        
        // Destroy and reinitialize Select2 to properly handle option visibility
        $("#stationSelect").select2("destroy");
        
        $("#stationSelect option").each(function() {
            if ($(this).val() === "") {
                $(this).prop("disabled", false);
                return; // Skip placeholder
            }
            
            var show = true;
            
            if (regionId && $(this).data("region") != regionId) {
                show = false;
            }
            if (divisionId && $(this).data("division") != divisionId) {
                show = false;
            }
            if (districtId && $(this).data("district") != districtId) {
                show = false;
            }
            
            if (show) {
                $(this).prop("disabled", false).show();
            } else {
                $(this).prop("disabled", true).hide();
            }
        });
        
        // Reinitialize Select2
        $("#stationSelect").select2({
            theme: "bootstrap4",
            width: "100%"
        });
        
        $("#stationSelect").val("").trigger("change");
    }
});
</script>';

include __DIR__ . '/../layouts/main.php';
?>
