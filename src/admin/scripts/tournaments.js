import axios from "axios";
jQuery(document).ready(function ($) {
    let api_url = window.location.origin + "/wp-json/iesf/v1/tournaments";
    $('.tournament-admin-data form').on('submit', function (e) {
        e.preventDefault();
        $(this).addClass('loading');
        let $form = $(this);
        let cm_pw = $(this).attr('data-cm-nonce');
        if (!cm_pw) {
            cm_pw = $(this).find('input[name="cm_pw"]').val();
        }
        let tournament_id = $(this).find('input[name="tournament_id"]').val();
        $form.find('p.response-msg').removeClass('success error');
        $form.find('p.response-msg').html('');

        axios.get(api_url, {
            params: {
                cm_pw: cm_pw,
                tournament_id: tournament_id
            }
        })
            .then(function (response) {
                console.log(response);
                $form.removeClass('loading');
                $form.find('p.response-msg').addClass('success');
                let postId = response.data.wp_id;
                $form.find('p.response-msg').html(response.data.message + ' - - - <a href="/wp-admin/post.php?post=' + postId + '&action=edit">Edit Tournament</a>');

            })
            .catch(function (error) {
                console.log(error.message);
                $form.removeClass('loading');
                $form.find('p.response-msg').addClass('error');
                $form.find('p.response-msg').html('<b>Error:</b> ' + error.response.data.message);
            });

    });

    $('.sync-tournament').on('click', function (e) {
        e.preventDefault();
        let $theBtn = $(this);

        if ($theBtn.hasClass('loading')) {
            return;
        }
        $(this).addClass('loading');
        let cm_pw = $('.tournament-admin-data #cm_pw').val();
        let tournament_id = $('.tournament-admin-data #tournament_id_hidden').val();

        axios.get(api_url, {
            params: {
                cm_pw: cm_pw,
                tournament_id: tournament_id
            }
        })
            .then(function (response) {
                console.log(response);
                $theBtn.removeClass('loading');

                let $notice = ` <div class="tournament-admin-notice">
                                    <p style="font-size:32px; color:#fff;">${response.data.message} <br> - Page will reload in <span>3</span> seconds.</p>
                                    <div class="loadbar-wrapper" style="width:300px;">
                                    <div class="loadbar" style="width:0%"></div>
                                    </div>
                                    <p style="font-size:22px; color:#fff; margin-top:1rem;">If page does not reload automatically, Click <a href="#" onclick="location.reload();">here</a> or manually refresh the page.</p>
                                </div>`;
                $('body').append($notice);


                setTimeout(function () {
                    location.reload();
                }, 3500);

                // Count down
                let $countDown = $('.tournament-admin-notice p span');
                let count = 3;
                let countDownInterval = setInterval(function () {
                    count--;
                    $countDown.text(count);
                    if (count === 0) {
                        clearInterval(countDownInterval);
                    }
                }, 1000);

                // Increment loadbar from 0% to 100%
                let $loadBar = $('.tournament-admin-notice .loadbar');
                let timeForLoadBar = 3000;

                let loadBarInterval = setInterval(function () {
                    let currentWidth = $loadBar.width();
                    let newWidth = currentWidth + 1;
                    $loadBar.width(newWidth);
                    if (newWidth >= 300) {
                        clearInterval(loadBarInterval);
                    }
                }, timeForLoadBar / 300);












            })
            .catch(function (error) {
                console.log(error.message);
                $theBtn.removeClass('loading');

            });

    });
}); 