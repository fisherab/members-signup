<?php

/** 
 * Class holding the opportunity functions. An opportunity is something that you can sign up for.
 */
class Members_Signup_Opportunity {

    /**
     * This is hooked to 'init' to create the ms_opportunity post type
     */
    public function register_opportunity() {
        $labels = array(
            'name'               => __( 'Opportunities' ),
            'singular_name'      => __( 'Opportunity' ),
            'add_new'            => __( 'Add New' ),
            'add_new_item'       => __( 'Add New Opportunity' ),
            'edit_item'          => __( 'Edit Opportunity' ),
            'new_item'           => __( 'New Opportunity' ),
            'all_items'          => __( 'All Opportunities' ),
            'view_item'          => __( 'View Opportunity' ),
            'search_items'       => __( 'Search Opportunities' ),
            'not_found'          => __( 'No Opportunitie found' ),
            'not_found_in_trash' => __( 'No Opportunities found in the Trash' ), 
        );
        $args = array(
            'labels'          => $labels,
            'description'     => 'Holds all information about a specific opportunity',
            'public'          => true,
            'menu_position'   => 5,
            'supports'        => ['title'],
            'has_archive'     => true,
            'capability_type' => 'page',
        );
        register_post_type( 'ms_opportunity', $args ); 
    }

    /**
     * This is hooked to add_meta_boxes to ms_opportunity posts
     */
    public function add_opportunity_boxes() {
        global $post;

        add_meta_box(
            'opportunity_id',
            'Opportunity ID',
            [$this,'opportunity_id_content'],
            'ms_opportunity', 'advanced', 'default');


        $managers = get_post_meta($post->ID,'managers',true);
        $count = $managers ? count($managers) : 0;
        for ($x = 0; $x < $count+1; $x++) {
            add_meta_box( 
                'manager_box_' . $x,
                __( 'Manager ' . $x),
                array($this, 'manager_content'),
                'ms_opportunity',
                'advanced',
                'default',
                [$x]
            );
        }

        $fields = get_post_meta($post->ID,'fields',true);
        $count = $fields ? count($fields) : 0;
        for ($x = 0; $x < $count+1; $x++) {
            add_meta_box(
                'field_box_name' . $x,
                __( 'Field name'), [$this, 'field_name_content'],
                'ms_opportunity',
                'advanced',
                'default',
                [$x]
            );
            add_meta_box(
                'field_box_type' . $x,
                __( 'and type'), [$this, 'field_type_content'],
                'ms_opportunity',
                'advanced',
                'default',
                [$x]
            );
        }
    }

    /** 
     * Invoked by add_opportunity to display opportunity id
     */
    public function opportunity_id_content($post) {
        echo '<legend>Reference with shortcodes of [ms-subscribe id=' . $post->ID . '], [ms-list-people id=' . $post->ID . '] and [ms-list-opportunities]</legend>';
    }


    /**
     * Invoked by add_opportunity_boxes to display boxes to input manager names for a specific opportunity.
     */
    public function manager_content( $post, $args ) {
        $x = $args['args'][0];
        $post_name = 'manager_'.$x;
        $managers = get_post_meta($post->ID,'managers',true);
        if ($managers && array_key_exists($x, $managers)) {
            $manager = $managers[$x];
        } else {
            $manager = 0;
        }
        echo '<label for="' . $post_name . '"></label>';
        echo '<select id="' . $post_name . '" name="' . $post_name . '" size="1">';
        echo '<option selected value="0"></option>';
        foreach (get_users('orderby=meta_value&meta_key=last_name') as $user) {
            $selected = ($user->ID == $manager) ? ' selected' : '';
            $name = $user->get('first_name') . ' ' . $user->get('last_name') . esc_html(' <') . $user->get('user_email') . esc_html('>'); 
            echo '<option' . $selected . ' value="' .$user->ID. '">'. $name . '</option>';
        }
        echo '</select>';
    }

    public function field_name_content($post, $args) {
        $x = $args['args'][0];
        $unique_name = 'field_name_' . $x;
        $fields = get_post_meta($post->ID,'fields',true);
        if ($fields && array_key_exists($x, $fields)) {
            $field = $fields[$x]['name'];
        } else {
            $field = "";
        }
        echo '<label for="' . $unique_name . '"></label>';
        echo '<input type="text" id="' . $unique_name . '" name="' . $unique_name . '" value="' . $field . '">';
    }

    public function field_type_content($post, $args) {
        $x = $args['args'][0];
        $unique_name = 'field_type_' . $x;
        $fields = get_post_meta($post->ID,'fields',true);
        if ($fields && array_key_exists($x, $fields)) {
            $typex = $fields[$x]['type'];
        } else {
            $typex = "";
        }
        echo '<label for="' . $unique_name . '"></label>';
        echo '<select id="' . $unique_name . '" name="' . $unique_name . '" size="1">';
        foreach (["Text","Checkbox"] as $type) {
            $selected = ($typex == $type) ? ' selected' : '';
            echo '<option' . $selected . ' value="' .$type . '">'. $type . '</option>';
        }
        echo '</select>';
    }

    public function save_opportunity($post_id) {

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  {
            return;
        }

        if ( ! isset ($_POST['post_type']) || 'ms_opportunity' != $_POST['post_type'] ) {
            return;
        }

        if ( !current_user_can( 'edit_page', $post_id ) ){
            return;
        } 

        $managers = [];
        for ($x = 0; ; $x++) {
            $key = 'manager_'.$x;
            if (! array_key_exists($key, $_POST)) break;
            $value = intval($_POST[$key]);
            if ($value != 0) {
                $managers[] = $_POST[$key];
            }
        }

        $fields = [];
        for ($x = 0; ; $x++) {
            $key_for_name = 'field_name_'.$x;
            $key_for_type = 'field_type_'.$x;
            if (! array_key_exists($key_for_name, $_POST)) break;
            if ($_POST[$key_for_name]) {
                $fields[] = ['name' => sanitize_text_field($_POST[$key_for_name]), 'type' => sanitize_text_field($_POST[$key_for_type])];
            }
        }
        update_post_meta( $post_id, 'managers', $managers);
        update_post_meta( $post_id, 'fields', $fields);
    }
}
