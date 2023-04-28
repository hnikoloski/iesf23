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
                <?php if (get_field('groups_repeater', $current_page_id)) : ?>
                    <div class="tabs-tab-header-item active">
                        <p>Groups</p>
                    </div>
                <?php endif; ?>
                <?php if (get_field('brackets_repeater')) : ?>
                    <div class="tabs-tab-header-item ">
                        <p>Brackets</p>
                    </div>
                <?php endif; ?>
                <?php if (get_field('description')) : ?>
                    <div class="tabs-tab-header-item ">
                        <p>Description</p>
                    </div>
                <?php endif; ?>
                <div class="tabs-tab-header-item">
                    <p>Lineup</p>
                </div>
            </div>
            <div class="tabs-tab-content">
                <?php if (get_field('groups_repeater', $current_page_id)) : ?>
                    <div class="tabs-tab-content-item">
                        <?php if (have_rows('groups_repeater')) : ?>
                            <div class="group group-wrapper">
                                <?php while (have_rows('groups_repeater')) : the_row(); ?>
                                    <div class="group group-single">
                                        <h4><?php the_sub_field('group_name'); ?></h4>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Team</th>
                                                    <th>MP</th>
                                                </tr>
                                            </thead>
                                            <?php if (have_rows('group_teams')) : ?>
                                                <tbody>
                                                    <?php while (have_rows('group_teams')) : the_row(); ?>
                                                        <tr>
                                                            <td><?php echo get_row_index(); ?></td>
                                                            <td><?php the_sub_field('team_name'); ?></td>
                                                            <td><?php the_sub_field('gamesPlayedCount'); ?></td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            <?php endif; ?>
                                        </table>

                                    </div>
                                <?php
                                endwhile; ?>
                            </div>
                        <?php
                        endif; ?>
                    </div>
                <?php endif; ?>
                <?php if (get_field('brackets_repeater', $current_page_id)) : ?>
                    <div class="tabs-tab-content-item">
                        <?php if (have_rows('brackets_repeater')) : ?>

                            <div class="brackets brackets-row">
                                <div class="theme theme-dark">
                                    <div class="bracket">
                                        <?php
                                        while (have_rows('brackets_repeater')) :
                                            the_row();
                                            $rowIndex = get_row_index(); ?>
                                            <div class="column round-<?php print_r($rowIndex); ?>">
                                                <?php
                                                while (have_rows('match_games')) : the_row();

                                                    $single_team_1_title = get_sub_field('single_team_1'); // relationship field
                                                    if ($single_team_1_title) {
                                                        foreach ($single_team_1_title as $post) :
                                                            setup_postdata($post);
                                                            $single_team_1_title = get_the_title($post->ID);
                                                            $post_1_id = $post->ID;
                                                        endforeach;
                                                    } else {
                                                        $single_team_1_title = 'N/A';
                                                        $post_1_id = '';
                                                    }
                                                    wp_reset_postdata();

                                                    $single_team_2_title = get_sub_field('single_team_2'); // relationship field
                                                    if ($single_team_2_title) {
                                                        foreach ($single_team_2_title as $post) :
                                                            setup_postdata($post);
                                                            $single_team_2_title = get_the_title($post->ID);
                                                            $post_2_id = $post->ID;
                                                        endforeach;
                                                    } else {
                                                        $single_team_2_title = 'N/A';
                                                        $post_2_id = '';
                                                    }
                                                    wp_reset_postdata();

                                                    $team_score_1 = get_sub_field('team_score_1');
                                                    $team_score_2 = get_sub_field('team_score_2');
                                                    $single_team_1_logo = get_field('logo_s', $post_1_id) ? get_field('logo_s', $post_1_id) : get_template_directory_uri() . '/assets/images/placeholder_team.png';
                                                    $single_team_2_logo = get_field('logo_s', $post_2_id) ? get_field('logo_s', $post_2_id) : get_template_directory_uri() . '/assets/images/placeholder_team.png';
                                                    $winnerClass = '';
                                                    if ($team_score_1 > $team_score_2) {
                                                        $winnerClass = 'winner-top';
                                                    } else {
                                                        $winnerClass = 'winner-bottom';
                                                    }
                                                ?>

                                                    <?php
                                                    ?>
                                                    <div class="match <?php echo $winnerClass; ?>">
                                                        <div class="match-top team">
                                                            <span class="image">
                                                                <img src="<?php echo $single_team_1_logo; ?>" alt="<?php echo $single_team_1_title; ?>" class="full-size-img full-size-img-contain">
                                                            </span>
                                                            <span class="name">
                                                                <?php echo $single_team_1_title; ?>
                                                            </span>
                                                            <span class="score">
                                                                <?php echo $team_score_1; ?>
                                                            </span>
                                                        </div>
                                                        <div class="match-bottom team">
                                                            <span class="image">
                                                                <img src="<?php echo $single_team_2_logo; ?>" alt="<?php echo $single_team_2_title; ?>" class="full-size-img full-size-img-contain">
                                                            </span>
                                                            <span class="name">
                                                                <?php echo $single_team_2_title; ?>
                                                            </span>
                                                            <span class="score">
                                                                <?php echo $team_score_2; ?>
                                                            </span>
                                                        </div>
                                                        <div class="match-lines">
                                                            <div class="line one"></div>
                                                            <div class="line two"></div>
                                                        </div>
                                                        <div class="match-lines alt">
                                                            <div class="line one"></div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php endwhile; ?>

                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (get_field('description')) : ?>
                    <div class="tabs-tab-content-item">
                        <?php
                        echo get_field('description');
                        ?>
                    </div>
                <?php endif; ?>
                <div class="tabs-tab-content-item">
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
                                    <?php
                                    $imgUrl = get_field('logo_m') ? get_field('logo_m') : get_template_directory_uri() . '/assets/images/placeholder_team.png';

                                    ?>
                                    <div class="wrap">
                                        <div class="img-wrapper">
                                            <img src="<?php echo $imgUrl; ?>" alt="<?php the_title(); ?>" class="full-size-img full-size-img-cover">
                                        </div>
                                        <?php the_title(); ?>
                                    </div>
                                    <a href="#!" class="accordion-expand"><span class="material-symbols-outlined">
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
                                                            <img src="<?php echo $imgUrl; ?>" alt="<?php echo get_the_title($player->ID); ?>" class="full-size-img full-size-img-cover">
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
                        <p class="info"><?php echo $filled_slot_count ? $filled_slot_count : 0; ?> / <?php echo $total_slot_count; ?></p>
                    </li>
                <?php endif; ?>
                <?php if ($format) : ?>
                    <li>
                        <p class="label">Format:</p>
                        <p class="info"><?php echo $format['label']; ?></p>
                    </li>
                <?php endif; ?>
                <?php
                // get game taxonomies
                $game_terms = get_the_terms(get_the_ID(), 'games');
                if ($game_terms) : ?>
                    <li>
                        <p class="label">Game:</p>
                        <?php foreach ($game_terms as $term) : ?>

                            <p class="info d-flex align-items-center">
                                <?php echo $term->name; ?>
                            </p>
                        <?php endforeach; ?>
                    </li>
                <?php endif; ?>

            </ul>

        </div>
    </div>
</div>

<?php
get_footer();
