<a href="<?php echo htmlspecialchars($link); ?>">
<div id="cart">
    <div class="js-sidebar-cart">
        <div id="mini-cart">
            <i class="fa fa-shopping-cart"></i>

            <span class="cart_text">
                <strong>
                    <span class="mob-cart-amount">
                        <span class="mob-cart-amount-num">
                            <?php echo $num_items ? $num_items : '0'; ?>
                        </span>
                    </span>
                </strong>
                <span class="cart-text"> <?php echo $num_items > 1 ? 'varor' : 'vara'; ?> </span>
                <strong>
                    <span class="mob-cart-total"><?php echo $cart_total; ?>
                    </span>
                </strong>
            </span>

        </div>
    </div>
</div>
</a>
<!--div id="cart">
    <a href="<?php echo htmlspecialchars($link); ?>">
        <img class="image" src="{snippet:template_path}images/<?php echo !empty($num_items) ? 'cart_filled.svg' : 'cart.svg'; ?>" alt="" />
        <div class="badge quantity"><?php echo $num_items ? $num_items : ''; ?></div>
    </a>
</div-->


