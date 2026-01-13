# OC Ultra Theme - Development Guide

## Best Practice: Using Events Instead of Controller Overrides

### The Problem

When you need to add custom data to a template (like adding images to categories), there are two approaches:

1. ❌ **Bad Approach**: Override the entire controller
2. ✅ **Good Approach**: Use view events to inject data

### Why Events Are Better

#### Override Entire Controller (❌ Not Recommended)
```php
// Creates extension/oc_ultra/catalog/controller/product/category.php
// Copy entire controller (~500 lines)
// Modify small section to add images
// Return custom view
```

**Problems:**
- Duplicates 500+ lines of code
- Hard to maintain when OpenCart updates
- Conflicts with other extensions
- Must update when core changes
- Increases theme file size

#### Use View Events (✅ Recommended)
```php
// In startup/oc_ultra.php (~30 lines)
public function category(string &$route, array &$args, mixed &$output): void {
    // Load models
    $this->load->model('catalog/category');
    $this->load->model('tool/image');
    
    // Modify existing data
    foreach ($args['categories'] as &$category_item) {
        // Add images to existing categories array
        $category_item['image'] = $this->model_tool_image->resize($image, 300, 300);
    }
}
```

**Benefits:**
- Only ~30 lines of code
- Uses core controller logic
- No conflicts with updates
- Easy to maintain
- Lighter weight
- Can be toggled on/off easily

---

## Implementation Pattern

### Step 1: Register the Event

In `catalog/controller/startup/oc_ultra.php`:

```php
public function index(): void {
    if ($this->config->get('config_theme') == 'oc_ultra' && $this->config->get('theme_oc_ultra_status')) {
        // Register event before view is rendered
        $this->event->register('view/product/category/before', 
            new \Opencart\System\Engine\Action('extension/oc_ultra/startup/oc_ultra.category'));
    }
}
```

**Event Naming Pattern:**
- `view/{route}/before` - Fires before view is rendered
- Receives `$args` array containing all data passed to template
- Can modify `$args` to add/change data

### Step 2: Create Event Handler Method

```php
public function category(string &$route, array &$args, mixed &$output): void {
    // 1. Load required models
    $this->load->model('catalog/category');
    $this->load->model('tool/image');
    
    // 2. Check if data exists
    if (isset($args['categories']) && is_array($args['categories'])) {
        
        // 3. Loop through and enhance existing data
        foreach ($args['categories'] as &$category_item) {
            // Extract category ID from existing href
            if (isset($category_item['href'])) {
                parse_str(parse_url($category_item['href'], PHP_URL_QUERY), $query_params);
                if (isset($query_params['path'])) {
                    $parts = explode('_', $query_params['path']);
                    $category_id = (int)array_pop($parts);
                    
                    // Fetch additional data
                    $category_info = $this->model_catalog_category->getCategory($category_id);
                    
                    // Add new field to existing array
                    if ($category_info && !empty($category_info['image'])) {
                        $category_item['image'] = $this->model_tool_image->resize(
                            $category_info['image'], 300, 300
                        );
                    } else {
                        $category_item['image'] = $this->model_tool_image->resize(
                            'placeholder.png', 300, 300
                        );
                    }
                }
            }
        }
    }
}
```

### Step 3: Use in Template

The modified data is automatically available in your template:

```twig
{# extension/oc_ultra/catalog/view/template/product/category.twig #}
{% for category in categories %}
  <div class="category-item">
    <img src="{{ category.image }}" alt="{{ category.name }}">
    <h5>{{ category.name }}</h5>
  </div>
{% endfor %}
```

---

## Real Examples from OC Ultra Theme

### Example 1: Adding Category Images (Current Implementation)

**Event Registration:**
```php
$this->event->register('view/product/category/before', 
    new \Opencart\System\Engine\Action('extension/oc_ultra/startup/oc_ultra.category'));
```

**Handler Method:**
```php
public function category(string &$route, array &$args, mixed &$output): void {
    if (isset($args['categories'])) {
        $this->load->model('catalog/category');
        $this->load->model('tool/image');
        
        foreach ($args['categories'] as &$category_item) {
            // Extract ID and add image field
            $category_item['image'] = $this->model_tool_image->resize($image, 300, 300);
        }
    }
}
```

### Example 2: Adding Featured Categories to Home

**Event Registration:**
```php
$this->event->register('view/common/home/before', 
    new \Opencart\System\Engine\Action('extension/oc_ultra/startup/oc_ultra.home'));
```

**Handler Method:**
```php
public function home(string &$route, array &$args, mixed &$output): void {
    $this->load->model('catalog/category');
    $this->load->model('tool/image');
    
    $args['featured_categories'] = [];
    
    if ($this->config->get('theme_oc_ultra_featured_category')) {
        // Build custom featured categories array
        foreach ($featured_categories as $featured_category) {
            $args['featured_categories'][] = [
                'name'  => $category_info['name'],
                'image' => $this->model_tool_image->resize($image, 800, 600),
                'href'  => $this->url->link('product/category', '...')
            ];
        }
    }
}
```

### Example 3: Adding Header Text

**Event Registration:**
```php
$this->event->register('view/common/header/before', 
    new \Opencart\System\Engine\Action('extension/oc_ultra/startup/oc_ultra.header'));
```

**Handler Method:**
```php
public function header(string &$route, array &$args, mixed &$output): void {
    // Simply add new field to args
    $args['theme_header_text'] = $this->config->get('theme_oc_ultra_header_text') ?: '';
}
```

---

## Common View Events

| Event | When to Use | Data Available |
|-------|-------------|----------------|
| `view/common/header/before` | Modify header data | Navigation, user info, cart |
| `view/common/footer/before` | Modify footer data | Footer links, info |
| `view/common/home/before` | Add home page sections | Banners, featured products |
| `view/product/category/before` | Enhance category page | Categories, products, filters |
| `view/product/product/before` | Enhance product page | Product details, options, images |
| `view/checkout/*/before` | Modify checkout data | Cart, payment, shipping |

---

## Quick Reference: Controller Override vs Event

### When You Need To:

#### ✅ Use Events When:
- Adding new fields to existing data
- Modifying template variables
- Adding new sections to pages
- Enhancing existing arrays
- Loading additional data for templates
- Theme customizations

#### ⚠️ Consider Controller Override When:
- Completely changing page logic
- Major algorithmic changes
- Complex validation changes
- Fundamental behavior modification
- (But still prefer events if possible!)

---

## Template Override Pattern

Events work with template overrides:

```php
public function event(string &$route, array &$args, mixed &$output): void {
    $override = [
        'product/category',  // Override template
        // ...
    ];
    
    if (in_array($route, $override)) {
        $route = 'extension/oc_ultra/' . $route;
    }
}
```

**Flow:**
1. Core controller runs → generates data
2. View event fires → enhances data with `category()` method
3. Template override loads → uses enhanced data
4. Template renders with all data

---

## Tips & Best Practices

### 1. Always Check Data Exists
```php
if (isset($args['categories']) && is_array($args['categories'])) {
    // Safe to modify
}
```

### 2. Use References for Modification
```php
foreach ($args['categories'] as &$category_item) {
    // & makes it a reference, so changes persist
    $category_item['new_field'] = 'value';
}
```

### 3. Load Models Once
```php
$this->load->model('catalog/category');  // Load once per method
$this->load->model('tool/image');

foreach ($items as &$item) {
    // Use $this->model_catalog_category many times
}
```

### 4. Provide Defaults
```php
$args['theme_setting'] = $this->config->get('setting') ?: 'default_value';
```

### 5. Keep Methods Focused
```php
// Good: One method per event
public function category() { }  // Only handles category data
public function home() { }      // Only handles home data

// Bad: One method doing everything
public function addAllData() { } // Handles everything
```

---

## Debugging Events

### Check if Event Fires
```php
public function category(string &$route, array &$args, mixed &$output): void {
    error_log('Category event fired!');
    error_log('Categories count: ' . count($args['categories']));
}
```

### Inspect Data Structure
```php
error_log('Args: ' . print_r($args, true));
```

### Verify Event Registration
Check that theme is active and event is registered in `index()` method.

---

## Summary

| Aspect | Controller Override | View Event |
|--------|-------------------|------------|
| Code Size | 500+ lines | ~30 lines |
| Maintenance | High | Low |
| Update Safety | Poor | Excellent |
| Conflicts | High risk | Low risk |
| Performance | Same | Same |
| Flexibility | Full control | Data modification only |
| **Recommendation** | ❌ Avoid | ✅ Preferred |

**Rule of Thumb:** If you only need to add/modify template data, use view events. Only override controllers for fundamental logic changes.
