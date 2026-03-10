# Biometrics Migration Guide - Person-Based System

## Overview
This migration converts the biometric system from suspect-based to person-based storage. This ensures biometric data persists regardless of a person's case involvement status.

## Why This Change?
- **Person ≠ Suspect**: A person's status as "suspect" is case-specific and temporary
- **Permanent Data**: Biometrics should persist even if person is cleared from suspicion
- **No Duplication**: Same person in multiple cases should use same biometrics
- **Consistent Identity**: Biometrics available across all cases and roles (suspect, witness, etc.)

## Migration Steps

### Step 1: Backup Current Data
```sql
-- Backup the current suspect_biometrics table
CREATE TABLE suspect_biometrics_backup_20251228 AS SELECT * FROM suspect_biometrics;
```

### Step 2: Run Migration Script
Execute the migration SQL file:
```bash
mysql -u root -p ghpims < database/migrations/migrate_biometrics_to_person_based.sql
```

Or via phpMyAdmin:
1. Open phpMyAdmin
2. Select `ghpims` database
3. Go to SQL tab
4. Copy contents of `database/migrations/migrate_biometrics_to_person_based.sql`
5. Execute

### Step 3: Verify Migration
```sql
-- Check record counts match
SELECT 
    'Migration Summary' as info,
    (SELECT COUNT(*) FROM person_biometrics) as new_records,
    (SELECT COUNT(*) FROM suspect_biometrics_backup) as old_records;

-- Verify no data loss
SELECT 
    COUNT(DISTINCT sb.id) as old_count,
    COUNT(DISTINCT pb.id) as new_count
FROM suspect_biometrics_backup sb
LEFT JOIN suspects s ON sb.suspect_id = s.id
LEFT JOIN person_biometrics pb ON pb.person_id = s.person_id 
    AND pb.biometric_type = sb.biometric_type
    AND pb.captured_at = sb.captured_at;
```

### Step 4: Update File Paths (if needed)
The migration updates storage paths from:
- `storage/biometrics/suspects/{suspect_id}/` 
To:
- `storage/biometrics/persons/{person_id}/`

If you have existing files, run this script to move them:
```bash
# This will be handled automatically by the application
# Old files will still be accessible during transition
```

## What Changed

### Database Schema
- **Table**: `suspect_biometrics` → `person_biometrics`
- **Foreign Key**: `suspect_id` → `person_id`
- **References**: `suspects.id` → `persons.id`

### Model
- **Class**: `SuspectBiometric` → `PersonBiometric`
- **Methods**: `getBySuspectId()` → `getByPersonId()`

### Controller
- **BiometricController**: Now fetches `person_id` from suspect and stores biometrics against person
- **Storage Path**: Updated to use person_id in file paths

### Views
- **capture_biometrics.php**: Queries `person_biometrics` table using `person_id`

## Testing Checklist

After migration, verify:

- [ ] Existing biometrics are visible on person profile
- [ ] Existing biometrics are visible when viewing suspect in case
- [ ] Can capture new biometrics for a suspect
- [ ] New biometrics save to person_biometrics table
- [ ] Biometrics persist if person is cleared from case
- [ ] Same person in multiple cases shows same biometrics
- [ ] Export fingerprint sheet still works
- [ ] Bulk fingerprint upload works
- [ ] Green checkmark shows in case view when biometrics exist

## Rollback Plan

If issues occur, you can rollback:

```sql
-- Restore old table
DROP TABLE IF EXISTS person_biometrics;
RENAME TABLE suspect_biometrics_backup TO suspect_biometrics;

-- Revert code changes using git
git checkout HEAD -- app/Models/PersonBiometric.php
git checkout HEAD -- app/Controllers/BiometricController.php
git checkout HEAD -- views/suspects/capture_biometrics.php
```

## Benefits After Migration

1. **Data Integrity**: Biometrics never lost when person cleared from case
2. **Efficiency**: No duplicate biometric captures for same person
3. **Consistency**: Same biometrics across all cases
4. **Scalability**: Person can be suspect, witness, complainant - biometrics always available
5. **Compliance**: Better audit trail for biometric data

## Support

If you encounter issues:
1. Check `storage/logs/app.log` for errors
2. Verify person_id exists for all suspects
3. Ensure file permissions on storage/biometrics/persons/
4. Check database foreign key constraints

## Next Steps

After successful migration:
1. Monitor application for 24-48 hours
2. Verify no errors in logs
3. Drop backup table after confirming stability:
   ```sql
   DROP TABLE suspect_biometrics_backup;
   ```
