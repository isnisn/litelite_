<!DOCTYPE html>
<html lang="{snippet:language}" dir="{snippet:text_direction}">
<head>
<title>{snippet:title}</title>
<meta charset="{snippet:charset}" />
<meta name="description" content="{snippet:description}" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="{snippet:template_path}css/framework.css" />
<link rel="stylesheet" href="{snippet:template_path}css/app.css" />
    <link href="https://fonts.googleapis.com/css?family=Concert+One|Fredoka+One|Lacquer|Lalezar|Modak|Ultra&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Baloo+Bhaina&display=swap" rel="stylesheet">

    {snippet:head_tags}
{snippet:style}
</head>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-XXXXXXXX-XX', 'your domain name');
    ga('send', 'pageview');
</script>
<body>
<div class="usp-top">
    <div class="usp-one">
        <i class="fa fa-paw" aria-hidden="true"></i>Trygg e-handel sedan 2005
    </div>
    <div class="usp-one">
        <i class="fa fa-paw" aria-hidden="true"></i>30 dagars öppet köp
    </div>
    <div class="usp-one">
        <i class="fa fa-paw" aria-hidden="true"></i>Säkra betalningar och snabba leveranser
    </div>
</div>

<div id="page" class="twelve-eighty">

  <?php include vmod::check(FS_DIR_TEMPLATE . 'views/box_cookie_notice.inc.php'); ?>

  <header id="header" class="hidden-print">
      <div class="logotype-div">
    <a class="logotype" href="<?php echo document::href_ilink(''); ?>">
      <img src="<?php echo document::href_link('images/logotype.png'); ?>" alt="<?php echo settings::get('store_name'); ?>" title="<?php echo settings::get('store_name'); ?>" />
    </a>
      </div>
      <div class="search-div">
      <?php echo functions::form_draw_form_begin('search_form', 'get', document::ilink('search'), false, 'class="navbar-form"'); ?>
      <?php echo functions::form_draw_search_field('query', true, 'placeholder="'. language::translate('text_search_products', 'Search products') .' &hellip;"'); ?>
      <?php echo functions::form_draw_form_end(); ?>
      </div>
    <div class="text-right">
      <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_cart.inc.php'); ?>
    </div>
  </header>

  <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_site_menu.inc.php'); ?>


  <main id="main">
    {snippet:content}
  </main>

  <?php include vmod::check(FS_DIR_APP . 'includes/boxes/box_site_footer.inc.php'); ?>
</div>

<a id="scroll-up" class="hidden-print" href="#">
  <?php echo functions::draw_fonticon('fa-chevron-circle-up fa-3x', 'style="color: #000;"'); ?>
</a>

{snippet:foot_tags}
<script src="{snippet:template_path}js/app.js"></script>
{snippet:javascript}
</body>
</html>