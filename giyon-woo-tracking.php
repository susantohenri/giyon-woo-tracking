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
add_action('wp_ajax_giyon_woo_tracking_get', 'giyon_woo_tracking_get');
add_action('wp_ajax_giyon_woo_tracking_set', 'giyon_woo_tracking_set');

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
    wp_register_style('giyon-woo-tracking', plugin_dir_url(__FILE__) . 'giyon-woo-tracking.css', [], '1.0.0');
    wp_enqueue_style('giyon-woo-tracking');

    wp_register_script('giyon-woo-tracking', plugin_dir_url(__FILE__) . 'giyon-woo-tracking.js', ['jquery'], '1.0.0');
    wp_enqueue_script('giyon-woo-tracking');
    wp_localize_script('giyon-woo-tracking', 'giyon_woo_tracking', [
        'ajax' => admin_url('admin-ajax.php'),
        'form_selector' => '.giyon-woo-tracking'
    ]);
    include_once(plugin_dir_path(__FILE__) . 'giyon-woo-tracking-metabox.php');
}

function giyon_tracking_column_header($columns)
{
    return array_merge($columns, ['giyon-woo-tracking' => __('Tracking', 'textdomain')]);
}

function giyon_tracking_column_content($column_key, $order)
{
    if ($column_key == 'giyon-woo-tracking') {
        $data = giyon_woo_tracking_get_data($order->id);
        echo "<a target='_blank' href='{$data['tracking_link']}'>{$data['shipping_company']} <b>{$data['tracking_number']}</b> <i>{$data['tracking_link']}</i></a>";
    }
}

function giyon_tracking_email_complete_order($order, $sent_to_admin, $plain_text, $email)
{
    if ('customer_completed_order' != $email->id) return true;
    $order_id = $order->id;
    $data = giyon_woo_tracking_get_data($order_id);
    if ('' == $data['tracking_number']) return true;
    echo "
        <b>Shipping Company :</b> {$data['shipping_company']}<br>
        <b>Tracking Number :</b> {$data['tracking_number']}<br>
        <b>Tracking Link :</b> {$data['tracking_link']}<br>
        <br>
    ";
}

function giyon_woo_tracking_get_data($order_id)
{
    $order = wc_get_order($order_id);
    $shipping_company = $order->get_meta('giyon-woo-tracking-shipping_company');
    $tracking_number = $order->get_meta('giyon-woo-tracking-tracking_number');
    $tracking_link = '' == $tracking_number ? '' : site_url("t/{$order_id}");
    return [
        'shipping_company' => $shipping_company,
        'tracking_number' => $tracking_number,
        'tracking_link' => $tracking_link
    ];
}

function giyon_woo_tracking_get()
{
    $order_id = $_GET['order_id'];
    $data = giyon_woo_tracking_get_data($order_id);
    exit(json_encode($data));
}

function giyon_woo_tracking_set()
{
    $order_id = $_POST['order_id'];
    $shipping_company = $_POST['shipping_company'];
    $tracking_number = $_POST['tracking_number'];

    $order = wc_get_order($order_id);
    $order->update_meta_data('giyon-woo-tracking-shipping_company', $shipping_company);
    $order->update_meta_data('giyon-woo-tracking-tracking_number', $tracking_number);

    // update_post_meta($order_id, 'giyon-woo-tracking-shipping_company', $shipping_company);
    // update_post_meta($order_id, 'giyon-woo-tracking-tracking_number', $tracking_number);

    // $tracking_link = 'https://track.kuronekoyamato.co.jp/english/tracking';
    // if ('Japan Post' == $shipping_company) $tracking_link = "https://trackings.post.japanpost.jp/services/srv/search/direct?reqCodeNo1={$tracking_number}&searchKind=S002&locale=en";
    // $order->update_meta_data('giyon-woo-tracking-tracking_link', $tracking_link);

    $order->save();
    exit(200);
}

add_action('init', function () {
    $url = explode('/', $_SERVER['REQUEST_URI']);
    $page = $url[1];
    if (!$page || !in_array($page, ['t'])) return true;

    $order_id = $url[2];
    $data = giyon_woo_tracking_get_data($order_id);
    if ('Yamato' == $data['shipping_company']) wp_redirect('https://track.kuronekoyamato.co.jp/english/tracking');
    else {
        $japan_post = wp_remote_get("https://trackings.post.japanpost.jp/services/sp/srv/search/direct?locale=en&reqCodeNo={$data['tracking_number']}", [
            'user-agent' => 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Mobile Safari/537.36'
        ]);
        $body = $japan_post['body'];
        $body = end(explode('</h2>', $body));
        $body = reset(explode('<ul', $body));
        $scrape_css = plugin_dir_url(__FILE__) . 'giyon-woo-tracking-scrape.css';
        echo "
            <link rel='stylesheet' type='text/css' href='https://trackings.post.japanpost.jp/services/css/sp/smt.css' media='screen,print' />
            <link rel='stylesheet' type='text/css' href='https://trackings.post.japanpost.jp/services/css/sp/jquery.mobile-1.1.1.min.css' media='screen,print' />
            <link rel='stylesheet' type='text/css' href='{$scrape_css}' media='screen,print' />
            <div>$body</div>
        ";
    }
    exit;
});
