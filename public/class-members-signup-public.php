<?php

/**
 * The public-facing functionality of the plugin.
 */
class Members_Signup_Public {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->register_short_codes();
        add_action('template_redirect', [$this, 'process_send_registration']);
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/members-signup-public.css', array(), $this->version, 'all' );
    }

    public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/members-signup-public.js', array( 'jquery' ), $this->version, false );
    }

    private function register_short_codes() {
        add_shortcode('ms-subscribe', [$this, 'subscribe']);
        add_shortcode('ms-list-people', [$this, 'list_people']);
    }

    public function subscribe($atts, $content = null) {
        $me = wp_get_current_user();
        if ($me->ID === 0) return 'Sorry you must be logged in to subscribe.';
        if (array_key_exists('id', $atts)) {
            $opportunity_id = $atts['id'];
            if (get_post_status($opportunity_id) == false) { 
                return 'Failed to find opportunity with ID of '.$opportunity_id;
            }
            $fields = get_post_meta($opportunity_id, 'fields', true);
            $managers = get_post_meta($opportunity_id, 'managers', true);
            $tman =($managers) ? in_array($me->ID, $managers) : false;
        } else {
            return 'Competion not specified in call to short code.';
        }

        $html = '';
        $html .= '<form action="." method="POST">';
        $html .= wp_nonce_field('send_registration', 'annie_the_aesthetic_aardvark', true, false);
        $html .= '<input type="hidden" name="opportunity_id" value="' .$opportunity_id . '">';
        if ($tman) {
            $html .= '<label for="as"><br/>As manager, for whom are you acting?</label>'; 
            $html .= '<select id="as" name="as_id">';
            foreach (get_users('orderby=meta_value&meta_key=last_name') as $user) {
                $selected = ($user->ID == $me->ID) ? ' selected' : '';
                $name = $user->get('first_name') . ' ' . $user->get('last_name') . esc_html(' <') . $user->get('user_email') . esc_html('>'); 
                $html .= '<option' . $selected . ' value="' .$user->ID. '">'. $name . '</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<input type="hidden" name="as_id" value="' . $me->ID . '">';
            $fieldvalues = get_post_meta($opportunity_id, 'fields_for_' . $me->ID, true);
        }

        $n=0;
        foreach ($fields as $field) {
            $name = 'field_' . $n;
            $html .= '<div>';
            $current = isset($fieldvalues[$field[0]]) ? $fieldvalues[$field[0]] : "";
            if ($field[1] == "Text") {
                $html .= '<label for="' . $name . '">' . $field[0] . '</label>';
                $html .= '<input type="text" id="' . $name . '" name="' . $name . '" value="' . $current . '">'; 
            } else if ($field[1] == "Checkbox") {
                $checked = $current ? " checked" : "";
                $html .= '<label for="' . $name . '">' . $field[0] . '</label>';
                $html .= '<input type="checkbox" value="1" id="' . $name . '" name="' . $name . '" ' . $checked . '>'; 
            }
            $html .= '</div>';
            $n++;
        }
        if ($fieldvalues) {
            $html .= '<input type="submit" value="Update" name="send_registration" class="submit"/>';
            $html .= ' or ';
            $html .= '<input type="submit" value="Unsubscribe" name="send_registration" class="submit"/>';
        } else {
            $html .= '<input type="submit" value="Subscribe" name="send_registration" class="submit"/>';
        }
        $html .= '</form>';

        return $html;
    }

    public function process_send_registration() {
        if ( ! isset( $_POST['send_registration'] ) || ! isset( $_POST['annie_the_aesthetic_aardvark'] ) )  {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['annie_the_aesthetic_aardvark'], 'send_registration' ) ) {
            return;
        }
        $as_id = $_POST['as_id'];
        $opportunity_id = $_POST['opportunity_id'];
        if ($_POST['send_registration'] === "Unsubscribe") {
            delete_post_meta($opportunity_id, 'fields_for_' . $as_id);
            return;
        }
        $fields = get_post_meta($opportunity_id, 'fields', true);
        $fieldvalues = [];
        $n = 0;
        foreach ($fields as $field) {
            if (array_key_exists('field_' .$n, $_POST)) {
                $value = $_POST['field_' .$n];
                $fieldvalues[$field[0]] = $value;
            }
            $n++;
        }
        update_post_meta($opportunity_id, 'fields_for_' . $as_id, $fieldvalues);
    }

    public function list_people($atts, $content = null) {
        $me = wp_get_current_user();
        if ($me->ID === 0) return 'Sorry you must be logged in to see who has subscribed.';
 
        if (array_key_exists('id', $atts)) {
            $opportunity_id = $atts['id'];
            if (get_post_status($opportunity_id) == false) {
                return 'Failed to find opportunity with ID of '.$opportunity_id;
            }
        } else {
            return 'Competion not specified in call to short code.';
        }
        $fields = get_post_meta($opportunity_id, 'fields', true);
        $html = "";
        $html .= '<table>';
        $html .= '<tr><th>Name</th>';
        foreach ($fields as $field) {
            $html .= '<th>' . $field[0] . '</th>';
        }
        $html .= '</tr>';

        $fieldvalues = get_post_meta($opportunity_id);
        foreach ($fieldvalues as $key => $fieldvalue) {
            if (preg_match('/^fields_for_(.*)$/',$key,$matches) == 1) {
                $person_id = $matches[1];
                $user = get_user_by ('ID', $person_id);
                $name = $user->get('first_name') . ' ' . $user->get('last_name') . esc_html(' <') . $user->get('user_email') . esc_html('>'); 
                $values = unserialize($fieldvalue[0]); 
                $html .= '<tr><td>' . $name . '</td>';
                foreach ($fields as $field) {
                    if ($field[1] == "Text") {
                        $html .= '<td>' . $values[$field[0]] . '</td>';
                    } else if ($field[1] == "Checkbox") {
                       $html .= '<td>' . (isset($values[$field[0]]) ? '&check;' : '&cross;') . '</td>';  
                    }
                }
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        return $html;
    }
}
