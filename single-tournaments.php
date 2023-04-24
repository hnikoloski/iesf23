<?php
get_header();
get_template_part('template-parts/inner-hero');
$scheduled_start_time = get_field('scheduled_start_time', get_the_ID());
$start_time = get_field('start_time', get_the_ID());
$end_time = get_field('end_time', get_the_ID());
$scheduled_playoffs_start_time = get_field('scheduled_playoffs_start_time', get_the_ID());
$total_slot_count = get_field('total_slot_count', get_the_ID());
$filled_slot_count = get_field('filled_slot_count', get_the_ID());
$maximum_lineup_player_count = get_field('maximum_lineup_player_count', get_the_ID());
$maximum_lineup_benchwarmer_count = get_field('maximum_lineup_benchwarmer_count', get_the_ID());
$maximum_lineup_coach_count = get_field('maximum_lineup_coach_count', get_the_ID());
$team_matchmaking_enabled = get_field('team_matchmaking_enabled', get_the_ID());
$lineup_ids = get_field('lineup_ids', get_the_ID());
$format = get_field('format', get_the_ID());
$logo_s = get_field('logo_s', get_the_ID());
$logo_m = get_field('logo_m', get_the_ID());
$logo_l = get_field('logo_l', get_the_ID());
$logo_xl = get_field('logo_xl', get_the_ID());
$thumbnail_s = get_field('thumbnail_s', get_the_ID());
$thumbnail_m = get_field('thumbnail_m', get_the_ID());
$thumbnail_l = get_field('thumbnail_l', get_the_ID());
$banner_s = get_field('banner_s', get_the_ID());
$banner_m = get_field('banner_m', get_the_ID());
$banner_l = get_field('banner_l', get_the_ID());
$current_page_id = get_the_ID();
?>
<div class="grid">
    <div class="content">
        <div class="tabs">
            <div class="tabs-tab-header">
                <div class="tabs-tab-header-item active" data-tab="1">
                    <p>Description</p>
                </div>
                <div class="tabs-tab-header-item" data-tab="2">
                    <p>Lineup</p>
                </div>
            </div>
            <div class="tabs-tab-content">
                <div class="tabs-tab-content-item active" data-tab="1">
                    <?php
                    echo get_field('description');
                    ?>
                </div>
                <div class="tabs-tab-content-item" data-tab="2">
                    <?php
                    // post_type=teams
                    $args = array(
                        'post_type' => 'teams',
                        'posts_per_page' => -1,
                        // relationship field is the same as the current tournament id
                        'meta_query' => array(
                            array(
                                'key' => 'played_in',
                                'value' =>  $current_page_id,
                                'compare' => 'LIKE'
                            )
                        )
                    );
                    $query = new WP_Query($args);

                    if ($query->have_posts()) : ?>
                        <ul class="lineup-wrapper">
                            <?php while ($query->have_posts()) : $query->the_post(); ?>
                                <li>
                                    <?php the_title(); ?><a href="#!" class="accordion-expand"><span class="material-symbols-outlined">
                                            expand_more
                                        </span></a>
                                    <div class="lineup-members">
                                        <?php
                                        // querry based on the relationship field
                                        $args = array(
                                            'post_type' => 'players',
                                            'posts_per_page' => -1,
                                            'meta_query' => array(
                                                array(
                                                    'key' => 'teams',
                                                    'value' =>  get_the_ID(),
                                                    'compare' => 'LIKE'
                                                )
                                            )
                                        );
                                        $get_players = get_posts($args);

                                        if ($get_players) : ?>
                                            <ul class="lineup-members-list">
                                                <?php foreach ($get_players as $player) : ?>
                                                    <li>
                                                        <div class="img-wrapper">
                                                            <?php
                                                            $imgUrl = get_field('picture', $player->ID) ? get_field('picture', $player->ID) : get_template_directory_uri() . '/assets/images/placeholder_person.png';
                                                            ?>
                                                            <img src="<?php echo $imgUrl; ?>" alt="<?php echo get_the_title($player->ID); ?>">
                                                        </div>

                                                        <p>
                                                            <?php echo get_the_title($player->ID); ?>
                                                            <?php if (get_field('isCaptain', $player->ID)) : ?>
                                                                <span class="material-symbols-outlined captain">
                                                                    star
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if (get_field('isSub', $player->ID)) : ?>
                                                                <span class="material-symbols-outlined sub">
                                                                    swap_horiz
                                                                </span>
                                                            <?php endif; ?>
                                                        </p>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif;
                                        wp_reset_postdata();


                                        ?>

                                    </div>

                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php endif;
                    wp_reset_postdata();

                    ?>
                </div>
            </div>

        </div>
    </div>
    <div class=" sidebar">

        <div class="tournament-info-card" style="--logo-img:url(<?php echo $logo_m; ?>);">
            <ul>
                <?php if ($scheduled_start_time) : ?>
                    <li>
                        <p class="label">Start Time:</p>
                        <p class="info"><?php echo date('F j, Y', strtotime($scheduled_start_time)); ?> - <?php echo date('g:i A', strtotime($scheduled_start_time)); ?></p>
                    </li>
                <?php endif; ?>
                <?php if ($scheduled_playoffs_start_time) : ?>
                    <li>
                        <p class="label">Playoffs Start Time:</p>
                        <p class="info"><?php echo date('F j, Y', strtotime($scheduled_playoffs_start_time)); ?> - <?php echo date('g:i A', strtotime($scheduled_playoffs_start_time)); ?></p>
                    </li>
                <?php endif; ?>
                <?php if ($end_time) : ?>
                    <li>
                        <p class="label">End Time:</p>
                        <p class="info"><?php echo date('F j, Y', strtotime($end_time)); ?> - <?php echo date('g:i A', strtotime($end_time)); ?></p>
                    </li>
                <?php endif; ?>

                <?php if ($total_slot_count) : ?>
                    <li>
                        <p class="label">Slots:</p>
                        <p class="info"><?php echo $total_slot_count; ?> / <?php echo $filled_slot_count ? $filled_slot_count : 0; ?></p>
                    </li>
                <?php endif; ?>
                <?php if ($format) : ?>
                    <li>
                        <p class="label">Format:</p>
                        <p class="info"><?php echo $format['label']; ?></p>
                    </li>
                <?php endif; ?>
            </ul>

        </div>
    </div>
</div>

<?php
get_footer();
