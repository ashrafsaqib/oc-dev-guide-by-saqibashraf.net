# OpenCart 4 Development Guide

A comprehensive guide documenting the development of extensions, plugins, themes, and bug fixes for OpenCart 4.

## About This Repository

OpenCart 4 introduced significant architectural changes that have left many developers struggling to adapt. With limited documentation available online, this repository serves as a practical resource based on real-world development experience.

## Purpose

This guide aims to:
- Document the major changes in OpenCart 4 compared to previous versions
- Provide practical examples for common development tasks
- Help developers navigate the new architecture and patterns
- Share solutions to common problems and bugs
- Bridge the documentation gap in the OpenCart 4 ecosystem

## Contents

- [Modifying Controllers functions with Events Opencart 4](Modifying%20Controllers%20functions%20with%20Events%20Opencart%204.MD) - Learn how to use OpenCart 4's event system to intercept and modify controller responses without touching core files
- [Theme Development Guide](Theme%20DEVELOPMENT%20GUIDE.md) - Best practices for theme development using events instead of controller overrides, with real-world examples from OC Ultra theme

## Sample Extensions

Ready-to-use code samples demonstrating various OpenCart 4 extension types:

### Complete Extensions
- **[Custom Checkout](samples/custom.ocmod/)** - Complete checkout customization module with admin panel, custom templates, and OCMOD integration
- **[Example Plugin](samples/example_plugin.ocmod/)** - Basic plugin structure showing the standard OpenCart 4 extension architecture

### OpenCart Official Sample Pack
Located in [samples/opencat sample extensions pack/](samples/opencat%20sample%20extensions%20pack/):

- **[Language Extension Example](samples/opencat%20sample%20extensions%20pack/oc_language_example.ocmod/)** - Demonstrates how to create language packs with multi-language support
- **[OCMOD Extension Example](samples/opencat%20sample%20extensions%20pack/oc_ocmod_example.ocmod/)** - Shows OCMOD XML modification patterns
- **[Payment Extension Example](samples/opencat%20sample%20extensions%20pack/oc_payment_example.ocmod/)** - Complete payment gateway implementation with admin panel, cron jobs, and customer views
- **[Theme Extension Example](samples/opencat%20sample%20extensions%20pack/oc_theme_example.ocmod/)** - Theme development structure with startup controllers and helper libraries

## Topics Covered

- ✅ Event System Architecture
- 🔄 Extension Development (Coming Soon)
- ✅ Theme Development
- 🔄 Plugin Development (Coming Soon)
- 🔄 Bug Fixes & Solutions (Coming Soon)
- 🔄 Migration from OpenCart 3 (Coming Soon)

## Who This Is For

- OpenCart developers transitioning from version 3 to 4
- New developers learning OpenCart 4 development
- Anyone building custom extensions, themes, or modifications
- Developers troubleshooting OpenCart 4 issues

## Contributing

This is a living document based on ongoing learning and development. If you've discovered useful patterns, solutions, or insights about OpenCart 4 development, contributions are welcome.

## Author

**Saqib Ashraf**
- Website: [saqibashraf.net](https://saqibashraf.net)

## License

This documentation is provided as-is for educational purposes.

---

*Last Updated: January 2026*
