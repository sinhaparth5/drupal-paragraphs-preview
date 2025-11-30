# CPA Quick Start Guide

## Installation Steps

### 1. Upload the Module

Place the `cpa_module` folder in your Drupal installation:

```bash
# For custom modules
/modules/custom/cpa

# Or for contrib modules (if publishing to drupal.org)
/modules/contrib/cpa
```

**Note**: Rename `cpa_module` to just `cpa` when placing it in your Drupal directory.

### 2. Enable the Module

**Via Drush:**
```bash
drush en cpa -y
drush cr
```

**Via UI:**
1. Go to `/admin/modules`
2. Find "Component Performance Auditor" under the "Performance" package
3. Check the box next to it
4. Click "Install"
5. Clear cache

### 3. Set Permissions

1. Go to `/admin/people/permissions`
2. Find the "Component Performance Auditor" section
3. Grant permissions:
   - **Administer Component Performance Auditor**: For developers who need full access
   - **View CPA performance reports**: For team members who only need to view reports

### 4. Configure Settings

1. Go to `/admin/config/development/cpa`
2. Configure your preferences:
   - Enable/disable visual overlay
   - Set slow query threshold (default: 50ms)
   - Choose which component types to track
   - Adjust sampling rate if needed

### 5. Start Using CPA

**Visual Overlay:**
1. Browse any page on your site (as a user with CPA permissions)
2. Look for the performance toolbar in the bottom-right corner
3. Hover over any page element to see performance metrics
4. Color-coded outlines indicate performance levels

**Dashboard:**
1. Go to `/admin/reports/cpa`
2. View summary statistics
3. See slowest components
4. Analyze cache effectiveness

## Quick Tips

### First Time Setup
1. Start with default settings
2. Browse a few key pages
3. Look for red (slow) components
4. Check the dashboard for highest query counts

### Optimizing Performance
1. **Red components**: Priority fixes - likely uncacheable or slow queries
2. **Yellow components**: Review queries and cache configuration
3. **High query counts**: Consider query optimization or caching
4. **Cache misses**: Review cache tags and contexts

### Production Use
- Set sampling rate to 10-25% to reduce overhead
- Disable detailed query logging
- Consider disabling in production entirely and use on staging

## Troubleshooting

**Overlay not showing:**
- Clear cache: `drush cr`
- Check permissions
- Verify JavaScript console for errors
- Ensure you're logged in with proper permissions

**No performance data:**
- Browse a few pages to generate data
- Check that component types are enabled in settings
- Verify database logging is available

**Performance impact too high:**
- Disable query logging
- Reduce sampling rate
- Limit component types being tracked
- Increase max_components limit

## File Structure Reference

```
cpa/
├── cpa.info.yml              # Module metadata
├── cpa.module                # Hook implementations
├── cpa.install               # Installation/schema
├── cpa.permissions.yml       # Permission definitions
├── cpa.routing.yml           # URL routes
├── cpa.services.yml          # Service definitions
├── cpa.libraries.yml         # Asset libraries
├── README.md                 # Full documentation
├── CHANGELOG.md              # Version history
├── config/
│   ├── install/
│   │   └── cpa.settings.yml  # Default configuration
│   └── schema/
│       └── cpa.schema.yml    # Configuration schema
├── src/
│   ├── Controller/
│   │   ├── CpaApiController.php
│   │   └── CpaDashboardController.php
│   ├── EventSubscriber/
│   │   └── RenderSubscriber.php
│   ├── Form/
│   │   └── CpaSettingsForm.php
│   └── Service/
│       ├── CacheTracker.php
│       ├── CpaAuditor.php
│       └── QueryLogger.php
├── css/
│   ├── cpa-overlay.css       # Overlay styles
│   └── cpa-dashboard.css     # Dashboard styles
└── js/
    ├── cpa-overlay.js        # Overlay functionality
    └── cpa-dashboard.js      # Dashboard functionality
```

## Support

For issues, questions, or contributions:
- Check the README.md for detailed documentation
- Review the inline code comments
- Submit issues to the project queue (when available)

## Next Steps

After installation:
1. Review the full README.md for detailed feature explanations
2. Configure settings based on your needs
3. Test on development/staging first
4. Share findings with your team
5. Use data to prioritize performance optimizations

Happy optimizing! 🚀
