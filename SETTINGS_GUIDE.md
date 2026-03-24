# Site Settings Management Guide

## Overview
The admin panel now includes a CRUD system for managing site settings, including the statistics displayed on the About page.

## Features
- Manage statistics numbers (Students, Courses, Instructors, Partners)
- Update site name and description
- Settings are cached for better performance
- Easy-to-use admin interface

## How to Use

### Accessing Settings
1. Login as Admin
2. Go to Admin Dashboard
3. Click on "Settings" in the navigation menu
4. URL: `http://127.0.0.1:8000/admin/settings`

### Updating Statistics
The About page statistics can be updated from the admin settings:

- **Students Count**: Number of students to display
- **Courses Count**: Number of courses to display
- **Instructors Count**: Number of instructors to display
- **Partners Count**: Number of partners to display

### Steps to Update:
1. Navigate to Admin Settings
2. Find the "About Stats" section
3. Update the numbers as needed
4. Click "Save Settings"
5. The changes will appear immediately on the About page

## Technical Details

### Database Table
- Table: `site_settings`
- Fields:
  - `key`: Unique identifier for the setting
  - `value`: The setting value
  - `type`: Data type (text, number, textarea, image)
  - `group`: Group name for organizing settings
  - `label`: Display label
  - `description`: Help text

### Available Settings
- `about_students_count`: Students count on About page
- `about_courses_count`: Courses count on About page
- `about_instructors_count`: Instructors count on About page
- `about_partners_count`: Partners count on About page
- `site_name`: Website name
- `site_description`: Website description

### Caching
Settings are cached for 1 hour to improve performance. The cache is automatically cleared when settings are updated.

### Adding New Settings
To add new settings, use the SiteSetting model:

```php
\App\Models\SiteSetting::set(
    'new_setting_key',
    'default_value',
    'text', // type
    'general', // group
    'Setting Label',
    'Setting description'
);
```

### Getting Settings in Views
```php
{{ \App\Models\SiteSetting::get('setting_key', 'default_value') }}
```

## Files Modified
1. `database/migrations/2026_03_23_172702_create_site_settings_table.php` - Database migration
2. `app/Models/SiteSetting.php` - Model for site settings
3. `database/seeders/SiteSettingSeeder.php` - Default settings seeder
4. `app/Http/Controllers/AdminController.php` - Added settings methods
5. `routes/web.php` - Updated settings routes
6. `resources/views/admin/settings.blade.php` - Admin settings page
7. `resources/views/pages/about.blade.php` - Updated to use dynamic settings

## Support
For any issues or questions, please contact the development team.
