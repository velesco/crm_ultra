# Static Analysis Summary (Larastan)

**Generated:** 2025-08-28  
**Tool:** Larastan/PHPStan  
**Level:** 1  
**Files Analyzed:** 138  
**Total Errors:** 1073

## Error Categories

### 1. Missing Facade Imports (High Priority)
**Files Affected:** Multiple controllers  
**Error:** `Call to static method call() on an unknown class Artisan`  
**Solution:** Add `use Illuminate\Support\Facades\Artisan;`

### 2. Model Method Recognition Issues (Medium Priority)
**Files Affected:** All controllers and services using Eloquent  
**Error:** `Call to an undefined static method App\Models\Contact::where()`  
**Note:** This is a false positive - Larastan doesn't recognize Eloquent magic methods

### 3. Critical Files Requiring Attention

#### app/Services/EmailService.php
- 341 errors related to model method calls
- Missing type hints and return types

#### app/Http/Controllers/Admin/AnalyticsController.php  
- 180+ errors related to query builder methods
- Missing imports for facades

#### app/Services/WhatsAppService.php
- 89 errors related to model interactions
- Missing type declarations

#### app/Services/GoogleSheetsService.php
- Multiple undefined method calls
- Missing model imports

## Quick Fixes

### 1. Add Missing Imports
```php
// Add to affected controllers
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
```

### 2. Model Method Issues
Most model-related errors are false positives due to Eloquent's magic methods. Consider adding PHPDoc blocks:

```php
/**
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder create(array $attributes = [])
 */
class Contact extends Model
{
    // ...
}
```

### 3. Configuration Improvement
Create `phpstan.neon` config file:

```neon
includes:
    - ./vendor/nunomaduro/larastan/extension.neon

parameters:
    paths:
        - app

    level: 5
    ignoreErrors:
        - '#Call to an undefined static method App\\Models\\.*::(where|create|find).*#'
    
    excludes_analyse:
        - *.blade.php
```

## Recommended Actions

1. **Immediate:** Fix missing facade imports (20+ files)
2. **Short-term:** Add type hints and return types to services
3. **Long-term:** Configure PHPStan to better handle Laravel patterns
4. **Optional:** Add PHPDoc blocks to models for better IDE support