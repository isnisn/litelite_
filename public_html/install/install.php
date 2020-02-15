<?php
  ob_start();

  require_once('../includes/compatibility.inc.php');
  require_once('includes/functions.inc.php');
  require_once('includes/header.inc.php');

  echo '<h1>Installer</h1>' . PHP_EOL;

  error_reporting(version_compare(PHP_VERSION, '5.4.0', '<') ? E_ALL | E_STRICT : E_ALL);
  ini_set('ignore_repeated_errors', 'On');
  ini_set('log_errors', 'Off');
  ini_set('display_errors', 'On');
  ini_set('html_errors', 'On');

  date_default_timezone_set($_REQUEST['store_time_zone']);

  if (empty($_REQUEST['install'])) {
    header('Location: index.php');
    exit;
  }

  ### Set Environment Variables #################################

// Set platform name
  preg_match('#define\(\'PLATFORM_NAME\', \'([^\']+)\'\);#', file_get_contents('../includes/app_header.inc.php'), $matches);
  define('PLATFORM_NAME', isset($matches[1]) ? $matches[1] : false);

// Set platform version
  preg_match('#define\(\'PLATFORM_VERSION\', \'([^\']+)\'\);#', file_get_contents('../includes/app_header.inc.php'), $matches);
  define('PLATFORM_VERSION', isset($matches[1]) ? $matches[1] : false);

  $installation_path = file_absolute_path(dirname(__FILE__) .'/..') .'/';

  $_REQUEST['db_type'] = !empty($_REQUEST['db_type']) ? $_REQUEST['db_type'] : 'mysql';
  $_REQUEST['db_server'] = !empty($_REQUEST['db_server']) ? $_REQUEST['db_server'] : '127.0.0.1';
  $_REQUEST['db_collation'] = !empty($_REQUEST['db_collation']) ? $_REQUEST['db_collation'] : 'utf8_bin';
  $_REQUEST['admin_folder'] = str_replace('\\', '/', $_REQUEST['admin_folder']);
  $_REQUEST['admin_folder'] = rtrim($_REQUEST['admin_folder'], '/') . '/';

  ### PHP > Check Version #######################################

  echo '<p>Checking PHP version... ';

  if (version_compare(PHP_VERSION, '5.4', '<')) {
    die('<span class="error">[Error] PHP 5.4+ minimum requirement - Detected '. PHP_VERSION .'</span></p>');
  } else if (version_compare(PHP_VERSION, '7.1', '<=')) {
    echo PHP_VERSION .' <span class="ok">[OK]</span><br />'
       . '<span class="warning">[Warning] PHP 7.3+ recommended - Detected '. PHP_VERSION .' that has reached <a href="https://www.php.net/supported-versions.php" target="_blank">end of life</a></span></span></p>';
  } else {
    echo PHP_VERSION .' <span class="ok">[OK]</span></p>' . PHP_EOL;
  }

  ### PHP > Check Disabled Functions ############################

  echo '<p>Checking for disabled PHP functions... ';

  $critical_functions = array('error_log', 'ini_set');
  $important_functions = array('allow_url_fopen', 'exec', 'apache_get_modules');

  if ($disabled_functions = array_intersect($critical_functions, preg_split('#, ?#', ini_get('disable_functions')))) {
    die('<span class="error">[Error] Critical functions disabled ('. implode(', ', $disabled_functions) .'). You need to unblock them in php.ini</span></p>');
  } else if ($disabled_functions = array_intersect($important_functions, preg_split('#, ?#', ini_get('disable_functions')))) {
    echo '<span class="warning">[Warning] Some common functions are disabled ('. implode(', ', $disabled_functions) .'). It is recommended that you unblock them in php.ini.</span></p>';
  } else {
    echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
  }

  ### PHP > Check display_errors ################################

  echo '<p>Checking PHP display_errors... ';

  if (in_array(strtolower(ini_get('display_errors')), array('1', 'true', 'on', 'yes'))) {
    echo ini_get('display_errors') . ' <span class="ok">[OK]</span></p>';
  } else {
    echo ini_get('display_errors') . ' <span class="warning">[Warning] Missing permissions to display errors?</span></p>';
  }

  ### PHP > Check file system ################################

  echo '<p>Checking $_SERVER["DOCUMENT_ROOT"]... ';

  if (preg_match('#^'. preg_quote(str_replace("\\", '/', $_SERVER['DOCUMENT_ROOT']), '#') .'#', str_replace("\\", '/', __FILE__))) {
    echo $_SERVER['DOCUMENT_ROOT'] . ' <span class="ok">[OK]</span></p>';
  } else {
    echo $_SERVER['DOCUMENT_ROOT'] . ' <span class="warning">[Warning] There is a problem with your PHP configuration causing $_SERVER["DOCUMENT_ROOT"] and __FILE__ to return conflicting values.</span></p>';
  }

  ### Database > Connection #####################################

  echo '<p>Connecting to database... ';

  define('DB_TYPE', $_REQUEST['db_type']);
  define('DB_SERVER', $_REQUEST['db_server']);
  define('DB_USERNAME', $_REQUEST['db_username']);
  define('DB_PASSWORD', $_REQUEST['db_password']);
  define('DB_DATABASE', $_REQUEST['db_database']);
  define('DB_TABLE_PREFIX', $_REQUEST['db_table_prefix']);
  define('DB_DATABASE_CHARSET', 'utf8');
  define('DB_PERSISTENT_CONNECTIONS', 'false');

  define('DB_CONNECTION_CHARSET', 'utf8');
  require_once('../includes/library/lib_database.inc.php');

  if (!database::connect()) {
    die('Unable to connect <span class="error">[Error]</span></p>');
  } else {
    echo 'Connected! <span class="ok">[OK]</span></p>' . PHP_EOL;
  }

  ### Database > Check Version ##################################

  echo '<p>Checking MySQL version... ';

  $version_query = database::query("SELECT VERSION();");
  $version = database::fetch($version_query);

  if (version_compare($version['VERSION()'], '5.5', '<')) {
    die($version['VERSION()'] . ' <span class="error">[Error] MySQL 5.5+ required</span></p>');
  } else if (version_compare($version['VERSION()'], '5.7', '<')) {
    echo PHP_VERSION .' <span class="ok">[OK]</span><br />'
       . '<span class="warning">MySQL 5.7+ recommended</span></span></p>';
  } else {
    echo $version['VERSION()'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL;
  }

  ### Database > Check Charset ##################################

  echo '<p>Checking MySQL database default character set... ';

  $charset_query = database::query(
    "select default_character_set_name, default_collation_name from information_schema.SCHEMATA
    where schema_name = '". database::input(DB_DATABASE) ."'
    limit 1;"
  );
  $charset = database::fetch($charset_query);

  if ($charset['default_character_set_name'] != 'utf8') {
    echo($charset['default_character_set_name'] . ' <span class="warning">[Warning] The database default charset is not \'utf8\' and you might experience trouble with foreign characters. Try performing the following MySQL query: "ALTER DATABASE `'. DB_DATABASE .'` CHARACTER SET utf8 COLLATE '. $_REQUEST['db_collation'] .';"</span></p>');
  } else {
    echo $charset['default_character_set_name'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL;

    echo '<p>Checking MySQL database default collation... ';

    if ($charset['default_collation_name'] != $_REQUEST['db_collation']) {
      echo($charset['default_collation_name'] . ' <span class="warning">[Warning] The database default collation is not \''. $_REQUEST['db_collation'] .'\' and you might experience trouble with foreign characters. Try performing the following MySQL query: "ALTER DATABASE `'. DB_DATABASE .'` CHARACTER SET utf8 COLLATE '. $_REQUEST['db_collation'] .';"</span></p>');
    } else {
      echo $charset['default_collation_name'] . ' <span class="ok">[OK]</span></p>' . PHP_EOL;
    }
  }

  ### Config > Write ############################################

  echo '<p>Writing config file... ';

  $config = file_get_contents('config');

  $map = array(
    '{ADMIN_FOLDER}' => rtrim($_REQUEST['admin_folder'], '/'),
    '{DB_TYPE}' => $_REQUEST['db_type'],
    '{DB_SERVER}' => $_REQUEST['db_server'],
    '{DB_USERNAME}' => $_REQUEST['db_username'],
    '{DB_PASSWORD}' => $_REQUEST['db_password'],
    '{DB_DATABASE}' => $_REQUEST['db_database'],
    '{DB_TABLE_PREFIX}' => $_REQUEST['db_table_prefix'],
    '{DB_DATABASE_CHARSET}' => 'utf8',
    '{DB_PERSISTENT_CONNECTIONS}' => 'false',
    '{CLIENT_IP}' => $_REQUEST['client_ip'],
    '{PASSWORD_SALT}' => substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ", 10)), 0, 128),
  );

  foreach ($map as $search => $replace) {
    $config = str_replace($search, $replace, $config);
  }

  define('PASSWORD_SALT', $map['{PASSWORD_SALT}']); // we need it for later

  if (file_put_contents('../includes/config.inc.php', $config)) {
    echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
  } else {
    die('<span class="error">[Error]</span></p>' . PHP_EOL);
  }

  ### Database > Cleaning #######################################

  echo '<p>Cleaning database... ';

  $sql = file_get_contents('clean.sql');
  $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

  foreach (explode('-- --------------------------------------------------------', $sql) as $query) {
    $query = preg_replace('#^-- .*?\R+#m', '', $query);
    database::query($query);
  }

  echo '<span class="ok">[OK]</span></p>' . PHP_EOL;

  ### Database > Tables > Structure #############################

  echo '<p>Writing database tables... ';

  $sql = file_get_contents('structure.sql');

  $map = array(
    '`lc_' => '`'.DB_TABLE_PREFIX,
    '{DATABASE_COLLATION}' => $_REQUEST['db_collation'],
  );

  foreach ($map as $search => $replace) {
    $sql = str_replace($search, $replace, $sql);
  }

  foreach (explode('-- --------------------------------------------------------', $sql) as $query) {
    $query = preg_replace('#^-- .*?\R+#m', '', $query);
    database::query($query);
  }

  echo '<span class="ok">[OK]</span></p>' . PHP_EOL;

  ### Database > Tables > Data ##################################

  echo '<p>Writing database table data... ';

  $sql = file_get_contents('data.sql');
  $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

  $map = array(
    '{STORE_NAME}' => $_REQUEST['store_name'],
    '{STORE_EMAIL}' => $_REQUEST['store_email'],
    '{STORE_TIME_ZONE}' => $_REQUEST['store_time_zone'],
    '{STORE_COUNTRY_CODE}' => $_REQUEST['country_code'],
  );

  foreach ($map as $search => $replace) {
    $sql = str_replace($search, database::input($replace), $sql);
  }

  $sql = explode('-- --------------------------------------------------------', $sql);

  foreach ($sql as $query) {
    $query = preg_replace('#^-- .*?\R+#m', '', $query);
    database::query($query);
  }

  echo '<span class="ok">[OK]</span></p>' . PHP_EOL;

  ### Files > Default Data ######################################

  echo '<p>Copying default files...';
  if (file_xcopy('data/default/public_html/', $installation_path)) {
    echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
  } else {
    echo ' <span class="error">[Error]</span></p>' . PHP_EOL;
  }

  ### .htaccess mod rewrite #####################################

  echo '<p>Setting mod_rewrite base path...';

  $htaccess = file_get_contents('htaccess');

  $base_dir = str_replace(file_absolute_path($_SERVER['DOCUMENT_ROOT']), '', $installation_path);

  $htaccess = strtr($htaccess, array(
    '{BASE_DIR}' => $base_dir,
    '{ADMIN_DIR_FULL}' => $installation_path . $_REQUEST['admin_folder'],
  ));

  if (file_put_contents('../.htaccess', $htaccess)) {
    echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
  } else {
    echo ' <span class="error">[Error]</span></p>' . PHP_EOL;
  }

  ### Admin > Folder ############################################

  if (!empty($_REQUEST['admin_folder']) && $_REQUEST['admin_folder'] != 'admin/') {
    echo '<p>Renaming admin folder...';
    if (is_dir('../admin/')) {
      rename('../admin/', '../'.$_REQUEST['admin_folder']);
      echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
    } else {
      echo ' <span class="error">[Error: Not found]</span></p>' . PHP_EOL;
    }
  }

  ### Admin > .htaccess Protection ##############################

  echo '<p>Securing admin folder...';

  $htaccess = '# Solve 401 rewrite and auth conflict on some machines' . PHP_EOL
            .  'ErrorDocument 401 "Access Forbidden"' . PHP_EOL
            . PHP_EOL
            . '# Basic authentication' . PHP_EOL
            . '<IfModule mod_auth.c>' . PHP_EOL
            . '  AuthType Basic' . PHP_EOL
            . '  AuthName "Restricted Area"' . PHP_EOL
            . '  AuthUserFile "' . $installation_path . $_REQUEST['admin_folder'] . '.htpasswd"' . PHP_EOL
            . '  Require valid-user' . PHP_EOL
            . '</IfModule>' . PHP_EOL
            . '<IfModule mod_auth_basic.c>' . PHP_EOL
            . '  AuthType Basic' . PHP_EOL
            . '  AuthName "Restricted Area"' . PHP_EOL
            . '  AuthUserFile "' . $installation_path . $_REQUEST['admin_folder'] . '.htpasswd"' . PHP_EOL
            . '  Require valid-user' . PHP_EOL
            . '</IfModule>';

  if (is_dir('../'.$_REQUEST['admin_folder'])) {
    file_put_contents('../'. $_REQUEST['admin_folder'] .'.htaccess', $htaccess);
    echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
  } else {
    echo ' <span class="error">[Error: Not found]</span></p>' . PHP_EOL;
  }

  ### Admin > .htpasswd Users ###################################

  echo '<p>Granting admin access for user '. $_REQUEST['username'] .'...';

  if (is_dir('../'.$_REQUEST['admin_folder'])) {
    $htpasswd = $_REQUEST['username'] .':{SHA}'. base64_encode(sha1($_REQUEST['password'], true)) . PHP_EOL;
    if (file_put_contents('../'. $_REQUEST['admin_folder'] . '.htpasswd', $htpasswd)) {
      echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
    } else {
      echo ' <span class="error">[Error]</span></p>' . PHP_EOL;
    }
  } else {
    echo ' <span class="error">[Error: Not found]</span></p>' . PHP_EOL;
  }

  ### Admin > Database > Users ##################################

  require('../includes/functions/func_password.inc.php');

  database::query(
    "insert into ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_users`') ."
    (`id`, `status`, `username`, `password_hash`, `date_updated`, `date_created`)
    values ('1', '1', '". database::input($_REQUEST['username']) ."', '". database::input(password_hash($_REQUEST['password'], PASSWORD_DEFAULT)) ."', '". date('Y-m-d H:i:s') ."', '". date('Y-m-d H:i:s') ."');"
  );

  ### Set platform database version #############################

  echo '<p>Set platform database version...';

  if (defined('PLATFORM_VERSION')) {

    database::query(
      "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
      set `value` = '". database::input(PLATFORM_VERSION) ."'
      where `key` = 'platform_database_version'
      limit 1;"
    );

    echo ' <strong>'. PLATFORM_VERSION .'</strong></p>' . PHP_EOL;

  } else {
    echo ' <span class="error">[Error: Not defined]</span></p>' . PHP_EOL;
  }

  ### Regional Data Patch #######################################

  if (!empty($_REQUEST['country_code'])) {

    echo '<p>Patching installation with regional data...';

    $directories = glob('data/*{'. $_REQUEST['country_code'] .',XX}*/', GLOB_BRACE);

    if (!empty($directories)) {
      foreach ($directories as $dir) {

        $dir = basename($dir);
        if ($dir == 'demo') continue;
        if ($dir == 'default') continue;

        foreach (glob('data/'. $dir .'/*.sql') as $file) {
          $sql = file_get_contents($file);

          if (empty($sql)) continue;

          $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

          foreach (explode('-- --------------------------------------------------------', $sql) as $query) {
            $query = preg_replace('#^-- .*?\R+#m', '', $query);
            database::query($query);
          }
        }
      }

      if (file_exists('data/'. $dir .'/public_html/')) {
        file_xcopy('data/'. $dir .'/public_html/', $installation_path);
      }
    }

    echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
  }

  ### Database > Tables > Demo Data #############################

  if (!empty($_REQUEST['demo_data'])) {
    echo '<p>Writing demo data... ';

    $sql = file_get_contents('data/demo/data.sql');

    if (!empty($sql)) {
      $sql = str_replace('`lc_', '`'.DB_TABLE_PREFIX, $sql);

      $sql = explode('-- --------------------------------------------------------', $sql);

      foreach ($sql as $query) {
        $query = preg_replace('#^-- .*?\R+#m', '', $query);
        database::query($query);
      }
    }

    echo '<span class="ok">[OK]</span></p>' . PHP_EOL;
  }

  ### Files > Demo Data #########################################

  if (!empty($_REQUEST['demo_data'])) {
    echo '<p>Copying demo files...';

    if (file_xcopy('data/demo/public_html/', $installation_path)) {
      echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
    } else {
      echo ' <span class="error">[Error]</span></p>' . PHP_EOL;
    }
  }

  ### Files > Delete Some Files #########################################

  echo '<p>Delete some files...';

  if (file_delete('vqmod/xml/multiple_category_images.xml')) {
    echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
  } else {
    echo ' <span class="error">[Skipped]</span></p>' . PHP_EOL;
  }

  ### Files > Development Type ##################################

  echo '<p>Preparing CSS files...' . PHP_EOL;

  if (!empty($_REQUEST['development_type']) && $_REQUEST['development_type'] == 'advanced') {

    $files_to_delete = array(
      '../includes/templates/default.catalog/css/app.css',
      '../includes/templates/default.catalog/css/checkout.css',
      '../includes/templates/default.catalog/css/framework.css',
      '../includes/templates/default.catalog/css/printable.css',
    );

    foreach ($files_to_delete as $file) {
      echo 'Delete '. $file;
      if (file_delete($file)) {
        echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
      } else {
        echo '<span class="error">[Error]</span></p>' . PHP_EOL;
      }
    }

  } else {

    $files_to_delete = array(
      '../includes/templates/default.catalog/css/*.min.css',
      '../includes/templates/default.catalog/css/*.min.css.map',
      '../includes/templates/default.catalog/js/*.min.js',
      '../includes/templates/default.catalog/js/*.min.js.map',
      '../includes/templates/default.catalog/less/',
    );

    foreach ($files_to_delete as $file) {
      echo 'Delete '. $file;
      if (file_delete($file)) {
        echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;
      } else {
        echo ' <span class="error">[Error]</span></p>' . PHP_EOL;
      }
    }

    foreach (glob('../includes/templates/default.catalog/layouts/*.inc.php') as $file) {
      echo 'Modify '. $file . PHP_EOL;
      $contents = file_get_contents($file);
      $search_replace = array(
        'app.min.css' => 'app.css',
        'checkout.min.css'  => 'checkout.css',
        'framework.min.css' => 'framework.css',
        'printable.min.css' => 'printable.css',
        'app.min.js' => 'app.js',
      );
      file_put_contents($file, strtr($contents, $search_replace));
    }
  }

  ### Set cache breakpoint ######################################

  echo '<p>Set cache breakpoint...';

  database::query(
    "update ". str_replace('`lc_', '`'.DB_TABLE_PREFIX, '`lc_settings`') ."
    set value = '". date('Y-m-d H:i:s') ."'
    where `key` = 'cache_system_breakpoint'
    limit 1;"
  );

  echo ' <span class="ok">[OK]</span></p>' . PHP_EOL;

  ### #############################################################

  echo PHP_EOL . '<h2>Complete</h2>' . PHP_EOL
     . '<p>Installation complete! Please delete the <strong>~/install/</strong> folder.</p>' . PHP_EOL
     . '<p>You may now log in to the <a href="../'. $_REQUEST['admin_folder'] .'">administration area</a> and start configuring your store.</p>' . PHP_EOL
     . '<p>Check out our <a href="https://wiki.litecart.net/" target="_blank">LiteCart Wiki</a> for some great tips. Turn to our <a href="https://www.litecart.net/forums/" target="_blank">Community Forums</a> if you have questions.</p>' . PHP_EOL;

  echo '<form method="get" action="http://twitter.com/intent/tweet" target="_blank">' . PHP_EOL
     . '  <input type="hidden" value="https://www.litecart.net/" />' . PHP_EOL
     . '  <div class="form-group">' . PHP_EOL
     . '    <div class="input-group">' . PHP_EOL
     . '      <input type="text" class="form-control" name="text" value="Woohoo! I just installed #LiteCart and I am super excited! :)" />' . PHP_EOL
     . '      <span class="input-group-btn">' . PHP_EOL
     . '        <button class="btn btn-primary" type="submit">Tweet!</button>' . PHP_EOL
     . '      </span>' . PHP_EOL
     . '    </div>' . PHP_EOL
     . '  </div>' . PHP_EOL
     . '</form>' . PHP_EOL;

  if (!empty($_REQUEST['redirect'])) {
    header('Location: '. $_REQUEST['redirect']);
    exit;
  }

  require('includes/footer.inc.php');
