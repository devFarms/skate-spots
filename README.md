# Skate Spots WordPress Plugin

A comprehensive WordPress plugin for managing skate spots, movies, scenes, and skaters with custom database tables, front-end submission forms, and admin moderation.

## Features

- **Custom Database Tables**: Normalized database structure with proper relationships
- **Frontend Forms**: Anyone can submit spots, movies, scenes, and skaters
- **Admin Moderation**: Approve, reject, or delete submissions
- **User Authentication**: Custom login/registration (no admin access for regular users)
- **Role-Based Permissions**: Editors+ can bypass approval process
- **Interactive Map**: Display all approved skate spots on a Leaflet map
- **Shortcodes**: Easy embedding of maps and lists on any page
- **AJAX Submissions**: Smooth form submissions without page reload

## File Structure

```
skate-spots/
├── skate-spots.php              # Main plugin file
├── README.md                    # This file
├── includes/                    # Core functionality
│   ├── class-database.php       # Database table creation
│   ├── class-spots.php          # Spots CRUD operations
│   ├── class-movies.php         # Movies CRUD operations
│   ├── class-scenes.php         # Scenes CRUD operations
│   ├── class-skaters.php        # Skaters CRUD operations
│   ├── class-shortcodes.php     # Shortcode handlers
│   ├── class-frontend-forms.php # Frontend form handlers
│   ├── class-user-auth.php      # User registration/login
│   └── class-admin.php          # Admin interface
├── admin/                       # Admin interface files
│   ├── css/
│   │   └── admin-styles.css     # Admin styling
│   └── views/                   # Admin templates
│       ├── spots-list.php       # Spots admin page
│       ├── movies-list.php      # Movies admin page
│       ├── scenes-list.php      # Scenes admin page
│       └── skaters-list.php     # Skaters admin page
├── public/                      # Frontend files
│   ├── css/
│   │   └── frontend-styles.css  # Frontend styling
│   └── js/
│       └── frontend-scripts.js  # Frontend JavaScript
└── assets/                      # Additional assets
    └── js/
        └── map.js               # Map utilities
```

## Installation

1. **Upload the Plugin**
   - Upload the `skate-spots` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

2. **Activate the Plugin**
   - Go to WordPress admin → Plugins
   - Find "Skate Spots" and click "Activate"
   - Database tables will be created automatically

3. **Verify Installation**
   - Check WordPress admin menu for "Skate Spots" item
   - Verify tables were created in database:
     - `wp_skate_spots`
     - `wp_skate_movies`
     - `wp_skate_scenes`
     - `wp_skate_skaters`
     - `wp_skate_skater_scenes`

## Database Structure

### Tables

**wp_skate_spots**
- Stores skate spot locations with GPS coordinates
- Fields: id, title, description, latitude, longitude, address, city, country, spot_type, image_url, status, created_at, updated_at

**wp_skate_movies**
- Stores skateboard movie information
- Fields: id, title, release_date, description, director, status, created_at, updated_at

**wp_skate_scenes**
- Links movies to spots (where scenes were filmed)
- Fields: id, movie_id, spot_id, scene_title, scene_description, video_url, timestamp, status, created_at, updated_at

**wp_skate_skaters**
- Stores skater profiles
- Fields: id, name, bio, social_url, status, created_at, updated_at

**wp_skate_skater_scenes**
- Links skaters to scenes (many-to-many relationship)
- Fields: skater_id, scene_id

### Status Values
- `0` = Pending (awaiting approval)
- `1` = Approved (visible on frontend)
- `2` = Rejected (hidden)

## Shortcodes

### Map Shortcode
Display an interactive map with all approved spots:
```
[skate_spots_map height="600px"]
```

### Movies List
Display all approved movies:
```
[skate_movies_list limit="50"]
```

### Scenes List
Display all approved scenes:
```
[skate_scenes_list]
```

### Skaters List
Display all approved skaters:
```
[skate_skaters_list]
```

### User Registration
Display registration form:
```
[skate_register_form]
```

### User Login
Display login form:
```
[skate_login_form]
```

### Logout Link
Display logout link:
```
[skate_logout_link text="Sign Out"]
```

### Submission Forms

**Submit a Spot:**
```
[skate_spot_form]
```

**Submit a Movie:**
```
[skate_movie_form]
```

**Submit a Scene:**
```
[skate_scene_form]
```

**Submit a Skater:**
```
[skate_skater_form]
```

## Usage Guide

### For Site Visitors

1. **Register an Account**
   - Create a page and add `[skate_register_form]`
   - Users register but cannot access wp-admin

2. **Login**
   - Create a page and add `[skate_login_form]`
   - Logged-in users see personalized content

3. **Submit Content**
   - Create pages for each form type
   - Submissions go to "Pending" status
   - Users see confirmation message

### For Editors/Admins

1. **Bypass Approval**
   - Editors and Administrators can submit content that's auto-approved
   - Their submissions skip the moderation queue

2. **Manage Submissions**
   - Go to WordPress admin → Skate Spots menu
   - View all submissions by status
   - Approve, reject, or delete entries

3. **Assign Roles**
   - Go to Users → All Users
   - Edit a user and change their role to "Editor" for bypass approval

### Setting Up Your Site

**Recommended Page Structure:**

1. **Map Page** (`/skate-map/`)
   - Add shortcode: `[skate_spots_map]`

2. **Movies Page** (`/movies/`)
   - Add shortcode: `[skate_movies_list]`

3. **Scenes Page** (`/scenes/`)
   - Add shortcode: `[skate_scenes_list]`

4. **Skaters Page** (`/skaters/`)
   - Add shortcode: `[skate_skaters_list]`

5. **Submit Pages**
   - `/submit-spot/` → `[skate_spot_form]`
   - `/submit-movie/` → `[skate_movie_form]`
   - `/submit-scene/` → `[skate_scene_form]`
   - `/submit-skater/` → `[skate_skater_form]`

6. **User Pages**
   - `/register/` → `[skate_register_form]`
   - `/login/` → `[skate_login_form]`

## Admin Interface

Access admin pages through **WordPress Admin → Skate Spots**:

- **Spots**: View and manage all spot submissions
- **Movies**: View and manage all movie submissions
- **Scenes**: View and manage all scene submissions
- **Skaters**: View and manage all skater submissions

### Filtering
Use the status tabs to filter:
- All: View all entries
- Pending: View entries awaiting approval
- Approved: View published entries
- Rejected: View rejected entries

### Actions
For each entry:
- **Approve**: Set status to approved (visible on frontend)
- **Reject**: Set status to rejected (hidden)
- **Delete**: Permanently remove from database

## Security Features

- **Nonce Verification**: All forms use WordPress nonces
- **Data Sanitization**: All inputs are sanitized
- **AJAX Protection**: AJAX calls require nonces
- **Admin Access Restricted**: Non-editors can't access wp-admin
- **SQL Injection Prevention**: Uses $wpdb prepared statements
- **XSS Prevention**: Output escaped with esc_html(), esc_url(), etc.

## Customization

### Styling
- Frontend: Edit `public/css/frontend-styles.css`
- Admin: Edit `admin/css/admin-styles.css`

### Functionality
- Add custom fields to forms in `class-frontend-forms.php`
- Modify database structure in `class-database.php`
- Customize CRUD operations in individual class files

### Add New Spot Types
Edit the spot type dropdown in `class-frontend-forms.php`:
```php
<option value="custom">Custom Type</option>
```

## Development

### Adding New Features

1. **New Database Table**
   - Add table creation in `class-database.php`
   - Create new CRUD class file
   - Add to main plugin file

2. **New Shortcode**
   - Add method in `class-shortcodes.php`
   - Register in constructor

3. **New Form**
   - Add form method in `class-frontend-forms.php`
   - Add AJAX handler
   - Register shortcode

## Troubleshooting

### Tables Not Created
- Deactivate and reactivate plugin
- Check database permissions
- Verify `wp_` prefix matches your installation

### Forms Not Submitting
- Check browser console for JavaScript errors
- Verify AJAX URL is correct
- Clear browser cache

### Map Not Loading
- Check if Leaflet CSS/JS are loading
- Verify spots have valid latitude/longitude
- Check browser console for errors

### Permissions Issues
- Verify user roles are correct
- Check if user is properly logged in
- Clear WordPress cache

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

## Support

For issues or questions:
1. Check the troubleshooting section
2. Verify all files are uploaded correctly
3. Check WordPress debug.log for errors

## License

GPL v2 or later

## Credits

- Built with WordPress best practices
- Uses Leaflet.js for mapping
- Custom database architecture