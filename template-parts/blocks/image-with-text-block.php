<?php

/**
 * Image with Text Block Template.
 */

$anchor = '';
if (!empty($block['anchor'])) {
    $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'iesf-blocks iesf-img-with-text-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}

$image = get_field('image_with_text_block_image');
$headline = get_field('image_with_text_block_headline');
$description = get_field('image_with_text_block_description');
$cta = get_field('image_with_text_block_cta');

$image_position = get_field('image_with_text_block_image_position');

?>

<div <?= $anchor; ?> class="<?= esc_attr($class_name); ?>" style="<?php echo $styles; ?>">
    <div class="wrapper">
        <div class="col col-image">
            <?php if ($image) : ?>
                <div class="img-wrapper">
                    <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" />
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>