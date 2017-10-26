<?php

/**
 * @package Boldface\Boldface
 */
declare( strict_types = 1 );
namespace Boldface\Boldface\Views;

/**
 * Views for the user
 *
 * @since 1.0
 */
class user extends \Boldface\Bootstrap\Views\abstractViews {

  /**
   * Print the user HTML
   *
   * @access public
   * @since  1.0
   */
  public function html() {
    printf(
      '<div class="%1$s"><ul class="%2$s">%3$s</ul></div>',
      \apply_filters( 'Boldface\Boldface\Views\user\class', 'container-fluid bg-faded' ),
      \apply_filters( 'Boldface\Boldface\Views\user\list\class', 'container user-list d-flex flex-wrap justify-content-start list-unstyled' ),
      \apply_filters( 'Boldface\Boldface\Views\user\list', '' )
    );
  }

  /**
   * Return the user list with a new user attached to the end
   *
   * @access public
   * @since  1.0
   *
   * @param string $carry The current user list
   * @param string $id    The user ID
   *
   * @return string The new user list
   */
  public function userList( string $carry, string $id ) : string {
    $id = intval( $id );
    return $carry . sprintf(
      '<li class="%1$s" data-title="%2$s" data-content="%3$s"><a href="%4$s">%5$s</a></li>',
      \apply_filters( 'Boldface\Boldface\Views\user\list\class', '' ),
      \apply_filters( 'Boldface\Boldface\Views\user\list\data-title', '', $id ),
      \apply_filters( 'Boldface\Boldface\Views\user\list\data-content', '', $id ),
      \get_author_posts_url( $id ),
      \apply_filters( 'Boldface\Boldface\Views\user\list\content', '', $id )
    );
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
    $css = str_replace( 'padding-top:56px;', 'padding-top:60px;', $css );
    return $css . '
      .user-list li {
        padding: 0.5rem;
        border-right: 3px solid #fff;
        transition: background-color 0.25s ease-in-out;
      }
      .user-list li:hover {
        background-color: #ddd;
      }
      .user-list img {
        transition: transform 0.25s ease-in-out;
      }
      .user-list li:hover img {
        transform: scale(1.1);
      }
      .author h1 {
        margin: 1.75rem auto;
      }
      .author h2 {
        margin-top: 4rem;
      }
      a:hover {
        text-decoration: none;
      }
      main img {
        margin-bottom: 1.5rem;
      }
      @media (max-width:769px) {
        .user-list li {
          flex-basis: initial;
          flex-wrap: wrap;
        }
        .user-list p {
          display: none;
        }
        .user-list img {
          margin: 0 !important;
        }
      }
      ';
  }

  /**
   * Print extra tables on the user profile
   *
   * @access public
   * @since  1.0
   *
   * @param \WP_User $user The \WP_User object
   */
  public function show_user_profile( \WP_User $user ) { ?>
    <script>
    (function($){
      $('#description').parents('tr').remove();
    })(jQuery);
    </script>
    <table class="form-table">
      <tr>
        <th>
          <label for="description">Biographical Info</label>
        </th>
        <td>
          <?php
          $desc = \get_user_meta( $user->ID, 'description', true );
          \wp_editor( $desc, 'description' );
          ?>
          <p class="description">Share a little biographical information to fill out your profile.</p>
        </td>
      </tr>
    </table>
    <h3>Custom Avatar</h3>
    <table class="form-table">
      <tr>
        <th>
          <label for="custom_avatar">Custom Avatar URL:</label>
        </th>
        <td>
          <input type="text" name="custom_avatar" id="custom_avatar" value="<?php echo \esc_attr( \get_the_author_meta( 'custom_avatar', $user->ID ) ); ?>">
        </td>
      </tr>
    </table>
    <h3>Job Title</h3>
    <table class="form-table">
      <tr>
        <th>
          <label for="job_title">Job Title:</label>
        </th>
        <td>
          <input type="text" name="job_title" id="job_title" value="<?php echo \esc_attr( \get_the_author_meta( 'job_title', $user->ID ) ); ?>">
        </td>
      </tr>
    </table>
  <?php
  }
}
