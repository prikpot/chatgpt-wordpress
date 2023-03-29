<?php
/**
 * Plugin Name: ChatGPT Client
 * Description: A simple WordPress plugin to interact with ChatGPT via the OpenAI API.
 * Version: 1.0
 * Author: Your Name
 * Author URI: Your Website
 */

function chatgpt_client_enqueue_scripts() {
    wp_enqueue_style('chatgpt_client_style', plugin_dir_url(__FILE__) . 'chatgpt-client.css');
    wp_enqueue_script('chatgpt_client_script', plugin_dir_url(__FILE__) . 'chatgpt-client.js', array('jquery'), false, true);
    wp_localize_script('chatgpt_client_script', 'chatgpt_client_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'chatgpt_client_enqueue_scripts');

function chatgpt_client_shortcode() {
    ob_start();
    ?>
    <form id="chatgpt-client-form">
        <label for="question">Your Question:</label>
        <input type="text" id="question" name="question" required />
        <button type="submit">Ask ChatGPT</button>
    </form>
    <div id="chatgpt-response-container"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('chatgpt_client', 'chatgpt_client_shortcode');

function chatgpt_send_question() {
    $api_key = get_option('chatgpt_client_api_key');
    $question = sanitize_text_field($_POST['question']);

    $url = 'https://api.openai.com/v1/engines/text-davinci-002/completions';
    $headers = array(
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $api_key
    );
    $body = array(
        'prompt' => $question,
        'max_tokens' => 50,
        'n' => 1,
        'stop' => null,
        'temperature' => 0.5
    );

    $response = wp_remote_post($url, array(
        'headers' => $headers,
        'body' => json_encode($body)
    ));

    if (is_wp_error($response)) {
        echo 'An error occurred. Please try again.';
    } else {
        $response_data = json_decode(wp_remote_retrieve_body($response), true);
        $answer = $response_data['choices'][0]['text'];
        echo trim($answer);
    }

    wp_die();
}
add_action('wp_ajax_chatgpt_send_question', 'chatgpt_send_question');
add_action('wp_ajax_nopriv_chatgpt_send_question', 'chatgpt_send_question');

function chatgpt_client_create_settings_page() {
    add_options_page(
        'ChatGPT Client Settings',
        'ChatGPT Client',
        'manage_options',
        'chatgpt-client-settings',
        'chatgpt_client_render_settings_page'
    );
}
add_action('admin_menu', 'chatgpt_client_create_settings_page');

function chatgpt_client_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>ChatGPT Client Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('chatgpt_client_options');
            do_settings_sections('chatgpt-client-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function chatgpt_client_settings_init() {
    register_setting('chatgpt_client_options', 'chatgpt_client_api_key');

    add_settings_section(
        'chatgpt_client_api_key_section',
        'API Key',
        null,
        'chatgpt-client-settings'
    );

    add_settings_field(
        'chatgpt_client_api_key',
        'OpenAI API Key',
        'chatgpt_client_api_key_render',
        'chatgpt-client-settings',
        'chatgpt_client_api_key_section'
    );
}
add_action('admin_init', 'chatgpt_client_settings_init');

function chatgpt_client_api_key_render() {
    $api_key = get_option('chatgpt_client_api_key');
    ?>
    <input type="text" name="chatgpt_client_api_key" value="<?php echo esc_attr($api_key); ?>" size="50" />
    <?php
}

