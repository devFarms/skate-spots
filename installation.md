# Skate Spots Plugin - Quick Installation Guide

## Step 1: Upload Plugin Files

Create this exact folder structure in `/wp-content/plugins/`:

```
skate-spots/
├── skate-spots.php
├── README.md
├── INSTALLATION.md
├── includes/
│   ├── class-database.php
│   ├── class-spots.php
│   ├── class-movies.php
│   ├── class-scenes.php
│   ├── class-skaters.php
│   ├── class-shortcodes.php
│   ├── class-frontend-forms.php
│   ├── class-user-auth.php
│   └── class-admin.php
├── admin/
│   ├── css/
│   │   └── admin-styles.css
│   └── views/
│       ├── spots-list.php
│       ├── movies-list.php
│       ├── scenes-list.php
│       └── skaters-list.php
├── public/
│   ├── css/
│   │   └── frontend-styles.css
│   └── js/
│       └── frontend-scripts.js
└── assets/
    └── js/
        └── map.js
```

## Step 2: Activate Plugin

1. Go to **WordPress Admin → Plugins**
2. Find "Skate Spots"
3. Click **Activate**
4. Database tables will be created automatically

## Step 3: Create Pages

Create the following pages with these shortcodes:

### Display Pages

**Map Page** (slug: `skate-map`)
```
[skate_spots_map height="600px"]
```

**Movies** (slug: `movies`)
```
[skate_movies_list]
```

**Scenes** (slug: `scenes`)
```
[skate_scenes_list]
```

**Skaters** (slug: `skaters`)
```
[skate_skaters_list]
```

### Submission Pages

**Submit Spot** (slug: `submit-spot`)
```
[skate_spot_form]
```

**Submit Movie** (slug: `submit-movie`)
```
[skate_movie_form]
```

**Submit Scene** (slug: `submit-scene`)
```
[skate_scene_form]
```

**Submit Skater** (slug: `submit-skater`)
```
[skate_skater_form]
```

### User Pages

**Register** (slug: `register`)
```
[skate_register_form]
```

**Login** (slug: `login`)
```
[skate_login_form]
```

**Logout Link** (add to navigation menu or widget)
```
[skate_logout_link text="Logout"]
```

## Step 4: Configure Menu

Create a navigation menu with these pages:
- Map
- Movies
- Scenes
- Skaters
- Submit Spot
- Submit Movie
- Submit Scene
- Submit Skater
- Register/Login

## Step 5: Test Functionality

### Test Submission Workflow

1. **As Non-Logged-In User:**
   - Go to "Submit Spot"
   - Fill out form and submit
   - Should see: "Spot submitted successfully! It will be reviewed by an administrator."
   - Status = Pending

2. **As Administrator:**
   - Go to **Admin → Skate Spots → Spots**
   - See pending submission
   - Click "Approve"
   - Entry now shows on map

3. **As Editor:**
   - Login with Editor role
   - Submit a spot
   - Should see: "Spot successfully added!"
   - Appears immediately (bypasses approval)

### Test User Registration

1. Go to Register page
2. Create an account
3. User is automatically logged in
4. User cannot access wp-admin (redirected to home)

### Test Map

1. Create at least one approved spot with coordinates
2. Go to Map page
3. Verify spot appears on map
4. Click marker to see popup

## Step 6: Admin Interface

Access through **WordPress Admin → Skate Spots**

### Menu Items:
- **Spots**: Manage skate spot submissions
- **Movies**: Manage movie submissions
- **Scenes**: Manage scene submissions
- **Skaters**: Manage skater submissions

### For Each Admin Page:
- Filter by status (All, Pending, Approved, Rejected)
- Approve/Reject/Delete entries
- View submission details

## Common Setup Tasks

### Make a User an Editor

1. Go to **Users → All Users**
2. Click on username
3. Change "Role" to "Editor"
4. Save
5. This user can now auto-approve their own submissions

### Customize Spot Types

Edit `includes/class-frontend-forms.php` and find the spot_type dropdown:

```php
<option value="custom-type">Custom Type</option>
```

### Change Map Default Center

Edit `includes/class-shortcodes.php` and find:

```javascript
var map = L.map('skate-spots-map').setView([37.7749, -122.4194], 4);
```

Change coordinates and zoom level as needed.

## Verification Checklist

- [ ] Plugin activated successfully
- [ ] Admin menu "Skate Spots" appears
- [ ] Database tables created (check phpMyAdmin)
- [ ] Map page displays with Leaflet map
- [ ] Forms display correctly
- [ ] Can submit spot (pending status)
- [ ] Can approve from admin
- [ ] Approved spot appears on map
- [ ] User registration works
- [ ] Login/logout works
- [ ] Non-admins redirected from wp-admin

## Database Tables

Verify these tables exist in your database:

- `wp_skate_spots`
- `wp_skate_movies`
- `wp_skate_scenes`
- `wp_skate_skaters`
- `wp_skate_skater_scenes`

(Replace `wp_` with your actual table prefix)

## Troubleshooting

**Tables not created?**
- Deactivate and reactivate plugin
- Check database user permissions

**Forms not working?**
- Check browser console for JavaScript errors
- Clear browser and WordPress cache
- Verify jQuery is loaded

**Map not showing?**
- Add at least one approved spot with valid coordinates
- Check browser console for Leaflet errors
- Verify internet connection (Leaflet loads from CDN)

**Admin pages empty?**
- Verify template files exist in `admin/views/`
- Check file permissions

## Next Steps

1. Add your first skate spot
2. Customize the CSS to match your theme
3. Set up user roles and permissions
4. Add navigation menus
5. Test the approval workflow
6. Launch your site!

## Support

Refer to README.md for detailed documentation on:
- Customization options
- Security features
- Development guidelines
- Complete feature list