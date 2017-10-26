<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Controllers;

/**
 * Controllers for the user
 *
 * @since 1.0
 */
class user extends \Boldface\Bootstrap\Controllers\abstractControllers {

  /**
   * @var Render priority
   *
   * @access protected
   * @since  1.0
   */
  public $priority = 10;

  /**
   * Add actions and filters from the admin_init hook
   *
   * @access public
   * @since  1.0
   */
  public function admin_init() {
    \add_action( 'load-profile.php', [ $this, 'user_edit' ] );
    \add_action( 'load-user-edit.php', [ $this, 'user_edit' ] );

    \add_action( 'show_user_profile', [ $this->model->view, 'show_user_profile' ] );
    \add_action( 'edit_user_profile', [ $this->model->view, 'show_user_profile' ] );

    \add_action( 'personal_options_update', [ $this->model, 'profile_update' ] );
    \add_action( 'edit_user_profile_update', [ $this->model, 'profile_update' ] );

    if( \current_user_can( 'edit_user' ) ) \remove_all_filters( 'pre_user_description' );
  }

  /**
   * Add actions and filters from the init hook
   *
   * @access public
   * @since  1.0
   */
  public function init() {
    global $wp_rewrite; $wp_rewrite->author_base = 'team';
    \add_filter( 'get_avatar', [ $this->model, 'get_avatar' ], 10, 6 );
  }

  /**
   * Add actions and filters from the wp hook
   *
   * @access public
   * @since  1.0
   */
  public function wp() {
    \add_filter( 'Boldface\Bootstrap\Header\inline_css', [ $this->model->view, 'css' ] );
    \add_filter( 'Boldface\Boldface\Models\user\bio', [ $this->model, 'author_photo' ], 10, 2 );

    if( \is_author() ) {
      \remove_all_filters( 'Boldface\Bootstrap\Views\loop' );
      \add_filter( 'Boldface\Bootstrap\Views\loop', [ $this->model, 'author_bio' ] );
      \add_filter( 'Boldface\Bootstrap\Models\author_title', [ $this->model, 'author_title' ] );
      \add_filter( 'Boldface\Bootstrap\Views\contactForm7\elements\class', '__return_empty_string' );
    }

    \add_filter( 'Boldface\Boldface\Views\user\list', [ $this->model, 'userList' ] );
    \add_filter( 'Boldface\Boldface\Views\user\list\content', [ $this->model, 'avatar' ], 10, 2 );
    \add_filter( 'Boldface\Boldface\Views\user\list\data-title', [ $this->model, 'popoversTitle' ], 10, 2 );
    \add_filter( 'Boldface\Boldface\Views\user\list\data-content', [ $this->model, 'popoversContent' ], 10, 2 );

    \add_filter( 'Boldface\Boldface\Models\user\users', [ $this->model, 'getUsers' ] );
    \add_filter( 'Boldface\Boldface\Models\user\users\args', [ $this->model, 'getUsersArgs' ] );

    \add_filter( 'Boldface\Bootstrap\Views\popovers\select', [ $this->model, 'popoversSelect' ] );
    \add_filter( 'Boldface\Bootstrap\Views\popovers\options', [ $this->model, 'popoversOptions' ] );
  }

  /**
   * On profile and user edit pages, add action to enqueue_scripts
   *
   * @access public
   * @since  1.0
   */
  public function user_edit() {
    \add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
  }

  /**
   * Enqueue scripts
   *
   * @access public
   * @since  1.0
   */
  public function enqueue_scripts() {
    \wp_enqueue_media();
    \wp_enqueue_script( 'custom-avatar', \get_stylesheet_directory_uri() . '/assets/js/custom-avatar.js', [ 'jquery' ] );
  }
}
