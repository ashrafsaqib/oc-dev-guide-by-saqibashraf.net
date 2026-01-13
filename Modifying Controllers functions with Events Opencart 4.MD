# Modifying JSON Output from Core Controller Functions

## Overview

OpenCart's event system allows you to intercept and modify responses from any controller method without modifying core files. This is particularly useful for modifying JSON API responses.

## How the Event System Works

### Event Triggers

For every controller method, OpenCart triggers two events:

1. **`controller/{route}/before`** - Triggered before the controller method executes
   - Parameters: `&$route`, `&$args`
   - Can modify input parameters

2. **`controller/{route}/after`** - Triggered after the controller method executes
   - Parameters: `&$route`, `&$args`, `&$output`
   - Can modify the output/response

### Event Location in Code

Events are triggered in `/system/engine/loader.php`:

```php
// Trigger the pre events
$this->event->trigger('controller/' . $trigger . '/before', [&$route, &$args]);

$output = $callable(...$args);

// Trigger the post events
$this->event->trigger('controller/' . $trigger . '/after', [&$route, &$args, &$output]);
```

## Critical Discovery: Void Methods vs Return Values

### The Problem

Most admin controller methods return `void` (nothing), not the actual output:

```php
public function upload(): void {
    // ... code ...
    $this->response->setOutput(json_encode($json));
    // No return statement - returns void!
}
```

When a method returns `void`, the `$output` parameter in the `/after` event will be **`null`**, not the response body.

### The Solution

For void methods, you must retrieve the output from the **response object** instead:

```php
public function afterUpload(string &$route, array &$args, &$output): void {
    // DON'T use $output - it's null for void methods!
    // DO use the response object:
    $responseOutput = $this->response->getOutput();
    
    if (empty($responseOutput)) {
        return;
    }
    
    // Process and modify
    $json = json_decode($responseOutput, true);
    
    if ($json) {
        // Modify the JSON
        $json['custom_field'] = 'custom value';
        
        // Set it back
        $this->response->setOutput(json_encode($json));
    }
}
```

## Complete Example

### Scenario: Modify the JSON response from marketplace/installer.upload

#### Step 1: Create Event Handler

**File**: `extension/your_extension/admin/controller/module/your_extension/event.php`

```php
<?php
namespace Opencart\Admin\Controller\Extension\YourExtension\Module\YourExtension;

class Event extends \Opencart\System\Engine\Controller {
    
    /**
     * Modify JSON response after file upload
     * Event: controller/marketplace/installer.upload/after
     */
    public function afterInstallerUpload(string &$route, array &$args, &$output): void {
        // Get the response output from the response object
        $responseOutput = $this->response->getOutput();
        
        // Only process if we have content
        if (empty($responseOutput)) {
            return;
        }
        
        // Decode the existing JSON output
        $json = json_decode($responseOutput, true);
        
        // Modify the JSON data
        if ($json) {
            // Add new fields
            $json['custom_field'] = 'custom value';
            $json['modified_by'] = 'your_extension';
            $json['timestamp'] = date('Y-m-d H:i:s');
            
            // Modify existing fields
            if (isset($json['success'])) {
                $json['success'] = 'Enhanced: ' . $json['success'];
            }
            
            // Add custom error handling
            if (isset($json['error'])) {
                $json['custom_error_code'] = 'UPLOAD_FAILED';
            }
            
            // Set the modified JSON back to the response
            $this->response->setOutput(json_encode($json));
        }
    }
}
```

#### Step 2: Register the Event

Events must be registered in the database. Add this to your extension's install method:

```php
public function install(): void {
    $this->load->model('setting/event');
    
    $this->model_setting_event->addEvent([
        'code'        => 'your_extension_installer_upload',
        'description' => 'Modify installer upload response',
        'trigger'     => 'admin/controller/marketplace/installer/upload/after',
        'action'      => 'extension/your_extension/module/your_extension/event.afterInstallerUpload',
        'status'      => 1,
        'sort_order'  => 1
    ]);
}
```

## Key Differences: Methods That Return Values

Some controller methods actually return values instead of using `$this->response->setOutput()`:

```php
public function getList(): string {
    // ... code ...
    return $this->load->view('marketplace/installer_extension', $data);
}
```

For these methods, the `$output` parameter **will contain** the returned value, and you can modify it directly:

```php
public function afterGetList(string &$route, array &$args, &$output): void {
    // $output contains the actual return value
    if (!empty($output)) {
        $output = str_replace('original', 'modified', $output);
    }
}
```

## Common Patterns

### 1. Adding Debug Information

```php
public function afterControllerMethod(string &$route, array &$args, &$output): void {
    $responseOutput = $this->response->getOutput();
    
    if ($responseOutput) {
        $json = json_decode($responseOutput, true);
        
        if ($json) {
            $json['debug'] = [
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
                'memory_usage' => memory_get_peak_usage(true),
                'route' => $route
            ];
            
            $this->response->setOutput(json_encode($json));
        }
    }
}
```

### 2. Conditional Modification

```php
public function afterUpload(string &$route, array &$args, &$output): void {
    // Only modify if override parameter is present
    if (!isset($this->request->get['override'])) {
        return;
    }
    
    $responseOutput = $this->response->getOutput();
    
    if ($responseOutput) {
        $json = json_decode($responseOutput, true);
        
        if ($json) {
            $json['override_enabled'] = true;
            $this->response->setOutput(json_encode($json));
        }
    }
}
```

### 3. Error Enhancement

```php
public function afterApiCall(string &$route, array &$args, &$output): void {
    $responseOutput = $this->response->getOutput();
    
    if ($responseOutput) {
        $json = json_decode($responseOutput, true);
        
        if ($json && isset($json['error'])) {
            // Add helpful error information
            $json['error_code'] = 'API_001';
            $json['error_timestamp'] = date('Y-m-d H:i:s');
            $json['support_link'] = 'https://example.com/support';
            
            $this->response->setOutput(json_encode($json));
        }
    }
}
```

## Troubleshooting

### Issue: Getting deprecation warning about json_decode null parameter

**Cause**: Trying to decode `$output` parameter which is null for void methods.

**Solution**: Use `$this->response->getOutput()` instead.

### Issue: Changes not appearing

**Possible causes**:
1. Event not registered in database (check `oc_event` table)
2. Event status is 0 (disabled)
3. Event trigger path is incorrect
4. Extension is not installed
5. Cache needs clearing

### Issue: JSON malformed after modification

**Cause**: Not properly re-encoding JSON or encoding twice.

**Solution**: Always decode, modify, then encode once:
```php
$json = json_decode($responseOutput, true);
// modify $json
$this->response->setOutput(json_encode($json)); // encode once
```

## Best Practices

1. **Always check if output exists** before processing
2. **Validate JSON** after decoding (check if it's an array/not null)
3. **Use meaningful event codes** in database registration
4. **Set appropriate sort_order** if multiple events hook the same trigger
5. **Don't modify core files** - always use events
6. **Log errors** for debugging in production
7. **Consider backward compatibility** when adding fields

## Event Priority

Multiple extensions can hook the same event. The `sort_order` field determines execution order:

- **Lower numbers execute first** (0, 1, 2, ...)
- **Higher numbers execute last** (98, 99, 100, ...)

If you need your modification to run before/after others, adjust the sort_order accordingly.

## Summary

- **Void methods**: Use `$this->response->getOutput()` to get the response
- **Return methods**: Use `&$output` parameter directly
- **Always validate** before decoding JSON
- **Re-set the response** using `$this->response->setOutput()`
- **Register events** in the database for them to work

This approach allows powerful customization without touching core files!
