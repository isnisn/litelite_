<section id="box-checkout-payment" class="box">

    Here goes Klarna checkout snippet

<?php

header('X-Robots-Tag: noindex');

if (empty(cart::$items)) return;

if (empty($shipping)) $shipping = new mod_shipping();
if (empty($payment)) $payment = new mod_payment();


if (!empty(session::$data['order_id'])) {
    $resume_id = session::$data['order_id'];
}


if (!empty(session::$data['klarna_open_session_order_id'])) {
    // Resume incomplete order in session
    if (!empty($resume_id)) {
        $order_query = database::query(
            "select * from ". DB_TABLE_ORDERS ."
          where id = ". (int)$resume_id ."
          and order_status_id = 0
          and date_created > '". date('Y-m-d H:i:s', strtotime('-15 minutes')) ."'
          limit 1;"
        );

        if (database::num_rows($order_query)) {
            $data = database::fetch($order_query);
            session::$data['order'] = new ent_order($resume_id);
            session::$data['order']->reset();
            session::$data['order']->data['uid'] = $data['uid'];
            session::$data['order']->data['id'] = $resume_id;
        }
    }

} else {
    session::$data['order'] = new ent_order();
    session::$data['order']->save();
    session::$data['order_id'] = session::$data['order']->data['id'];
}

$order = session::$data['order'];

// Build Order
$order->data['weight_class'] = settings::get('store_weight_class');
$order->data['currency_code'] = currency::$selected['code'];
$order->data['currency_value'] = currency::$currencies[currency::$selected['code']]['value'];
$order->data['language_code'] = language::$selected['code'];
$order->data['customer'] = customer::$data;
$order->data['display_prices_including_tax'] = !empty(customer::$data['display_prices_including_tax']) ? true : false;

foreach (cart::$items as $item) {
    $order->add_item($item);
}


if (!empty($shipping->data['selected'])) {
    $order->data['shipping_option'] = $shipping->data['selected'];
}

if (!empty($payment->data['selected'])) {
    $order->data['payment_option'] = $payment->data['selected'];
}

$order_total = new mod_order_total();
$rows = $order_total->process($order);
foreach ($rows as $row) {
    $order->add_ot_row($row);
}

// Output
$box_checkout_summary = new ent_view();

$box_checkout_summary->snippets = array(
    'items' => array(),
    'order_total' => array(),
    'tax_total' => !empty($order->data['tax_total']) ? currency::format($order->data['tax_total'], false) : null,
    'incl_excl_tax' => !empty(customer::$data['display_prices_including_tax']) ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'),
    'payment_due' => $order->data['payment_due'],
    'error' => $order->validate($shipping, $payment),
    'selected_shipping' => null,
    'selected_payment' => null,
    'consent' => null,
    'confirm' => !empty($payment->data['selected']['confirm']) ? $payment->data['selected']['confirm'] : language::translate('title_confirm_order', 'Confirm Order'),
);

foreach ($order->data['items'] as $item) {
    $box_checkout_summary->snippets['items'][] = array(
        'link' => document::ilink('product', array('product_id' => $item['product_id'])),
        'name' => $item['name'],
        'sku' => $item['sku'],
        'options' => $item['options'],
        'price' => $item['price'],
        'tax' => $item['tax'],
        'sum' => !empty(customer::$data['display_prices_including_tax']) ? currency::format(($item['price'] + $item['tax']) * $item['quantity'], false) : currency::format($item['price'] * $item['quantity'], false),
        'quantity' => (float)$item['quantity'],
    );
}

if (!empty($shipping->data['selected'])) {
    $box_checkout_summary->snippets['selected_shipping'] = array(
        'icon' => is_file(FS_DIR_APP . $shipping->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_APP . $shipping->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
        'title' => $shipping->data['selected']['title'],
    );
}

if (!empty($payment->data['selected'])) {
    $box_checkout_summary->snippets['selected_payment'] = array(
        'icon' => is_file(FS_DIR_APP . $payment->data['selected']['icon']) ? functions::image_thumbnail(FS_DIR_APP . $payment->data['selected']['icon'], 160, 60, 'FIT_USE_WHITESPACING') : '',
        'title' => $payment->data['selected']['title'],
    );
}

foreach ($order->data['order_total'] as $row) {
    $box_checkout_summary->snippets['order_total'][] = array(
        'title' => $row['title'],
        'value' => $row['value'],
        'tax' => $row['tax'],
    );
}

$terms_of_purchase_id = settings::get('privacy_policy');
$privacy_policy_id = settings::get('terms_of_purchase');

switch(true) {
    case ($terms_of_purchase_id && $privacy_policy_id):
        $box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy_and_terms_of_purchase', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
        break;
    case ($privacy_policy_id):
        $box_checkout_summary->snippets['consent'] = language::translate('consent:privacy_policy', 'I have read the <a href="%privacy_policy_link" target="_blank">Privacy Policy</a> and I consent.');
        break;
    case ($terms_of_purchase_id):
        $box_checkout_summary->snippets['consent'] = language::translate('consent:terms_of_purchase', 'I have read the <a href="%terms_of_purchase_link" target="_blank">Terms of Purchase</a> and I consent.');
        break;
}

$aliases = array(
    '%privacy_policy_link' => document::href_ilink('information', array('page_id' => $privacy_policy_id)),
    '%terms_of_purchase_link' => document::href_ilink('information', array('page_id' => $terms_of_purchase_id)),
);

$box_checkout_summary->snippets['consent'] = strtr($box_checkout_summary->snippets['consent'], $aliases);

//$order = session::$data['order'];
$payment = new mod_payment();

if (!empty($payment->modules) && count($payment->options($order->data['items'], $order->data['currency_code'], $order->data['customer'])) > 0) {
    if (empty($payment->data['selected'])) {
        notices::add('errors', language::translate('error_no_payment_method_selected', 'No payment method selected'));
        header('Location: '. document::ilink('checkout'));
        exit;
    }

    if ($payment_error = $payment->pre_check($order)) {
        notices::add('errors', $payment_error);
        header('Location: '. document::ilink('checkout'));
        exit;
    }

    if (!empty($_POST['comments'])) {
        $order->data['comments']['session'] = array(
            'author' => 'customer',
            'text' => $_POST['comments'],
        );
    }

    if ($gateway = $payment->transfer($order)) {

        if (!empty($gateway['error'])) {
            notices::add('errors', $gateway['error']);
            header('Location: '. document::ilink('checkout'));
            exit;
        }

        switch (@strtoupper($gateway['method'])) {

            case 'POST':
                echo '<p>'. language::translate('title_redirecting', 'Redirecting') .'...</p>' . PHP_EOL
                    . '<form name="gateway_form" method="post" action="'. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')) .'">' . PHP_EOL;
                if (is_array($gateway['fields'])) {
                    foreach ($gateway['fields'] as $key => $value) echo '  ' . functions::form_draw_hidden_field($key, $value) . PHP_EOL;
                } else {
                    echo $gateway['fields'];
                }
                echo '</form>' . PHP_EOL
                    . '<script>' . PHP_EOL;
                if (!empty($gateway['delay'])) {
                    echo '  var t=setTimeout(function(){' . PHP_EOL
                        . '    document.forms["gateway_form"].submit();' . PHP_EOL
                        . '  }, '. ($gateway['delay']*1000) .');' . PHP_EOL;
                } else {
                    echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
                }
                echo '</script>';
                exit;

            case 'HTML':
                echo $gateway['content'];
                require_once vmod::check(FS_DIR_APP . 'includes/app_footer.inc.php');
                exit;

            case 'GET':
            default:
                header('Location: '. (!empty($gateway['action']) ? $gateway['action'] : document::ilink('order_process')));
                exit;
        }
    }
}

?>

<script>
    $('#box-checkout-payment .option.active :input').prop('disabled', false);
    $('#box-checkout-payment .option:not(.active) :input').prop('disabled', true);
</script>

