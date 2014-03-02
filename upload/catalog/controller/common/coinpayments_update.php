<?php  
/*
Copyright (c) 2014 CoinPayments.net
Copyright (c) 2013 John Atkinson (jga)

Permission is hereby granted, free of charge, to any person obtaining a copy of this 
software and associated documentation files (the "Software"), to deal in the Software 
without restriction, including without limitation the rights to use, copy, modify, 
merge, publish, distribute, sublicense, and/or sell copies of the Software, and to 
permit persons to whom the Software is furnished to do so, subject to the following 
conditions:

The above copyright notice and this permission notice shall be included in all copies 
or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER 
DEALINGS IN THE SOFTWARE.
*/
class ControllerCommonCoinPaymentsUpdate extends Controller {
	private $api_public = '';
	private $api_secret = '';
	private $wanted_coins = array('KDC','BTC','LTC');
	
	public function index() {
		if (extension_loaded('curl') && count($this->wanted_coins)) {
			$data = array();
			
			$do_update = false;
			
			$ts = $this->cache->get('coinpayments_last_check');
			if (!$ts) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code = '".$this->db->escape(strtoupper($this->wanted_coins[0]))."'"); //check the first coin just for timestamping purposes						
				if(!$query->row) {
					//can't find coin, run update...
					$do_update = true;
				} else {
					$ts = strtotime($query->row['date_modified']);
					if ($ts === FALSE || (time() - $ts) > 600) {
						//error parsing date or 600 secs has passed, run update...
						$do_update = true;
					}
				}
			} else if ((time() - $ts) > 600) {
				$do_update = true;
			}
			
			if ($do_update) {
				$this->runUpdate();
			}
		}
	}
	
	private function createCoin($code, $name) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency WHERE code = '".$this->db->escape($code)."'"); //check the first coin just for timestamping purposes						
			if(!$query->row) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "currency (title, code, symbol_right, decimal_place, status) VALUES ('".$this->db->escape($name)."', '".$this->db->escape($code)."', ' ".$this->db->escape($code)."', '8', '1')");
			}
	}
		
	public function runUpdate() {
		$this->cache->set('coinpayments_last_check', time());
		try {
			$data = $this->api_call('rates');
			if ($data['error'] == 'ok') {
				if (isset($data['result']) && is_array($data['result'])) {
					$default_currency_code = $this->config->get('config_currency');
					if (isset($data['result'][$default_currency_code])) {
						$base_rate = $data['result'][$default_currency_code]['rate_btc'];
						foreach ($this->wanted_coins as $c) {
							$c = strtoupper($c);
							if (isset($data['result'][$c])) {
								$this->createCoin($c, $data['result'][$c]['name']);
							}
							$rate = (float)round($base_rate * (1/$data['result'][$c]['rate_btc']), 8);
							$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '" . $rate . "', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($c) . "'");
						}
						$this->db->query("UPDATE " . DB_PREFIX . "currency SET value = '1.00000', date_modified = '" .  $this->db->escape(date('Y-m-d H:i:s')) . "' WHERE code = '" . $this->db->escape($default_currency_code) . "'");
						$this->cache->delete('currency');					
					} else {
						throw new Exception('Error getting CoinPayments exchange rates: could not find your base currency in the rates: '.$default_currency_code);
					}
				} else {
					throw new Exception('Error getting CoinPayments exchange rates: no rates returned!');
				}
			} else {
				throw new Exception('Error getting CoinPayments exchange rates: '.$data['error']);
			}		
		} catch (Exception $e) {
			print $e->getMessage()."<br />";
		}
	}
	
	private function api_call($cmd, $req = array()) {      
	    // Set the API command and required fields 
	    $req['version'] = 1; 
	    $req['cmd'] = $cmd; 
	    $req['key'] =  $this->api_public; 
	    $req['format'] = 'json'; //supported values are json and xml 
	     
	    // Generate the query string 
	    $post_data = http_build_query($req, '', '&'); 
	     
	    // Calculate the HMAC signature on the POST data 
	    $hmac = hash_hmac('sha512', $post_data, $this->api_secret); 
	     
	    // Create cURL handle and initialize (if needed) 
	    static $ch = NULL; 
	    if ($ch === NULL) { 
	        $ch = curl_init('https://www.coinpayments.net/api.php'); 
	        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE); 
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
	    } 
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac)); 
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); 
	     
	    // Execute the call and close cURL handle      
	    $data = curl_exec($ch);                 
	    // Parse and return data if successful. 
	    if ($data !== FALSE) { 
	        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) { 
	            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP 
	            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING); 
	        } else { 
	            $dec = json_decode($data, TRUE); 
	        } 
	        if ($dec !== NULL && count($dec)) { 
	            return $dec; 
	        } else { 
	            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message 
	            return array('error' => 'Unable to parse JSON result ('.json_last_error().')'); 
	        } 
	    } else { 
	        return array('error' => 'cURL error: '.curl_error($ch)); 
	    } 
	}
}
