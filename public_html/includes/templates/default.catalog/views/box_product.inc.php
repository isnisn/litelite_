<article id="box-product" class="box" data-id="<?php echo $product_id; ?>" data-sku="<?php echo htmlspecialchars($sku); ?>" data-name="<?php echo htmlspecialchars($name); ?>" data-price="<?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>">

  <div class="row">
    <div class="col-sm-4 col-md-6">
      <div class="images row">

        <div class="col-xs-12">
          <a class="main-image thumbnail" href="<?php echo document::href_link(WS_DIR_APP . $image['original']); ?>" data-toggle="lightbox" data-gallery="product">
            <img class="img-responsive" src="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $image['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($name); ?>" />
            <?php echo $sticker; ?>
          </a>
        </div>

        <?php foreach ($extra_images as $image) { ?>
        <div class="col-xs-4">
          <a class="extra-image thumbnail" href="<?php echo document::href_link(WS_DIR_APP . $image['original']); ?>" data-toggle="lightbox" data-gallery="product">
            <img class="img-responsive" src="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $image['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $image['thumbnail_2x']); ?> 2x" alt="" title="<?php echo htmlspecialchars($name); ?>" />
          </a>
        </div>
        <?php } ?>

      </div>
    </div>

    <div class="col-sm-8 col-md-6">
        <?php if (!empty($manufacturer)) { ?>
            <div class="manufacturer">
                <a href="<?php echo htmlspecialchars($manufacturer['link']); ?>">
                    <?php if ($manufacturer['image']) { ?>
                        <img src="<?php echo document::href_link(WS_DIR_APP . $manufacturer['image']['thumbnail']); ?>" srcset="<?php echo document::href_link(WS_DIR_APP . $manufacturer['image']['thumbnail']); ?> 1x, <?php echo document::href_link(WS_DIR_APP . $manufacturer['image']['thumbnail_2x']); ?> 2x" alt="<?php echo htmlspecialchars($manufacturer['name']); ?>" title="<?php echo htmlspecialchars($manufacturer['name']); ?>" />
                    <?php } else { ?>
                        <h3><?php echo $manufacturer['name']; ?></h3>
                    <?php } ?>
                </a>
            </div>
        <?php } ?>

      <h1 class="product-name title"><?php echo $name; ?></h1>
        <div class="price-wrapper">
            <?php if ($campaign_price) { ?>
                <del class="regular-price"><?php echo currency::format($regular_price); ?></del> <strong class="campaign-price"><?php echo currency::format($campaign_price); ?></strong>
            <?php } else { ?>
                <span class="price"><?php echo currency::format($regular_price); ?></span>
            <?php } ?>
        </div>
      <?php if ($short_description) { ?>
      <p class="short-description">
        <?php echo $short_description; ?>
      </p>
      <?php } ?>

      <?php if ($cheapest_shipping_fee !== null) { ?>
      <div class="cheapest-shipping" style="margin: 1em 0;">
        <?php echo functions::draw_fonticon('fa-truck'); ?> <?php echo strtr(language::translate('text_cheapest_shipping_from_price', 'Cheapest shipping from <strong class="value">%price</strong>'), array('%price' => currency::format($cheapest_shipping_fee))); ?>
      </div>
      <?php } ?>

        <div class="tax" style="margin: 0 0 1em 0;">
            <?php /* if ($tax_rates) { ?>
          <?php echo $including_tax ? language::translate('title_including_tax', 'Including Tax') : language::translate('title_excluding_tax', 'Excluding Tax'); ?>: <span class="total-tax"><?php echo currency::format($total_tax); ?></span>
         <?php } else { ?>
          <?php echo language::translate('title_excluding_tax', 'Excluding Tax'); ?>
         <?php } */?>
        </div>
        <?php echo functions::form_draw_form_begin('buy_now_form', 'post'); ?>
        <?php if (!$catalog_only_mode) { ?>
            <div class="form-group">
                <!--label><?php echo language::translate('title_quantity', 'Quantity'); ?></label-->
                <div style="display: flex">
                    <div class="input-group" style="flex: 0 1 95px;">
                        <?php echo (!empty($quantity_unit['decimals'])) ? functions::form_draw_decimal_field('quantity', isset($_POST['quantity']) ? true : 1, $quantity_unit['decimals'], 1, null) : (functions::form_draw_number_field('quantity', isset($_POST['quantity']) ? true : 1, 1)); ?>
                        <?php echo !empty($quantity_unit['name']) ? '<div class="input-group-addon">'. $quantity_unit['name'] .'</div>' : ''; ?>
                    </div>

                    <div style="flex: 1 0 auto; padding-left: 1em;">
                        <?php echo '<button class="btn btn-success" name="add_cart_product" value="true" type="submit"'. (($quantity <= 0 && !$orderable) ? ' disabled="disabled"' : '') .'>'. language::translate('title_add_to_cart', 'Add To Cart') .'</button>'; ?>
                    </div>
                </div>
            </div>
        <?php } ?>

      <?php /* if ($sku || $mpn || $gtin) { ?>
      <div class="codes" style="margin: 1em 0;">
        <?php if ($sku) { ?>
        <div class="sku">
          <?php echo language::translate('title_sku', 'SKU'); ?>:
          <span class="value"><?php echo $sku; ?></span>
        </div>
        <?php } ?>

        <?php if ($mpn) { ?>
        <div class="mpn">
          <?php echo language::translate('title_mpn', 'MPN'); ?>:
          <span class="value"><?php echo $mpn; ?></span>
        </div>
        <?php } ?>

        <?php if ($gtin) { ?>
        <div class="gtin">
          <?php echo language::translate('title_gtin', 'GTIN'); ?>:
          <span class="value"><?php echo $gtin; ?></span>
        </div>
        <?php } ?>
      </div>
      <?php } */?>

      <div class="stock-status" style="margin: 1em 0;">
       <?php if ($quantity > 0) { ?>
        <div class="stock-available">
          <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
          <span class="value"><?php echo $stock_status; ?></span>
        </div>
        <?php if ($delivery_status) { ?>
        <div class="stock-delivery">
          <?php echo language::translate('title_delivery_status', 'Delivery Status'); ?>:
          <span class="value"><?php echo $delivery_status; ?></span>
        </div>
        <?php } ?>
       <?php } else { ?>
        <?php if ($sold_out_status) { ?>
          <div class="<?php echo $orderable ? 'stock-partly-available' : 'stock-unavailable'; ?>">
            <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
            <span class="value"><?php echo $sold_out_status; ?></span>
          </div>
        <?php } else { ?>
          <div class="stock-unavailable">
            <?php echo language::translate('title_stock_status', 'Stock Status'); ?>:
            <span class="value"><?php echo language::translate('title_sold_out', 'Sold Out'); ?></span>
          </div>
        <?php } ?>
       <?php } ?>
      </div>

      <hr />
        <!-- IF special stuff -->
        <?php if($product_type != 3 ) {//Not normal product?>
        <?php if($product_type != 4 ) {//Not normal product?>
        <div class="tab">
            <button type="button" class="tablinks default" onclick="openTabs(event, 'framsida')">Framsida</button>
            <button type="button" class="tablinks" onclick="openTabs(event, 'baksida')">Baksida</button>
        </div>

      <div id="framsida" class="tabcontent default">

          <!-- Adjust canvas size accordingly -->
          <?php if($product_type == 1) { ?>
              <canvas id="FrammyCanvas" width="217" height="145"></canvas>
          <?php } else if($product_type == 2) { ?>
              <canvas id="FrammyCanvas" width="160" height="200"></canvas>
          <?php } ?>
          <script type="text/javascript">
              var canvas = document.getElementById("FrammyCanvas");
              var height = canvas.height;
              var width = canvas.width;

              var context = canvas.getContext("2d");
              var imageObj = new Image();
              imageObj.src = "<?php echo document::href_link(WS_DIR_APP . "images/engraving_" . $product_type . ".jpg"); ?>";
              imageObj.onload = function()
              {
                  context.drawImage(imageObj, 0, 0, width, height);
              }
              var myForm = document.getElementById('buy_now_div');
              myForm.addEventListener('keyup', function(e)
              {
                  context.drawImage(imageObj, 0, 0, width, height);
                  //var text1 = document.getElementById('myText_1').value;
                  var text1 = $("input[name='options[Rad1]']").val();
                  var text2 = $("input[name='options[Rad2]']").val();
                  var fontSizes = [25, 16, 12, 10, 5, 2];
                  var textDimensions,
                      i = 0;
                  do {
                      context.fillStyle ="#555";
                      context.font = fontSizes[i++] + 'px Arial';
                      textDimensions = context.measureText(text1);
                      textDimensions2 = context.measureText(text2);

                  } while (textDimensions.width >= (canvas.width - 80));

                  context.fillText(text1, (canvas.width - textDimensions.width) / 2, 65);
                  context.fillText(text2, (canvas.width - textDimensions2.width) / 2, 95);
                  e.preventDefault();
              });
          </script>
          <div id="buy_now_div" class="buy_now" style="margin: 1em 0;">

              <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

              <?php if($product_type != 3) { ?>
                  <div class="form-group required">
                      <input class="form-control" type="text" name="options[Rad1]" value="" data-type="text" data-price-adjust="0" data-tax-adjust="0" required="required" maxlength="15" placeholder="Rad 1 framsida">
                      <input class="form-control" type="text" name="options[Rad2]" value="" data-type="text" data-price-adjust="0" data-tax-adjust="0" required="required" maxlength="15" placeholder="Rad 2 framsida">
                  </div>

              <?php } ?>
          </div>
      </div>
        <div id="baksida" class="tabcontent">

            <!-- Adjust canvas size accordingly -->
            <?php if($product_type == 1) { ?>
                <canvas id="myCanvas" width="217" height="145"></canvas>
            <?php } else if($product_type == 2) { ?>
                <canvas id="myCanvas" width="160" height="200"></canvas>
            <?php } ?>
            <script type="text/javascript">
                var canvas_ = document.getElementById("myCanvas");
                var height = canvas_.height;
                var width = canvas_.width;

                var ctx = canvas_.getContext("2d");
                var imageObj = new Image();

                imageObj.src = "<?php echo document::href_link(WS_DIR_APP . "images/engraving_" . $product_type . ".jpg"); ?>";
                imageObj.onload = function()
                {
                    ctx.drawImage(imageObj, 0, 0, width, height);
                }
                var myForm = document.getElementById('baksida');
                myForm.addEventListener('keyup', function(e)
                {
                    ctx.drawImage(imageObj, 0, 0, width, height);
                    //var text1 = document.getElementById('myText_1').value;
                    var text1_ = $("input[name='options[Rad1b]']").val();
                    var text2_ = $("input[name='options[Rad2b]']").val();
                    var fontSizes = [25, 16, 12, 10, 5, 2];
                    var textDimensions,
                        i = 0;
                    do {
                        ctx.fillStyle ="#555";
                        ctx.font = fontSizes[i++] + 'px Arial';
                        textDimensions_ = ctx.measureText(text1_);
                        textDimensions2_ = ctx.measureText(text2_);

                    } while (textDimensions_.width >= (canvas_.width - 80));

                    ctx.fillText(text1_, (canvas_.width - textDimensions_.width) / 2, 65);
                    ctx.fillText(text2_, (canvas_.width - textDimensions2_.width) / 2, 95);
                    e.preventDefault();
                });
            </script>
            <div id="buy_now_div" class="buy_now" style="margin: 1em 0;">

                <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

                <?php if($product_type != 3) { ?>
                    <div class="form-group required">
                        <input class="form-control" type="text" name="options[Rad1b]" value="" data-type="text" data-price-adjust="0" data-tax-adjust="0" required="required" maxlength="15" placeholder="Rad 1 baksida">
                        <input class="form-control" type="text" name="options[Rad2b]" value="" data-type="text" data-price-adjust="0" data-tax-adjust="0" required="required" maxlength="15" placeholder="Rad 2 baksida">
                    </div>

                <?php } ?>
            </div>
        </div>

        <?php } ?>
        <?php } ?>
          <?php if ($options && $product_type == 3) { ?>
              <?php foreach ($options as $option) { ?>
                  <div class="form-group">
                      <label><?php echo $option['name']; ?></label>
                      <?php echo $option['description'] ? '<div>' . $option['description'] . '</div>' : ''; ?>
                      <?php echo $option['values']; ?>
                  </div>
              <?php } ?>
          <?php } ?>

        <!-- Julius K9 design own patch start -->
        <?php if($product_type == 4) { ?>
        <div>

            <!-- Adjust canvas size accordingly -->
                <canvas id="FrammyCanvas" width="357" height="111"></canvas>
            <script type="text/javascript">
                var canvas = document.getElementById("FrammyCanvas");
                var height = canvas.height;
                var width = canvas.width;

                var context = canvas.getContext("2d");
                var imageObj = new Image();
                imageObj.src = "<?php echo document::href_link(WS_DIR_APP . "images/design_print.jpg"); ?>";
                imageObj.onload = function()
                {
                    context.drawImage(imageObj, 0, 0, width, height);
                }
                var myForm = document.getElementById('buy_now_div');

                myForm.addEventListener('keyup', function(e)
                {
                    var box = document.getElementById("fonttype");
                    var fonttype = box.options[box.selectedIndex].value;
                    var box_ = document.getElementById("fontcolorjulius");
                    var color = $("input[name=fontcolor]:checked").val();

                    context.drawImage(imageObj, 0, 0, width, height);

                    var text1 = $("input[name='options[Rad1]']").val();
                    var fontSizes = [90, 82, 74, 66, 58, 52, 48, 36, 32];
                    var textDimensions,
                        i = 0;
                    do {
                        context.fillStyle = color;
                        context.font = 'normal ' + fontSizes[i++] + 'px ' + fonttype;

                        textDimensions = context.measureText(text1);
                    } while (textDimensions.width >= (canvas.width - 5));

                    context.fillText(text1, (canvas.width - textDimensions.width) / 2, 85);

                    e.preventDefault();
                });

            </script>
            <div id="buy_now_div" class="buy_now" style="margin: 1em 0;">

                <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

                <?php if($product_type != 3) { ?>
                    <div class="form-group required">
                        <input class="form-control" type="text" name="options[Rad1]" value="" data-type="text" data-price-adjust="0" data-tax-adjust="0" required="required" maxlength="15" placeholder="Egen text">

                    <label>Typsnitt</label>
                    <select id="fonttype" onchange="updateFont()">
                        <option value="Arial">Arial</option>
                        <option value="Helvetica">Helvetica</option>
                        <option value="Ultra">Ultra</option>
                        <option value="Lacquer">Lacquer</option>
                        <option value="Lalezar">Lalezar</option>
                        <option value="Concert One">Concert One</option>
                    </select>
                    </div>

                    <div>
                        <p class="line-item-property__field" style="overflow:auto">
                            <label>Textfärg</label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor1" value="#ffffff" checked="checked" placeholder="">
                                <span class="ccheckmark" style="background-color:#fff;border:1px solid #eee"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor2" value="#ccc" placeholder="">
                                <span class="ccheckmark" style="background: linear-gradient(45deg, rgba(192,192,192,1) 0%, rgba(255,255,255,1) 50%, rgba(128,128,128,1) 100%); /* w3c */"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor3" value="#B9D5B6" placeholder="">
                                <span class="ccheckmark" style="background: linear-gradient(45deg, #84c97d 0%, #B9D5B6 100%); box-shadow: 0px 0px 5px 0px rgb(62, 255, 0);
    border: 0;"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor4" value="#e4002b" placeholder="">
                                <span class="ccheckmark" style="background-color:#e4002b"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor5" value="#F8BED6" placeholder="">
                                <span class="ccheckmark" style="background-color:#F8BED6"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor6" value="#C7B2DE" placeholder="">
                                <span class="ccheckmark" style="background-color:#C7B2DE"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor7" value="#C5CFDA" placeholder="">
                                <span class="ccheckmark" style="background-color:#C5CFDA"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor8" value="#3B8C9C" placeholder="">
                                <span class="ccheckmark" style="background-color:#3B8C9C"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor9" value="#9FBB78" placeholder="">
                                <span class="ccheckmark" style="background-color:#9FBB78"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor10" value="gold" placeholder="">
                                <span class="ccheckmark" style="background: radial-gradient(ellipse farthest-corner at right bottom, #FEDB37 0%, #FDB931 8%, #9f7928 30%, #8A6E2F 40%, transparent 80%),
                radial-gradient(ellipse farthest-corner at left top, #FFFFFF 0%, #FFFFAC 8%, #D1B464 25%, #5d4a1f 62.5%, #5d4a1f 100%);"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor11" value="#E7E269" placeholder="">
                                <span class="ccheckmark" style="background-color:#E7E269"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor12" value="#DF7F34" placeholder="">
                                <span class="ccheckmark" style="background-color:#DF7F34"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor13" value="#D44877" placeholder="">
                                <span class="ccheckmark" style="background-color:#D44877"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor14" value="#439ACA" placeholder="">
                                <span class="ccheckmark" style="background-color:#439ACA"></span>
                            </label>
                            <label class="ccontainer">
                                <input onclick=updateColor() type="radio" name="fontcolor" id="fontcolor15" value="#75FA4C" placeholder="">
                                <span class="ccheckmark" style="background-color:#75FA4C"></span>
                            </label>
                        </p>
                    </div>

                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <!-- Julius K9 design own patch end -->
        <?php echo functions::form_draw_hidden_field('product_id', $product_id); ?>

        <?php echo functions::form_draw_form_end(); ?>

      <hr />

      <div class="social-bookmarks text-center">
        <a class="link" href="#"><?php echo functions::draw_fonticon('fa-link', 'style="color: #333;"'); ?></a>
        <a class="twitter" href="<?php echo document::href_link('http://twitter.com/home/', array('status' => $name .' - '. $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Twitter'); ?>"><?php echo functions::draw_fonticon('fa-twitter-square fa-lg', 'style="color: #55acee;"'); ?></a>
        <a class="facebook" href="<?php echo document::href_link('http://www.facebook.com/sharer.php', array('u' => $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Facebook'); ?>"><?php echo functions::draw_fonticon('fa-facebook-square fa-lg', 'style="color: #3b5998;"'); ?></a>
        <a class="googleplus" href="<?php echo document::href_link('https://plus.google.com/share', array('url' => $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Google+'); ?>"><?php echo functions::draw_fonticon('fa-google-plus-square fa-lg', 'style="color: #dd4b39;"'); ?></a>
        <a class="pinterest" href="<?php echo document::href_link('http://pinterest.com/pin/create/button/', array('url' => $link)); ?>" target="_blank" title="<?php echo sprintf(language::translate('text_share_on_s', 'Share on %s'), 'Pinterest'); ?>"><?php echo functions::draw_fonticon('fa-pinterest-square fa-lg', 'style="color: #bd081c;"'); ?></a>
      </div>

    </div>
  </div>

  <?php if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') { ?>
  <ul class="nav nav-tabs">
    <?php if ($description) { ?><li><a data-toggle="tab" href="#tab-description"><?php echo language::translate('title_description', 'Description'); ?></a></li><?php } ?>
    <?php if ($technical_data) { ?><li><a data-toggle="tab" href="#tab-technical-data"><?php echo language::translate('title_technical_data', 'Technical Data'); ?></a></li><?php } ?>
  </ul>

  <div class="tab-content">
    <div id="tab-description" class="tab-pane description">
      <?php echo $description; ?>
    </div>

    <?php if ($technical_data) { ?>
    <div id="tab-technical-data" class="tab-pane technical-data">
      <table class="table table-striped table-hover">
<?php
  for ($i=0; $i<count($technical_data); $i++) {
    if (strpos($technical_data[$i], ':') !== false) {
      @list($key, $value) = explode(':', $technical_data[$i]);
      echo '  <tr>' . PHP_EOL
         . '    <td>'. trim($key) .':</td>' . PHP_EOL
         . '    <td>'. trim($value) .'</td>' . PHP_EOL
         . '  </tr>' . PHP_EOL;
    } else if (trim($technical_data[$i]) != '') {
      echo '  <thead>' . PHP_EOL
         . '    <tr>' . PHP_EOL
         . '      <th colspan="2">'. $technical_data[$i] .'</th>' . PHP_EOL
         . '    </tr>' . PHP_EOL
         . '  </thead>' . PHP_EOL
         . '  <tbody>' . PHP_EOL;
    } else {
      echo ' </tbody>' . PHP_EOL
         . '</table>' . PHP_EOL
         . '<table class="table table-striped table-hover">' . PHP_EOL;
    }
  }
?>
      </table>
    </div>
    <?php } ?>
  </div>
  <?php } ?>

</article>

<script>
  Number.prototype.toMoney = function() {
    var n = this,
      c = <?php echo (int)currency::$selected['decimals']; ?>,
      d = '<?php echo language::$selected['decimal_point']; ?>',
      t = '<?php echo language::$selected['thousands_sep']; ?>',
      p = '<?php echo currency::$selected['prefix']; ?>',
      x = '<?php echo currency::$selected['suffix']; ?>',
      s = n < 0 ? '-' : '',
      i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + '',
      j = (j = i.length) > 3 ? j % 3 : 0;

    return s + p + (j ? i.substr(0, j) + t : '') + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : '') + x;
  }

  $('#box-product form[name=buy_now_form]').bind('input propertyChange', function(e) {

    var regular_price = <?php echo currency::format_raw($regular_price); ?>;
    var sales_price = <?php echo currency::format_raw($campaign_price ? $campaign_price : $regular_price); ?>;
    var tax = <?php echo currency::format_raw($total_tax); ?>;

    $(this).find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function(){
      if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
      if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
    });

    $(this).find('select option:checked').each(function(){
      if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
      if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
    });

    $(this).find('input[type!="radio"][type!="checkbox"]').each(function(){
      if ($(this).val() != '') {
      if ($(this).data('price-adjust')) regular_price += $(this).data('price-adjust');
      if ($(this).data('price-adjust')) sales_price += $(this).data('price-adjust');
      if ($(this).data('tax-adjust')) tax += $(this).data('tax-adjust');
      }
    });

    $(this).find('.regular-price').text(regular_price.toMoney());
    $(this).find('.campaign-price').text(sales_price.toMoney());
    $(this).find('.price').text(sales_price.toMoney());
    $(this).find('.total-tax').text(tax.toMoney());
  });

  $('#box-product[data-id="<?php echo $product_id; ?>"] .social-bookmarks .link').off().click(function(e){
    e.preventDefault();
    prompt("<?php echo language::translate('text_link_to_this_product', 'Link to this product'); ?>", '<?php echo $link; ?>');
  });
</script>
