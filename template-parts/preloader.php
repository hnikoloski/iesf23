    <?php
    $custom_logo_id = get_theme_mod('custom_logo');
    $logoUrl = wp_get_attachment_image_src($custom_logo_id, 'full');
    ?>
    <div id="preloader">
        <div class="img-wrapper">
            <img src="<?= $logoUrl[0]; ?>" alt="<?= get_bloginfo(); ?>" class="full-size-img full-size-img-contain d-block">
        </div>
    </div>