opencart-exchange-rates
=======================

CoinPayments Exchange Rate Extension by CoinPayments.net

Based on the Bitcoin Exchange Rate v1.2 OpenCart Extension by John Atkinson (jga) from BTC Gear (http://www.btcgear.com)

===INSTALLATION===

Installation is a three step process.  You must upload coinpayments_update.php and you must modify index.php.

STEP 1) Open coinpayments_update.php in your Text Editor. You will see these lines:<br />
	private $api_public = '';<br />
	private $api_secret = '';<br />
	private $wanted_coins = array('KDC','BTC','LTC');<br />

Fill in your API Public and Private keys from one of your API keys on the API Keys page at CoinPayments.net. The API key only needs the 'rates' permission.
Put the currency codes of the currencies you want to add/update in OpenCart in the $wanted_coins array.

Save and close the file.
  
STEP 2) Upload coinpayments_update.php:<br />
Place coinpayments_update.php in the folder [OpenCart]/catalog/controller/common

STEP 3) Modify index.php:<br />
Add the following lines to the index.php file in the base directory of your Opencart installation.

After:<br />
// SEO URL's<br />
$controller->addPreAction(new Action('common/seo_url'));

Add:<br />
// Update Currencies<br />
$controller->addPreAction(new Action('common/coinpayments_update'));

Enjoy!  Please contact support@coinpayments.net with any issues you may have.

Copyright (c) 2014 CoinPayments.net<br />
Copyright (c) 2013 John Atkinson (jga)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
