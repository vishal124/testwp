<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Controllers;

/**
 * Controllers for the gallery
 *
 * @since 1.0
 */
class gallery extends \Boldface\Bootstrap\Controllers\abstractControllers {

  /**
   * Add actions and filters from the admin_init hook
   *
   * @access public
   * @since  1.0
   */
  public function admin_init() {
    \add_filter( 'attachment_fields_to_edit', [ $this->model, 'attachment_fields_to_edit' ], 10, 2 );
    \add_filter( 'attachment_fields_to_save', [ $this->model, 'attachment_fields_to_save' ], 10, 2 );
  }

  /**
   * Add actions and filters from the wp hook
   *
   * @access public
   * @since  1.0
   */
  public function wp() {
    \add_filter( 'gallery_class', [ $this->model->view, 'gallery_class' ] );
    \add_filter( 'Boldface\Bootstrap\Header\inline_css', [ $this->model->view, 'css' ] );
    \add_filter( 'Boldface\Boldface\Models\user\bio', [ $this->model, 'author_bio' ] );
    \add_filter( 'Boldface\Boldface\Models\lightbox\title', [ $this->model, 'lightbox_title' ], 10, 2 );
    \add_filter( 'Boldface\Boldface\Models\user\aside', [ $this->model, 'aside' ] );
  }
}
