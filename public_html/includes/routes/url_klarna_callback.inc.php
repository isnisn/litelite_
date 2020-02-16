<?php
  class url_klarna_callback {
    
    function routes() {
      return array(
        array(
          'pattern' => '#^klarna_callback$#',
          'page' => 'klarna_callback',
          'params' => '',
          'redirect' => false,
          'post_security' => false,
            'options' => array(
                'redirect' => true,
            ),
        ),
      );
    }
  }
?>