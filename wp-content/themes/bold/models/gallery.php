<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Models;

/**
 * Models for the gallery
 *
 * since 1.0
 */
class gallery extends \Boldface\Bootstrap\Models\abstractModels {

  /**
   * @var The shortcode HTML
   *
   * @access protected
   * @since  1.0
   */
  protected $shortcode_html = '';

  /**
   * Remove the gallery shortcode(s) from the bio and add them to the footer
   *
   * @access public
   * @since  1.0
   *
   * @param string $bio The unfiltered biography
   *
   * @return string The filtered biography without the gallery shortcode
   */
  public function author_bio( string $bio ) : string {
    preg_match_all('/\[gallery(.*)\]/', $bio, $matches );
    $this->shortcodes = $matches[0];
    foreach( $matches[0] as $match ) {
      $bio = str_replace( $match, '', $bio );
      $this->view->html .= \do_shortcode( $match );
    }
    return $bio;
  }

  /**
   * Modify the div to an aside
   *
   * @access public
   * @since  1.0
   *
   * @return string The gallery HTML
   */
  public function aside() : string {
    // Replace the outermost div with an aside
    $pos = strpos( $this->view->html, '<div' );
    $this->view->html = substr_replace( $this->view->html, '<aside', $pos, strlen( '<div' ) );
    $pos = strrpos( $this->view->html, '</div>' );
    $this->view->html = substr_replace( $this->view->html, '</aside>', $pos, strlen( '</div>' ) );

    $start = strpos( $this->view->html, "class='gallery" );
    $stop = strpos( $this->view->html, "'>", $start );
    $class = substr( $this->view->html, $start + strlen( "class='" ), $stop-$start - strlen( "class='") );
    return $this->view->html = str_replace( $class, \apply_filters( 'gallery_class', $class ), $this->view->html );
  }

  /**
   * Return the filtered attachement fields
   *
   * @access public
   * @since  1.0
   *
   * @param array    $form_fields The form fields to edit
   * @param \WP_Post $post The WordPress post
   *
   * @return array The filtered attachement fields
   */
  public function attachment_fields_to_edit( array $form_fields, \WP_Post $post ) : array {
    $form_fields[ 'gallery_link_url' ] = [
      'label' => 'Gallery Link URL',
      'input' => 'text',
      'value' => \get_post_meta( $post->ID, '_gallery_link_url', true ),
    ];
    return $form_fields;
  }

  /**
   * Update the post meta for the gallery link URL
   *
   * @access public
   * @since  1.0
   *
   * @param array $post       An array of post data
   * @param array $attachment The attachment
   */
  public function attachment_fields_to_save( array $post, array $attachment ) {
    if( isset( $attachment[ 'gallery_link_url' ] ) )
      \update_post_meta( $post[ 'ID' ], '_gallery_link_url', $attachment[ 'gallery_link_url' ] );
  }

  /**
   * Return the filtered lightbox link title
   *
   * @access public
   * @since  1.0
   *
   * @param string $title  The lightbox link title
   * @param int    $id     Post ID
   *
   * @return string The filtered lightbox link title
   */
  public function lightbox_title( string $title, int $id ) : string {
    if( '' === ( $gallery_link_url = \get_post_meta( $id, '_gallery_link_url', true ) ) ) return $title;
    return sprintf( '&lt;a href=&quot;%1$s&quot;&gt;%2$s&lt;/&gt;', $gallery_link_url, $title ?: 'Visit Website' );
  }
}
