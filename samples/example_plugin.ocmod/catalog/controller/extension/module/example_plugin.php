<?php
class ControllerExtensionModuleExamplePlugin extends Controller {
    public function index($setting) {
        if (isset($setting['module_example_plugin_status']) && $setting['module_example_plugin_status']) {
            $data['module_example_plugin_status'] = $this->config->get('module_example_plugin_status');
            return $this->load->view('extension/module/example_plugin', $data);
        }
    }

    public function history_added(&$route, &$args) {
        $this->log->write('Example Plugin: Order history added for order_id: ' . $args[0]);
    }
}
