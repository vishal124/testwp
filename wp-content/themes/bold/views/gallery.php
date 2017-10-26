<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Views;

/**
 * Views for the gallery
 *
 * @since 1.0
 */
class gallery extends \Boldface\Bootstrap\Views\abstractViews {

  /**
   * Return the filtered powered by text
   *
   * @access public
   * @since  1.0
   *
   * @param string $css The powered by text
   *
   * @return string The powered by text
   */
  public function powered_by( string $content ) : string {
    return '<span class="text-muted">Boldface Design Cooperative</span>';
  }

  /**
   * Return the filtered gallery class
   *
   * @access public
   * @since  1.0
   *
   * @param string $css The unfiltered gallery class
   *
   * @return string The filtered gallery class
   */
  public function gallery_class( string $class ) : string {
    return 'gallery d-flex flex-md-column flex-wrap justify-content-around justify-content-md-start';
  }

  /**
   * Return the filtered CSS
   *
   * @access public
   * @since  1.0
   *
   * @param string $css The unfiltered CSS
   *
   * @return string The filtered CSS
   */
  public function css( string $css ) : string {
    return $css . '
      body {
        margin-bottom: 175px;
      }
      .gallery {
      }
      .gallery img {
        filter: grayscale(1);
        transition: filter 0.25s ease-in-out;
      }
      .gallery img:hover {
        filter: grayscale(0);
      }
      @media (max-width:769px) {
        body {
          margin-bottom: 75px;
        }
        .gallery {
          position: relative;
          top: initial;
          right: initial;
        }
        .footer.navbar {
          padding: 4px 8px;
        }
        .footer.navbar .gallery-item {
          margin: 2px;
        }
        .footer.navbar img {
          height: 50px;
          width: auto;
        }
      }
';
  }
}
