<?php

// Create an admin page called 'CM Options'
function cm_options_page()
{
    add_menu_page(
        'CM Settings',
        'CM Settings',
        'manage_options',
        'cm-settings',
        'cm_admin_page_callback',
        'dashicons-cloud',
        100
    );
}
add_action('admin_menu', 'cm_options_page');


function cm_admin_page_callback()
{
?>
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="wrap">

        <form action="options.php" method="post">
            <?php
            settings_fields('cm_options_group');
            do_settings_sections('cm-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

// Register the settings
function cm_register_settings()
{
    register_setting('cm_options_group', 'cm_options', 'cm_options_sanitize');

    add_settings_section('cm_settings_section', 'CM Settings', 'cm_settings_section_callback', 'cm-settings');

    add_settings_field('cm_api_password', 'Api Password', 'cm_api_password_callback', 'cm-settings', 'cm_settings_section');
}

add_action('admin_init', 'cm_register_settings');

function cm_settings_section_callback()
{
    echo '<h3>Your Custom Internal Api Password</h3>
    <p>Use this password to access your custom api</p>';
}

function cm_api_password_callback()
{
    $options = get_option('cm_options');
    $value = $options['cm_api_password'] ?? '';
    echo '<input type="password" name="cm_options[cm_api_password]" value="' . esc_attr($value) . '" />';
}

function cm_options_sanitize($input)
{
    $output = get_option('cm_options');

    $output['cm_api_password'] = sanitize_text_field($input['cm_api_password']);

    return $output;
}


// Create a new settings page under the CM Settings page called Get Tournament
function cm_get_tournament_page()
{
    add_submenu_page(
        'cm-settings',
        'Get Tournament',
        'Get Tournament',
        'manage_options',
        'cm-get-tournament',
        'cm_get_tournament_page_callback'
    );
}

add_action('admin_menu', 'cm_get_tournament_page');

function cm_get_tournament_page_callback()
{
?>
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="wrap tournament-admin-data">
        <?php
        $cmApiPass = '';
        // CHeck if user is admin
        if (current_user_can('manage_options')) {
            // Get the api password
            $cmApiPass = get_option('cm_options')['cm_api_password'];
        }

        ?>

        <form action="options.php" method="post" data-cm-nonce="<?php echo $cmApiPass; ?>">
            <div class="form-group">
                <p>
                    Get a tournament by ID. If the tournament exists, it will be updated. If it doesn't exist, it will be created.
                </p>
                <label for="tournament_id">Tournament ID</label>
                <input type="text" name="tournament_id" id="tournament_id" class="form-control">
                <input type="submit" value="Get Tournament" class="btn btn-primary">
                <p class="response-msg"></p>
            </div>
        </form>
    </div>
<?php
}

// Enqueue scripts and styles for the admin page
function cm_admin_scripts()
{
    wp_enqueue_style('cm-admin-style', get_template_directory_uri() . '/dist/css/admin.css', [], 1, 'all');
    wp_enqueue_script('cm-admin-script', get_template_directory_uri() . '/dist/js/tournaments.js', ['jquery'], 1, true);
}

add_action('admin_enqueue_scripts', 'cm_admin_scripts');


// Get the admin/metaboxes.php file
require_once get_template_directory() . '/inc/admin/metaboxes.php';
