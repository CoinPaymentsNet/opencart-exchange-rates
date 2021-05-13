<?php

class ControllerExtensionModuleCoinpaymentsCurrencyRates extends Controller
{

    /** @var array $error Validation errors */
    private $error = array();

    /**
     * CoinPayments Payment Admin Controller Constructor
     * @param Registry $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('extension/module/coinpayments_currency_rates');
        if (!empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->ajax = true;
            $this->response->addHeader('Content-type: application/json');
        }
    }

    /**
     * Primary settings page
     * @return void
     */
    public function index()
    {
        $this->load->model('setting/setting');
        $this->document->setTitle($this->language->get('heading_title'));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('module_coinpayments_currency', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success') . " / status: " . ($this->request->post['module_coinpayments_currency_rates_status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'));
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_advanced'] = $this->language->get('text_advanced');
        $data['entry_client_id'] = $this->language->get('entry_client_id');
        $data['entry_client_secret'] = $this->language->get('entry_client_secret');
        $data['entry_status'] = $this->language->get('entry_status');

        $data['help_client_id'] = $this->language->get('help_client_id');
        $data['help_client_secret'] = $this->language->get('help_client_secret');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['url_action'] = $this->url->link('extension/module/coinpayments_currency_rates', 'user_token=' . $this->session->data['user_token'], 'SSL');
        $data['url_cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', 'SSL')
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/coinpayments_currency_rates', 'user_token=' . $this->session->data['user_token'], 'SSL')
        );
        if (isset($this->request->post['module_coinpayments_currency_rates_client_id'])) {
            $data['module_coinpayments_currency_rates_client_id'] = $this->request->post['module_coinpayments_currency_rates_client_id'];
        } else {
            $data['module_coinpayments_currency_rates_client_id'] = $this->config->get('module_coinpayments_currency_rates_client_id');
        }
        if (isset($this->request->post['module_coinpayments_currency_rates_client_secret'])) {
            $data['module_coinpayments_currency_rates_client_secret'] = $this->request->post['module_coinpayments_currency_rates_client_secret'];
        } else {
            $data['module_coinpayments_currency_rates_client_secret'] = $this->config->get('module_coinpayments_currency_rates_client_secret');
        }
        if (isset($this->request->post['module_coinpayments_currency_rates_status'])) {
            $data['module_coinpayments_currency_rates_status'] = $this->request->post['module_coinpayments_currency_rates_status'];
        } else {
            $data['module_coinpayments_currency_rates_status'] = $this->config->get('module_coinpayments_currency_rates_status');
        }


        $data['error_warning'] = '';
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } elseif (isset($this->session->data['warning'])) {
            $data['error_warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        } else {
            $data['error_warning'] = '';
        }

        $data['success'] = '';
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['error_status'] = '';
        if (isset($this->error['status'])) {
            $data['error_status'] = $this->error['status'];
        }

        $data['error_client_id'] = '';
        if (isset($this->error['client_id'])) {
            $data['error_client_id'] = $this->error['client_id'];
        }

        $data['error_client_secret'] = '';
        if (isset($this->error['client_secret'])) {
            $data['error_client_secret'] = $this->error['client_secret'];
        }

        $data['error_invalid_credentials'] = '';
        if (isset($this->error['invalid_credentials'])) {
            $data['error_invalid_credentials'] = $this->error['invalid_credentials'];
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/coinpayments_currency_rates', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/ebay_listing')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['module_coinpayments_currency_rates_client_id'])) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        }

        if (empty($this->request->post['module_coinpayments_currency_rates_client_secret'])) {
            $this->error['client_secret'] = $this->language->get('error_client_secret');
        }

        if (empty($this->error)) {
            $this->load->model('extension/module/coinpayments_currency_rates');

            $client_id = $this->request->post['module_coinpayments_currency_rates_client_id'];
            $client_secret = $this->request->post['module_coinpayments_currency_rates_client_secret'];

            if (!$this->model_extension_module_coinpayments_currency_rates->validateCredentials($client_id, $client_secret)) {
                $this->error['invalid_credentials'] = $this->language->get('error_invalid_credentials');
            }
        }


        return !$this->error;
    }


    public function install()
    {
        $this->load->model('setting/setting');

        $this->load->model('setting/event');
        $settings = $this->model_setting_setting->getSetting('module_coinpayments_currency');
        $settings['module_coinpayments_currency_rates_status'] = 1;
        $this->model_setting_setting->editSetting('module_coinpayments_currency', $settings);

        $this->db->query("ALTER TABLE `" . DB_PREFIX . "currency` modify value double(40,30) not null");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "currency` modify code varchar(4) not null");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` modify currency_value decimal(40,30) default 1.00000000 not null");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` modify currency_code varchar(4) not null");


        $controllers_list = array(
            'account/order',
            'account/wishlist',
            'checkout/cart',
            'checkout/confirm',
            'common/cart',
            'extension/module/bestseller',
            'extension/module/featured',
            'extension/module/latest',
            'product/category',
            'product/manufacturer',
            'product/product',
            'product/search',
            'product/special',
        );

        foreach ($controllers_list as $controller) {
            $this->model_setting_event->addEvent('coinpayments_currency_check', 'catalog/controller/' . $controller . '/before', 'extension/module/coinpayments_currency_rates/index');
        }
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->load->model('setting/event');

        $settings = $this->model_setting_setting->getSetting('module_coinpayments_currency');
        $settings['module_coinpayments_currency_rates_status'] = 0;
        $this->model_setting_setting->editSetting('module_coinpayments_currency', $settings);


        $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` modify currency_value decimal(15, 8) default 1.00000000 not null");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "currency` modify value double(15, 8) not null");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "currency` modify code varchar(3) not null");
        $this->db->query("ALTER TABLE `" . DB_PREFIX . "order` modify currency_code varchar(3) not null");

        $this->model_setting_event->deleteEventByCode('coinpayments_currency_check');

    }
}