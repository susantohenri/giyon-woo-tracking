<?php

/**
 * Plugin Name: Giyon Woo Tracking
 * Plugin URI: https://github.com/susantohenri/giyon-woo-tracking
 * Description: Custom WooCommerce Tracking Plugin for Giyon.
 * Version: 1.0.0
 * Author: Henrisusanto
 * Author URI: https://github.com/susantohenri/
 * Text Domain: giyon-woo-tracking
 * Domain Path: /i18n/languages/
 * Requires at least: 6.5
 * Requires PHP: 8.1.29
 */

add_action('add_meta_boxes', 'giyon_tracking_meta_box');
function giyon_tracking_meta_box()
{
    add_meta_box(
        'giyon-woo-tracking',
        __('Tracking Code'),
        'giyon_tracking_meta_box_content',
        'woocommerce_page_wc-orders',
        'side',
        'default'
    );
}

function giyon_tracking_meta_box_content()
{
    include_once(plugin_dir_path(__FILE__) . 'giyon-woo-tracking-metabox.php');
}
