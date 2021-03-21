<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Members_Signup_Admin {

    private $plugin_name;
    private $version;
    private $opportunity;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->opportunity = new Members_Signup_Opportunity();
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/members-signup-admin.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/members-signup-admin.js', array( 'jquery' ), $this->version, false );

    }

    public function register_opportunity () {
        $this->opportunity->register_opportunity();
    }

    public function add_opportunity_boxes() {
        $this->opportunity->add_opportunity_boxes();
    }

    public function save_opportunity($post_id) {
        $this->opportunity->save_opportunity($post_id);
    }

}
