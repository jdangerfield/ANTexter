<?php
    /** *
    *Plugin Name: Action Texts
    *Description: Send custom text messages to groups of users on Action Network
    *Author: CodeWalker Institute
    *Version: 1.0
    **/


define(SMS_CAUCUS_URL, 'http://sms-caucus.herokuapp.com/');

    add_action("admin_menu", "texter");

    function texter() {
        add_menu_page("Action Texts", "Action Texter", "manage_options", "texter_settings_page", "texter_form");
    };

    function texter_form() {

        if (!current_user_can( 'manage_options' )) {
            wp_die( "Sorry. You don't have access to use this plugin." );
        }
        echo include('ANTHtml.php');
    };

    function add_roles_on_plugin_activation() {
        if (!is_plugin_active('advanced-custom-fields/acf.php')) {
            // Deactivate the plugin
				deactivate_plugins(__FILE__);
				
				// Throw an error in the wordpress admin console
				$error_message = __('This plugin requires the <a href="https://www.advancedcustomfields.com/">Advanced Custom Fields</a> plugin to be active!', 'advanced_custom_fields');
				die($error_message);
        } else {
            add_role( 'action_texter', 'Action Texter', array( 'read' => true, 'level_0' => true ) );
            setupACF();
        }
    }

    register_activation_hook( __FILE__, 'add_roles_on_plugin_activation' );

    function remove_roles_on_plugin_deactivation() {
        //check if role exist before removing it
        if( get_role('action_texter') ) {
            remove_role( 'action_texter' );
        }

    }

    register_deactivation_hook( __FILE__, 'remove_roles_on_plugin_deactivation' );

add_action( 'wp_ajax_send_test_text', 'send_test_text' );


//@FormParam("tasid") String twilioAccountSid, @FormParam("tat") String twilioAuthToken,
//                               @FormParam("tsid") String twilioServiceId, @FormParam("from") String from,
//                               @FormParam("body") String body, @FormParam("to") String to
function send_test_text() {
    $postData = array(
        "to" => strval($_POST['to']),
        "body" => strval($_POST['body']),
        "tasid" => get_field( 'twilio_account_sid', 'user_'. get_current_user_id()),
        "tat" => get_field( 'twilio_auth_token', 'user_'. get_current_user_id()),
        "from" => get_field( 'twilio_from_number', 'user_'. get_current_user_id()),
        "apiKey" => get_field('action_texts_api_key', 'user_'. get_current_user_id())
    );
    
    $response = wp_remote_post(SMS_CAUCUS_URL . 'send-test-text', array( "body" => $postData));

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    }

    echo $response['body'];

    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_send_bulk_text', 'send_bulk_text' );
//@FormParam("tasid") String twilioAccountSid, @FormParam("tat") String twilioAuthToken,
//                           @FormParam("tsid") String twilioServiceId, @FormParam("from") String from,
//                           @FormParam("anak") String actionNetworkApiKey, @FormParam("antid") String actionNetworkTagId,
//                           @FormParam("body") String body
function send_bulk_text() {
    $postData = array(
        "body" => strval($_POST['body']),
        "antid" => strval($_POST["tags"]),
        "anak" => get_field( 'action_network_api_key', 'user_'. get_current_user_id()),
        "tasid" => get_field( 'twilio_account_sid', 'user_'. get_current_user_id()),
        "tat" => get_field( 'twilio_auth_token', 'user_'. get_current_user_id()),
        "from" => get_field( 'twilio_from_number', 'user_'. get_current_user_id()),
        "apiKey" => get_field('action_texts_api_key', 'user_'. get_current_user_id())
    );

    $response = wp_remote_post(SMS_CAUCUS_URL . 'bulk-send', array( "body" => $postData));

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    }

//    if ($response['body']) {
//        $json = json_decode($response['body'], true);
//        add_user_meta(get_current_user_id(), "actionTextPID", $json['id'], false);
//    }

    echo $response['body'];

    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_check_progress', 'check_progress' );
function check_progress() {
    $response = wp_remote_get(SMS_CAUCUS_URL . 'check-stats?pid=' . $_GET['pid']);

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        echo "Something went wrong: $error_message";
    }

    echo $response['body'];

    wp_die(); // this is required to terminate immediately and return a proper response
}

function setupACF() {
    if(function_exists("register_field_group"))
    {
        register_field_group(array (
            'id' => 'acf_action-texter-fields',
            'title' => 'Action Texter Fields',
            'fields' => array (
                array (
                    'key' => 'field_5a9dab6243415',
                    'label' => 'Action Network API Key',
                    'name' => 'action_network_api_key',
                    'type' => 'text',
                    'instructions' => 'This is the API key you should have received from Action Network. If you do not have one you can request one.',
                    'required' => 1,
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'formatting' => 'html',
                    'maxlength' => '',
                ),
                array (
                    'key' => 'field_5a9dabe843416',
                    'label' => 'Twilio Account SID',
                    'name' => 'twilio_account_sid',
                    'type' => 'text',
                    'instructions' => 'This is your Twilio Account SID you should be able to retrieve this value from the Twilio Dashboard.',
                    'required' => 1,
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'formatting' => 'html',
                    'maxlength' => '',
                ),
                array (
                    'key' => 'field_5a9dac3d43417',
                    'label' => 'Twilio Auth Token',
                    'name' => 'twilio_auth_token',
                    'type' => 'text',
                    'instructions' => 'This is your Twilio Auth Token, you should be able to retrieve this value from the Twilio Dashboard.',
                    'required' => 1,
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'formatting' => 'html',
                    'maxlength' => '',
                ),
                array (
                    'key' => 'field_5a9dac6043418',
                    'label' => 'Twilio From Number',
                    'name' => 'twilio_from_number',
                    'type' => 'text',
                    'instructions' => 'This is the number you provisioned in Twilio',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'formatting' => 'html',
                    'maxlength' => '',
                ),
                array (
                    'key' => 'field_5a9dac7e43419',
                    'label' => 'Twilio Service ID',
                    'name' => 'twilio_service_id',
                    'type' => 'text',
                    'instructions' => 'If you set up a service in Twilio you can set the service Id here instead of a From Number.',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'formatting' => 'html',
                    'maxlength' => '',
                ),
                array (
                    'key' => 'field_5a9dce18fb22d',
                    'label' => 'Action Texts API Key',
                    'name' => 'action_texts_api_key',
                    'type' => 'text',
                    'instructions' => 'This is your API issued by us!',
                    'required' => 1,
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'formatting' => 'html',
                    'maxlength' => '',
                ),
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'ef_user',
                        'operator' => '==',
                        'value' => 'administrator',
                        'order_no' => 0,
                        'group_no' => 0,
                    ),
                ),
                array (
                    array (
                        'param' => 'ef_user',
                        'operator' => '==',
                        'value' => 'action_texter',
                        'order_no' => 0,
                        'group_no' => 1,
                    ),
                ),
            ),
            'options' => array (
                'position' => 'normal',
                'layout' => 'no_box',
                'hide_on_screen' => array (
                ),
            ),
            'menu_order' => 0,
        ));
    }
}
?>