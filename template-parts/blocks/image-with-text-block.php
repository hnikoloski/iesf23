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

$styles = '';
if ($image_position == 'right') {
    $styles .= '--image-order: 2;';
    $styles .= '--text-order: 1;';
} else {
    $styles .= '--image-order: 1;';
    $styles .= '--text-order: 2;';
}
?>

<div <?= $anchor; ?> class="<?= esc_attr($class_name); ?>" style="<?php echo $styles; ?>">
    <div class="wrapper">
        <div class="col col-image">
            <?php if ($image) : ?>
                <div class="img-wrapper">
                    <img src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt']; ?>" class="full-size-img full-size-img-cover" />
                </div>
            <?php endif; ?>
        </div>

        <div class="col col-text">
            <?php if ($headline) : ?>
                <h3 class="headline"><?php echo $headline; ?></h3>
            <?php endif; ?>

            <?php if ($description) : ?>
                <div class="description"><?php echo $description; ?></div>
            <?php endif; ?>

            <?php if ($cta) : ?>
                <a href="<?php echo $cta['url']; ?>" class="btn btn-clear btn-clear-arrow"><?php echo $cta['title']; ?> <span class="material-symbols-outlined">
                        arrow_right_alt
                    </span></a>
            <?php endif; ?>

        </div>
    </div>
</div>