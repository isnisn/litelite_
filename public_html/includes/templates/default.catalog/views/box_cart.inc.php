<div id="cart">

    <div class="js-sidebar-cart"><div id="mini-cart">


            <i class="fa fa-shopping-cart"></i>
            <span class="cart_text"><span class="hidden-text"></span><strong><span class="mob-cart-amount"> <span class="mob-cart-amount-num"><?php echo $num_items ? $num_items : ''; ?></span></span></strong><span class="hidden-text"> varor för totalt</span> <strong><span class="mob-cart-total"><?php echo $cart_total; ?></span></strong></span>
            <div class="clear"></div>

            <div class="button_wrap">
                <a class="js-sidebar-cart-button" href="<?php echo htmlspecialchars($link); ?>">Till Kassan</a>
            </div>
            <div class="clear"></div>
        </div>
    </div>

</div>

