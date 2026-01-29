# OpenCart 3 Theme Structure Guide

This guide explains the standard structure for creating a theme in OpenCart 3.x, including required files and best practices for admin integration.

## 1. Theme Directory Structure

Your theme files should be placed in:

```
catalog/view/theme/your_theme/
```

**Example:**
```
catalog/view/theme/electronic/
  ├── css/
  ├── fonts/
  ├── image/
  ├── js/
  └── template/
      ├── account/
      ├── checkout/
      ├── common/
      ├── error/
      ├── extension/
      ├── information/
      ├── mail/
      └── product/
```

- Place your Twig template files in the appropriate subfolders under `template/`.
- Place your CSS, JS, images, and fonts in their respective folders.

## 2. Admin Integration (Theme Settings)

To make your theme appear in the admin theme list and allow settings:

### Required Admin Files

```
admin/controller/extension/theme/your_theme.php
admin/language/en-gb/extension/theme/your_theme.php
admin/view/template/extension/theme/your_theme.twig
```

- **Controller:** Handles theme settings and installation logic.
- **Language:** Contains all text strings for the admin interface.
- **Twig Template:** The settings form for your theme in the admin panel.

### Example Controller (your_theme.php)
- Should have `index()` for settings and `install()` to register the theme.

### Example Language File (your_theme.php)
- Should define headings, entries, button text, and error messages.

### Example Twig Template (your_theme.twig)
- Should provide a form for theme settings (status, directory, etc.).
- Use the same structure as the default theme for compatibility.

## 3. Extension Registration

- The theme must be installed as an extension (via admin or `install()` method in the controller).
- After installation, it will appear in the admin > Extensions > Themes list and in the store settings dropdown.

## 4. Best Practices

- Always match the structure and naming conventions of the default theme for maximum compatibility.
- Clear the modification and twig cache after installing or updating theme files.
- Use language files for all admin text.
- Test your theme on both the storefront and admin.

---

**Summary:**
- Place theme files in `catalog/view/theme/your_theme/`
- Add admin controller, language, and twig files for settings
- Register the theme as an extension
- Follow the default theme as a reference for structure and compatibility
