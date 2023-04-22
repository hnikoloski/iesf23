<?php

// Middlewear

define('API_NAMESPACE', 'iesf/v1');
define('REFRESH_KEY', get_field('cm_refresh_key', 'option'));
define('CM_API_URL', 'https://publicapi.challengermode.com/mk1/v1/');

$keyExpired = false;
// Register API routes
add_action('rest_api_init', function () {
    // register_rest_route(API_NAMESPACE, '/auth', array(
    //     'methods' => 'POST',
    //     'callback' => 'iesf_auth',
    //     // Only allow admins to access this route
    //     'permission_callback' => function () {
    //         return current_user_can('administrator');
    //     },
    // ));

    // Single tournament
    register_rest_route(API_NAMESPACE, '/tournaments', array(
        'methods' => 'GET',
        'callback' => 'iesf_tournaments',
    ));

    // // Spaces
    // register_rest_route(API_NAMESPACE, '/spaces', array(
    //     'methods' => 'GET',
    //     'callback' => 'iesf_spaces',
    // ));

    // Search Spaces
    // register_rest_route(API_NAMESPACE, '/spaces/search', array(
    //     'methods' => 'GET',
    //     'callback' => 'iesf_spaces_search',
    // ));

    // Tournament Posts
    register_rest_route(API_NAMESPACE, '/tournaments/posts', array(
        'methods' => 'GET',
        'callback' => 'iesf_tournaments_posts',
    ));
});

function iesf_auth()
{
    global $keyExpired;
    $refreshKey = REFRESH_KEY;
    if (!$keyExpired) {

        $response = wp_remote_post(CM_API_URL . 'auth/access_keys', array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ),
            'body' => json_encode(array(
                'refreshKey' => $refreshKey,
            )),
        ));

        $body = json_decode(wp_remote_retrieve_body($response));

        if (isset($body->value)) {
            $results = array(
                'value' => $body->value,
                'expiresAt' => $body->expiresAt,
            );
            // Convert expiresAt to timestamp
            $expiresAt = date("Y-m-d H:i:s", strtotime($results['expiresAt']));
            $currentTime = date("Y-m-d H:i:s", time());

            if ($expiresAt < $currentTime) {
                $keyExpired = true;
            } else {
                $keyExpired = false;
            }
            $results = array(
                'value' => $body->value,
                'expiresAt' => $expiresAt,
                'keyExpired' => $keyExpired,
            );

            // api_access_key, api_key_expires_at, api_key_is_expired
            // update acf options
            update_field('api_access_key', $results['value'], 'option');
            update_field('api_key_expires_at', $results['expiresAt'], 'option');
            update_field('api_key_is_expired', $results['keyExpired'] ? 'true' : 'false', 'option');

            return $results;
        } else {
            return new WP_Error('token_error', 'Token error', array('status' => 500));
        }
    } else {
        $results = array(
            'value' => get_field('api_access_key', 'option'),
            'expiresAt' => get_field('api_key_expires_at', 'option'),
            'keyExpired' => get_field('api_key_is_expired', 'option') == 'true' ? true : false,
        );

        return $results;
    }
}

// Tournaments
function iesf_tournaments($request)
{
    $tournament_id = $request['tournament_id'];
    $cm_pw = $request['cm_pw'];

    if (get_option('cm_options')['cm_api_password'] != $cm_pw) {
        return new WP_Error('cm_pw_error', 'Challenger Mode API password is incorrect', array('status' => 500));
    }

    $bearer = iesf_auth()['value'];
    $remote_url = CM_API_URL . 'tournaments/' . $tournament_id;

    if (!$tournament_id) {
        return new WP_Error('tournament_id_error', 'Tournament ID is required', array('status' => 500));
    }
    $tournamentData = array();

    // Set Bearer token
    $headers = array(
        'Authorization' => 'Bearer ' . $bearer,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    );
    // Get from /tournaments/{id}
    $response = wp_remote_get($remote_url, array(
        'headers' => $headers,
    ));

    $body = json_decode(wp_remote_retrieve_body($response));



    if (isset($body)) {
        // Get the gameTitle from the body
        if ($body->id == null) {
            return new WP_Error('tournament_id_error', 'Tournament ID is incorrect', array('status' => 500));
        }
        $tournamentData = array(
            'id' => $body->id,
            'name' => $body->name,
            'gameTitle' => $body->gameTitle,
            'realm' => $body->realm,
            'countries' => $body->countries,
        );

        // Check if logo is null 
        if ($body->logoImage) {
            $tournamentData['logo'] = array(
                's' => $body->logoImage->small->url,
                'm' => $body->logoImage->medium->url,
                'l' => $body->logoImage->large->url,
                'xl' => $body->logoImage->xlarge->url,
            );
        }
        // Check if banner is null
        if ($body->bannerImage) {
            $tournamentData['banner'] = array(
                's' => $body->bannerImage->small->url,
                'm' => $body->bannerImage->medium->url,
                'l' => $body->bannerImage->large->url,
            );
        }
        // Check if thumbnailImage is null
        if ($body->thumbnailImage) {

            $tournamentData['thumbnailImage'] = array(
                's' => $body->thumbnailImage->small->url,
                'm' => $body->thumbnailImage->medium->url,
                'l' => $body->thumbnailImage->large->url,
            );
        }
        if ($body->scheduledStartTime) {
            $convertedTime = date("Y-m-d H:i:s", strtotime($body->scheduledStartTime));
            $tournamentData['scheduledStartTime'] = $convertedTime;
        }
        if ($body->startTime) {
            $convertedTime = date("Y-m-d H:i:s", strtotime($body->scheduledEndTime));
            $tournamentData['startTime'] = $convertedTime;
        }
        if ($body->endTime) {
            $convertedTime = date("Y-m-d H:i:s", strtotime($body->endTime));
            $tournamentData['endTime'] = $convertedTime;
        }


        foreach ($tournamentData['countries'] as $country) {
            $countrySlug = sanitize_title($country->code);
            $countryName = $country->code;

            // Check if country exists
            $countryTerm = term_exists($countrySlug, 'countries');
            if ($countryTerm == 0 || $countryTerm == null) {
                // Create country
                $countryTerm = wp_insert_term($countryName, 'countries', array(
                    'slug' => $countrySlug,
                ));
            }
        }

        // create a post_type=tournaments from tournamentData if it doesn't exist

        $tournamentSlug = sanitize_title($tournamentData['name']);
        $tournamentTitle = $tournamentData['name'];

        // Check if tournament exists by slug 
        $tournamentPost = get_page_by_path($tournamentSlug, OBJECT, 'tournaments');
        if ($tournamentPost == null) {
            // Create tournament
            $tournamentPost = wp_insert_post(array(
                'post_title' => $tournamentTitle,
                'post_name' => $tournamentSlug,
                'post_type' => 'tournaments',
                'post_status' => 'publish',
            ));
            wp_set_object_terms($tournamentPost, $tournamentData['gameTitle'], 'games');

            // $tournamentData['countries']
            foreach ($tournamentData['countries'] as $country) {
                $countrySlug = sanitize_title($country->code);
                $countryName = $country->code;

                // Check if country exists
                $countryTerm = term_exists($countrySlug, 'countries');
                if ($countryTerm == 0 || $countryTerm == null) {
                    // Create country
                    $countryTerm = wp_insert_term($countryName, 'countries', array(
                        'slug' => $countrySlug,
                    ));
                }
                wp_set_object_terms($tournamentPost, $countryName, 'countries', true);
            }


            $tournamentData['message'] =  '<b> ' . $tournamentTitle . ' </b> - has been created';
        } else {
            $tournamentPost = $tournamentPost->ID;

            // update the post 
            $tournamentPost = array(
                'ID' => $tournamentPost,
                'post_title' => $tournamentTitle,
                'post_name' => $tournamentSlug,
                'post_type' => 'tournaments',
                'post_status' => 'publish',
            );
            wp_update_post($tournamentPost);

            wp_set_object_terms($tournamentPost['ID'], $tournamentData['gameTitle'], 'games');

            // $tournamentData['countries']
            foreach ($tournamentData['countries'] as $country) {
                $countrySlug = sanitize_title($country->code);
                $countryName = $country->code;

                // Check if country exists
                $countryTerm = term_exists($countrySlug, 'countries');
                if ($countryTerm == 0 || $countryTerm == null) {
                    // Create country
                    $countryTerm = wp_insert_term($countryName, 'countries', array(
                        'slug' => $countrySlug,
                    ));
                }
                wp_set_object_terms($tournamentPost['ID'], $countryName, 'countries', true);
            }

            $tournamentData['message'] =  '<b> ' . $tournamentTitle . ' </b> - has been updated';
        }


        // Update Acf Fields

        if ($tournamentPost['ID']) {
            $tournamentPost = $tournamentPost['ID'];
        }

        update_field('tournament_cm_id', $tournamentData['id'], $tournamentPost);
        if ($body->logoImage) {

            update_field('logo_s', $tournamentData['logo']['s'], $tournamentPost);
            update_field('logo_m', $tournamentData['logo']['m'], $tournamentPost);
            update_field('logo_l', $tournamentData['logo']['l'], $tournamentPost);
            update_field('logo_xl', $tournamentData['logo']['xl'], $tournamentPost);
        }

        if ($body->bannerImage) {
            update_field('banner_s', $tournamentData['banner']['s'], $tournamentPost);
            update_field('banner_m', $tournamentData['banner']['m'], $tournamentPost);
            update_field('banner_l', $tournamentData['banner']['l'], $tournamentPost);
        }

        if ($body->thumbnailImage) {
            update_field('thumbnail_s', $tournamentData['thumbnailImage']['s'], $tournamentPost);
            update_field('thumbnail_m', $tournamentData['thumbnailImage']['m'], $tournamentPost);
            update_field('thumbnail_l', $tournamentData['thumbnailImage']['l'], $tournamentPost);
        }

        if ($body->scheduledStartTime) {
            update_field('scheduled_start_time', $tournamentData['scheduledStartTime'], $tournamentPost);
        }

        if ($body->startTime) {

            update_field('start_time', date("Y-m-d H:i:s", strtotime($body->startTime)), $tournamentPost);
        }

        if ($body->endTime) {
            update_field('end_time', $tournamentData['endTime'], $tournamentPost);
        }

        // Od ovde nadlu
        if ($body->scheduledPlayoffsStartTime) {
            $tournamentData['scheduledPlayoffsStartTime'] = date("Y-m-d H:i:s", strtotime($body->scheduledPlayoffsStartTime));
            update_field('scheduled_playoffs_start_time', $tournamentData['scheduledPlayoffsStartTime'], $tournamentPost);
        }
        if ($body->totalSlotCount) {
            $tournamentData['totalSlotCount'] = $body->totalSlotCount;
            update_field('total_slot_count', $tournamentData['totalSlotCount'], $tournamentPost);
        }
        if ($body->maximumLineupPlayerCount) {
            $tournamentData['maximumLineupPlayerCount'] = $body->maximumLineupPlayerCount;
            update_field('maximum_lineup_player_count', $tournamentData['maximumLineupPlayerCount'], $tournamentPost);
        }

        if ($body->maximumLineupBenchwarmerCount) {
            $tournamentData['maximumLineupBenchwarmerCount'] = $body->maximumLineupBenchwarmerCount;
            update_field('maximum_lineup_benchwarmer_count', $tournamentData['maximumLineupBenchwarmerCount'], $tournamentPost);
        }

        if ($body->maximumLineupCoachCount) {
            $tournamentData['maximumLineupCoachCount'] = $body->maximumLineupCoachCount;
            update_field('maximum_lineup_coach_count', $tournamentData['maximumLineupCoachCount'], $tournamentPost);
        }
        if ($body->teamMatchmakingEnabled) {
            $tournamentData['teamMatchmakingEnabled'] = $body->teamMatchmakingEnabled;
            update_field('team_matchmaking_enabled', $tournamentData['teamMatchmakingEnabled'], $tournamentPost);
        }
        if ($body->lineupIds) {
            // Arr od pojke useri ke e 
            $tournamentData['lineupIds'] = $body->lineupIds;

            update_field('lineup_ids', $tournamentData['lineupIds'], $tournamentPost);
        }

        if ($body->filledSlotCount) {
            $tournamentData['filledSlotCount'] = $body->filledSlotCount;
            update_field('filled_slot_count', $tournamentData['filledSlotCount'], $tournamentPost);
        }

        if ($body->format) {
            $tournamentData['format'] = $body->format;
            update_field('format', $tournamentData['format'], $tournamentPost);
        }

        $tournamentData['wp_id'] = $tournamentPost;

        return $tournamentData;
    } else {
        return new WP_Error('token_error', 'Token error', array('status' => 500));
    }
}

// Spaces
function iesf_spaces($request)
{
    $bearer = iesf_auth()['value'];
    $remote_url = CM_API_URL . 'spaces';
    $space_id = $request['space_id'];

    // space_id is required
    if (!$space_id) {
        return new WP_Error('space_id_error', 'Space ID is required', array('status' => 500));
    }

    // Set Bearer token
    $headers = array(
        'Authorization' => 'Bearer ' . $bearer,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    );
    // Get from /spaces
    $response = wp_remote_get($remote_url . '/' . $space_id, array(
        'headers' => $headers,
    ));

    $body = json_decode(wp_remote_retrieve_body($response));

    if (isset($body)) {
        $results = $body;

        return $results;
    } else {
        return new WP_Error('token_error', 'Token error', array('status' => 500));
    }
}

// Search Spaces
function iesf_spaces_search($request)
{
    $bearer = iesf_auth()['value'];
    $remote_url = CM_API_URL . 'spaces/search?slug=' . $request['slug'];
    // Set Bearer token
    $headers = array(
        'Authorization' => 'Bearer ' . $bearer,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    );

    // Get from /spaces
    $response = wp_remote_get($remote_url, array(
        'headers' => $headers,
    ));

    $body = json_decode(wp_remote_retrieve_body($response));

    if (isset($body)) {
        $results = $body;

        return $results;
    } else {
        return new WP_Error('token_error', 'Token error', array('status' => 500));
    }
}

// Tournament posts
function iesf_tournaments_posts($request)
{
    $region = $request['region'];
    $game = $request['game'];
    $country = $request['country'];
    $showPastTournaments = $request['show_past_tournaments'];
    $args = array(
        'post_type' => 'tournaments',
        'posts_per_page' => 6,
        'orderby' => 'menu_order',
        'post_status' => 'publish',
        'offset' => 0,
    );

    if ($region && $region != '*') {
        $args['tax_query'][] = array(
            'taxonomy' => 'region',
            'field' => 'slug',
            'terms' => $region,
        );
    }

    if ($game && $game != '*') {
        $args['tax_query'][] = array(
            'taxonomy' => 'games',
            'field' => 'slug',
            'terms' => $game,
        );
    }

    if ($country && $country != '*') {
        $args['tax_query'][] = array(
            'taxonomy' => 'countries',
            'field' => 'slug',
            'terms' => $country,
        );
    }

    $tournaments = new WP_Query($args);

    $results = array();

    if ($tournaments->have_posts()) {
        while ($tournaments->have_posts()) {
            $tournaments->the_post();
            $regions = wp_get_post_terms(get_the_ID(), 'region');
            $games = wp_get_post_terms(get_the_ID(), 'games');
            $countries = wp_get_post_terms(get_the_ID(), 'countries');

            $bannerImgS = get_field('banner_s', get_the_ID());
            $bannerImgM = get_field('banner_m', get_the_ID());
            $bannerImgL = get_field('banner_l', get_the_ID());
            $title = get_the_title();
            $theId = get_the_ID();

            $startTime = get_field('scheduled_start_time', $theId);
            // Covert to just day and month
            $startTime = date('Y-m-d H:i:s', strtotime($startTime));
            $endTime = get_field('end_time', $theId);
            // Covert to YYYY-MM-DD HH:mm:ss
            $endTime = date('Y-m-d H:i:s', strtotime($endTime));

            // If ended Return;
            if ($showPastTournaments == 'false') {
                if ($endTime < date('Y-m-d H:i:s')) {
                    continue;
                }
            }

            $tournament = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'slug' => get_post_field('post_name', get_the_ID()),
                'date_created' => get_the_date('Y-m-d'),
                'date_modified' => get_the_modified_date('Y-m-d'),
                'regions' => $regions,
                'games' => $games,
                'countries' => $countries,
                'banner_s' => $bannerImgS,
                'banner_m' => $bannerImgM,
                'banner_l' => $bannerImgL,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'link' => get_the_permalink(),

            );
            array_push($results, $tournament);
        }
    } else {
        $results['message'] = 'No tournaments found';
        $results['status'] = 404;
        return $results;
    }
    if ($results == []) {
        $results['message'] = 'No tournaments found';
        $results['status'] = 404;
        return $results;
    }

    return $results;
}
