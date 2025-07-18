<?php
/**
 * Plugin Name: Fastest Age Verification
 * Description: A non-blocking, fastest age verification popup for WordPress with customizable logo, button colors, and user-defined minimum age.
 * Version: 1.4.1
 * Author: Bulbul Islam
 * Author URI: https://wpdevs.online/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue admin scripts
function age_verification_admin_enqueue_scripts($hook) {
    if ($hook !== 'settings_page_age-verification-settings') {
        return;
    }

    // Ensure WordPress media uploader is available
    wp_enqueue_media();

    // Load the admin script from the new location
    wp_enqueue_script(
        'age-verification-admin-script',
        plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',
        array('jquery'),
        '1.4.1',
        true
    );
}
add_action('admin_enqueue_scripts', 'age_verification_admin_enqueue_scripts');

// Enqueue frontend scripts
function age_verification_enqueue_scripts() {
    if (!is_user_logged_in() && !is_admin()) {
        wp_enqueue_script(
            'age-verification-script',
            plugin_dir_url(__FILE__) . 'assets/js/age-verification.js',
            array('jquery'),
            '1.4.1',
            true
        );

        // Enqueue the CSS file (after moving to css/ folder)
        wp_enqueue_style(
            'age-verification-style',
            plugin_dir_url(__FILE__) . 'assets/css/style.css', // Correct path to the new location
            array(),
            '1.4.1'
        );

        // Retrieve settings values
        $logo_url = esc_url(get_option('age_verification_logo', ''));
        $yes_color = esc_attr(get_option('age_verification_yes_button_color', '#007bff'));
        $no_color = esc_attr(get_option('age_verification_no_button_color', '#dc3545'));
        $min_age = absint(get_option('age_verification_min_age', 21)); // Default age is 21

        wp_localize_script('age-verification-script', 'ageVerificationData', array(
            'logoUrl' => $logo_url,
            'yesButtonColor' => $yes_color,
            'noButtonColor' => $no_color,
            'minAge' => $min_age,
        ));
    }
}
add_action('wp_enqueue_scripts', 'age_verification_enqueue_scripts');

// Add settings page
function age_verification_add_admin_menu() {
    add_options_page(
        'Age Verification Settings',
        'Age Verification',
        'manage_options',
        'age-verification-settings',
        'age_verification_settings_page'
    );
}
add_action('admin_menu', 'age_verification_add_admin_menu');

// Settings page content
function age_verification_settings_page() {
    ?>
    <div class="wrap">
        <h2>Age Verification Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('age_verification_settings_group');
            do_settings_sections('age-verification-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function age_verification_register_settings() {
    register_setting('age_verification_settings_group', 'age_verification_logo', 'age_verification_sanitize_text');
    register_setting('age_verification_settings_group', 'age_verification_yes_button_color', 'age_verification_sanitize_color');
    register_setting('age_verification_settings_group', 'age_verification_no_button_color', 'age_verification_sanitize_color');
    register_setting('age_verification_settings_group', 'age_verification_min_age', 'age_verification_sanitize_integer');

    add_settings_section('age_verification_logo_section', 'Logo Settings', 'age_verification_logo_section_callback', 'age-verification-settings');
    add_settings_field('age_verification_logo', 'Logo Upload', 'age_verification_logo_field_callback', 'age-verification-settings', 'age_verification_logo_section');

    add_settings_section('age_verification_button_color_section', 'Button Color Settings', 'age_verification_button_color_section_callback', 'age-verification-settings');
    add_settings_field('age_verification_yes_button_color', 'Yes Button Color', 'age_verification_yes_button_color_field_callback', 'age-verification-settings', 'age_verification_button_color_section');
    add_settings_field('age_verification_no_button_color', 'No Button Color', 'age_verification_no_button_color_field_callback', 'age-verification-settings', 'age_verification_button_color_section');

    add_settings_section('age_verification_age_section', 'Minimum Age Requirement', 'age_verification_age_section_callback', 'age-verification-settings');
    add_settings_field('age_verification_min_age', 'Minimum Age', 'age_verification_min_age_field_callback', 'age-verification-settings', 'age_verification_age_section');
}
add_action('admin_init', 'age_verification_register_settings');

function age_verification_logo_section_callback() {
    echo '<p>Upload a logo to display in the age verification popup.</p>';
}

function age_verification_logo_field_callback() {
    $logo_url = esc_attr(get_option('age_verification_logo'));
    ?>
    <input type="text" name="age_verification_logo" id="age_verification_logo" value="<?php echo esc_attr($logo_url); ?>" style="width: 300px;" />
    <input type="button" class="button button-secondary" id="age_verification_logo_upload_button" value="Upload Logo" />
    <?php
}

function age_verification_button_color_section_callback() {
    echo '<p>Customize the colors of the "Yes" and "No" buttons in the age verification popup.</p>';
}

function age_verification_yes_button_color_field_callback() {
    $color = esc_attr(get_option('age_verification_yes_button_color', '#007bff')); // Default color
    echo '<input type="color" name="age_verification_yes_button_color" value="' . esc_attr($color) . '">';
}

function age_verification_no_button_color_field_callback() {
    $color = esc_attr(get_option('age_verification_no_button_color', '#dc3545')); // Default color
    echo '<input type="color" name="age_verification_no_button_color" value="' . esc_attr($color) . '">';
}

function age_verification_age_section_callback() {
    echo '<p>Set the minimum age required for site access.</p>';
}

function age_verification_min_age_field_callback() {
    $min_age = esc_attr(get_option('age_verification_min_age', 21)); // Default age is 21
    echo '<input type="number" name="age_verification_min_age" value="' . esc_attr($min_age) . '" min="1" max="100">';
}

// Sanitization functions
function age_verification_sanitize_text($input) {
    return sanitize_text_field($input);
}

function age_verification_sanitize_color($input) {
    if (preg_match('/^#[a-f0-9]{6}$/i', $input)) {
        return sanitize_text_field($input);
    }
    return '';
}

function age_verification_sanitize_integer($input) {
    return absint($input);
}