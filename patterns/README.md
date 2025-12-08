# SciuuuS Kids Block Patterns

## Overview
Custom Gutenberg block patterns for easily recreating page sections from the Elementor-based site. These patterns provide pre-designed, reusable content layouts that maintain visual consistency while using native WordPress blocks.

## Pattern Organization

### Homepage Patterns (`patterns/homepage/`)
Patterns specific to the homepage layout:

1. **Delivery Banner** (`delivery-banner.php`)
   - Orange banner with "🚚 Consegna gratuita 📦" message
   - Full-width, centered text
   - Use at top of homepage

2. **Hero Section - Main** (`hero-main.php`)
   - Main hero section with three headings
   - "Scarpe comode e leggere" / "che rispettano lo sviluppo dei bambini" / "Scopri le nostre barefoot"
   - CTA button linking to discovery page
   - Complex layout with main kite image, decorative tree image, small icon grid (5 images), and balloon decorations
   - Responsive layout that stacks on mobile

## How to Use Block Patterns

### In the Block Editor:

1. **Edit a page** in WordPress
2. Click the **+ (Add block)** button
3. Go to the **"Patterns"** tab
4. Look for the **"SciuuuS Kids - Homepage"** category
5. Click on a pattern to insert it
6. Customize the content (text, images, links) as needed

### Pattern Workflow:

```
1. Insert pattern from block inserter
2. Pattern appears with placeholder/live content
3. Edit text directly in place
4. Replace images by clicking on them
5. Update button links
6. Save page
```

## Pattern Customization

### Editing Content:
- **Text**: Click and type directly
- **Images**: Click image → Replace → Upload or Media Library
- **Buttons**: Click button → Edit link in toolbar
- **Colors**: Select element → Block settings → Color options

### Common Edits:
- Change button text: Click button, type new text
- Update button link: Click button → Link icon → Enter new URL
- Swap images: Click image → Replace button in toolbar
- Adjust spacing: Select block → Settings panel → Spacing options

## CSS Styling

Custom styles for patterns are in: `assets/css/homepage-patterns.css`

Key classes:
- `.delivery-banner` - Orange delivery message banner
- `.hero-section-main` - Main hero container
- `.main-hero-image` - Center kite image
- `.decorative-image` - Side tree image
- `.small-decorative` - Small icon grid images
- `.balloon-decorative` - Bottom balloon images

## Responsive Behavior

All patterns are fully responsive:
- **Desktop (>768px)**: Full 3-column layout with side decorations
- **Tablet (768px)**: Adjusted spacing, columns stack partially
- **Mobile (<480px)**: Full vertical stacking, reduced image sizes

## Adding New Patterns

To create a new pattern:

1. Create a new `.php` file in `patterns/homepage/`
2. Use this template:

```php
<?php
return array(
    'title'       => __('Pattern Name', 'blocksy-child'),
    'description' => __('Pattern description', 'blocksy-child'),
    'categories'  => array('sciuuuskids-homepage'),
    'content'     => '<!-- Block markup here -->',
);
```

3. Add corresponding CSS to `homepage-patterns.css` if needed
4. The pattern will automatically appear in the block inserter

## Pattern Categories

Current categories:
- **sciuuuskids-homepage** - Homepage-specific patterns

Future categories planned:
- **sciuuuskids-content** - General content patterns
- **sciuuuskids-products** - Product page patterns
- **sciuuuskids-features** - Feature sections

## Tips for Pattern Migration

### Converting Elementor to Block Patterns:

1. **Identify repeating sections** on the live site
2. **Screenshot the section** for reference
3. **Note the content structure**: headings, text, images, buttons
4. **Create pattern file** with equivalent Gutenberg blocks
5. **Add custom CSS** for styling that doesn't translate directly
6. **Test responsive behavior** at all breakpoints

### Best Practices:

- Keep patterns focused on single sections
- Use descriptive names and categories
- Include all images referenced in the pattern
- Test pattern insertion on a draft page first
- Document any special configuration needed

## Version History

- **v1.0.0** - Initial release with delivery banner and hero section patterns

## Support

For issues or questions about patterns, check:
1. WordPress Block Editor documentation
2. Child theme `README.md`
3. Pattern file comments for specific guidance
