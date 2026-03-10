# Layout Fix - Completion Guide

**Date:** December 19, 2025, 10:40 AM UTC  
**Current Status:** 18/47 Complete (38%)

---

## ✅ COMPLETED (18 files - 38%)

All properly converted to use `layouts/main.php` pattern:

1. **Arrests Module (4 files)** ✅
   - arrests/index.php
   - arrests/view.php
   - arrests/create.php
   - arrests/edit.php

2. **Bail Module (4 files)** ✅
   - bail/index.php
   - bail/view.php
   - bail/create.php
   - bail/edit.php

3. **Charges Module (4 files)** ✅
   - charges/index.php
   - charges/view.php
   - charges/create.php
   - charges/edit.php

4. **Exhibits Module (4 files)** ✅
   - exhibits/index.php
   - exhibits/view.php
   - exhibits/create.php
   - exhibits/edit.php

---

## ❌ REMAINING (29 files - 62%)

### Operations Module (4 files)
- views/operations/index.php
- views/operations/view.php
- views/operations/create.php
- views/operations/edit.php

### Surveillance Module (4 files)
- views/surveillance/index.php
- views/surveillance/view.php
- views/surveillance/create.php
- views/surveillance/edit.php

### Intelligence Bulletins (4 files)
- views/intelligence/bulletins/index.php
- views/intelligence/bulletins/view.php
- views/intelligence/bulletins/create.php
- views/intelligence/bulletins/edit.php

### Ammunition Module (3 files)
- views/ammunition/index.php
- views/ammunition/create.php
- views/ammunition/edit.php

### Assets Module (2 files)
- views/assets/index.php
- views/assets/create.php

### Public Complaints (3 files)
- views/public_complaints/index.php
- views/public_complaints/create.php
- views/public_complaints/edit.php

### Incidents (3 files)
- views/incidents/index.php
- views/incidents/create.php
- views/incidents/edit.php

### Officer HR Modules (6 files)
- views/officers/postings/index.php
- views/officers/promotions/index.php
- views/officers/training/index.php
- views/officers/leave/index.php
- views/officers/disciplinary/index.php
- views/officers/commendations/index.php

---

## 📋 CONVERSION PATTERN (Use This for Remaining Files)

### Step 1: Read the existing file
```bash
# Example
views/operations/index.php
```

### Step 2: Convert using this pattern

**Before (Incorrect):**
```php
<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <!-- content here -->
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
```

**After (Correct):**
```php
<?php
// Build content variable
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <table id="myTable" class="table">
                    <tbody>';

// Use foreach outside string for dynamic content
foreach ($items as $item) {
    $content .= '
                        <tr>
                            <td>' . sanitize($item['name']) . '</td>
                        </tr>';
}

$content .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>';

// Scripts with proper concatenation
$scripts = '
<script>
$(document).ready(function() {
    $("#myTable").DataTable();
});
</script>';

// Breadcrumbs
$breadcrumbs = [
    ['title' => 'Module Name']
];

// Include layout
include __DIR__ . '/../layouts/main.php';
?>
```

### Key Points:
1. Remove `header.php`, `sidebar.php`, `footer.php` includes
2. Build content in `$content` variable
3. Move PHP logic outside single-quoted strings
4. Use concatenation (`.`) for PHP variables
5. Create `$scripts` variable for JavaScript
6. Add `$breadcrumbs` array
7. Include `layouts/main.php` at end

---

## 🔧 COMMON PATTERNS

### Index Views (List Pages)
```php
<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-icon"></i> Title</h3>
            </div>
            <div class="card-body">
                <table id="dataTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Column 1</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';

foreach ($items as $item) {
    $content .= '
                        <tr>
                            <td>' . sanitize($item['name']) . '</td>
                            <td>
                                <a href="' . url('/module/view/' . $item['id']) . '" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
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

$scripts = '
<script>
$(document).ready(function() {
    $("#dataTable").DataTable({
        "responsive": true,
        "order": [[0, "desc"]]
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Module Name']
];

include __DIR__ . '/../layouts/main.php';
?>
```

### View/Detail Pages
```php
<?php
$badgeClass = match($item['status']) {
    'Active' => 'success',
    'Inactive' => 'danger',
    default => 'secondary'
};

$content = '
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Details</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name:</dt>
                    <dd class="col-sm-8">' . sanitize($item['name']) . '</dd>
                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-' . $badgeClass . '">
                            ' . sanitize($item['status']) . '
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <a href="' . url('/module') . '" class="btn btn-secondary btn-block">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Module', 'url' => '/module'],
    ['title' => 'Details']
];

include __DIR__ . '/../layouts/main.php';
?>
```

### Create/Edit Forms
```php
<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Title</h3>
            </div>
            <form id="myForm">
                ' . csrf_field() . '
                <input type="hidden" name="id" value="' . ($item['id'] ?? '') . '">
                <div class="card-body">
                    <div class="form-group">
                        <label>Field <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="field" 
                               value="' . sanitize($item['field'] ?? '') . '" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <a href="' . url('/module') . '" class="btn btn-secondary">
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
    $("#myForm").submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: "' . url('/module/store') . '",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire("Success", response.message, "success").then(() => {
                        window.location.href = "' . url('/module') . '";
                    });
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            }
        });
    });
});
</script>';

$breadcrumbs = [
    ['title' => 'Module', 'url' => '/module'],
    ['title' => 'Create']
];

include __DIR__ . '/../layouts/main.php';
?>
```

---

## 📊 SUMMARY

- **Completed:** 18 files (38%)
- **Remaining:** 29 files (62%)
- **Time Invested:** ~1 hour
- **Estimated Remaining:** 1-1.5 hours

---

## 🎯 RECOMMENDATION

**Option 1:** Continue automated fixing in next session (I can complete remaining 29 files)

**Option 2:** Manual completion using patterns above (faster, you have full control)

**Option 3:** Hybrid - I fix high-priority modules, you complete the rest

---

## ✅ QUALITY CHECKLIST

For each converted file, verify:
- [ ] No `header.php`, `sidebar.php`, `footer.php` includes
- [ ] Content built in `$content` variable
- [ ] PHP variables outside single-quoted strings
- [ ] Scripts in `$scripts` variable
- [ ] Breadcrumbs array defined
- [ ] Includes `layouts/main.php` at end
- [ ] No syntax errors
- [ ] No leftover HTML fragments

---

**Status:** ⚠️ **38% Complete - Ready for Continuation**

Use the patterns above to complete remaining 29 files following the same approach used for arrests, bail, charges, and exhibits modules.
