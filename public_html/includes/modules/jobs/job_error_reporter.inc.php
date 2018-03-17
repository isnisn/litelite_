<?php

  class job_error_reporter {
    public $id = __CLASS__;
    public $name = 'Error Reporter';
    public $description = '';
    public $author = 'LiteCart Dev Team';
    public $version = '1.0';
    public $website = 'http://www.litecart.net';
    public $priority = 0;

    public function process($force, $last_pushed) {

      if (empty($this->settings['status'])) return;

      if (empty($force)) {
        if (!empty($this->settings['working_hours'])) {
          list($from_time, $to_time) = explode('-', $this->settings['working_hours']);
          if (time() < strtotime("Today $from_time") || time() > strtotime("Today $to_time")) return;
        }

        switch ($this->settings['report_frequency']) {
          case 'Immediately':
            break;
          case 'Hourly':
            if (strtotime($last_pushed) > strtotime('-1 hour')) return;
            break;
          case 'Daily':
            if (strtotime($last_pushed) > strtotime('-1 day')) return;
            break;
          case 'Weekly':
            if (strtotime($last_pushed) > strtotime('-1 week')) return;
            break;
          case 'Monthly':
            if (strtotime($last_pushed) > strtotime('-1 month')) return;
            break;
        }
      }

      $error_log_file = ini_get('error_log');
      $contents = file_get_contents($error_log_file);
      if (!empty($contents)) {
        $result = functions::email_send(
          null,
          !empty($this->settings['email_receipient']) ? $this->settings['email_receipient'] : settings::get('store_email'),
          '[Error Report] '. settings::get('store_name'),
          PLATFORM_NAME .' '. PLATFORM_VERSION ."\r\n\r\n". $contents
        );
        if ($result === true) file_put_contents($error_log_file, '');
      }
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
          'key' => 'report_frequency',
          'default_value' => 'Weekly',
          'title' => language::translate(__CLASS__.':title_report_frequency', 'Report Frequency'),
          'description' => language::translate(__CLASS__.':description_report_frequency', 'How often the reports should be sent.'),
          'function' => 'radio("Immediately","Hourly","Daily","Weekly","Monthly")',
        ),
        array(
          'key' => 'working_hours',
          'default_value' => '07:00-21:00',
          'title' => language::translate(__CLASS__.':title_working_hours', 'Working Hours'),
          'description' => language::translate(__CLASS__.':description_working_hours', 'During what hours of the day the job would operate e.g. 07:00-21:00.'),
          'function' => 'input()',
        ),
        array(
          'key' => 'email_receipient',
          'default_value' => settings::get('store_email'),
          'title' => language::translate(__CLASS__.':title_email_receipient', 'Email Receipient'),
          'description' => language::translate(__CLASS__.':description_email_receipient', 'The email address where reports will be sent.'),
          'function' => 'input()',
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
  }
