<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wptest');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'QF7ufI*]Y?{/?*cGYo-DO*43T/Fu4(j`t<Uz!4m/JTZyAf8nynsw89$&d-7b<5_/');
define('SECURE_AUTH_KEY',  ';*IP]G0]Fo{wAr4X|<iE?2fh_=x#o,>nB}Tey{t_(f>RFh=B/C*3SuK1Dl^<P0YD');
define('LOGGED_IN_KEY',    '$tc%w<DPj| N(`D5mo=-z:]yV6!cGYt|++Ge^,lrj[$DIkU{x;`36Qs:R^{<B[AI');
define('NONCE_KEY',        '$wAB)B1lUzR,:HhB>+^xf6TP&+We_h9FP{(3!PP.nu[BMKoow^n1/H3D8G[f0KyU');
define('AUTH_SALT',        'b$i(n8BQJYdm*a(2/-[b$Dx&)7;QOoU,Mkkw:WXKYV#$S>4CFKdpPa4T7hCzO]|Q');
define('SECURE_AUTH_SALT', '=Y]5m.e~F^OKL!.2avEb7[bx$do_G%KV6e[!zRM (G>F,d;nW%VPa0$^3m}Ad<s+');
define('LOGGED_IN_SALT',   '6Z_sKGN_PVT*lskIsx_VnKS-#Yg[!~6`RRn+dxN/m#zNDOL|[7*8^-QW[ciQLjpn');
define('NONCE_SALT',       'O7&!g!u=Lq5H5tnF/iAGr(i$/1x)mcpl,8a+NRzGod_Gb))M+S`b2{=yyfp K1q3');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
