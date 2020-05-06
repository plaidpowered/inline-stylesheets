<?php
/**
 * Plugin Name: Inline &lt;style&gt;
 * Description: Inlines the stylesheets into the head
 * Author: Paul Houser
 * Author URI: https://plaidpowered.com
 * Text Domain: inline-stylesheets
 * Version: 1.0.1
 *
 * @package inline_stylesheets
 */

namespace PlaidPowered;

require_once __DIR__ . '/class-inlinecss.php';

add_action( 'plugins_loaded', array( 'PlaidPowered\InlineCSS', 'init' ) ) );
