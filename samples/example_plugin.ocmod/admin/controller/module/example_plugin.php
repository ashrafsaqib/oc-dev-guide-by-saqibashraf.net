<?php
namespace Opencart\Admin\Controller\Extension\ExamplePlugin\Module;

class ExamplePlugin extends \Opencart\System\Engine\Controller {
    public function index() {
        $this->load->language('extension/module/example_plugin');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/example_plugin', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['action'] = $this->url->link('extension/module/example_plugin', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_example_plugin_status'])) {
            $data['module_example_plugin_status'] = $this->request->post['module_example_plugin_status'];
        } else {
            $data['module_example_plugin_status'] = $this->config->get('module_example_plugin_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/example_plugin', $data));
    }

    public function save() {
        $this->load->language('extension/module/example_plugin');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('module_example_plugin', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $this->index();
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/example_plugin')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('setting/event');
        $this->load->model('user/user_group');

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "example_plugin` (`id` INT(11) NOT NULL AUTO_INCREMENT, `some_data` VARCHAR(255) NOT NULL, PRIMARY KEY (`id`))");

        $event_data = [
            'code'       => 'example_plugin',
            'trigger'    => 'catalog/model/checkout/order/addOrderHistory/after',
            'action'     => 'extension/module/example_plugin|history_added',
            'status'     => 1,
            'sort_order' => 0,
            'description'=> 'Example Plugin Event'
        ];

        $this->model_setting_event->addEvent($event_data);

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/example_plugin');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/example_plugin');
    }

    public function uninstall() {
        $this->load->model('setting/event');

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "example_plugin`");

        $this->model_setting_event->deleteEventByCode('example_plugin');
    }
}
