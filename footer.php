</main> <!-- #main -->

<footer id="colophon" class="site-footer">
    <div class="site-info">
        <div class="logo-wrapper">
            <img src="<?= get_field('footer_logo', 'options'); ?>" alt="<?= get_bloginfo(); ?>" class="full-size-img full-size-img-contain d-block">
        </div>
        <ul class="socials">
            <?php if (get_field('facebook_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('facebook_link', 'options'); ?>" title="Facebook" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('facebook_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">Facebook</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (get_field('instagram_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('instagram_link', 'options'); ?>" title="Instagram" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('instagram_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">Instagram</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (get_field('linkedin_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('linkedin_link', 'options'); ?>" title="LinkedIn" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('linkedin_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">LinkedIn</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (get_field('twitter_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('twitter_link', 'options'); ?>" title="Twitter" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('twitter_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">Twitter</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (get_field('youtube_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('youtube_link', 'options'); ?>" title="YouTube" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('youtube_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">YouTube</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (get_field('twitch_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('twitch_link', 'options'); ?>" title="Twitch" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('twitch_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">Twitch</span>
                    </a>
                </li>
            <?php endif; ?>
            <?php if (get_field('tiktok_link', 'options')) : ?>
                <li>
                    <a href="<?php the_field('tiktok_link', 'options'); ?>" title="TikTok" target="_blank" rel="noopener noreferrer" style="--social-icon: url(<?php echo get_field('tiktok_icon', 'options'); ?>)" class="social-icon">
                        <span class="screen-reader-text">TikTok</span>
                    </a>
                </li>
            <?php endif; ?>



        </ul>
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