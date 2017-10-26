<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Models;

/**
 * Models for the user
 *
 * @since 1.0
 */
class user extends \Boldface\Bootstrap\Models\abstractModels {

  /**
   * Update the user avatar
   *
   * @access public
   * @since  1.0
   *
   * @param int $user_id The user ID
   */
  public function profile_update( int $user_id ) {
    if( ! \current_user_can( 'edit_user', $user_id ) ) return false;

    $avatar = \sanitize_text_field( $_POST[ 'custom_avatar' ] );
    \update_user_meta( $user_id, 'custom_avatar', $avatar );

    $job_title = \sanitize_text_field( $_POST[ 'job_title' ] );
    \update_user_meta( $user_id, 'job_title', $job_title );
  }

  /**
   * Filter the avatar
   *
   * @access public
   * @since  1.0
   *
   * @param string $avatar      The img tag for the user's avatar
   * @param mixed  $id_or_email The Gravatar to retrieve. Accepts a user_id, gravatar md5 hash,
   *                            user email, WP_User object, WP_Post object, or WP_Comment object
   * @param int    $size        Square avatar width and height in pixels to retrieve
   * @param string $default     URL for the default image or a default type
   * @param string $alt         Alternative text to use in the avatar image tag
   * @param array  $args        Arguments passed to get_avatar_data(), after processing
   *
   * @return string The HTML for the user avatar
   */
  public function get_avatar( string $avatar, $id_or_email, int $size, string $default, string $alt, array $args ) : string {
    $email = is_object( $id_or_email ) ? $id_or_email->comment_author_email : $id_or_email;

    if( \is_email( $email ) && ! \email_exists( $email ) ) return $avatar;

    $custom = \get_the_author_meta( 'custom_avatar', $id_or_email );
    $class = 'float-left';
    $alt = \get_the_author_meta( 'display_name', $id_or_email );

    if( $custom ) return sprintf( '<img src="%1$s" width="%2$s" height="%2$s" alt="%3$s" class="%4$s">', $custom, $size, $alt, $class );
    elseif( $avatar ) return $avatar;
    else return sprintf( '<img src="%1$s" width="%2$s" height="%2$s" alt="%3$s" class="%4$s">', $default, $size, $alt, $class );
  }

  /**
   * Filter the author photo
   *
   * @access public
   * @since  1.0
   *
   * @param string $html The HTML for the author photo
   * @param int    $id   The user ID
   *
   * @return string The filtered author photo
   */
  public function author_photo( string $html, int $id ) : string {
    $avatar = \get_the_author_meta( 'custom_avatar', $id );
    $img = '' !== $avatar ? sprintf( '<img src="%1$s" class="img img-fluid" alt="%2$s">', $avatar, '' ) : '';
    return sprintf( '
      <div class="row">
        <div class="col-md-2">%1$s</div>
        <div class="col-md-8">%2$s</div>
        <div class="col-md-2">%3$s</div>
      </div>',
      $img,
      $html,
      \apply_filters( 'Boldface\Boldface\Models\user\aside', '' )
    );
  }

  /**
   * Return the filtered author title
   *
   * @access public
   * @since  1.0
   *
   * @param string $title The unfiltered author title
   *
   * @return string The filtered author title
   */
  public function author_title( string $title ) : string {
    global $author;
    return sprintf(
      '<div class="row"><div class="col-md-2"></div><div class="col-md-8"><h1>%1$s</h1></div></div>',
      \get_userdata( $author )->display_name . ', ' . \esc_attr( \get_the_author_meta( 'job_title', $author ) )
    );
  }

  /**
   * Return the author biography
   *
   * @access public
   * @since 1.0
   *
   * @param string $title The unfiltered content
   *
   * @return string The filtered content
   */
  public function author_bio( string $content ) : string {
    global $author;
    $title  = \apply_filters( 'Boldface\Bootstrap\Models\author_title', '' );
    $bio = \wpautop( $content . \get_the_author_meta( 'description', $author ) );
    return $content . \do_shortcode( $title . \apply_filters( 'Boldface\Boldface\Models\user\bio', $bio, $author ) );
  }

  /**
   * Return the user list items
   *
   * @access public
   * @since  1.0
   *
   * @return string The user list items
   */
  public function userList() : string {
    return array_reduce( \apply_filters( 'Boldface\Boldface\Models\user\users', [] ), [ $this->view, 'userList' ], '' );
  }

  /**
   * Return the users
   *
   * @access public
   * @since  1.0
   *
   * @param array $users The unfiltered $users
   *
   * @return array The users
   */
  public function getUsers( array $users ) : array {
    return \get_users( \apply_filters( 'Boldface\Boldface\Models\user\users\args', [] ) );
  }

  /**
   * Return the user args
   *
   * @access public
   * @since  1.0
   *
   * @param array The unfiltered user args
   *
   * @return array The user args
   */
  public function getUsersArgs( array $args ) : array {
    return [
      'fields'     => 'ids',
      'orderby'    => 'registered',
      'order'      => 'ASC',
      'hide_empty' => false,
      'style'      => 'none',
      'echo'       => false,
      'role__in'   => [ 'administrator', 'editor', 'author' ],
    ];
  }

  /**
   * Return the user avatar
   *
   * @access public
   * @since  1.0
   *
   * @param string $content The unfiltered content
   * @param int    $id      The user ID
   *
   * @return string The user avatar
   */
  public function avatar( string $content, int $id ) : string {
    return \get_avatar( $id, '50' );
  }

  /**
   * Return the popover title
   *
   * @access public
   * @since  1.0
   *
   * @param string $content The unfiltered content
   * @param int    $id      The user ID
   *
   * @return string The popover title
   */
  public function popoversTitle( string $content, int $id ) : string {
    return \esc_attr( \get_userdata( $id )->display_name ?: '' );
  }

  /**
   * Return the popover content
   *
   * @access public
   * @since  1.0
   *
   * @param string $content The unfiltered content
   * @param int    $id      The user ID
   *
   * @return string The popover content
   */
  public function popoversContent( string $content, int $id ) : string {
    return \esc_attr( \get_the_author_meta( 'job_title', $id ) );
  }

  /**
   * Return the popover selector
   *
   * @access public
   * @since  1.0
   *
   * @param string $select The unfiltered selector
   *
   * @return string The popover selector
   */
  public function popoversSelect( string $select ) : string {
    return '.user-list li';
  }

  /**
   * Return the popover options
   *
   * @access public
   * @since  1.0
   *
   * @param string $options The unfiltered options
   *
   * @return string The popover options
   */
  public function popoversOptions( string $options ) : string {
    return '{trigger:\'hover\',placement:\'bottom\'}';
  }
}
