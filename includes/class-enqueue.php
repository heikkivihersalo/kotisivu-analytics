<?php

/* Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

class Enqueue {
    /**
     * WordPress script handle. 
     */
    private $handle;

    /**
     * PHP variables that can be passed to client-side (javascript)
     */
    private $js_variable;

    /**
     * Script type (application/javascript, module etc.)
     */
    private $script_type;


    /**
     * Constructor
     */
    public function __construct($handle, $js_variable, $script_type) {
        $this->handle = $handle;
        $this->js_variable = $js_variable;
        $this->script_type = $script_type;

        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_filter('script_loader_tag', array($this, 'add_module_attribute'), 10, 3);
    }


    /**
     * Enqueue scripts
     * 
     */
    public function enqueue_scripts() {
        wp_register_script($this->handle, DIR_URL . 'js/main.js', '', filemtime(DIR_PATH . 'js/main.js'), true);
        wp_localize_script($this->handle, 'serverside_localize', $this->js_variable);
        wp_enqueue_script($this->handle);
    }


    /**
     * Enqueue Admin Styles
     * 
     */
    public function admin_enqueue_scripts() {
        wp_register_style($this->handle, DIR_URL . 'css/admin.css', '', filemtime(DIR_PATH . 'css/admin.css'), 'all');
        wp_enqueue_style($this->handle);
    }


    /**
     * Add module attribute to script tag
     * 
     */
    public function add_module_attribute($tag, $handle, $src) {
        /* If not current script handle, return original tag */
        if ($this->handle !== $handle) return $tag;

        /* Return script tag with defer and type module */
        return '<script type="' . $this->script_type . '" src="' . esc_url($src) . '" data-no-optimize="1" data-no-defer="1"></script>';
    }
}
