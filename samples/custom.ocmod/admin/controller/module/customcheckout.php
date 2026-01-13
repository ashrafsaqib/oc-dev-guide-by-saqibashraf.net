<?php
namespace Opencart\Admin\Controller\Extension\Custom\Module;
/**
 * Class Account
 *
 * @package Opencart\Admin\Controller\Extension\Opencart\Module
 */
class Customcheckout extends \Opencart\System\Engine\Controller {
	/**
	 * Index
	 *
	 * @return void
	 */
	public function index(): void {
		$this->load->language('extension/custom/module/customcheckout');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/custom/module/customcheckout', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/custom/module/customcheckout.save', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		// $data['module_customcheckout_status'] = $this->config->get('module_customcheckout_status');
		$data['module_customcheckout_token'] = $this->config->get('module_customcheckout_token') ?? '';
		$data['module_customcheckout_groupid'] = $this->config->get('module_customcheckout_groupid') ?? '';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['refresh'] = HTTP_CATALOG . 'index.php?route=extension/custom/module/confirm.getdata&redirect='.urlencode($this->url->link('extension/custom/module/customcheckout', 'user_token=' . $this->session->data['user_token']));

		$this->response->setOutput($this->load->view('extension/custom/module/customcheckout', $data));
	}

	/**
	 * Save
	 *
	 * @return void
	 */
	public function save(): void {
		$this->load->language('extension/custom/module/customcheckout');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/custom/module/customcheckout')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			// Setting
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('module_customcheckout', $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
