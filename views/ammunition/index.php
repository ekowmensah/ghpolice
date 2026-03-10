<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <!-- Filter Section -->
        <div class="card card-primary collapsed-card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filter Ammunition Stock</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Ammunition Type</label>
                            <select id="typeFilter" class="form-control">
                                <option value="">All Types</option>';

foreach ($ammunition_types as $type) {
    $selected = (isset($filter_type) && $filter_type == $type) ? 'selected' : '';
    $content .= '<option value="' . htmlspecialchars($type) . '" ' . $selected . '>' . htmlspecialchars($type) . '</option>';
}

$content .= '
                                </select>
                            </div>
                        </div>';

// Show progressive filters based on access level
if ($access_level === 'National' || !empty($regions)) {
    $content .= '
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Filter by Region</label>
                                <select id="regionFilter" class="form-control">
                                    <option value="">All Regions</option>';
    foreach ($regions as $region) {
        $content .= '<option value="' . $region['id'] . '">' . htmlspecialchars($region['region_name']) . '</option>';
    }
    $content .= '
                                </select>
                            </div>
                        </div>';
}

if (in_array($access_level, ['National', 'Region']) || !empty($divisions)) {
    $content .= '
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Filter by Division</label>
                                <select id="divisionFilter" class="form-control">
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

if (in_array($access_level, ['National', 'Region', 'Division']) || !empty($districts)) {
    $content .= '
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Filter by District</label>
                                <select id="districtFilter" class="form-control">
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Filter by Station</label>
                                <select id="stationFilter" class="form-control">
                                    <option value="">All Stations</option>';
if ($access_level === 'District' && !empty($stations)) {
    foreach ($stations as $station) {
        $content .= '<option value="' . $station['id'] . '">' . htmlspecialchars($station['station_name']) . '</option>';
    }
}
$content .= '
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" id="clearFilters" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times"></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Filter Behavior:</strong> 
                                <span id="filterHelp">Showing National Pool only. Select filters to view specific organizational levels.</span>
                            </div>
                        </div>
                    </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">';

// Calculate totals for National pool only (initial display)
$nationalPoolRounds = 0;
$nationalPoolItems = 0;

foreach ($ammunition as $ammo) {
    if ($ammo['stock_level'] === 'National' && $ammo['is_pool']) {
        $nationalPoolRounds += $ammo['quantity'];
        $nationalPoolItems++;
    }
}

$content .= '
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="totalRounds">' . number_format($nationalPoolRounds) . '</h3>
                        <p id="totalRoundsLabel">National Pool Rounds</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="stockItems">' . $nationalPoolItems . '</h3>
                        <p id="stockItemsLabel">National Pool Items</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="lowStockCount">0</h3>
                        <p>Low Stock Alerts</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3 id="typeCount">0</h3>
                        <p>Ammunition Types</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ammunition Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-boxes"></i> Ammunition Stock</h3>
                <div class="card-tools">
                    <a href="' . url('/ammunition/create') . '" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Stock
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Caliber</th>
                            <th>Quantity</th>
                            <th>Min. Threshold</th>
                            <th>Status</th>
                            <th>Last Restocked</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($ammunition as $ammo) {
    $stockLevel = 'success';
    $stockText = 'Good Stock';
    
    if ($ammo['quantity'] <= 0) {
        $stockLevel = 'danger';
        $stockText = 'Out of Stock';
    } elseif ($ammo['quantity'] <= $ammo['minimum_threshold']) {
        $stockLevel = 'warning';
        $stockText = 'Low Stock';
    }
    
    // Determine location display
    $locationDisplay = '';
    if ($ammo['is_pool']) {
        $locationDisplay = '<span class="badge badge-info">' . htmlspecialchars($ammo['stock_level']) . ' Pool</span><br>';
        switch ($ammo['stock_level']) {
            case 'National':
                $locationDisplay .= '<small>National Headquarters</small>';
                break;
            case 'Region':
                $locationDisplay .= '<small>' . htmlspecialchars($ammo['region_name'] ?? 'Unknown Region') . '</small>';
                break;
            case 'Division':
                $locationDisplay .= '<small>' . htmlspecialchars($ammo['division_name'] ?? 'Unknown Division') . '</small>';
                break;
            case 'District':
                $locationDisplay .= '<small>' . htmlspecialchars($ammo['district_name'] ?? 'Unknown District') . '</small>';
                break;
        }
    } else {
        $locationDisplay = htmlspecialchars($ammo['station_name'] ?? 'Unknown Station');
    }
    
    $content .= '
                        <tr class="ammo-row" 
                            data-type="' . htmlspecialchars($ammo['ammunition_type']) . '"
                            data-region="' . ($ammo['region_id'] ?? '') . '"
                            data-division="' . ($ammo['division_id'] ?? '') . '"
                            data-district="' . ($ammo['district_id'] ?? '') . '"
                            data-station="' . ($ammo['station_id'] ?? '') . '"
                            data-quantity="' . $ammo['quantity'] . '"
                            data-threshold="' . $ammo['minimum_threshold'] . '"
                            data-level="' . htmlspecialchars($ammo['stock_level']) . '">
                            <td>' . $locationDisplay . '</td>
                            <td>' . htmlspecialchars($ammo['ammunition_type']) . '</td>
                            <td><strong>' . htmlspecialchars($ammo['caliber']) . '</strong></td>
                            <td><span class="badge badge-' . $stockLevel . '" style="font-size: 1em;">' . number_format($ammo['quantity']) . '</span></td>
                            <td>' . number_format($ammo['minimum_threshold']) . '</td>
                            <td><span class="badge badge-' . $stockLevel . '">' . $stockText . '</span></td>
                            <td>' . ($ammo['last_restocked_date'] ? date('M j, Y', strtotime($ammo['last_restocked_date'])) : 'N/A') . '</td>
                            <td>';
    
    if ($ammo['is_pool']) {
        $content .= '
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#allocateModal' . $ammo['id'] . '">
                                    <i class="fas fa-share"></i> Allocate
                                </button> ';
    }
    
    $content .= '
                                <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#restockModal' . $ammo['id'] . '">
                                    <i class="fas fa-plus"></i> Restock
                                </button>
                            </td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

// Add modals for restock and allocate (simplified for now - will be enhanced later)
foreach ($ammunition as $ammo) {
    $content .= '
<!-- Restock Modal for ' . $ammo['id'] . ' -->
<div class="modal fade" id="restockModal' . $ammo['id'] . '">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/ammunition/restock/' . $ammo['id']) . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Restock Ammunition</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Current Stock:</strong> ' . number_format($ammo['quantity']) . ' rounds<br>
                        <strong>Type:</strong> ' . htmlspecialchars($ammo['ammunition_type']) . '<br>
                        <strong>Caliber:</strong> ' . htmlspecialchars($ammo['caliber']) . '
                    </div>
                    <div class="form-group">
                        <label>Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" required min="1" placeholder="Number of rounds">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>';

    if ($ammo['is_pool']) {
        $content .= '
<!-- Allocate Modal for ' . $ammo['id'] . ' -->
<div class="modal fade" id="allocateModal' . $ammo['id'] . '">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="' . url('/ammunition/allocate/' . $ammo['id']) . '" method="POST">
                <input type="hidden" name="csrf_token" value="' . csrf_token() . '">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-share"></i> Allocate Ammunition</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Available:</strong> ' . number_format($ammo['quantity']) . ' rounds<br>
                        <strong>Type:</strong> ' . htmlspecialchars($ammo['ammunition_type']) . '<br>
                        <strong>Caliber:</strong> ' . htmlspecialchars($ammo['caliber']) . '<br>
                        <strong>From:</strong> ' . htmlspecialchars($ammo['stock_level']) . ' Pool
                    </div>
                    <div class="form-group">
                        <label>Allocate To Level <span class="text-danger">*</span></label>
                        <select name="to_level" id="allocateLevel' . $ammo['id'] . '" class="form-control" required>
                            <option value="">Select Level</option>';
        
        // Show allocation options based on current level
        switch ($ammo['stock_level']) {
            case 'National':
                $content .= '<option value="Region">Region Pool</option>';
                $content .= '<option value="Division">Division Pool</option>';
                $content .= '<option value="District">District Pool</option>';
                $content .= '<option value="Station">Station (Operational)</option>';
                break;
            case 'Region':
                $content .= '<option value="Division">Division Pool</option>';
                $content .= '<option value="District">District Pool</option>';
                $content .= '<option value="Station">Station (Operational)</option>';
                break;
            case 'Division':
                $content .= '<option value="District">District Pool</option>';
                $content .= '<option value="Station">Station (Operational)</option>';
                break;
            case 'District':
                $content .= '<option value="Station">Station (Operational)</option>';
                break;
        }
        
        $content .= '
                        </select>
                    </div>
                    
                    <div id="allocateRegion' . $ammo['id'] . '" class="form-group" style="display:none;">
                        <label>Select Region <span class="text-danger">*</span></label>
                        <select name="to_location_id" class="form-control allocate-region-select">
                            <option value="">Select Region</option>';
        foreach ($regions as $region) {
            $content .= '<option value="' . $region['id'] . '">' . htmlspecialchars($region['region_name']) . '</option>';
        }
        $content .= '
                        </select>
                    </div>
                    
                    <div id="allocateDivision' . $ammo['id'] . '" class="form-group" style="display:none;">
                        <label>Select Division <span class="text-danger">*</span></label>
                        <select id="allocateDivisionSelect' . $ammo['id'] . '" class="form-control">
                            <option value="">Select Division</option>
                        </select>
                    </div>
                    
                    <div id="allocateDistrict' . $ammo['id'] . '" class="form-group" style="display:none;">
                        <label>Select District <span class="text-danger">*</span></label>
                        <select id="allocateDistrictSelect' . $ammo['id'] . '" class="form-control">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    
                    <div id="allocateStation' . $ammo['id'] . '" class="form-group" style="display:none;">
                        <label>Select Station <span class="text-danger">*</span></label>
                        <select name="to_location_id" id="allocateStationSelect' . $ammo['id'] . '" class="form-control">
                            <option value="">Select Station</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" required min="1" max="' . $ammo['quantity'] . '" placeholder="Rounds to allocate">
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate</button>
                </div>
            </form>
        </div>
    </div>
</div>';
    }
}

$scripts = '
<script>
$(document).ready(function() {
    var allDivisions = ' . json_encode($divisions) . ';
    var allDistricts = ' . json_encode($districts) . ';
    var allStations = ' . json_encode($stations) . ';
    
    // Function to update helper text
    function updateFilterHelp() {
        var helpText = "";
        
        if ($("#stationFilter").val()) {
            helpText = "Showing selected Station only.";
        } else if ($("#districtFilter").val()) {
            helpText = "Showing selected District Pool only (excludes stations within district).";
        } else if ($("#divisionFilter").val()) {
            helpText = "Showing selected Division Pool only (excludes districts and stations within division).";
        } else if ($("#regionFilter").val()) {
            helpText = "Showing selected Region Pool only (excludes divisions, districts, and stations within region).";
        } else {
            helpText = "Showing National Pool only. Select filters to view specific organizational levels.";
        }
        
        $("#filterHelp").text(helpText);
    }
    
    // Function to update summary cards
    function updateSummaryCards() {
        var poolRounds = 0;
        var poolItems = 0;
        var hierarchicalRounds = 0;
        var hierarchicalItems = 0;
        var lowStockCount = 0;
        var types = {};
        var currentLevel = "National Pool";
        
        var regionFilter = $("#regionFilter").val();
        var divisionFilter = $("#divisionFilter").val();
        var districtFilter = $("#districtFilter").val();
        var stationFilter = $("#stationFilter").val();
        
        // Determine current filter level for label
        if (stationFilter) {
            currentLevel = "Station";
        } else if (districtFilter) {
            currentLevel = "District";
        } else if (divisionFilter) {
            currentLevel = "Division";
        } else if (regionFilter) {
            currentLevel = "Region";
        }
        
        // Calculate both pool-only and hierarchical totals
        $(".ammo-row").each(function() {
            var quantity = parseInt($(this).data("quantity")) || 0;
            var threshold = parseInt($(this).data("threshold")) || 0;
            var type = $(this).data("type");
            var rowLevel = $(this).data("level");
            var rowRegion = $(this).data("region");
            var rowDivision = $(this).data("division");
            var rowDistrict = $(this).data("district");
            var rowStation = $(this).data("station");
            
            var isVisible = $(this).is(":visible");
            var includeInHierarchy = false;
            
            // Pool-only count (visible rows)
            if (isVisible) {
                poolRounds += quantity;
                poolItems++;
                
                if (quantity <= threshold) {
                    lowStockCount++;
                }
                
                if (type) {
                    types[type] = true;
                }
            }
            
            // Hierarchical count (current level + all children)
            if (stationFilter) {
                // Station level - no children
                includeInHierarchy = (rowStation == stationFilter);
            } else if (districtFilter) {
                // District + all stations in district
                includeInHierarchy = (rowDistrict == districtFilter);
            } else if (divisionFilter) {
                // Division + all districts + stations in division
                includeInHierarchy = (rowDivision == divisionFilter);
            } else if (regionFilter) {
                // Region + all divisions + districts + stations in region
                includeInHierarchy = (rowRegion == regionFilter);
            } else {
                // National + everything
                includeInHierarchy = true;
            }
            
            if (includeInHierarchy) {
                hierarchicalRounds += quantity;
                hierarchicalItems++;
            }
        });
        
        // Update display
        $("#totalRounds").html(poolRounds.toLocaleString() + " <small>(" + hierarchicalRounds.toLocaleString() + " total)</small>");
        $("#totalRoundsLabel").text(currentLevel + " Rounds");
        $("#stockItems").html(poolItems + " <small>(" + hierarchicalItems + " total)</small>");
        $("#stockItemsLabel").text(currentLevel + " Items");
        $("#lowStockCount").text(lowStockCount);
        $("#typeCount").text(Object.keys(types).length);
    }
    
    // Function to filter table rows
    function filterRows() {
        var typeFilter = $("#typeFilter").val();
        var regionFilter = $("#regionFilter").val();
        var divisionFilter = $("#divisionFilter").val();
        var districtFilter = $("#districtFilter").val();
        var stationFilter = $("#stationFilter").val();
        
        $(".ammo-row").each(function() {
            var show = true;
            var rowLevel = $(this).data("level");
            
            // Type filter
            if (typeFilter && $(this).data("type") != typeFilter) {
                show = false;
            }
            
            // Hierarchical filtering - show only the selected level
            if (stationFilter) {
                // Show only the selected station
                if ($(this).data("station") != stationFilter) {
                    show = false;
                }
            } else if (districtFilter) {
                // Show only District pool for the selected district
                var rowDistrict = $(this).data("district");
                if (rowLevel != "District" || rowDistrict != districtFilter) {
                    show = false;
                }
            } else if (divisionFilter) {
                // Show only Division pool for the selected division
                var rowDivision = $(this).data("division");
                if (rowLevel != "Division" || rowDivision != divisionFilter) {
                    show = false;
                }
            } else if (regionFilter) {
                // Show only Region pool for the selected region
                var rowRegion = $(this).data("region");
                if (rowLevel != "Region" || rowRegion != regionFilter) {
                    show = false;
                }
            } else {
                // No location filter - show only National pool
                if (rowLevel != "National") {
                    show = false;
                }
            }
            
            if (show) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
        
        // Update helper text and summary cards after filtering
        updateFilterHelp();
        updateSummaryCards();
    }
    
    // Type filter change
    $("#typeFilter").change(function() {
        filterRows();
    });
    
    // Region filter change
    $("#regionFilter").change(function() {
        var regionId = $(this).val();
        
        $("#divisionFilter").html("<option value=\"\">All Divisions</option>").val("");
        $("#districtFilter").html("<option value=\"\">All Districts</option>").val("");
        $("#stationFilter").html("<option value=\"\">All Stations</option>").val("");
        
        if (regionId) {
            $.ajax({
                url: "' . url('/ammunition/get-divisions') . '",
                method: "GET",
                data: { region_id: regionId },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var options = "<option value=\"\">All Divisions</option>";
                        response.divisions.forEach(function(division) {
                            options += "<option value=\"" + division.id + "\">" + division.division_name + "</option>";
                        });
                        $("#divisionFilter").html(options);
                    }
                }
            });
        }
        
        filterRows();
    });
    
    // Division filter change
    $("#divisionFilter").change(function() {
        var divisionId = $(this).val();
        
        $("#districtFilter").html("<option value=\"\">All Districts</option>").val("");
        $("#stationFilter").html("<option value=\"\">All Stations</option>").val("");
        
        if (divisionId) {
            $.ajax({
                url: "' . url('/ammunition/get-districts') . '",
                method: "GET",
                data: { division_id: divisionId },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        var options = "<option value=\"\">All Districts</option>";
                        response.districts.forEach(function(district) {
                            options += "<option value=\"" + district.id + "\">" + district.district_name + "</option>";
                        });
                        $("#districtFilter").html(options);
                    }
                }
            });
        }
        
        filterRows();
    });
    
    // District filter change
    $("#districtFilter").change(function() {
        var districtId = $(this).val();
        
        $("#stationFilter").html("<option value=\"\">All Stations</option>").val("");
        
        if (districtId) {
            var options = "<option value=\"\">All Stations</option>";
            allStations.forEach(function(station) {
                if (station.district_id == districtId) {
                    options += "<option value=\"" + station.id + "\">" + station.station_name + "</option>";
                }
            });
            $("#stationFilter").html(options);
        }
        
        filterRows();
    });
    
    // Station filter change
    $("#stationFilter").change(function() {
        filterRows();
    });
    
    // Clear filters button
    $("#clearFilters").click(function() {
        $("#typeFilter").val("");
        $("#regionFilter").val("");
        $("#divisionFilter").html("<option value=\"\">All Divisions</option>").val("");
        $("#districtFilter").html("<option value=\"\">All Districts</option>").val("");
        $("#stationFilter").html("<option value=\"\">All Stations</option>").val("");
        $(".ammo-row").show();
        updateFilterHelp();
        updateSummaryCards();
    });
    
    // Allocate modal level change handler
    $("[id^=allocateLevel]").change(function() {
        var modalId = $(this).attr("id").replace("allocateLevel", "");
        var level = $(this).val();
        
        // Hide all location selects
        $("#allocateRegion" + modalId).hide().find("select").prop("name", "");
        $("#allocateDivision" + modalId).hide().find("select").prop("name", "");
        $("#allocateDistrict" + modalId).hide().find("select").prop("name", "");
        $("#allocateStation" + modalId).hide().find("select").prop("name", "");
        
        // Show appropriate location select based on level
        switch(level) {
            case "Region":
                $("#allocateRegion" + modalId).show().find("select").prop("name", "to_location_id");
                break;
            case "Division":
                $("#allocateDivision" + modalId).show();
                loadAllocateDivisions(modalId);
                break;
            case "District":
                $("#allocateDistrict" + modalId).show();
                loadAllocateDistricts(modalId);
                break;
            case "Station":
                $("#allocateStation" + modalId).show().find("select").prop("name", "to_location_id");
                loadAllocateStations(modalId);
                break;
        }
    });
    
    // Load divisions for allocation (load all divisions)
    function loadAllocateDivisions(modalId) {
        var options = "<option value=\"\">Select Division</option>";
        allDivisions.forEach(function(division) {
            options += "<option value=\"" + division.id + "\">" + division.division_name + "</option>";
        });
        $("#allocateDivisionSelect" + modalId).html(options).prop("name", "to_location_id");
    }
    
    // Load districts for allocation (load all districts)
    function loadAllocateDistricts(modalId) {
        var options = "<option value=\"\">Select District</option>";
        allDistricts.forEach(function(district) {
            options += "<option value=\"" + district.id + "\">" + district.district_name + "</option>";
        });
        $("#allocateDistrictSelect" + modalId).html(options).prop("name", "to_location_id");
    }
    
    // Load stations for allocation (load all stations)
    function loadAllocateStations(modalId) {
        var options = "<option value=\"\">Select Station</option>";
        allStations.forEach(function(station) {
            options += "<option value=\"" + station.id + "\">" + station.station_name + "</option>";
        });
        $("#allocateStationSelect" + modalId).html(options);
    }
});
</script>';

include __DIR__ . '/../layouts/main.php';
?>
