<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

$base_path = substr(ABSPATH, strlen($_SERVER['DOCUMENT_ROOT']));
define('WP_SITEURL', "http://${_SERVER['HTTP_HOST']}${base_path}");
define('WP_HOME',    "http://${_SERVER['HTTP_HOST']}${base_path}");

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wp_blog');

/** MySQL database username */
define('DB_USER', 'instafluence');

/** MySQL database password */
define('DB_PASSWORD', 'c290cm4nf36');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '%|FRg{UcdS2<67 OtNh|F7,IZpF;Isi~T-PK.i%Lp!iS:P+E50c,5R-Wwt}}xFPx');
define('SECURE_AUTH_KEY',  '87S<eKP+?MkJOZl@DH^b++#=~%nhg]$PK<^]`e,*?DU3Y:?wx,|Qo]A}$O@tW^X3');
define('LOGGED_IN_KEY',    'kH>q{:|mz)fvn.+(V^}(I:?_0[6]7;gN4I$V,`]6X,#xj_fkEhZZp?[`HRn,Pb=8');
define('NONCE_KEY',        '3A{-ys :#y6/10mU4#+Q<n(.yOnnp&H_Z-i?cBotN)@PO-0+C0+EI?N|sF!Go!Ii');
define('AUTH_SALT',        'lch{>Tf;tJBhK=#Ve65wyoV+K@nz_=>*,c4KNLgj|<wU8I(Ur=q9k{<rD.;W0Dyg');
define('SECURE_AUTH_SALT', '1mC F@B@bUEu+FShCy6N}J{f$TE]gzTOz=a0.F(&Q**.C^!+h56|fkH>n=Pj)xL4');
define('LOGGED_IN_SALT',   'YBz`j#D+4> /a&H`j&[Np$!&38j>jP!X{qEYzE8rRM,#:u_@&^-t+r<4v-5uW`zq');
define('NONCE_SALT',       '}N7O9;5sZ~AZ9>LrlN-3@gb`mobJ,98]D*!g4h#qd]69l]BHl*RHhlT yc`C_E|=');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'fbk_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
