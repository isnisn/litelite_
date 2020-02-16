<?php
header('X-Robots-Tag: noindex');
document::$layout = 'checkout';

document::$snippets['title'][] = 'Your head title here';
document::$snippets['description'] = 'Your meta description here';

$_mypage = new ent_view();

$_mypage->snippets = array(
    'title' => 'Hello World',
    'content' => 'Lorem ipsum dolor',
);

echo $_mypage->stitch('pages/klarna_checkout');
?>
