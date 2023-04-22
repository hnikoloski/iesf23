<?php

/**
 * Hero Block Template.
 */

$anchor = '';
if (!empty($block['anchor'])) {
    $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'iesf-blocks iesf-hero-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

// Load values and assign defaults.
$background_image = get_field('hero_block_background_image')['url'];
$background_image_overlay = get_field('hero_block_background_image_overlay');
$background_color = get_field('hero_block_background_color');
$background_video = get_field('hero_block_background_video');


$styles = '--bgImg: url(' . $background_image . ');' .
    '--bgImgOverlay: ' . $background_image_overlay . ';' .
    '--bgColor: ' . $background_color . ';';

$title = get_field('hero_block_title');
$sub_title = get_field('hero_block_subtitle');
$cta = get_field('hero_block_cta');
?>

<div <?= $anchor; ?> class="<?= esc_attr($class_name); ?>" style="<?php echo $styles; ?>">
    <?php if ($background_video) : ?>
        <video autoplay muted loop id="hero_video" poster="<?php echo $background_image; ?>">
            <source src="<?php echo $background_video; ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="wrapper">
        <?php if ($title) : ?>
            <h1 class="title"><?php echo $title; ?></h1>
        <?php endif; ?>
        <?php if ($sub_title) : ?>
            <div class="sub-title"><?php echo $sub_title; ?></div>
        <?php endif; ?>
        <?php if ($cta) : ?>
            <a href="<?php echo $cta['url']; ?>" class="btn btn-dblue" target="<?php echo $cta['target']; ?>"><?php echo $cta['title']; ?></a>
        <?php endif; ?>
    </div>
</div>