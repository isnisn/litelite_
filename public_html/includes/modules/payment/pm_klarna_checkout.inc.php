<?php

  class pm_klarna_checkout {
    public $id = __CLASS__;
    public $name = 'Klarna Checkout';
    public $description = '';
    public $author = 'TiM International';
    public $version = '1.1';
    public $website = 'https://www.klarna.se';
    public $priority = 0;
    
    public function __construct() {
      if (empty(session::$data['klarna'])) session::$data['klarna'] = array();
      $this->cache = &session::$data['klarna'];
    }
    
    public function options() {
      
      $options = array();
      
      if (empty($this->settings['status'])) return;
      
      if (!empty($this->settings['geo_zone_id'])) {
        if (!functions::reference_in_geo_zone($this->settings['geo_zone_id'], customer::$data['country_code'], customer::$data['zone_code'])) return;
      }
      
      return array(
        'title' => 'Klarna',
        'options' => array(
          array(
            'id' => 'checkout',
            'icon' => $this->settings['icon'],
            'name' => language::translate(__CLASS__.':title_option_checkout', 'Checkout'),
            'description' => language::translate(__CLASS__.':title_option_description', 'Invoice, installment, card, or direct bank'),
            'fields' => '',
            'cost' => 0,
            'tax_class_id' => 0,
            'confirm' => language::translate(__CLASS__.':title_button_confirm', 'Next Step / Payment'),
          ),
        ),
      );
    }
    
    public function transfer($order) {
      
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'Klarna/Checkout.php');

      if ($this->settings['gateway'] != 'Live') {
        $this->settings['merchant_id'] = '200';
        $this->settings['merchant_key'] = 'test';
      }

      $cart = array(
        'purchase_country' => 'SE',
        'purchase_currency' => 'SEK',
        'locale' => 'sv-se',
        'shipping_address' => array(
          'given_name' => $order->data['customer']['shipping_address']['firstname'],
          'family_name' => $order->data['customer']['shipping_address']['lastname'],
          'street_address' => $order->data['customer']['shipping_address']['address1'],
          'postal_code' => $order->data['customer']['shipping_address']['postcode'],
          'city' => $order->data['customer']['shipping_address']['city'],
          'country' => $order->data['customer']['shipping_address']['country_code'],
          'email' => $order->data['customer']['email'],
          'phone' => $order->data['customer']['phone'],
        ),
        //'gui' => array(
        //  'layout' => 'desktop',
        //),
        'merchant' => array(
          'id' => $this->settings['merchant_id'],
          'terms_uri' => document::ilink('customer_service'),
          'checkout_uri' => document::ilink('klarna_checkout'),
          'confirmation_uri' => document::ilink('order_process'),
          'push_uri' => document::ilink('klarna_callback') . '?order_uid='. $order->data['uid'] .'&klarna_order={checkout.order.uri}',
        ),
        'cart' => array(
          'items' => array(),
        ),
      );

      foreach ($order->data['items'] as $item) {
        $price_incl_tax = $item['price'] + $item['tax'];
        $cart['cart']['items'][] = array(
          'type' => 'physical',
          'reference' => $item['sku'],
          'name' => $item['name'],
          'uri' => document::ilink('product', array('product_id' => $item['product_id'])),
          'quantity' => (int)$item['quantity'],
          'unit_price' => round(currency::calculate($price_incl_tax * 100, 'SEK')),
          'tax_rate' => round($item['tax']/$item['price']*10000),
        );
      }
      
      foreach ($order->data['order_total'] as $row) {
        if (empty($row['calculate'])) continue;
        $price_incl_tax = $row['value'] + $row['tax'];
        $cart['cart']['items'][] = array(
          'type' => ($row['module_id'] == 'ot_shipping_fee') ? 'shipping_fee' : 'physical',
          'reference' => $row['module_id'],
          'name' => $row['title'],
          'quantity' => 1,
          'unit_price' => round(currency::calculate($price_incl_tax * 100, 'SEK')),
          'tax_rate' => round($row['tax']/$row['value']*10000),
        );
      }

      if ($this->settings['gateway'] != 'Live') {
        $cart['shipping_address'] = array(
          'given_name' => 'Testperson-se',
          'family_name' => 'Approved',
          'street_address' => utf8_encode('Storgatan 1'),
          'postal_code' => '12345', // Mandatory
          'city' => 'Ankeborg',
          'country' => 'se',
          'email' => 'checkout@testdrive.klarna.com',  // Mandatory
          'phone' => '0765260000',
        );
      }
      
      if (strtolower(language::$selected['charset']) != 'utf-8') {
        $this->_klarna_str_encode($cart);
      }
      
      if ($this->settings['gateway'] == 'Live') {
        Klarna_Checkout_Order::$baseUri = 'https://checkout.klarna.com/checkout/orders';
      } else {
        Klarna_Checkout_Order::$baseUri = 'https://checkout.testdrive.klarna.com/checkout/orders';
      }

      Klarna_Checkout_Order::$contentType = "application/vnd.klarna.checkout.aggregated-order-v2+json";

      $connector = Klarna_Checkout_Connector::create($this->settings['merchant_key']);

    // Resume session
      if (!empty(session::$data['klarna_open_session_order_id'])) {

        $klarna_order = new Klarna_Checkout_Order($connector, Klarna_Checkout_Order::$baseUri .'/'. session::$data['klarna_open_session_order_id']);

        try {
          $klarna_order->fetch();
          if (!isset($klarna_order['status']) || $klarna_order['status'] == 'checkout_complete') {
            unset(session::$data['klarna_open_session_order_id']);
          } else {
            $klarna_order->update($cart);
          }

        } catch (Exception $e) {

          unset(session::$data['klarna_open_session_order_id']);
        }
      }

      if (empty(session::$data['klarna_open_session_order_id'])) {
            $klarna_order = new Klarna_Checkout_Order($connector);
            $klarna_order->create($cart);
            $klarna_order->fetch();

            $order->save();
      }

      session::$data['klarna_open_session_order_id'] = $klarna_order['id'];
      
      echo $klarna_order['gui']['snippet'];
      exit;
    }
    
    public function verify($order) {
      
      $attempts = 0;
      while(empty($klarna) && $attempts < 20) {
        if (!empty($attempts)) sleep(1);
        $klarna_query = database::query(
          "select * from ". DB_TABLE_PREFIX ."klarna
          where order_uid = '". database::input($order->data['uid']) ."'
          order by date_created
          limit 1;"
        );
        $klarna = database::fetch($klarna_query);
        $attempts++;
      }
      
      if (empty($klarna)) return array('error' => 'Missing transaction status');
      
      if (empty($klarna['klarna_order_uri'])) {
        return array('error' => 'Transaction was missing the Klarna order uri');
      }
      
      if ($this->settings['gateway'] != 'Live') {
        $this->settings['merchant_id'] = '200';
        $this->settings['merchant_key'] = 'test';
      }
      
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'Klarna/Checkout.php');
      
      Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
      
      $connector = Klarna_Checkout_Connector::create($this->settings['merchant_key']);
      
      $klarna_order = new Klarna_Checkout_Order($connector, $klarna['klarna_order_uri']);
      $klarna_order->fetch();
      
      if ($klarna_order['status'] != 'checkout_complete') {
        return array('error' => 'Klarna checkout was not completed');
      }
      
      unset(session::$data['klarna_open_session']);
      
    // Import address data
      $map = array(
        'given_name' => 'firstname',
        'family_name' => 'lastname',
        'street_address' => 'address1',
        'care_of' => 'address2',
        'postal_code' => 'postcode',
        'city' => 'city',
        'country' => 'country_code',
        'phone' => 'phone',
      );
      
      foreach ($map as $source => $target) {
        if (isset($klarna_order['shipping_address'][$source])) {
          $string = (strtoupper(language::$selected['charset']) != 'UTF-8') ? utf8_decode((string)$klarna_order['shipping_address'][$source]) : (string)$klarna_order['shipping_address'][$source];
          $order->data['customer']['shipping_address'][$target] = $string;
        }
      }
      
      $order->data['customer']['shipping_address']['country_code'] = strtoupper($order->data['customer']['shipping_address']['country_code']);
      
    // Tell Klarna order is created
      if ($klarna_order['status'] == 'checkout_complete') {
        $update['status'] = 'created';
        $update['merchant_reference'] = array(
          'orderid1' => $order->data['uid'],
        );
        $klarna_order->update($update);
      }
      
      return array(
        'order_status_id' => $this->settings['order_status_id'],
        'transaction_id' => $klarna_order['reference'],
      );
    }
    
    public function receipt() {
      global $order;
      
      $klarna_query = database::query(
        "select * from ". DB_TABLE_PREFIX ."klarna
        where order_uid = '". database::input($order->data['uid']) ."'
        order by date_created
        limit 1;"
      );
      $klarna = database::fetch($klarna_query);
      
      if (empty($klarna)) return;
      
      if ($this->settings['gateway'] != 'Live') {
        $this->settings['merchant_id'] = '200';
        $this->settings['merchant_key'] = 'test';
      }
      
      require_once(FS_DIR_HTTP_ROOT . WS_DIR_EXT . 'Klarna/Checkout.php');
      
      Klarna_Checkout_Order::$contentType = 'application/vnd.klarna.checkout.aggregated-order-v2+json';
      
      $connector = Klarna_Checkout_Connector::create($this->settings['merchant_key']);
      
      $klarna_order = new Klarna_Checkout_Order($connector, $klarna['klarna_order_uri']);
      $klarna_order->fetch();
      
      return $klarna_order['gui']['snippet'];
    }
    
    private function _klarna_str_encode(&$input) {
      if (is_string($input)) {
        $input = utf8_encode($input);
      } else if (is_array($input)) {
        foreach ($input as &$value) {
          $this->_klarna_str_encode($value);
        }
        unset($value);
      } else if (is_object($input)) {
        $vars = array_keys(get_object_vars($input));
        foreach ($vars as $var) {
          $this->_klarna_str_encode($input->$var);
        }
      }
    }
    
    private function _klarna_str_decode(&$input) {
      
      if (is_string($input)) {
        $input = utf8_decode($input);
      } else if (is_array($input)) {
        foreach ($input as &$value) {
          $this->_klarna_str_decode($value);
        }
        unset($value);
      } else if (is_object($input)) {
        $vars = array_keys(get_object_vars($input));
        foreach ($vars as $var) {
          $this->_klarna_str_decode($input->$var);
        }
      }
    }
    
    public function after_process() {
    }
    
    function settings() {
      return array(
        array(
          'key' => 'status',
          'default_value' => '1',
          'title' => language::translate(__CLASS__.':title_status', 'Status'),
          'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
          'function' => 'toggle("e/d")',
        ),
        array(
          'key' => 'icon',
          'default_value' => 'images/payment/klarna.png',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_id',
          'default_value' => '200',
          'title' => language::translate(__CLASS__.':title_merchant_id', 'Merchant ID'),
          'description' => language::translate(__CLASS__.':description_merchant_id', 'Your merchant ID provided by Klarna.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'merchant_key',
          'default_value' => 'test',
          'title' => language::translate(__CLASS__.':title_merchant_key', 'Merchant Key'),
          'description' => language::translate(__CLASS__.':description_merchant_key', 'Your merchant key provided by Klarna.'),
          'function' => 'password()',
        ),
        array(
          'key' => 'gateway',
          'default_value' => 'Live',
          'title' => language::translate(__CLASS__.':title_gateway', 'Gateway'),
          'description' => language::translate(__CLASS__.':description_gateway', 'Select your payment gateway.'),
          'function' => 'radio(\'Test\',\'Live\')',
        ),
        array(
          'key' => 'tax_class_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_tax_class', 'Tax Class'),
          'description' => language::translate(__CLASS__.':description_tax_class', 'The tax class for the invoice fee.'),
          'function' => 'tax_classes()',
        ),
        array(
          'key' => 'order_status_id',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_order_status', 'Order Status') .': '. language::translate(__CLASS__.':title_complete', 'Complete'),
          'description' => language::translate(__CLASS__.':description_order_status', 'Give successful orders made with this payment module the following order status.'),
          'function' => 'order_status()',
        ),
        array(
          'key' => 'geo_zone_id',
          'default_value' => '',
          'title' => language::translate(__CLASS__.':title_geo_zone_limitation', 'Geo Zone Limitation'),
          'description' => language::translate(__CLASS__.':description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.'),
          'function' => 'geo_zones()',
        ),
        array(
          'key' => 'priority',
          'default_value' => '0',
          'title' => language::translate(__CLASS__.':title_priority', 'Priority'),
          'description' => language::translate(__CLASS__.':description_priority', 'Process this module in the given priority order.'),
          'function' => 'int()',
        ),
      );
    }
    
    public function install() {
      database::query(
        "CREATE TABLE IF NOT EXISTS `". DB_TABLE_PREFIX ."klarna` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_uid` varchar(13) NOT NULL,
          `klarna_order_id` varchar(64) NOT NULL,
          `klarna_order_uri` varchar(256) NOT NULL,
          `parameters` TEXT NOT NULL,
          `ip` varchar(15) NOT NULL,
          `date_created` datetime NOT NULL,
          PRIMARY KEY (`id`)
        );"
      );
    }
    
    public function uninstall() {
      database::query(
        "DROP TABLE IF EXISTS `". DB_TABLE_PREFIX ."klarna`"
      );
    }
  }
    
?>