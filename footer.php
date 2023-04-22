</main> <!-- #main -->

<footer id="colophon" class="site-footer">
    <div class="site-info">
        <div class="logo-wrapper">
            <img src="<?= get_field('footer_logo', 'options'); ?>" alt="<?= get_bloginfo(); ?>" class="full-size-img full-size-img-contain d-block">
        </div>
    </div><!-- .site-info -->
    <div class="copy-bar">
        <?php
        $websiteDomain = $_SERVER['HTTP_HOST'];

        ?>
        <p>Copyright© <span class="current-year"></span> <?php echo $websiteDomain; ?> - All rights reserved.</p>
    </div> <!-- .copy-bar -->
</footer><!-- #colophon -->

<?php wp_footer(); ?>

</body>

</html>