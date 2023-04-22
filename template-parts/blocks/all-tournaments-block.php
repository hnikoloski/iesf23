<?php

/**
 * All tournaments Block Template.
 */

$anchor = '';
if (!empty($block['anchor'])) {
    $anchor = 'id="' . esc_attr($block['anchor']) . '" ';
}

// Create class attribute allowing for custom "className" and "align" values.
$class_name = 'iesf-blocks iesf-all-tournaments-block';
if (!empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $class_name .= ' align' . $block['align'];
}


?>

<div <?= $anchor; ?> class="<?= esc_attr($class_name); ?>">
    <header class="tournament-filter">
        <form class="filter-vals d-none">
            <input type="hidden" name="filter_games" value="*">
            <input type="hidden" name="filter_region" value="*">
            <input type="hidden" name="filter_country" value="*">
            <input type="hidden" name="show_past_tournaments" value="<?php echo get_field('show_past_tournaments') ? 'true' : 'false'; ?>">
        </form>
        <?php
        // taxonomy=region&post_type=tournaments
        // Get all regions
        $regions = get_terms(array(
            'taxonomy' => 'region',
            'hide_empty' => false,
        ));
        ?>
        <select class="region-filter select-filter" autocomplete="off" placeholder="Select a region" name="filter_region">
            <option value="*">All Continents</option>
            <?php foreach ($regions as $region) : ?>
                <option value="<?php echo $region->slug; ?>"><?php echo $region->name; ?></option>
            <?php endforeach; ?>
        </select>
        <?php
        // Get all games
        $games = get_terms(array(
            'taxonomy' => 'games',
            'hide_empty' => true,
        ));
        ?>
        <select class="game-filter select-filter" autocomplete="off" placeholder="Select a game" name="filter_games">
            <option value="*">All Games</option>
            <?php foreach ($games as $game) :
                var_dump($game);
            ?>
                <option value="<?php echo $game->slug; ?>"><?php echo $game->name; ?></option>
            <?php endforeach; ?>
        </select>

        <?php
        // taxonomy=countries
        $countries = get_terms(array(
            'taxonomy' => 'countries',
            'hide_empty' => false,
        ));
        ?>
        <select class="country-filter select-filter" autocomplete="off" placeholder="Select a country" name="filter_country">
            <option value="*">All Countries</option>
            <?php foreach ($countries as $country) : ?>
                <option value="<?php echo $country->slug; ?>"><?php echo $country->name; ?></option>
            <?php endforeach; ?>
        </select>

    </header>
    <div class="tournament-results">
        <?php
        $args = array(
            'post_type' => 'tournaments',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
        );

        $tournaments = new WP_Query($args);

        if ($tournaments->have_posts()) :
            while ($tournaments->have_posts()) :
                $tournaments->the_post();
                $bannerImgS = get_field('banner_s', get_the_ID());
                $bannerImgM = get_field('banner_m', get_the_ID());
                $bannerImgL = get_field('banner_l', get_the_ID());
                $title = get_the_title();
                $theId = get_the_ID();

                $startTime = get_field('scheduled_start_time', $theId);
                // Covert to just day and month
                $startTime = date('d M', strtotime($startTime));
                $endTime = get_field('end_time', $theId);
                // Covert to just day and month
                $endTime = date('d M', strtotime($endTime));
        ?>

                <div class="single-tournament single-tournament-card">
                    <?php if ($bannerImgS) : ?>
                        <img class="single-tournament-banner" src="<?php echo $bannerImgS; ?>" srcset="<?php echo $bannerImgS; ?> 300w, <?php echo $bannerImgM; ?> 600w, <?php echo $bannerImgL; ?> 1200w" sizes="(max-width: 300px) 300px, (max-width: 600px) 600px, 1200px" alt="<?php echo $title; ?>">
                        <div class="wrap">
                            <?php if ($startTime) : ?>
                                <div class="single-tournament-time">
                                    <span class="single-tournament-time-start"><?php echo $startTime; ?></span>
                                    <?php if ($endTime) : ?>
                                        - <span class="single-tournament-time-end"><?php echo $endTime; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <h3 class="single-tournament-title"><?php echo $title; ?></h3>
                            <p class="countdown" data-time="<?php echo get_field('scheduled_start_time', $theId); ?>"><span></span></p>
                            <a href="<?php echo get_permalink(); ?>" class="btn btn-clear btn-clear-arrow">View More<span class="material-symbols-outlined">
                                    arrow_right_alt
                                </span></a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>


    </div>

</div>