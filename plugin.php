<?php
/**
 * @link              http://tunapanda.org
 * @since             1.0
 * @package           tunapanda/wp-swag
 * @wordpress-plugin
 *
 * Plugin Name:       Swag
 * Plugin URI:        https://github.com/tunapanda/wp-swag
 * Description:       Swag is a gamified open source elearning platform meant for delivering educational content especially in areas without internet connection. It allow users to create and share their content within the platform without the need for internet.
 * Version:           1.0
 * Author:            Tunapanda
 * Author URI:        http://www.tunapanda.org/
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

ini_set("xdebug.overload_var_dump", "off");

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/app/App.php';

require_once __DIR__ . '/acf/acf.php';
