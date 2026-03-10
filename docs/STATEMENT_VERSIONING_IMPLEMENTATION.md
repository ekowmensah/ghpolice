# Statement Versioning Implementation Guide

## Database Migration Required

Run the SQL migration file: `db_migrations/add_statement_versioning.sql`

This adds:
- `status` ENUM('active', 'cancelled', 'superseded')
- `parent_statement_id` INT (references parent statement)
- `version` INT (version number)
- `cancelled_at` TIMESTAMP
- `cancelled_by` INT (user who cancelled)
- `cancellation_reason` TEXT

## New Routes Needed

Add to `routes/web.php`:

```php
// Statement management
$router->get('/cases/{id}/statements/{statementId}', 'CaseController@getStatement');
$router->post('/cases/{id}/statements/{statementId}/cancel', 'CaseController@cancelStatement');
```

## Controller Methods Needed

Add to `CaseController.php`:

1. `getStatement($caseId, $statementId)` - Get single statement with details
2. `cancelStatement($caseId, $statementId)` - Cancel a statement
3. Update `addStatement()` to handle parent_statement_id for versioning

## Features Implemented

### 1. Grouped Statement Display
- Statements grouped by type (Complainant/Suspect/Witness)
- Color-coded cards (info/danger/success)
- Preview shows first 150 characters
- Status badges (Active/Cancelled/Superseded)

### 2. View Statement Modal
- Full statement text display
- Shows version number if > 1
- Shows cancellation details if cancelled
- Action buttons for active statements

### 3. Cancel Statement
- Modal with reason textarea
- Marks statement as cancelled
- Preserves statement in database
- Records who cancelled and when

### 4. Rewrite Statement
- Opens new statement modal
- Pre-fills with existing statement text
- Links to parent statement via parent_statement_id
- Increments version number
- Marks parent as superseded

## Workflow

1. **View Statement**: Click eye icon → Modal shows full details
2. **Cancel Statement**: Click "Cancel Statement" → Enter reason → Statement marked cancelled
3. **Rewrite Statement**: Click "Rewrite Statement" → Edit in new modal → Submit → Old version marked superseded, new version created
