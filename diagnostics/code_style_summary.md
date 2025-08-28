# Code Style Analysis Summary

**Generated:** 2025-08-28  
**Tool:** Laravel Pint  
**Total Files Checked:** 242  
**Total Issues Found:** 148+ style violations

## Most Common Issues

1. **class_attributes_separation** - Missing blank lines between class attributes
2. **concat_space** - Incorrect spacing around string concatenation operators
3. **no_unused_imports** - Unused import statements present
4. **trailing_comma_in_multiline** - Missing trailing commas in multiline arrays/functions
5. **single_space_around_construct** - Incorrect spacing around language constructs
6. **method_chaining_indentation** - Incorrect indentation in method chaining

## Files with Most Issues

### Controllers
- `app/Http/Controllers/Admin/ComplianceController.php`
- `app/Http/Controllers/Admin/RevenueController.php` 
- `app/Http/Controllers/CustomReportController.php`
- `app/Http/Controllers/ExportController.php`

### Models
- `app/Models/Revenue.php`
- `app/Models/CustomReport.php`
- `app/Models/ExportRequest.php`

### Services
- `app/Services/AdminService.php`
- `app/Services/BackupService.php`
- `app/Services/EmailService.php`
- `app/Services/WhatsAppService.php`

### Migrations
- Multiple migration files have formatting issues
- Most common: `class_definition`, `braces_position`, `no_whitespace_in_blank_line`

## Resolution

Run the following command to auto-fix all style issues:

```bash
php vendor/bin/pint
```

This will automatically format all PHP files according to Laravel coding standards.