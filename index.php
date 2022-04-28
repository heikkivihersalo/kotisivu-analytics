<?php

/**
 * Plugin Name:       Kotisivu Server-Side Analytics
 * Description:       Server-Side Analytics for Google Analytics, Facebook CAPI etc.
 * Requires at least: 5.8
 * Version:           1.0.0
 * Author:            Kotisivu Dev
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ksd-server-side-analytics
 */

/* Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

/* Define plugin directory path */
define('DIR_URL', plugin_dir_url(__FILE__));
define('DIR_PATH', plugin_dir_path(__FILE__));

/* Define plugin name and options */
define('SERVER_SIDE_ANALYTICS_PLUGIN_NAME', 'ksd_server_side_analytics');
define('SERVER_SIDE_ANALYTICS_PLUGIN_VERSION', 1.0);
define('SERVER_SIDE_ANALYTICS_PLUGIN_OPTIONS', get_option(SERVER_SIDE_ANALYTICS_PLUGIN_NAME));

/* Define REST api custom endpoint */
define('SERVER_SIDE_ANALYTICS_REST_NAMESPACE', 'ksd-server-side-analytics');

class ServerSideAnalytics {

    /**
     * Plugin name
     */
    private $plugin_name;

    /**
     * Plugin version
     */
    private $plugin_version;

    /**
     * Plugin options saved as an array
     */
    private $plugin_options;

    /**
     * User defined option to enable or disable server-side analytics
     */
    private $active;

    /**
     * Tag Manager endpoint. Usually something like https://domain.tld/g/collect
     */
    private $endpoint;

    /**
     * Google Analytics 4 measurement ID
     */
    private $measurement_id;

    /**
     * HTTP Only cookie used by Google Analytics and Tag Manager. Default is FPID.
     */
    private $cookie_name;

    /**
     * Cookie consent tracking type
     * - Disabling CMP always loads server-side tracking with cookies. 
     * - Hybrid tracking always sends http-requests, but does not set cookies without consent. 
     * - Normal tracking only sents http-requests when user has given consent.
     */
    private $cookie_consent_tracking;

    /**
     * Cookie consent provider name
     * - Current integrations include cookiehub, cookiebot
     */
    private $cookie_consent_provider;

    /**
     * Cookie consent ID
     */
    private $cookie_consent_id;

    /**
     * Preview header for debugging purposes. 
     * You can get current preview string (X-Gtm-Server-Preview header) from Tag Manager -> Preview -> ... -> Send Requests Manually. 
     * Please note that preview string changes after a while so you might need to generate new one quite frequently.
     */
    private $preview_string;


    /**
     * Constructor
     * 
     */
    public function __construct() {
        /* Load options */
        $this->plugin_name = SERVER_SIDE_ANALYTICS_PLUGIN_NAME;
        $this->plugin_version = SERVER_SIDE_ANALYTICS_PLUGIN_VERSION;
        $this->plugin_options = SERVER_SIDE_ANALYTICS_PLUGIN_OPTIONS;

        /* Load classes */
        $this->load_classes();
        $this->setup_settings_page();

        /* Enqueue scripts */
        new Enqueue(
            $this->plugin_name,
            [
                'id'           => $this->plugin_options['id'],
                'url'          => $this->plugin_options['url'],
                'timeout'      => $this->plugin_options['timeout'],
                'js_container' => isset($this->plugin_options['js_container']) ? $this->plugin_options['js_container'] : '',
                'cmp_tracking' => $this->plugin_options['cmp_tracking'],
                'cmp_provider' => $this->plugin_options['cmp_provider'],
                'cmp_id'       => $this->plugin_options['cmp_id'],
            ],
            'text/javascript'
        );

        $this->load_rest_api();
    }


    /**
     * Load Classes
     * 
     */
    private function load_classes() {
        foreach (glob(dirname(__FILE__) . '/includes/*.php') as $class)
            require_once $class;
    }

    /**
     * Load Rest Api
     * 
     */
    private function load_rest_api() {
        add_action('rest_api_init',  array($this, 'register_rest_endpoints'));
    }

    /**
     * Setup Settings Page
     * 
     */
    public function setup_settings_page() {
        /* Load settings page */
        $settings = new SettingsPage(
            $this->plugin_name,
            $this->plugin_options,
            array(
                'active'            => '',          // Checkbox to activate Server-side tracking
                'js_container'      => '',          // Checkbox to activate javascript / client-side tracking
                'url'               => '',          // Tag Manager URL
                'id'                => '',          // Tag Manager ID
                'timeout'           => '',          // Tag Manager loading timeout / delay
                'endpoint'          => '',          // Server-side container endpoint to send requests
                'measurement_id'    => '',          // GA4 measurement ID
                'cookie_name'       => 'FPID',      // Cookie name for server-side tracking
                'cookie_expires'    => 63072000,    // Cookie expires value
                'cookie_samesite'   => 'Strict',    // Cookie same-site setting
                'cmp_tracking'      => 'disable',   // Tracking setup (disable, track without cookies, normal)
                'cmp_provider'      => '',          // CMP provider name (Cookiehub, Cookiebot)
                'cmp_id'            => '',          // CMP ID
                'debug_mode'        => '',          // Checkbox to enable GA4 debug mode
                'preview_string'    => ''           // Tag Manager preview string (X-Gtm-Server-Preview)
            ),
            SERVER_SIDE_ANALYTICS_REST_NAMESPACE
        );
    }


    /**
     * Get Client ID
     */
    private function get_cid() {
        if (isset($_COOKIE[$this->plugin_options['cookie_name']]) && $_COOKIE[$this->plugin_options['cookie_name']]) {
            return $_COOKIE[$this->plugin_options['cookie_name']];
        }

        return time() . "." . mt_rand(10000000, 90000000);
    }


    /**
     * Set Server-Side Cookie
     * If server-side cookie is set, return it's value. Else create new one.
     */
    private function set_server_side_cookie($cookie) {
        if (isset($_COOKIE[$this->plugin_options['cookie_name']]) && $_COOKIE[$this->plugin_options['cookie_name']]) {
            return $_COOKIE[$this->plugin_options['cookie_name']];
        }

        if ($cookie != '') {
            setcookie($cookie->name, $cookie->value, array(
                'expires' => time() + $this->plugin_options['cookie_expires'],
                'path' => '/',
                'domain' => $cookie->domain,
                'secure' => true,
                'httponly' => true,
                'samesite' => $this->plugin_options['cookie_samesite'],
            ));
        }
    }


    /**
     * Get Server-Side Cookie
     */
    private function get_server_side_cookie() {
        if (isset($_COOKIE[$this->plugin_options['cookie_name']]) && $_COOKIE[$this->plugin_options['cookie_name']]) {
            return new WP_Http_Cookie(array('name' => $this->plugin_options['cookie_name'], 'value' => $_COOKIE[$this->plugin_options['cookie_name']]));
        }
    }


    /**
     * Rest Client
     * 
     */
    public function register_rest_endpoints() {
        register_rest_route(
            SERVER_SIDE_ANALYTICS_REST_NAMESPACE . '/v1',
            '/track',
            array(
                'methods'               => 'POST',
                'callback'              => [$this, 'send_event_request'],
                'permission_callback'   => '__return_true'
            )
        );
    }


    /**
     * Send request to server-side
     *
     */
    public function send_event_request(WP_REST_Request $request) {
        if ($this->plugin_options['active'] != '1') {
            return false;
        }

        /* Check if client requested cookieless tracking */
        $parameters = $request->get_json_params();
        $cookieless = isset($parameters['cookieless']) ? $parameters['cookieless'] : '';
        $consent = isset($parameters['consent']) ? $parameters['consent'] : '';

        /* Build query URL */
        $data = array_merge(
            $request->get_json_params(),
            array(
                'tid'   => $this->plugin_options['measurement_id'], // Stream ID
                'cid'   => $this->get_cid(), // Client ID Value
                '_dbg'  => $this->plugin_options['debug_mode'] == '1' ? true : false, // Debug mode
            )
        );

        $query_url = $this->plugin_options['endpoint'] . '?' . str_replace('+', '%20', http_build_query($data));

        /* Send request */
        $response = wp_remote_get($query_url, array(
            'httpversion'   => '1.1',
            'user-agent'    => $_SERVER['HTTP_USER_AGENT'],
            'cookies'       => $this->get_server_side_cookie(),
            'headers' => array(
                'Content-Type'           => 'application/json; charset=utf-8',
                'Accept-Encoding'        => 'deflate, gzip, br',
                'X-Gtm-Server-Preview'   => $this->plugin_options['preview_string']
            ),
        ));

        /**
         * Get response cookie
         * If client requested cookieless tracking and has not given consent, do nothing
         */
        if ($cookieless != true || $consent == true) {
            $response_cookie = wp_remote_retrieve_cookie($response, $this->plugin_options['cookie_name']);
            $this->set_server_side_cookie($response_cookie);
        }
    }
}

new ServerSideAnalytics();
