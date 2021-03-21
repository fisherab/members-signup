<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 */
class Members_Signup {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if ( defined( 'MEMBERS_SIGNUP_VERSION' ) ) {
            $this->version = MEMBERS_SIGNUP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'members-signup';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    private function load_dependencies() {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-members-signup-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-members-signup-i18n.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-members-signup-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-members-signup-opportunity.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-members-signup-public.php';
        $this->loader = new Members_Signup_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Members_Signup_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    private function define_admin_hooks() {
        $plugin_admin = new Members_Signup_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'init', $plugin_admin, 'register_opportunity' );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_opportunity_boxes' );
        $this->loader->add_action( 'save_post',  $plugin_admin, 'save_opportunity' );
    }

    private function define_public_hooks() {
        $plugin_public = new Members_Signup_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

}
