<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Models;

/**
 * Models for the lightbox
 *
 * since 1.0
 */
class lightbox extends \Boldface\Bootstrap\Models\abstractModels {

  /**
   * Return the filtered attachment link URL
   *
   * @access public
   * @since  1.0
   *
   * @param string $link_html The page link HTML output
   * @param int    $id        Post ID
   * @param mixed  $size      Size of image. Image size or array of width and height values (in that order)
   * @param bool   $permalink Whether to add permalink to image
   * @param bool   $icon      Whether to include an icon
   * @param mixed  $text      If string, will be link text. Default false.
   *
   * @return string The filtered attachement link URL
   */
  public function wp_get_attachment_link( string $markup, int $id, $size, bool $permalink, bool $icon, $text ) : string {
    return sprintf(
      '<a href="%1$s" data-lightbox="%2$s" data-title="%3$s">%4$s</a>',
      $this->getAttribute( $markup, 'a', 'href' ),
      \apply_filters( 'Boldface\Boldface\Models\lightbox\value', 'gallery', $id ),
      \apply_filters( 'Boldface\Boldface\Models\lightbox\title', $this->getAttribute( $markup, 'img', 'alt' ), $id ),
      $this->getImg( $markup )
    );
  }

  /**
   * Return the filtered attachment image attributes
   *
   * @access public
   * @since  1.0
   *
   * @param array    $attr       An array of image attributes
   * @param \WP_Post $attachment The \WP_Post object for the attachment image
   * @param mixed    $size       Size of image. Image size or array of width and height values (in that order)
   *
   * @return array The filtered attachment image attributes
   */
  public function wp_get_attachment_image_attributes( array $attr, \WP_Post $attachment, $size ) : array {
    $attr[ 'class' ] .= ' img img-fluid';
    return $attr;
  }

  /**
   * Get the img markup from the original markup
   *
   * @access protected
   * @since  1.0
   *
   * @param string $markup The HTML markup
   *
   * @return string The img markup
   */
  protected function getImg( string $markup ) : string {
    $doc = new \DOMDocument();
    $doc->loadHTML( $markup );
    $tags = $doc->getElementsByTagName( 'img' );
    return $doc->saveHTML( $tags[0] );
  }

  /**
   * Abstract method to return the first tag attribute from the markup
   *
   * @access protected
   * @since  1.0
   *
   * @param string $markup    The HTML markup
   * @param string $tag       The HTML tag to get from the markup
   * @param string $attribute The HTML attribute to get from the tag
   *
   * @return string The alt attribute value
   */
  protected function getAttribute( string $markup, string $tag, string $attribute ) : string {
    $doc = new \DOMDocument();
    $doc->loadHTML( $markup );
    $tags = $doc->getElementsByTagName( $tag );
    return $tags[0]->getAttribute( $attribute ) ?: '';
  }
}
