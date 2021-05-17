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
    public function updateCurrencyRates($client_id, $client_secret, $default_currency)
    {

        $currencies_list = $this->coinpayments->getAcceptedCurrencies($client_id, $client_secret);
        if (isset($currencies_list['items']) && !empty($currencies_list['items'])) {
            $currencies_list['items'] = array_filter($currencies_list['items'], function ($currency) {
                return $currency['currency']['type'] != CoinpaymentsCurrencyRates::TOKEN_TYPE;
            });

            $this->updateAcceptedCurrencies($currencies_list['items']);
            $this->updateAcceptedCurrenciesRates($currencies_list['items'], $default_currency);
        }
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

    protected function updateAcceptedCurrenciesRates($currencies_list, $default_currency)
    {

        $coin_default_currency = $this->coinpayments->getCoinCurrency($default_currency);

        $rates = $this->coinpayments->getCurrenciesRates(
            $currencies_list,
            $coin_default_currency['id']
        );


        $coin_currency_ids = array();
        $coin_currency_statuses = array();
        foreach ($currencies_list as $currency) {
            $coin_currency_ids[$currency['currency']['id']] =  $currency['currency']['symbol'];
            $coin_currency_statuses[$currency['currency']['id']] =  $currency['currency']['status'] == 'active' && $currency['switcherStatus'] == 'enabled';
        }

        $rate_conditions = array(sprintf("WHEN '%s' THEN '%s'", $default_currency, '1'));
        $status_conditions = array(sprintf("WHEN '%s' THEN '%s'", $default_currency, '1'));
        if (!empty($rates['items'])) {
            foreach ($rates['items'] as $rate) {
                $rate_conditions[] = sprintf("WHEN '%s' THEN '%s'", $coin_currency_ids[$rate['quoteCurrencyId']], $rate['rate']);
                $status_conditions[] = sprintf("WHEN '%s' THEN '%s'", $coin_currency_ids[$rate['quoteCurrencyId']], $coin_currency_statuses[$rate['quoteCurrencyId']]);
            }
        }

        $coin_currency_ids[] = $default_currency;

        $this->db->query("
        UPDATE " . DB_PREFIX . "currency 
        SET value = (CASE code
        " . implode("\n", $rate_conditions) . "
        END), 
        status = (CASE code
        " . implode("\n", $status_conditions) . "
        END), 
        date_modified = '" . $this->db->escape(date('Y-m-d H:i:s')) . "' 
        WHERE code IN ('" . implode("', '", $coin_currency_ids) . "')");


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

        $this->db->query("INSERT INTO " . DB_PREFIX . "currency (title, code, symbol_right, decimal_place, status) VALUES ('" . implode("', '", $values) . "')");
    }

}
