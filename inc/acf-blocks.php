<?php

// Path: inc\acf-blocks.php




// Editor styles
add_action('enqueue_block_editor_assets', 'iesf_editor_styles');

function iesf_editor_styles()
{
    wp_enqueue_style('iesf-editor-styles', get_template_directory_uri() . '/dist/css/editor.css', [], 1, 'all');
}


function iesf_acf_init_block_types()
{
    if (function_exists('acf_register_block_type')) {
        // Hero Block
        acf_register_block_type(array(
            'name'              => 'hero',
            'title'             => __('Hero'),
            'description'       => __('A block to display hero.'),
            'render_template'   => 'template-parts/blocks/hero-block.php',
            'category'          => 'iesf-blocks',
            'icon'              => 'iesf',
            'keywords'          => array('hero', 'iesf'),
            'supports'          => array(
                'mode' => true,
            ),
        ));

        // All tournaments block
        acf_register_block_type(array(
            'name'              => 'all-tournaments',
            'title'             => __('All Tournaments'),
            'description'       => __('A block to display all tournaments.'),
            'render_template'   => 'template-parts/blocks/all-tournaments-block.php',
            'category'          => 'iesf-blocks',
            'icon'              => 'iesf',
            'keywords'          => array('all tournaments', 'iesf'),
            'supports'          => array(
                'mode' => true,
            ),
            // Enqueue blocks assets just once for all blocks
            'enqueue_assets' => function () {
                wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], 1, true);
            },
        ));

        // Cta Block
        acf_register_block_type(array(
            'name'              => 'cta-block',
            'title'             => __('CTA Block'),
            'description'       => __('A block to display a cta section.'),
            'render_template'   => 'template-parts/blocks/cta-section-block.php',
            'category'          => 'iesf-blocks',
            'icon'              => 'iesf',
            'keywords'          => array('cta', 'iesf'),
            'supports'          => array(
                'mode' => true,
            ),
        ));

        // Image with text block
        acf_register_block_type(array(
            'name'              => 'image-with-text',
            'title'             => __('Image with text'),
            'description'       => __('A block to display an image with text.'),
            'render_template'   => 'template-parts/blocks/image-with-text-block.php',
            'category'          => 'iesf-blocks',
            'icon'              => 'iesf',
            'keywords'          => array('image with text', 'iesf'),
            'supports'          => array(
                'mode' => true,
            ),
        ));
    }
}

add_action('acf/init', 'iesf_acf_init_block_types');
