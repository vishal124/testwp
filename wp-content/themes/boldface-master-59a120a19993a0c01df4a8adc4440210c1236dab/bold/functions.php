<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface;

/**
 * Filter the controllers list. Remove the breadcrumbs. Add gallery and user.
 *
 * @since 1.0
 *
 * @param array $list List of controllers
 *
 * @return array The new list of controllers
 */
function controllers_list( array $list ) : array {
  $list[ 'popovers' ] = '\Boldface\Bootstrap';
  unset( $list[ 'breadcrumbs' ] );
  unset( $list[ 'modal' ] );
  foreach( [ 'gallery', 'user', 'lightbox' ] as $item ) {
    require get_stylesheet_directory() . "/controllers/$item.php";
    require get_stylesheet_directory() . "/models/$item.php";
    require get_stylesheet_directory() . "/views/$item.php";
    $list[ $item ]= '\Boldface\Boldface';
  }
  return $list;
}
\add_filter( 'Boldface\Bootstrap\Controllers\list', 'Boldface\Boldface\controllers_list' );

/**
 * Add updater controller to the admin_init hook
 *
 * @since 1.0
 */
function admin_init() {
  require __DIR__ . '/controllers/updater.php';
  $updater = new \Boldface\Boldface\Controllers\updater();
  $updater->admin_init();
}
\add_action( 'admin_init', 'Boldface\Boldface\admin_init' );
