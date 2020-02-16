<?php
  if (empty($_GET['order_uid'])) {
    header('HTTP/1.1 400 Bad Request');
    trigger_error('Missing order uid', E_USER_ERROR);
  }

  if (empty($_GET['klarna_order'])) {
    header('HTTP/1.1 400 Bad Request');
    trigger_error('Missing klarna_order', E_USER_ERROR);
  }

  database::query(
    "insert into ". DB_TABLE_PREFIX ."klarna
    (order_uid, klarna_order_id, klarna_order_uri, parameters, ip, date_created)
    values('". database::input($_GET['order_uid']) ."', '". database::input(basename($_GET['klarna_order'])) ."', '". database::input($_GET['klarna_order']) ."', '". database::input(serialize($_GET)) ."', '". $_SERVER['REMOTE_ADDR'] ."', '". date('Y-m-d H:i:s') ."')"
  );
  
  die('OK');
?>