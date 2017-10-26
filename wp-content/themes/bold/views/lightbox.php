<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Views;

/**
 * Views for the lightbox
 *
 * @since 1.0
 */
class lightbox extends \Boldface\Bootstrap\Views\abstractViews {

  /**
   * Print the lightbox JS
   *
   * @access public
   * @since  1.0
   */
  public function lightbox_js() { ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.9.0/css/lightbox.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.9.0/js/lightbox.min.js"></script><script>lightbox.option({'disableScrolling':true,'showImageNumberLabel':false})</script><?php
  }
}
