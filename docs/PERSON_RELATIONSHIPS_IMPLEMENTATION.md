# Person Relationships Implementation Guide

## Overview
Bidirectional person-to-person relationship system with automatic reciprocal linking.

## Database Migration
**File:** `db_migrations/add_person_relationships.sql`

Run this SQL to create the `person_relationships` table.

## Features Implemented

### ✅ Backend Complete
1. **PersonRelationship Model** - Handles bidirectional relationship logic
2. **Controller Methods** - addRelationship, deleteRelationship
3. **Routes** - POST endpoints for creating and deleting relationships
4. **Automatic Reciprocal Linking** - When A links to B, B automatically links to A

### 📋 Relationship Types Supported

**Family Relationships:**
- Parent/Father/Mother ↔ Child/Son/Daughter
- Grandparent/Grandfather/Grandmother ↔ Grandchild/Grandson/Granddaughter
- Uncle ↔ Nephew/Niece
- Aunt ↔ Nephew/Niece
- Sibling, Twin, Cousin
- Spouse, Partner

**Social Relationships:**
- Friend, Colleague, Neighbor, Acquaintance

**Legal Relationships:**
- Guardian ↔ Ward
- Employer ↔ Employee

### 🔄 How Bidirectional Linking Works

**Example 1 - Symmetric:**
```
Person A links to Person B as "Friend"
→ Automatically creates: Person B is "Friend" of Person A
```

**Example 2 - Asymmetric:**
```
Person A links to Person B as "Father"
→ Automatically creates: Person B is "Child" of Person A
```

**Example 3 - Extended Family:**
```
Person A links to Person B as "Grandmother"
→ Automatically creates: Person B is "Grandchild" of Person A
```

## Frontend Implementation Needed

### 1. Add Relationships Section to Person Profile
Location: `views/persons/profile.php`

Add after existing sections (aliases, alerts, etc.):

```php
<!-- Relationships Section -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> Relationships</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addRelationshipModal">
                <i class="fas fa-plus"></i> Add Relationship
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($relationships)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Related Person</th>
                        <th>Relationship</th>
                        <th>Contact</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relationships as $rel): ?>
                        <tr>
                            <td>
                                <a href="<?= url('/persons/' . $rel['person_id_2']) ?>">
                                    <strong><?= sanitize($rel['related_person_name']) ?></strong>
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= sanitize($rel['relationship_type']) ?></span>
                            </td>
                            <td><?= sanitize($rel['contact'] ?? 'N/A') ?></td>
                            <td><?= sanitize($rel['notes'] ?? '-') ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger" onclick="deleteRelationship(<?= $rel['id'] ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">No relationships recorded</p>
        <?php endif; ?>
    </div>
</div>
```

### 2. Add Relationship Modal

```php
<!-- Add Relationship Modal -->
<div class="modal fade" id="addRelationshipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-users"></i> Add Relationship</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addRelationshipForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-group">
                        <label>Search Person</label>
                        <input type="text" class="form-control" id="relationship_person_search" placeholder="Search by name or Ghana Card...">
                        <input type="hidden" name="related_person_id" id="related_person_id" required>
                        <div id="relationship_search_results" class="mt-2"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Relationship Type <span class="text-danger">*</span></label>
                        <select name="relationship_type" class="form-control" required>
                            <option value="">Select Relationship</option>
                            <?php foreach ($relationship_types as $category => $types): ?>
                                <optgroup label="<?= $category ?>">
                                    <?php foreach ($types as $type): ?>
                                        <option value="<?= $type ?>"><?= $type ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Note:</strong> This will automatically create a reciprocal relationship. For example, if you mark Person A as "Father" of Person B, Person B will automatically be marked as "Child" of Person A.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Relationship
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### 3. Add JavaScript

```javascript
// Search for person to add relationship
$("#relationship_person_search").on("keyup", function() {
    const query = $(this).val();
    if (query.length < 2) {
        $("#relationship_search_results").html("");
        return;
    }
    
    $.ajax({
        url: "<?= url('/persons/search') ?>",
        method: "GET",
        data: { search: query },
        success: function(response) {
            let html = '<div class="list-group">';
            if (response.persons && response.persons.length > 0) {
                response.persons.forEach(function(person) {
                    html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectRelatedPerson(${person.id}, '${person.full_name}'); return false;">
                        <strong>${person.full_name}</strong><br>
                        <small>Ghana Card: ${person.ghana_card_number || 'N/A'}</small>
                    </a>`;
                });
            } else {
                html += '<div class="list-group-item">No persons found</div>';
            }
            html += '</div>';
            $("#relationship_search_results").html(html);
        }
    });
});

function selectRelatedPerson(personId, personName) {
    $("#related_person_id").val(personId);
    $("#relationship_person_search").val(personName);
    $("#relationship_search_results").html("");
}

// Submit relationship form
$("#addRelationshipForm").on("submit", function(e) {
    e.preventDefault();
    
    $.ajax({
        url: "<?= url('/persons/' . $person['id'] . '/relationships') ?>",
        method: "POST",
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert("Failed to create relationship. Please try again.");
        }
    });
});

// Delete relationship
function deleteRelationship(relationshipId) {
    if (!confirm("Are you sure you want to delete this relationship? This will also remove the reciprocal relationship.")) {
        return;
    }
    
    $.ajax({
        url: "<?= url('/persons/relationships/') ?>" + relationshipId + "/delete",
        method: "POST",
        data: { csrf_token: "<?= csrf_token() ?>" },
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert("Failed to delete relationship. Please try again.");
        }
    });
}
```

## Testing Checklist

- [ ] Run database migration
- [ ] Add UI to person profile page
- [ ] Test creating symmetric relationship (Friend)
- [ ] Test creating asymmetric relationship (Parent/Child)
- [ ] Test extended family relationships (Grandmother/Grandchild)
- [ ] Verify reciprocal relationships are created automatically
- [ ] Test deleting relationships (both directions removed)
- [ ] Test preventing self-relationships
- [ ] Test duplicate relationship prevention

## Usage Example

1. Go to Person A's profile
2. Click "Add Relationship"
3. Search for Person B
4. Select "Grandmother" as relationship type
5. Save
6. **Result:** 
   - Person A's profile shows: "Person B - Grandmother"
   - Person B's profile shows: "Person A - Grandchild"

Both relationships created with one action! ✅
