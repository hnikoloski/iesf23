<?php
// Check if archive page
if (is_archive()) {
    $banner = get_field('banner', 'option');
    $title = get_field('hero_title', 'option') ? get_field('hero_title', 'option') : get_the_archive_title();
} else {
    $banner = get_field('banner_s', get_the_ID());
    $title = get_the_title();
}
?>

<div class="inner-hero" style="--bg-image: url(<?php echo $banner; ?>);">
    <div class="wrapper">
        <h1><?php echo $title; ?></h1>
    </div>
</div>