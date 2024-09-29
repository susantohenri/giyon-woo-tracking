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
 * Requires PHP: 8.0.30
 */

add_action('add_meta_boxes', 'giyon_tracking_meta_box');
add_filter('manage_woocommerce_page_wc-orders_columns', 'giyon_tracking_column_header');
add_action('manage_woocommerce_page_wc-orders_custom_column', 'giyon_tracking_column_content', 10, 2);
add_action('woocommerce_email_before_order_table', 'giyon_tracking_email_complete_order', 20, 4);

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

function giyon_tracking_column_header($columns)
{
    return array_merge($columns, ['verified' => __('Tracking', 'textdomain')]);
}

function giyon_tracking_column_content($column_key, $order)
{
    if ($column_key == 'verified') echo $order->id;
}

function giyon_tracking_email_complete_order($order, $sent_to_admin, $plain_text, $email)
{
    echo '<h2 class="email-upsell-title">Lorem Ipsum sit Amet</p>';
}
