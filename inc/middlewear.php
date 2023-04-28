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

    // ));

    // Single tournament
    register_rest_route(API_NAMESPACE, '/tournaments', array(
        'methods' => 'GET',
        'callback' => 'iesf_tournaments',
        'permission_callback' => '__return_true',
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
        'permission_callback' => '__return_true',
    ));

    // // Tournament Lineup
    // register_rest_route(API_NAMESPACE, '/tournaments/lineup', array(
    //     'methods' => 'GET',
    //     'callback' => 'iesf_tournaments_lineup',
    // ));


    // iesf_tournaments_import_team_players
    register_rest_route(API_NAMESPACE, '/tournaments/import/players', array(
        'methods' => 'GET',
        'callback' => 'iesf_tournaments_import_team_players',
        'permission_callback' => '__return_true',
    ));
    // import groups and brackets
    register_rest_route(API_NAMESPACE, '/tournaments/import/groups', array(
        'methods' => 'GET',
        'callback' => 'iesf_tournaments_import_groups',
        'permission_callback' => '__return_true',
    ));

    // contact form submission
    register_rest_route(API_NAMESPACE, '/contact', array(
        'methods' => 'POST',
        'callback' => 'iesf_contact_form',
        'permission_callback' => '__return_true',
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
            print_r('There was an error with the CM API. Try again or contact Challenger Mode support.');
            return 'There was an error with the CM API. Try again or contact Challenger Mode support.';
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
    $remote_url = CM_API_URL . 'tournaments/' . $tournament_id . '/graph';
    $remote_url2 = CM_API_URL . 'tournaments/' . $tournament_id;

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

    $secondRequest = wp_remote_get($remote_url2, array(
        'headers' => $headers,
    ));

    $secondBody = json_decode(wp_remote_retrieve_body($secondRequest));


    if (isset($body)) {
        // var_dump($body);
        // die();
        // Get the gameTitle from the body
        if ($body->tournament->id == null) {
            return new WP_Error('tournament_id_error', 'Tournament ID is incorrect', array('status' => 500));
        }

        $countriesArr = array();
        foreach ($body->tournament->countries as $country) {
            $countriesArr[] = $country->twoLetterISOCode;
        }

        $tournamentData = array(
            'id' => $body->tournament->id,
            'name' => $body->tournament->name,
            'gameTitle' => $secondBody->gameTitle,
            'realm' => $body->tournament->realm,
            'countries' => $countriesArr,
        );

        // Check if logo is null 
        if ($body->tournament->logoImage) {
            $tournamentData['logo'] = array(
                's' => $body->tournament->logoImage->small->url,
                'm' => $body->tournament->logoImage->medium->url,
                'l' => $body->tournament->logoImage->large->url,
                'xl' => $body->tournament->logoImage->xLarge->url,
            );
        }
        // Check if banner is null
        if ($body->tournament->bannerImage) {
            $tournamentData['banner'] = array(
                's' => $body->tournament->bannerImage->small->url,
                'm' => $body->tournament->bannerImage->medium->url,
                'l' => $body->tournament->bannerImage->large->url,
            );
        }
        // Check if thumbnailImage is null
        if ($body->tournament->thumbnailImage) {

            $tournamentData['thumbnailImage'] = array(
                's' => $body->tournament->thumbnailImage->small->url,
                'm' => $body->tournament->thumbnailImage->medium->url,
                'l' => $body->tournament->thumbnailImage->large->url,
            );
        }
        if ($body->tournament->scheduledStartTime) {
            $convertedTime = date("Y-m-d H:i:s", strtotime($body->tournament->scheduledStartTime));
            $tournamentData['scheduledStartTime'] = $convertedTime;
        }
        // if ($body->startTime) {
        //     $convertedTime = date("Y-m-d H:i:s", strtotime($body->scheduledEndTime));
        //     $tournamentData['startTime'] = $convertedTime;
        // }
        if ($body->tournament->endTime) {
            $convertedTime = date("Y-m-d H:i:s", strtotime($body->tournament->endTime));
            $tournamentData['endTime'] = $convertedTime;
        }


        foreach ($tournamentData['countries'] as $country) {
            $countrySlug = sanitize_title($country);
            $countryName = $country;

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
                $countrySlug = sanitize_title($country);
                $countryName = $country;

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
                $countrySlug = sanitize_title($country);
                $countryName = $country;

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

        if (is_array($tournamentPost)) {
            $tournamentPost = $tournamentPost['ID'];
        } else {
            $tournamentPost = $tournamentPost;
        }


        update_field('tournament_cm_id', $tournamentData['id'], $tournamentPost);
        if ($body->tournament->logoImage) {

            update_field('logo_s', $tournamentData['logo']['s'], $tournamentPost);
            update_field('logo_m', $tournamentData['logo']['m'], $tournamentPost);
            update_field('logo_l', $tournamentData['logo']['l'], $tournamentPost);
            update_field('logo_xl', $tournamentData['logo']['xl'], $tournamentPost);
        }

        if ($body->tournament->bannerImage) {
            update_field('banner_s', $tournamentData['banner']['s'], $tournamentPost);
            update_field('banner_m', $tournamentData['banner']['m'], $tournamentPost);
            update_field('banner_l', $tournamentData['banner']['l'], $tournamentPost);
        }

        if ($body->tournament->thumbnailImage) {
            update_field('thumbnail_s', $tournamentData['thumbnailImage']['s'], $tournamentPost);
            update_field('thumbnail_m', $tournamentData['thumbnailImage']['m'], $tournamentPost);
            update_field('thumbnail_l', $tournamentData['thumbnailImage']['l'], $tournamentPost);
        }

        if ($body->tournament->scheduledStartTime) {
            update_field('scheduled_start_time', $tournamentData['scheduledStartTime'], $tournamentPost);
        }

        if ($body->tournament->startTime) {

            update_field('start_time', date("Y-m-d H:i:s", strtotime($body->tournament->startTime)), $tournamentPost);
        }

        if ($body->tournament->endTime) {
            update_field('end_time', $tournamentData['endTime'], $tournamentPost);
        }

        if ($body->tournament->scheduledPlayoffsStartTime) {
            $tournamentData['scheduledPlayoffsStartTime'] = date("Y-m-d H:i:s", strtotime($body->tournament->scheduledPlayoffsStartTime));
            update_field('scheduled_playoffs_start_time', $tournamentData['scheduledPlayoffsStartTime'], $tournamentPost);
        }

        if ($body->tournament->totalSlotCount) {
            $tournamentData['totalSlotCount'] = $body->tournament->totalSlotCount;
            update_field('total_slot_count', $tournamentData['totalSlotCount'], $tournamentPost);
        }

        if ($body->tournament->maximumLineupPlayerCount) {
            $tournamentData['maximumLineupPlayerCount'] = $body->tournament->maximumLineupPlayerCount;
            update_field('maximum_lineup_player_count', $tournamentData['maximumLineupPlayerCount'], $tournamentPost);
        }

        if ($body->tournament->maximumLineupBenchwarmerCount) {
            $tournamentData['maximumLineupBenchwarmerCount'] = $body->tournament->maximumLineupBenchwarmerCount;
            update_field('maximum_lineup_benchwarmer_count', $tournamentData['maximumLineupBenchwarmerCount'], $tournamentPost);
        }

        if ($body->tournament->maximumLineupCoachCount) {
            $tournamentData['maximumLineupCoachCount'] = $body->tournament->maximumLineupCoachCount;
            update_field('maximum_lineup_coach_count', $tournamentData['maximumLineupCoachCount'], $tournamentPost);
        }
        if ($body->tournament->teamMatchmakingEnabled) {
            $tournamentData['teamMatchmakingEnabled'] = $body->tournament->teamMatchmakingEnabled;
            update_field('team_matchmaking_enabled', $tournamentData['teamMatchmakingEnabled'], $tournamentPost);
        }

        if ($body->tournament->lineupIds) {
            $tournamentData['lineupIds'] = $body->tournament->lineupIds;
            update_field('lineup_ids', $tournamentData['lineupIds'], $tournamentPost);
        }

        if ($body->tournament->filledSlotCount) {
            $tournamentData['filledSlotCount'] = $body->tournament->filledSlotCount;
            update_field('filled_slot_count', $tournamentData['filledSlotCount'], $tournamentPost);
        }

        if ($body->tournament->format) {
            $tournamentData['format'] = $body->tournament->format;
            update_field('format', $tournamentData['format'], $tournamentPost);
        }
        if ($body->description) {
            if ($body->description->text) {
                $tournamentData['description'] = $body->description->text;
                $tournamentDescription = $tournamentData['description'];

                update_field('description', $tournamentDescription, $tournamentPost);
            }
        }

        if ($body->groups) {
            update_field('groups', $body->groups, $tournamentPost);
            $tournamentData['groups'] = $body->groups;

            $groupsData = $tournamentData['groups'];
        }

        if ($body->brackets) {
            update_field('brackets', $body->brackets, $tournamentPost);
            $tournamentData['brackets'] = $body->brackets;
            $bracketsData = $tournamentData['brackets'];
        }

        if ($body->lineups) {
            $lineUpIds = $body->lineups;

            $membersData = array();
            foreach ($body->members as $member) {
                $id = $member->id;
                $userId = $member->userId;

                $isCaptain = $member->isCaptain;
                $lineupId = $member->lineupId;
                $membersData[] = [
                    'id' => $id,
                    'userId' => $userId,
                    'isCaptain' => $isCaptain,
                    'lineupId' => $lineupId,
                ];
            }
            create_lineup_teams($lineUpIds, $membersData, $tournamentPost);
        }

        $tournamentData['wp_id'] = $tournamentPost;

        if (isset($bracketsData)) {
            create_brackets_data($bracketsData, $tournamentPost, $headers);
        }
        if (isset($groupsData)) {
            create_groups_data($groupsData, $tournamentPost);
        }
        return $body;
        // return $tournamentData;
    } else {
        return new WP_Error('token_error', 'Token error', array('status' => 500));
    }
}

function create_lineup_teams($ids, $membersData, $tournamentPost)
{

    $teamsWpIds = array();
    foreach ($ids as $team) {

        $teamName = $team->name;
        $teamSlug = sanitize_title($teamName);

        $teamPost = get_page_by_path($teamSlug, OBJECT, 'teams');
        if ($teamPost) {
            $teamPost = $teamPost->ID;
        } else {
            $teamPost = wp_insert_post(array(
                'post_title' => $teamName,
                'post_name' => $teamSlug,
                'post_type' => 'teams',
                'post_status' => 'publish',
            ));
        }
        // Get Member Ids from $membersData array
        $memberIds = array();
        foreach ($membersData as $member) {
            if ($member['lineupId'] == $team->id) {
                array_push($memberIds, $member['userId']);
            }
        }

        // Update acf fields
        update_field('team_id', $team->id, $teamPost);
        update_field('memberids', $memberIds, $teamPost);
        update_field('isDisqualified', $team->isDisqualified, $teamPost);
        // Update the relationship field 'played_in' with the tournament id and keep the current values
        $tournamentIds = get_field('played_in', $teamPost);
        if ($tournamentIds) {
            array_push($tournamentIds, $tournamentPost);
        } else {
            $tournamentIds = array($tournamentPost);
        }
        update_field('played_in', $tournamentIds, $teamPost);

        $args = array(
            'post_type' => 'teams',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'team_id',
                    'value' => $team->id,
                    'compare' => 'LIKE',
                ),
            ),
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $teamsWpIds[] = get_the_ID();
            }
        }
        wp_reset_postdata();
    }
    // Add a wait time to avoid api rate limit
    sleep(10);
    // Get the person profile from teamsWpIds

    // foreach ($teamsWpIds as $teamWpId) {
    //     // GET /v1/users/{id}
    //     $bearer = iesf_auth()['value'];
    //     $memberIds = get_field('memberids', $teamWpId);
    //     $remote_url = CM_API_URL . 'users/';

    //     $headers = array(
    //         'Authorization' => 'Bearer ' . $bearer,
    //         'Content-Type' => 'application/json',
    //         'Accept' => 'application/json',
    //     );

    //     $urls = array();
    //     foreach ($memberIds as $memberId) {
    //         $urls[] = $remote_url . $memberId;
    //     }

    //     // Async requests to get all the users from the api

    //     foreach ($urls as $url) {
    //         $responses = wp_remote_get($url, array(
    //             'headers' => $headers,
    //         ));

    //         $body = json_decode(wp_remote_retrieve_body($responses));

    //         $userName = $body->username;
    //         $userSlug = sanitize_title($userName);

    //         $userPost = get_page_by_path($userSlug, OBJECT, 'players');
    //         if ($userPost) {
    //             $userPost = $userPost->ID;
    //         } else {
    //             $userPost = wp_insert_post(array(
    //                 'post_title' => $userName,
    //                 'post_name' => $userSlug,
    //                 'post_type' => 'players',
    //                 'post_status' => 'publish',
    //             ));
    //         }

    //         // Update acf fields
    //         update_field('player_id', $body->id, $userPost);
    //         if ($body->biography) {
    //             update_field('biography', $body->biography, $userPost);
    //         }
    //         if ($body->picture) {
    //             update_field('picture', $body->picture, $userPost);
    //         }
    //         if ($body->overviewUrl) {
    //             update_field('cm_link', $body->overviewUrl, $userPost);
    //         }

    //         // Update the relationship field 'team' with the team id from $teamWpId
    //         $teamIds = get_field('teams', $userPost);

    //         if ($teamIds) {
    //             array_push($teamIds, $teamWpId);
    //         } else {
    //             $teamIds = array($teamWpId);
    //         }
    //     }
    // }
}

function create_brackets_data($bracketsData, $tournamentPostId, $headers)
{
    if (is_string($bracketsData)) {
        $bracketsData = json_decode($bracketsData);
    }
    $post_id = $tournamentPostId;

    // Decode bracketsData if it's a string
    if (is_string($bracketsData)) {
        $bracketsData = json_decode($bracketsData);
    }

    // Create array of bracket rounds
    $bracketRounds = array();
    foreach ($bracketsData as $bracket) {
        foreach ($bracket->rounds as $round) {
            $roundIndex = $round->index + 1;
            $matchSeriesIds = array();
            foreach ($round->matchSeriesIds as $matchSeries) {
                array_push($matchSeriesIds, $matchSeries);
            }
            $bracketRounds[] = array(
                'round' => $roundIndex,
                'matchSeriesIds' => $matchSeriesIds,
            );
        }
    }
    // print_r(json_encode($bracketRounds));
    $num_of_rounds = count($bracketRounds);

    // Clear all brackets_repeater and its sub fields
    update_field('brackets_repeater', array(), $post_id);

    // Create rows for each round
    for ($i = 0; $i < $num_of_rounds; $i++) {
        $bracket_round_row = add_row('brackets_repeater', array(
            'round' => $bracketRounds[$i]['round'],
        ), $post_id);
    }

    $brackets_repeater = get_field('brackets_repeater', $post_id);

    if (have_rows('brackets_repeater', $post_id)) {
        while (have_rows('brackets_repeater', $post_id)) {
            the_row();

            $subRepeaterSelector = 'match_games';
            $rowIndex = get_row_index();
            // Add rows for each matchSeriesId
            $num_of_inner_rows = count($bracketRounds[$rowIndex - 1]['matchSeriesIds']);
            for ($j = 0; $j < $num_of_inner_rows; $j++) {
                $api_url = CM_API_URL . 'tournaments/match_series/' . $bracketRounds[$rowIndex - 1]['matchSeriesIds'][$j];
                $matchRequest = wp_remote_get($api_url, array(
                    'headers' => $headers,
                ));
                $matchData = json_decode(wp_remote_retrieve_body($matchRequest));
                $linups = $matchData->lineups;

                $team1 = get_team_post_id($linups[0]->lineupId);
                $team2 = get_team_post_id($linups[1]->lineupId);
                $team1Score = $linups[0]->score;
                $team2Score = $linups[1]->score;

                $match_series_row = add_sub_row($subRepeaterSelector, array(
                    'match_series_id' => $bracketRounds[$rowIndex - 1]['matchSeriesIds'][$j],
                    'single_team_1' => $team1,
                    'team_score_1' => $team1Score,
                    'single_team_2' => $team2,
                    'team_score_2' => $team2Score,
                ), $post_id, 'brackets_repeater_' . $rowIndex);

                // Add small delay to not exceed API rate limit
                $pauseTime = 2;
                sleep($pauseTime);
            }
        }
    }

    // print_r(json_encode($bracketsData));
    // die();
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
    $page = $request['page'];
    // Sort by get_field('scheduled_start_time'). Earlier dates first. and if it is in the past, show it last.
    $args = array(
        'post_type' => 'tournaments',
        'posts_per_page' => 6,
        'post_status' => 'publish',
        'orderby' => 'meta_value',
        'meta_key' => 'scheduled_start_time',
        'order' => 'DESC',
        'tax_query' => array(
            'relation' => 'AND',
        ),

    );

    if ($page) {
        $args['offset'] = ($page - 1) * 6;
    }

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

    $total = 0;
    $results = array(
        'total' => $total,
        'tournaments' => array(),
    );
    if ($tournaments->have_posts()) {
        $total = $tournaments->found_posts;
        $results['total'] = $total;
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
            array_push($results['tournaments'], $tournament);
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

// Import players
function iesf_tournaments_import_team_players($request)
{

    $cm_pw = $request['cm_pw'];
    $bearer = iesf_auth()['value'];
    $post_id = $request['post_id'];
    $members_ids = $request['members_ids'];
    $members_ids = explode(',', $members_ids);
    $team_id = $request['team_id'];

    if (get_option('cm_options')['cm_api_password'] != $cm_pw) {
        return new WP_Error('cm_pw_error', 'Challenger Mode API password is incorrect', array('status' => 500));
    }

    // GET /v1/users/{id}
    $remote_url = CM_API_URL . 'users';

    // Set Bearer token
    $headers = array(
        'Authorization' => 'Bearer ' . $bearer,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    );

    $results = array();

    foreach ($members_ids as $member_id) {
        $response = wp_remote_get($remote_url . '/' . $member_id, array(
            'headers' => $headers,
        ));

        $body = json_decode(wp_remote_retrieve_body($response));

        if (isset($body)) {
            $results[] = $body;
        } else {
            return [
                'message' => 'There was an error getting the user data from CM. Please try again later or contact CM support.',
                'error' => true,
                'status' => 500,
            ];
        }
    }

    // Create players
    foreach ($results as $player) {

        $playerName = $player->username;
        $playerSlug = sanitize_title($playerName);

        //get_page_by_path($teamSlug, OBJECT, 'teams');
        $playerExists = get_page_by_path($playerSlug, OBJECT, 'players');

        if ($playerExists) {
            $playerId = $playerExists->ID;
        } else {
            $playerId = wp_insert_post(array(
                'post_title' => $playerName,
                'post_name' => $playerSlug,
                'post_type' => 'players',
                'post_status' => 'publish',
            ));
        }

        // Add player to team
        $teamPlayers = get_field('teams', $playerId);
        if ($teamPlayers) {
            $teamPlayers[] = $post_id;
        } else {
            $teamPlayers = array($post_id);
        }
        update_field('teams', $teamPlayers, $playerId);

        // Update ACF fields
        update_field('player_id', $player->id, $playerId);
        if ($player->biography) {
            update_field('biography', $player->avatar, $playerId);
        }
        if ($player->picture) {
            $picUrl = $player->picture;
            // Replace 64_64 with 256_256
            $picUrl = str_replace('64_64', '256_256', $picUrl);
            update_field('picture', $picUrl, $playerId);
        }
        if ($player->overviewUrl) {
            update_field('cm_link', $player->overviewUrl, $playerId);
        }
    }

    // /v1/tournaments/lineups/{id}
    $response = wp_remote_get(CM_API_URL . 'tournaments/lineups/' . $team_id, array(
        'headers' => $headers,
    ));

    $body = json_decode(wp_remote_retrieve_body($response));

    $checkIds = $body->memberIds;
    if ($body->teamId) {
        $secondResponse = wp_remote_get(CM_API_URL . 'teams/' . $body->teamId, array(
            'headers' => $headers,
        ));

        $secondBody = json_decode(wp_remote_retrieve_body($secondResponse));

        if ($secondBody->logoImage->small->url) {
            update_field('logo_s', $secondBody->logoImage->small->url, $post_id);
        }
        if ($secondBody->logoImage->medium->url) {
            update_field('logo_m', $secondBody->logoImage->medium->url, $post_id);
        }
        if ($secondBody->logoImage->large->url) {
            update_field('logo_l', $secondBody->logoImage->large->url, $post_id);
        }
        if ($secondBody->logoImage->xlarge->url) {
            update_field('logo_xl', $secondBody->logoImage->xlarge->url, $post_id);
        }
        if ($secondBody->description) {
            update_field('description', $secondBody->description, $post_id);
        }
        if ($secondBody->website) {
            update_field('website', $secondBody->name, $post_id);
        }
        if ($secondBody->country) {
            update_field('country', $secondBody->name, $post_id);
        }
    }
    // /v1/tournaments/lineup_members/{id}
    foreach ($checkIds as $checkId) {
        $response = wp_remote_get(CM_API_URL . 'tournaments/lineup_members/' . $checkId, array(
            'headers' => $headers,
        ));

        $body = json_decode(wp_remote_retrieve_body($response));


        // find a player with the the acf field player_id  same as $body->userId and update the isCaptain to $body->isCaptain
        $args = array(
            'post_type' => 'players',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'player_id',
                    'value' => $body->userId,
                    'compare' => '=',
                ),
            ),
        );


        $query = new WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $playerId = get_the_ID();
                update_field('isCaptain', $body->isCaptain, $playerId);
                update_field('isSub', $body->role ? true : false, $playerId);
            }
        }

        wp_reset_postdata();
    }

    return [
        'message' => 'Players imported successfully',
        'error' => false,
        'status' => 200,
    ];
}

// Import groups and brackets
function iesf_tournaments_import_groups($request)
{
    $cm_pw = $request['cm_pw'];
    $tournament_id = $request['tournament_id'];
    $post_id = $request['post_id'];
    $bearer = iesf_auth()['value'];

    // Check for missing parameters
    if (!$cm_pw || !$tournament_id || !$post_id) {
        return [
            'message' => 'Missing parameters',
            'error' => true,
            'status' => 400,
        ];
    }

    // Check if correct tournament
    if ($tournament_id != get_field('tournament_cm_id', $post_id)) {
        return [
            'message' => 'This is not the correct tournament',
            'error' => true,
            'status' => 400,
        ];
    }

    // Set headers for API request
    $headers = array(
        'Authorization' => 'Bearer ' . $bearer,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    );

    // Check if API password is correct
    if (get_option('cm_options')['cm_api_password'] != $cm_pw) {
        return new WP_Error('cm_pw_error', 'Challenger Mode API password is incorrect', array('status' => 500));
    }

    // Get brackets and groups data
    $groupsData = get_field('groups', $post_id);
    $bracketsData = get_field('brackets', $post_id);

    // Decode bracketsData if it's a string
    if (is_string($bracketsData)) {
        $bracketsData = json_decode($bracketsData);
    }

    // Create array of bracket rounds
    $bracketRounds = array();
    foreach ($bracketsData as $bracket) {
        foreach ($bracket->rounds as $round) {
            $roundIndex = $round->index + 1;
            $matchSeriesIds = array();
            foreach ($round->matchSeriesIds as $matchSeries) {
                array_push($matchSeriesIds, $matchSeries);
            }
            $bracketRounds[] = array(
                'round' => $roundIndex,
                'matchSeriesIds' => $matchSeriesIds,
            );
        }
    }

    // Clear all brackets_repeater and its sub fields
    update_field('brackets_repeater', array(), $post_id);

    // Add repeater row for each bracket round
    foreach ($bracketRounds as $braRound) {
        $brackets_repeater_row = array(
            'round' => $braRound['round'],
            'match_games' => array(),
        );
        $brackets_repeater_row_id = add_row('brackets_repeater', $brackets_repeater_row, $post_id);
    }
    // Loop through brackets_repeater rows and add sub rows for match games
    if (have_rows('brackets_repeater', $post_id)) {
        while (have_rows('brackets_repeater', $post_id)) {
            the_row();
            $rowIndex = get_row_index();
            foreach ($bracketRounds as $braRound) {
                if ($braRound['round'] == $rowIndex) {

                    foreach ($braRound['matchSeriesIds'] as $singleMatch) {
                        add_sub_row('match_games', array(
                            'match_series_id' => $singleMatch,
                        ), $post_id);

                        // Get match series data from API
                        $matchSeriesUrl = CM_API_URL . 'tournaments/match_series/' . $singleMatch;
                        $matchSeriesResponse = wp_remote_get($matchSeriesUrl, array(
                            'headers' => $headers,
                        ));
                        $matchSeriesBody = json_decode(wp_remote_retrieve_body($matchSeriesResponse));
                        // Extract relevant data from match series body
                        $lineups = $matchSeriesBody->lineups;
                        $team1 = array(
                            'lineupId' => $lineups[0]->lineupId,
                            'score' => $lineups[0]->score,
                        );
                        $team2 = array(
                            'lineupId' => $lineups[1]->lineupId,
                            'score' => $lineups[1]->score,
                        );

                        // Get post IDs for both teams
                        $team1_post_id = get_team_post_id($team1['lineupId']);
                        $team2_post_id = get_team_post_id($team2['lineupId']);

                        // Update match game data
                        update_sub_row('match_games', $rowIndex, array(
                            'single_team_1' => $team1_post_id,
                            'team_score_1' => $team1['score'],
                            'single_team_2' => $team2_post_id,
                            'team_score_2' => $team2['score'],
                        ), $post_id);
                    }
                }
            }
        }
    }

    return [
        'message' => 'Groups and brackets imported successfully',
        'error' => false,
        'tournament_id' => $tournament_id,
        'tournament_name' => get_the_title($post_id),
        'post_id' => $post_id,
        'status' => 200,
    ];
}


function get_team_post_id($team_id)
{
    $args = array(
        'post_type' => 'teams',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'team_id',
                'value' => $team_id,
                'compare' => '=',
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            return get_the_ID();
        }
    }
    wp_reset_postdata();
}
// contact form submission
function iesf_contact_form($request)
{
    $name = $request['name'];
    $email = $request['email'];
    $usrMsg = $request['message'];
    // sanitize all request data
    $name = sanitize_text_field($name);
    $email = sanitize_email($email);
    $usrMsg = sanitize_textarea_field($usrMsg);
    $userMsg = strip_tags($usrMsg);


    $error = array(
        'message' => 'One or more fields have an error. Please check and try again.',
        'error' => true,
        'status' => 500,
        'err_fields' => '',
    );

    // name validation  // name must be at least 2 characters
    if (!$name || strlen($name) < 2) {
        $error['err_fields'] .= 'name, ';
    }


    // email validation
    if (!$email) {
        $error['err_fields'] .= 'email, ';
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error['err_fields'] .= 'email, ';
        }
    }

    // message validation message must be at least 10 characters
    if (!$usrMsg || strlen($usrMsg) < 10) {
        $error['err_fields'] .= 'message, ';
    }


    if ($error['err_fields']) {
        $error['err_fields'] = rtrim($error['err_fields'], ', ');
        return $error;
    }
    // Send auto reply email
    $to = get_field('contact_email', 'option') ? get_field('contact_email', 'option') : get_option('admin_email');
    $custom_logo_id = get_theme_mod('custom_logo');
    $logoUrl = wp_get_attachment_image_src($custom_logo_id, 'full');
    $logoUrl = $logoUrl[0];
    $to = $email;
    $contactEmail = get_field('contact_email', 'option') ? get_field('contact_email', 'option') : get_option('admin_email');

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $headers[] = 'From: IESF <' . $contactEmail . '>';

    $message = '<table style="width: 100%; max-width: 600px; margin: 0 auto; border: 1px solid #ccc; border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.5; color: #333;">';
    $message .= '<tr>';
    $message .= '<td style="padding: 20px 30px; border: 1px solid #ccc; border-collapse: collapse; margin:0 auto; text-align:center;">';
    $message .= '<img src="' . $logoUrl . '" alt="IESF" style="width: 100px; height: auto;">';
    $message .= '</td>';
    $message .= '</tr>';
    $message .= '<tr>';
    $message .= '<td style="padding: 20px 30px; border: 1px solid #ccc; border-collapse: collapse;">';
    $message .= '<h2 style="margin: 0 0 20px; font-size: 18px; text-align:center;">Thank you for contacting us</h2>';
    $message .= '<p style="margin: 0 0 20px; text-align:center;">We have received your message and will get back to you as soon as possible.</p>';
    $message .= '</td>';
    $message .= '</tr>';
    $message .= '</table>';

    $subject = 'Thank you for contacting IESF';

    wp_mail($email, $subject, $message, $headers);

    // Send email to admin
    $toAdmin = get_field('contact_email', 'option') ? get_field('contact_email', 'option') : get_option('admin_email');

    $headersAdmin = array('Content-Type: text/html; charset=UTF-8');
    $headersAdmin[] = 'From: IESF Contact Form <' . $email . '>';

    $messageToAdmin = '<table style="width: 100%; max-width: 600px; margin: 0 auto; border: 1px solid #ccc; border-collapse: collapse; font-family: Arial, Helvetica, sans-serif; font-size: 14px; line-height: 1.5; color: #333;">';
    $messageToAdmin .= '<tr>';
    $messageToAdmin .= '<td style="padding: 20px 30px; border: 1px solid #ccc; border-collapse: collapse; margin:0 auto; text-align:center;">';
    $messageToAdmin .= '<img src="' . $logoUrl . '" alt="IESF" style="width: 100px; height: auto;">';
    $messageToAdmin .= '</td>';
    $messageToAdmin .= '</tr>';
    $messageToAdmin .= '<tr>';
    $messageToAdmin .= '<td style="padding: 20px 30px; border: 1px solid #ccc; border-collapse: collapse;">';
    $messageToAdmin .= '<h2 style="margin: 0 0 20px; font-size: 18px; text-align:center;">New message from IESF contact form</h2>';
    $messageToAdmin .= '<p style="margin: 0 0 20px; ">Name: ' . $name . '</p>';
    $messageToAdmin .= '<p style="margin: 0 0 20px; ">Email: ' . $email . '</p>';
    $messageToAdmin .= '<p style="margin: 0 0 20px; ">Message: ' . $usrMsg . '</p>';
    $messageToAdmin .= '</td>';
    $messageToAdmin .= '</tr>';
    $messageToAdmin .= '</table>';

    $subjectAdmin = 'New message from IESF contact form';

    // wp_mail($toAdmin, $subjectAdmin, $messageToAdmin, $headersAdmin);
    // Check if email sent successfully
    if (!wp_mail($toAdmin, $subjectAdmin, $messageToAdmin, $headersAdmin)) {
        return [
            'message' => 'Something went wrong. Please try again later',
            'error' => true,
            'status' => 500,
        ];
    }

    return [
        'message' => 'Message sent successfully',
        'error' => false,
        'status' => 200,
    ];
}
