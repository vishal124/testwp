<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Controllers;

/**
 * Controllers for the lightbox
 *
 * @since 1.0
 */
class lightbox extends \Boldface\Bootstrap\Controllers\abstractControllers {

  /**
   * Add actions and filters from the wp hook
   *
   * @access public
   * @since  1.0
   */
  public function wp() {
    \add_filter( 'wp_get_attachment_image_attributes', [ $this->model, 'wp_get_attachment_image_attributes' ], 10, 3 );
    \add_filter( 'wp_get_attachment_link', [ $this->model, 'wp_get_attachment_link' ], 10, 6 );
    \add_action( 'wp_footer', [ $this->model->view, 'lightbox_js' ] );
  }
}
