# Blocksy Child Theme for SciuuuS Kids

A code-based implementation of header and footer for the SciuuuS Kids WordPress site, replacing Elementor Pro dependencies.

## Features

- ✅ **Code-based header** - No Elementor dependency
- ✅ **Code-based footer** - Fully customizable
- ✅ **Content styling** - Beautiful typography for pages and posts ✨ NEW
- ✅ **CSS Variables** - Centralized design system with 146+ variables
- ✅ **Responsive design** - Mobile-first approach
- ✅ **WooCommerce integration** - Cart, account, search
- ✅ **Customizer settings** - Easy configuration
- ✅ **Widget areas** - 4 footer widget columns
- ✅ **Social media links** - Configurable via Customizer
- ✅ **Accessibility ready** - ARIA labels, keyboard navigation
- ✅ **Performance optimized** - Minimal dependencies

## Recent Updates (v1.2.0)

### Content Styling System
Professional typography for all WordPress pages and posts:
- **Page Titles**: Gochi Hand font, 42px
- **Paragraphs**: Quicksand font, 18px, 1.8 line-height
- **Headings**: Complete H1-H6 hierarchy
- **Rich Elements**: Blockquotes, lists, tables, code blocks, images
- **WordPress Blocks**: Full Gutenberg support
- **Responsive**: Optimized for mobile, tablet, desktop

### CSS Variables System
146 custom properties for easy customization:
- Brand colors and typography
- Spacing and layout dimensions
- Effects (shadows, transitions)
- Complete design token system

See `CONTENT-STYLING-GUIDE.md` for full documentation.

## Installation

### Step 1: Upload Theme

1. Download this `blocksy-child` folder
2. Upload to your WordPress installation at: `/wp-content/themes/blocksy-child/`
3. Make sure the parent theme "Blocksy" is installed

**Via FTP/SFTP:**
```bash
# Upload the entire blocksy-child directory to:
/wp-content/themes/blocksy-child/
```

**Via Docker (if using local):**
```bash
# Copy to your Docker volume
docker cp blocksy-child/ <container-name>:/var/www/html/wp-content/themes/
```

### Step 2: Activate Theme

1. Log in to WordPress Admin
2. Go to **Appearance → Themes**
3. Find "Blocksy Child - SciuuuS Kids"
4. Click **Activate**

### Step 3: Configure Menus

1. Go to **Appearance → Menus**
2. Create a new menu or assign existing menu
3. Assign to **Primary Menu** location
4. Create/assign **Footer Menu** (optional)

### Step 4: Configure Widgets

1. Go to **Appearance → Widgets**
2. Add widgets to these areas:
   - **Footer Column 1** - Company info, about text
   - **Footer Column 2** - Quick links, navigation
   - **Footer Column 3** - Customer service links
   - **Footer Column 4** - Contact info, newsletter

### Step 5: Upload Logo

1. Go to **Appearance → Customize**
2. Open **Site Identity**
3. Upload your logo (recommended: 300x100px, transparent PNG)

### Step 6: Configure Social Media

1. Go to **Appearance → Customize**
2. Open **Social Media Links** section
3. Enter your social media URLs:
   - Facebook
   - Instagram
   - Twitter
   - Pinterest
   - YouTube

### Step 7: Customize Colors (Optional)

1. Go to **Appearance → Customize**
2. Open **Theme Colors** section
3. Set:
   - **Primary Color** (default: #ff6b6b)
   - **Secondary Color** (default: #2c3e50)

## File Structure

```
blocksy-child/
├── style.css                      # Main stylesheet with CSS variables (v1.2.0)
├── functions.php                  # Theme functions (v1.2.0)
├── inc/
│   ├── header-custom.php         # Header functionality
│   ├── footer-custom.php         # Footer functionality
│   └── customizer.php            # Customizer settings
├── assets/
│   ├── css/
│   │   ├── header-custom.css     # Header styles (v1.2.0)
│   │   ├── footer-custom.css     # Footer styles (v1.1.0)
│   │   └── content-custom.css    # Content/page styles (v1.0.0) ✨ NEW
│   └── js/
│       └── custom.js             # Custom JavaScript
├── README.md                     # Theme overview (this file)
├── REFACTORING-SUMMARY.md        # CSS variables documentation
├── CONTENT-STYLING-GUIDE.md      # Content styling documentation ✨ NEW
└── Other documentation files
```

## Customization

### Changing Header Layout

Edit: `inc/header-custom.php`

The header is structured in sections:
- **Logo** - Line 34-38
- **Navigation** - Line 40-44
- **WooCommerce Elements** - Line 46-51
- **Mobile Toggle** - Line 53-61

### Changing Footer Layout

Edit: `inc/footer-custom.php`

The footer has:
- **Widget Areas** - 4 columns (Line 78-102)
- **Footer Bottom** - Copyright, social, menu (Line 105-126)

### Modifying Styles

**Header:** `assets/css/header-custom.css`
**Footer:** `assets/css/footer-custom.css`

### Adding Custom JavaScript

Edit: `assets/js/custom.js`

## Widget Areas

The theme provides these widget areas:

### Header
- **Header Top Bar** - Optional top bar above main header

### Footer
- **Footer Column 1** - First footer column
- **Footer Column 2** - Second footer column
- **Footer Column 3** - Third footer column
- **Footer Column 4** - Fourth footer column

## Menu Locations

- **Primary** - Main navigation menu
- **Footer** - Footer menu (policies, etc.)

## Hooks Available

### Header Hooks
```php
// Before header
do_action('sciuuuskids_before_header');

// After header
do_action('sciuuuskids_after_header');

// Inside header (before logo)
do_action('sciuuuskids_header_start');

// Inside header (after all elements)
do_action('sciuuuskids_header_end');
```

### Footer Hooks
```php
// Before footer
do_action('sciuuuskids_before_footer');

// After footer
do_action('sciuuuskids_after_footer');

// Inside footer (before widgets)
do_action('sciuuuskids_footer_start');

// Inside footer (after copyright)
do_action('sciuuuskids_footer_end');
```

## Troubleshooting

### Header Not Showing

**Problem:** Custom header not visible after activation

**Solution:**
1. Check if Blocksy theme is the active parent theme
2. Clear all caches (WordPress, browser, CDN)
3. Verify files are in correct location: `/wp-content/themes/blocksy-child/`

### Styles Not Loading

**Problem:** CSS not applying

**Solution:**
1. Hard refresh browser (Ctrl+Shift+R or Cmd+Shift+R)
2. Check file permissions (should be 644 for files, 755 for directories)
3. Verify paths in `functions.php` enqueue statements

### Mobile Menu Not Working

**Problem:** Mobile menu toggle not responsive

**Solution:**
1. Check if jQuery is loaded: View source and search for "jquery"
2. Check browser console for JavaScript errors (F12)
3. Clear browser cache

### Cart Count Not Updating

**Problem:** WooCommerce cart count not updating dynamically

**Solution:**
1. Verify WooCommerce AJAX cart fragments are enabled
2. Check if WooCommerce is active
3. Test adding product to cart and refresh page

## Development

### Local Development Setup

```bash
# Clone your WordPress installation
git clone your-repo.git

# Navigate to themes directory
cd wp-content/themes

# Copy child theme
cp -r /path/to/blocksy-child ./

# In WordPress admin, activate the child theme
```

### Making Changes

1. Edit files in `blocksy-child/` directory
2. Test changes locally first
3. Commit changes to version control
4. Deploy to production

## Production Deployment

### Via FTP

```bash
# Upload modified files to:
/wp-content/themes/blocksy-child/

# Clear all caches after upload
```

### Via Git

```bash
# From your WordPress root directory
git add wp-content/themes/blocksy-child/
git commit -m "Update child theme"
git push origin main

# On production server
git pull origin main

# Clear all caches
```

### Using Docker/Podman

```bash
# Copy updated theme to container
docker cp blocksy-child/ container-name:/var/www/html/wp-content/themes/

# Set correct permissions
docker exec container-name chown -R www-data:www-data /var/www/html/wp-content/themes/blocksy-child

# Clear WordPress cache
docker exec container-name wp cache flush --allow-root
```

## Performance Optimization

### Recommended Plugins

- **WP Rocket** - Caching
- **Autoptimize** - CSS/JS optimization
- **ShortPixel** - Image optimization
- **WP-Optimize** - Database cleanup

### Manual Optimization

1. **Minimize HTTP Requests**
   - Combine CSS/JS files if needed
   - Use CSS sprites for icons

2. **Enable Gzip Compression**
   ```apache
   # Add to .htaccess
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/css text/javascript
   </IfModule>
   ```

3. **Leverage Browser Caching**
   ```apache
   # Add to .htaccess
   <IfModule mod_expires.c>
       ExpiresActive On
       ExpiresByType text/css "access plus 1 year"
       ExpiresByType application/javascript "access plus 1 year"
   </IfModule>
   ```

## Support

For issues or questions:

1. Check this README
2. Review [documentation](documentation/)
3. Check WordPress error logs
4. Contact developer

## Version History

### Version 1.0.0 (Current)
- Initial release
- Code-based header implementation
- Code-based footer implementation
- WooCommerce integration
- Responsive design
- Customizer settings

## Credits

- **Theme:** Blocksy Child - SciuuuS Kids
- **Parent Theme:** Blocksy by CreativeThemes
- **Developer:** Damian
- **Website:** https://sciuuuskids.it

## License

This theme is licensed under the GPL v2 or later.

## Notes for Future Development

### Planned Features

- [ ] Product quick view modal
- [ ] Advanced mega menu
- [ ] Sticky add to cart
- [ ] Product comparison
- [ ] Wishlist integration
- [ ] Multi-language switcher

### Migration Notes

This theme replaces:
- Elementor Pro header builder
- Elementor Pro footer builder
- ShopLentor (optional WooCommerce builder)

All functionality is now code-based for better:
- Version control
- Performance
- Maintainability
- Portability

---

**Last Updated:** November 2024
