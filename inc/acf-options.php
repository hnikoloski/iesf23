<?php
// Path: inc\acf-options.php
/**
 * ACF Options
 */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title'    => 'Theme General Settings',
        'menu_title'    => 'Theme Settings',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false
    ));
    // Footer
    acf_add_options_sub_page(array(
        'page_title'    => 'Footer',
        'menu_title'    => 'Footer',
        'parent_slug'   => 'theme-general-settings',
    ));
    // Socials
    acf_add_options_sub_page(array(
        'page_title'    => 'Socials',
        'menu_title'    => 'Socials',
        'parent_slug'   => 'theme-general-settings',
    ));

    // Api Debug
    acf_add_options_sub_page(array(
        'page_title'    => 'Api Debug',
        'menu_title'    => 'Api Debug',
        'parent_slug'   => 'theme-general-settings',
    ));

    // Options for the Tournaments Archive Page
    acf_add_options_sub_page(array(
        'page_title'    => 'Tournaments Archive',
        'menu_title'    => 'Tournaments Archive',
        'parent_slug'   => 'edit.php?post_type=tournaments',
    ));
}
