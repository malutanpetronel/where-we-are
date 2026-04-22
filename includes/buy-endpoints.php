<?php

if (!defined('ABSPATH')) {
    exit; // Blocare acces direct
}

// Endpoint pentru inițierea cumpărării
add_action('init', 'ppwwa_register_buy_endpoint');
function ppwwa_register_buy_endpoint() {
    add_rewrite_rule('^buy/initiate$', 'index.php?buy_initiate=1', 'top');
}

add_filter('query_vars', 'ppwwa_add_buy_query_var');
function ppwwa_add_buy_query_var($vars) {
    $vars[] = 'buy_initiate';
    return $vars;
}

// Permite redirecționarea către shop.webnou.ro
add_filter('allowed_redirect_hosts', 'ppwwa_allow_shop_redirect');
function ppwwa_allow_shop_redirect($hosts) {
    $hosts[] = 'shop.webnou.ro';
    return $hosts;
}

add_action('template_redirect', 'ppwwa_handle_buy_request');
function ppwwa_handle_buy_request() {
    if (get_query_var('buy_initiate')) {
        $shop_url = 'https://shop.webnou.ro/product/where-we-are/';

        // Obține emailul utilizatorului curent
        $current_user = wp_get_current_user();
        if (!$current_user->exists()) {
            wp_die('You must be logged in to make a purchase.');
        }
        $email = $current_user->user_email;

        // Construiește URL-ul de redirecționare către magazinul WooCommerce
        $redirect_url = add_query_arg([
            'add-to-cart' => 2144,
            'quantity'    => 1,
            'wwa_ref'     => urlencode(home_url()),
            'wwa_email'   => urlencode($email),
        ], $shop_url);

        wp_safe_redirect($redirect_url);
        exit;
    }
}

// Endpoint pentru succesul cumpărării
add_action('init', 'ppwwa_register_buy_success_endpoint');
function ppwwa_register_buy_success_endpoint() {
    add_rewrite_rule('^buy/success$', 'index.php?buy_success=1', 'top');
}

add_filter('query_vars', 'ppwwa_add_buy_success_query_var');
function ppwwa_add_buy_success_query_var($vars) {
    $vars[] = 'buy_success';
    return $vars;
}

add_action('template_redirect', 'ppwwa_handle_buy_success_request');
function ppwwa_handle_buy_success_request() {
    if (get_query_var('buy_success')) {
        update_option('ppwwa_paid', 1); // ← prefix adăugat
        wp_die('Thank you for your purchase! Your payment was successful.');
    }
}