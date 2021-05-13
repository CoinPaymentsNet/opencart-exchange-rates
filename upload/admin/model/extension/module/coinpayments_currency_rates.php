<?php

/**
 * CoinPayments Payment Model
 */
class ModelExtensionModuleCoinpaymentsCurrencyRates extends Model
{

    /** @var CoinpaymentsCurrencyRate $coinpayments */
    private $coinpayments;

    /**
     * CoinPayments Payment Model Construct
     * @param Registry $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->coinpayments = new CoinpaymentsCurrencyRates($registry);
    }

    /**
     * @param $client_id
     * @param $client_secret
     * @return bool
     * @throws Exception
     */
    public function validateCredentials($client_id, $client_secret)
    {
        $valid = false;
        $currencies_list = $this->coinpayments->getAcceptedCurrencies($client_id, $client_secret);
        if (isset($currencies_list['items']) && !empty($currencies_list['items'])) {
            $currencies_list['items'] = array_filter($currencies_list['items'], function ($currency){
                return $currency['currency']['type'] != CoinpaymentsCurrencyRates::TOKEN_TYPE;
            });

            $this->updateAcceptedCurrencies($currencies_list['items']);
            $valid = true;
        }
        return $valid;
    }

    protected function updateAcceptedCurrencies($currencies_list)
    {

        $symbols = array_map(function ($currency) {
            return $currency['currency']['symbol'];
        }, $currencies_list);

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code IN ('" . implode("', '", $symbols) . "')");
        $exists = array_map(function ($currency) {
            return $currency['code'];
        }, $query->rows);

        foreach ($currencies_list as $currency) {
            if (!in_array($currency['currency']['symbol'], $exists)) {
                $this->createCoinCurrency($currency);
            }
        }

    }

    private function createCoinCurrency($currency)
    {

        $values = array(
            'title' => $this->db->escape($currency['currency']['name']),
            'code' => $this->db->escape($currency['currency']['symbol']),
            'symbol_right' => $this->db->escape($currency['currency']['symbol']),
            'decimal_place' => $currency['currency']['decimalPlaces'],
            'status' => $currency['currency']['status'] == 'active' && $currency['switcherStatus'] == 'enabled',
        );

        $this->db->query("INSERT INTO " . DB_PREFIX . "currency (title, code, symbol_right, decimal_place, status) VALUES ('". implode("', '", $values) . "')");
    }

}
