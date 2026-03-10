# Ghana Police Integrated Management System (GHPIMS)

A comprehensive case management system for the Ghana Police Service.

## Quick Start

### New Installation
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE ghpims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import improved schema
mysql -u root -p ghpims < db_improved.sql
```

### Upgrading Existing Database
```bash
# Backup first!
mysqldump -u root -p ghpims > backup_$(date +%Y%m%d).sql

# Run migration
mysql -u root -p ghpims < migration.sql
```

## Files

- **`db.sql`** - Original database schema
- **`db_improved.sql`** - Enhanced schema with all improvements (recommended)
- **`migration.sql`** - Upgrade script for existing databases
- **`DATABASE_DOCUMENTATION.md`** - Complete technical documentation
- **`IMPROVEMENTS_SUMMARY.md`** - Summary of latest enhancements

## Key Features

### Units / Departments
- 20 pre-loaded specialized unit types (CID, Traffic, SWAT, K-9, etc.)
- Create units at any level (station, district, division, region)
- Hierarchical unit structure with parent-child relationships
- Unit head assignment and officer assignments
- Permanent, temporary, and secondment assignments
- Complete assignment history tracking
- Position-specific roles within units

### Officers Management
- Complete officer database with 15 Ghana Police Service ranks
- Posting and transfer history tracking
- Promotion records with official orders
- Leave management (Annual, Sick, Maternity, etc.)
- Disciplinary records and investigations
- Training and certification tracking
- Awards and commendations
- Emergency contact and medical information

### Case Management
- Complete case lifecycle tracking
- Priority levels and deadlines
- Status history audit trail
- Case assignments and referrals

### Investigation Tools
- Suspect tracking with status history
- Arrest and custody management
- Evidence with chain of custody
- Witness and statement recording

### Legal Proceedings
- Court proceeding tracking
- Bail and custody records
- Charge documentation
- Verdict and sentencing records

### Security & Compliance
- Role-based access control
- Session management
- Comprehensive audit logging
- Evidence integrity verification

### Organizational Structure
- Hierarchical police structure (Region → Division → District → Station)
- User assignments at any level
- Data access based on hierarchy

## Database Improvements

### Performance
- 50+ strategic indexes for fast queries
- Optimized for hierarchical data access
- Efficient JOIN operations

### Security
- Password hashing support
- Failed login tracking
- Account lockout mechanism
- Two-factor authentication ready
- Complete audit trail with JSON change tracking

### Data Integrity
- Foreign key constraints
- Unique constraints to prevent duplicates
- NOT NULL on critical fields
- Default values for consistency

### New Features
- **Court Proceedings** - Complete court case management
- **Notifications** - Real-time user alerts
- **Case Assignments** - Formal officer assignment tracking
- **Evidence Chain of Custody** - Legal compliance
- **Case Status History** - Complete audit trail
- **Enhanced Audit Logs** - JSON-based change tracking

## Default Roles

1. **Super Admin** - Full system access
2. **Regional Commander** - Regional level access
3. **Divisional Commander** - Divisional level access
4. **District Commander** - District level access
5. **Station Officer** - Station level access
6. **Investigator** - Case investigation access
7. **Records Officer** - Records management access
8. **Evidence Officer** - Evidence management access

## System Requirements

- MySQL 5.7+ or MariaDB 10.2+
- InnoDB storage engine
- UTF8MB4 character set support

## Documentation

See `DATABASE_DOCUMENTATION.md` for:
- Complete table descriptions
- Relationship diagrams
- Security implementation guide
- Performance optimization tips
- Common query examples
- Best practices
- Maintenance procedures

## Support

For technical details, refer to the comprehensive documentation in `DATABASE_DOCUMENTATION.md`.

---

**Version:** 2.0 (Enhanced)  
**Last Updated:** December 2024
