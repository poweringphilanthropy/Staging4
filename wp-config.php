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


define('DB_NAME','poweri33_wp306_st4');
define('DB_USER','poweri33_st4');
define('DB_PASSWORD','448mhzoagC1dtu2bnDtq');
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'wlcmtn0bfdgypzptac4euhhtqeyr09br9eypj60uv8wi5iiqbu0z61oudnxudfjb');
define('SECURE_AUTH_KEY',  'gygtalaousktsyxba1lsl20viy2mz1wgywso8nizzn5mhxqhxflsl5v6artar2hb');
define('LOGGED_IN_KEY',    'i24pfgd0u1c8l7mthmtcmcc2oqndjvigzl9tnworg5gqenqdedeg4ii49snig65d');
define('NONCE_KEY',        'iqqb0aq0bbddlwkspdk9mekcobnexfiqarvi9tg5szshzok2jdt3virh9myblgtx');
define('AUTH_SALT',        'hbxu38asd8jxr1cup9bpyjefffz5royslva6z5nroibx83vxi1nsl27dbdaxxzfb');
define('SECURE_AUTH_SALT', 'gfcbzptv2gyvkhyrmmt0desubbadaplho4rddcwmb1jpxdvjpegeavt7lrsz1zqc');
define('LOGGED_IN_SALT',   'gjnbs8sqkjv2f7bagjrheezqozlkbvkpbrewrcvzkyukbmquqmetaluasfkakdxi');
define('NONCE_SALT',       'qvbo9z1wwavnvnzalpf54fclicr2ghbxxtbfoisujxm0bl0kx87bkgaglgbtbi5j');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
define('WP_CACHE_KEY_SALT', '2L1bOEw1mewb77BALP+1MA');
$table_prefix  = 'wpxj_';

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
define('WP_DEBUG', true	);
define('WP_DEBUG_DISPLAY', false);
define('WP_DEBUG_LOG', true);

/**
 * SALESFORCE
 */
define('G4G_ACCOUNT_C', '0010H00002OOyEH'); // live
define('G4G_RECORD_TYPE_ID', '012i00000016TZU');
define('G4G_GREATER_CAUSE_STRIPE_ID', 'acct_1BtLuVGddygpDFC9');
define('G4G_SALESFORCE_ENDPOINT', 'https://login.salesforce.com/');
define('G4G_SALESFORCE_CLIENT_ID', '3MVG9A2kN3Bn17hvRmtLqZZvdHXzPYnIwZ29RbAqCBsZUe10_4AJ_7txSg.7YSL98kfL32Z0psfnQ3wXlQduY');
define('G4G_SALESFORCE_CLIENT_SECRET', '5890542998420008556');
define('G4G_SALESFORCE_USERNAME', 'ksdeveloper@hq.kappasigma.org');
define('G4G_SALESFORCE_PASSWORD', 'KSapi1869!');

function md($debug_var, $mode = 'print_r', $force = false) {

    if ($force || isset($_GET['debug'])) {
        echo '<pre style="overflow:auto">';
        switch ($mode) {
            case 'var_dump':
                var_dump($debug_var);
                break;
            case 'print_r':
            default:
                print_r($debug_var);
                break;
        }
        echo '</pre>';
    }

} 

define('WP_HOME', 'https://www.' . $_SERVER['SERVER_NAME']);
define('WP_SITEURL', WP_HOME . '/');

function mdd($debug_var, $mode = 'print_r') {
	md($debug_var, $mode);
	die;
}
define( 'WP_MEMORY_LIMIT', '1065M' );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

# Disables all core updates. Added by SiteGround Autoupdate:
define( 'WP_AUTO_UPDATE_CORE', false );
