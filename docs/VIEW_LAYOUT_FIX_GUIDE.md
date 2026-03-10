# View Layout Fix Guide

## ❌ INCORRECT PATTERN (What was created)

```php
<?php include __DIR__ . '/../partials/header.php'; ?>
<?php include __DIR__ . '/../partials/sidebar.php'; ?>

<div class="content-wrapper">
    <!-- content here -->
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>
```

**Problem:** Missing DOCTYPE, head, CSS/JS includes, body wrapper

---

## ✅ CORRECT PATTERN (What should be used)

```php
<?php
$content = '
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Title</h3>
            </div>
            <div class="card-body">
                <!-- content here -->
            </div>
        </div>
    </div>
</div>';

$breadcrumbs = [
    ['title' => 'Module Name']
];

include __DIR__ . '/../layouts/main.php';
?>
```

---

## FILES THAT NEED FIXING (47 views)

### Arrests Module
- views/arrests/index.php
- views/arrests/view.php
- views/arrests/create.php
- views/arrests/edit.php

### Bail Module
- views/bail/index.php
- views/bail/view.php
- views/bail/create.php
- views/bail/edit.php

### Charges Module
- views/charges/index.php
- views/charges/view.php
- views/charges/create.php
- views/charges/edit.php

### Exhibits Module
- views/exhibits/index.php
- views/exhibits/view.php
- views/exhibits/create.php
- views/exhibits/edit.php

### Operations Module
- views/operations/index.php
- views/operations/view.php
- views/operations/create.php
- views/operations/edit.php

### Surveillance Module
- views/surveillance/index.php
- views/surveillance/view.php
- views/surveillance/create.php
- views/surveillance/edit.php

### Intelligence Bulletins Module
- views/intelligence/bulletins/index.php
- views/intelligence/bulletins/view.php
- views/intelligence/bulletins/create.php
- views/intelligence/bulletins/edit.php

### Ammunition Module
- views/ammunition/index.php
- views/ammunition/create.php
- views/ammunition/edit.php

### Assets Module
- views/assets/index.php
- views/assets/create.php

### Public Complaints Module
- views/public_complaints/index.php
- views/public_complaints/create.php
- views/public_complaints/edit.php

### Incident Reports Module
- views/incidents/index.php
- views/incidents/create.php
- views/incidents/edit.php

### Officer HR Modules
- views/officers/postings/index.php
- views/officers/promotions/index.php
- views/officers/training/index.php
- views/officers/leave/index.php
- views/officers/disciplinary/index.php
- views/officers/disciplinary/view.php
- views/officers/commendations/index.php
- views/officers/commendations/view.php
- views/officers/biometrics/view.php

---

## QUICK FIX STEPS

1. Open each view file
2. Remove header/sidebar/footer includes
3. Wrap content in `$content = '...'` variable
4. Add `$breadcrumbs` array
5. Add `include __DIR__ . '/../layouts/main.php';` at end
6. Escape PHP variables properly in string

---

## AUTOMATED FIX NEEDED

All 47 views need to be converted from the incorrect pattern to the correct pattern.

**Recommendation:** Create a script or manually fix each file following the correct pattern.
