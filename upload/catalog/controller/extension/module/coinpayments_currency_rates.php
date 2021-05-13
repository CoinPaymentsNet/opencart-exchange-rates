<?php

class ControllerExtensionModuleCoinpaymentsCurrencyRates extends Controller
{

    const CACHE_KEY = 'coinpayments_last_check';

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function index()
    {
        if ($this->config->get('module_coinpayments_currency_rates_status') == 1 && extension_loaded('curl')) {
            $update = false;

            if ((time() - $this->cache->get(self::CACHE_KEY)) > 60) {
                $update = true;
            }

            if ($update) {
                $this->update_coin_rates();
            }
        }
    }

    private function update_coin_rates()
    {
        $this->cache->set('coinpayments_last_check', time());
        try {
            $this->load->model('extension/module/coinpayments_currency_rates');

            $client_id = $this->config->get('module_coinpayments_currency_rates_client_id');
            $client_secret = $this->config->get('module_coinpayments_currency_rates_client_secret');
            $this->model_extension_module_coinpayments_currency_rates->updateCurrencyRates($client_id, $client_secret, $this->config->get('config_currency'));

        } catch (Exception $e) {
            print $e->getMessage() . "<br />";
        }
    }
}