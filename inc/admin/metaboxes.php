<?php
function my_tournaments_sync_meta_box()
{
    add_meta_box(
        'my_tournaments_sync',
        'Sync Tournament Data',
        'my_tournaments_sync_meta_box_callback',
        'tournaments',
        'side',
        'high'
    );
}
add_action('add_meta_boxes_tournaments', 'my_tournaments_sync_meta_box');

function my_tournaments_sync_meta_box_callback($post)
{
?>
    <style>
        .format-acf-checks ul::before {
            display: none;
        }

        .format-acf-checks ul {
            display: flex;
            flex-wrap: wrap;
        }

        .format-acf-checks ul li {
            flex: 0 0 calc(50% - 25px);
            width: calc(50% - 25px);
            margin-right: 0 !important;
            padding-right: 25px;
            float: unset;
        }

        .tournament-admin-notice {
            position: fixed;
            top: 0;
            left: 50%;
            padding: 15px;
            transform: translateX(-50%);
            background: rgb(7 21 31 / 91%);
            text-align: center;
            width: 100%;
            display: flex;
            height: 100%;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
            width: 100%;
            height: 100%;
            cursor: wait;
            flex-direction: column;
            align-items: center;
        }

        .tournament-admin-notice .loadbar-wrapper {
            border: 1px solid #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .tournament-admin-notice .loadbar {
            width: 0%;
            height: 50px;
            background: #fff;
            opacity: 0.8;
            z-index: 999;
        }
    </style>
    <div class="tournament-admin-data">
        <p>
            Sync the tournament data from CM. This will update the tournament data.
        </p>
        <form>
            <input type="hidden" id="cm_pw" name="cm_pw" value="<?php echo get_option('cm_options')['cm_api_password']; ?>">
            <input type="hidden" id="tournament_id_hidden" name="tournament_id" value="<?php echo get_field('tournament_cm_id', $post->ID); ?>">
            <input type="hidden" id="post_id" name="post_id" value="<?php echo $post->ID; ?>">
            <input type="submit" value="Sync" class="button button-primary sync-tournament">
        </form>
    </div>
<?php
}

function my_tournaments_sync_meta_box_scripts()
{
    global $post_type;

    if ($post_type == 'tournaments') {
        wp_enqueue_script('cm-admin-script', get_template_directory_uri() . '/dist/js/tournaments.js', ['jquery'], 1, true);
    }
}
add_action('admin_enqueue_scripts', 'my_tournaments_sync_meta_box_scripts');

function import_team_players()
{

    add_meta_box(
        'my_teams_sync',
        'Sync/Import Players from CM',
        'my_teams_sync_meta_box_callback',
        'teams',
        'side',
        'high'
    );
}
add_action('add_meta_boxes_teams', 'import_team_players');

function my_teams_sync_meta_box_callback($post)
{
?>
    <style>
        .format-acf-checks ul::before {
            display: none;
        }

        .format-acf-checks ul {
            display: flex;
            flex-wrap: wrap;
        }

        .format-acf-checks ul li {
            flex: 0 0 calc(50% - 25px);
            width: calc(50% - 25px);
            margin-right: 0 !important;
            padding-right: 25px;
            float: unset;
        }

        .tournament-admin-notice {
            position: fixed;
            top: 0;
            left: 50%;
            padding: 15px;
            transform: translateX(-50%);
            background: rgb(7 21 31 / 91%);
            text-align: center;
            width: 100%;
            display: flex;
            height: 100%;
            flex-wrap: wrap;
            justify-content: center;
            align-content: center;
            width: 100%;
            height: 100%;
            cursor: wait;
            flex-direction: column;
            align-items: center;
        }

        .tournament-admin-notice .loadbar-wrapper {
            border: 1px solid #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .tournament-admin-notice .loadbar {
            width: 0%;
            height: 50px;
            background: #fff;
            opacity: 0.8;
            z-index: 999;
        }
    </style>
    <div class="tournament-admin-data">
        <p>Sync/import players from this team from the CM API</p>
        <form>
            <input type="hidden" id="cm_pw" name="cm_pw" value="<?php echo get_option('cm_options')['cm_api_password']; ?>">
            <?php
            $memberIds = get_field('memberids', $post->ID);
            $memberIds = implode(',', $memberIds);
            ?>
            <input type="hidden" id="members_id_hidden" name="member_ids" value="<?php echo $memberIds; ?>">
            <input type="hidden" id="post_id_hidden" name="post_id" value="<?php echo $post->ID; ?>">
            <input type="hidden" id="team_id_hidden" name="team_id" value="<?php echo get_field('team_id', $post->ID); ?>">
            <input type="submit" value="Sync/Import" class="button button-primary sync-team">
        </form>
    </div>
<?php
}

function my_teams_sync_meta_box_scripts()
{
    global $post_type;

    if ($post_type == 'teams') {
        wp_enqueue_script('cm-admin-script', get_template_directory_uri() . '/dist/js/tournaments.js', ['jquery'], 1, true);
    }
}
add_action('admin_enqueue_scripts', 'my_teams_sync_meta_box_scripts');

function my_tournaments_import_groups_and_brackets()
{
    add_meta_box(
        'my_tournaments_import_groups_and_brackets',
        'Import Groups and Brackets',
        'my_tournaments_import_groups_and_brackets_callback',
        'tournaments',
        'side',
        'high'
    );
}
add_action('add_meta_boxes_tournaments', 'my_tournaments_import_groups_and_brackets');

function my_tournaments_import_groups_and_brackets_callback($post)
{
?>
    <div class="tournament-admin-data">
        <p>Import/sync groups and brackets from the CM API</p>
        <form>
            <input type="hidden" id="cm_pw" name="cm_pw" value="<?php echo get_option('cm_options')['cm_api_password']; ?>">
            <input type="hidden" id="tournament_id_hidden" name="tournament_id" value="<?php echo get_field('tournament_cm_id', $post->ID); ?>">
            <input type="hidden" id="post_id_hidden" name="post_id" value="<?php echo $post->ID; ?>">
            <input type="submit" value="Import/Sync" class="button button-primary import-groups-and-brackets">
        </form>
    </div>

<?php
}
