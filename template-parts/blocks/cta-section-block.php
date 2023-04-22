<?php

/**
 * Cta Block Template.
 */

$anchor = '';
if (!empty($block['anchor'])) {
    $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'iesf-blocks iesf-cta-section-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

$background_color = get_field('cta_section_block_background_color');
$background_image = get_field('cta_section_block_background_image')['url'];
$background_image_overlay = get_field('cta_section_block_background_image_overlay');
$text_color = get_field('cta_section_block_text_color') ? get_field('cta_section_block_text_color') : '#fff';

$headline = get_field('cta_section_block_headline');
$description = get_field('cta_section_block_description');
$cta = get_field('cta_section_block_cta');
$styles = '';

if ($background_image) {
    $styles .= '--bgImg: url(' . $background_image . ');';
}

if ($background_image_overlay) {
    $styles .= '--bgImgOverlay: ' . $background_image_overlay . ';';
}

if ($background_color) {
    $styles .= '--bgColor: ' . $background_color . ';';
}

if ($text_color) {
    $styles .= '--textColor: ' . $text_color . ';';
}


?>

<div <?= $anchor; ?> class="<?= esc_attr($class_name); ?>" style="<?php echo $styles; ?>">
    <div class="wrapper">
        <div class="content">

            <?php if ($headline) : ?>
                <h2 class="headline"><?php echo $headline; ?></h2>
            <?php endif; ?>
            <?php if ($description) : ?>
                <div class="description"><?php echo $description; ?></div>
            <?php endif; ?>
            <?php if ($cta) : ?>
                <a href="<?php echo $cta['url']; ?>" class="btn btn-clear btn-clear-arrow" target="<?php echo $cta['target']; ?>"><?php echo $cta['title']; ?> <span class="material-symbols-outlined">
                        arrow_right_alt
                    </span></a>
            <?php endif; ?>
        </div>
    </div>
</div>