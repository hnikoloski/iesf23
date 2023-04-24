import axios from "axios";
import moment from "moment";

jQuery(document).ready(function ($) {

    if ($('.iesf-all-tournaments-block').length > 0) {
        let api_url = window.location.origin + "/wp-json/iesf/v1/tournaments/posts";
        let selectFilters = $(".select-filter");

        selectFilters.on('change', function () {
            let $this = $(this);
            $this.siblings('.filter-vals').find('input[name="' + $this.attr('name') + '"]').val($this.val());
            // submit form
            $this.siblings('.filter-vals').submit();
        });

        // select2
        selectFilters.select2();

        // Search input placeholder
        selectFilters.on('select2:open', function (e) {
            let $this = $(this);
            $this.data('select2').$dropdown.find(':input.select2-search__field').attr('placeholder', 'Search...');
        });



        $('.iesf-all-tournaments-block .countdown').each(function () {
            let $this = $(this);
            let $date = $this.attr('data-time');

            let timeNow = moment().format('YYYY-MM-DD HH:mm:ss');

            if (moment($date).isBefore(timeNow)) {
                $this.html('Tournament has ended');
            } else {
                setInterval(function () {
                    let timeNow = moment().format('YYYY-MM-DD HH:mm:ss');
                    let diff = moment.duration(moment($date).diff(moment(timeNow)));
                    let days = diff.days();
                    let hours = diff.hours();
                    let minutes = diff.minutes();
                    let seconds = diff.seconds();
                    $this.find('span').html(days + ' days ' + hours + ' hours ' + minutes + ' minutes ' + seconds + ' seconds');
                }, 1000);

            }


        });

        $('.iesf-all-tournaments-block .filter-vals').on('submit', function (e) {
            e.preventDefault();
            let $this = $(this);

            let $game = $this.find('input[name="filter_games"]').val();
            let $region = $this.find('input[name="filter_region"]').val();
            let $country = $this.find('input[name="filter_country"]').val();
            let showPastTournaments = $this.find('input[name="show_past_tournaments"]').val();

            let $data = {
                region: $region,
                game: $game,
                country: $country,
                show_past_tournaments: showPastTournaments
            };

            axios.get(api_url, {
                params: $data
            }).then(function (response) {
                if (response.data.status == 404) {
                    $('.iesf-all-tournaments-block .tournament-results').html('<p class="no-results">No results found</p>');
                    return;

                }

                let tournaments = response.data;
                let tournamentsMarkup = '';
                tournaments.forEach(function (tournament) {
                    let startTime = moment(tournament.start_time).format('YYYY-MM-DD HH:mm:ss');
                    let endTime = moment(tournament.end_time).format('YYYY-MM-DD HH:mm:ss');
                    tournamentsMarkup += tournament_card_component(tournament.banner_s, tournament.banner_m, tournament.banner_l, tournament.title, startTime, endTime, tournament.link);
                });
                $('.iesf-all-tournaments-block .tournament-results').html(tournamentsMarkup);

            }).then(() => {
                $('.iesf-all-tournaments-block .countdown').each(function () {
                    let $this = $(this);
                    let $date = $this.attr('data-time');

                    let timeNow = moment().format('YYYY-MM-DD HH:mm:ss');

                    if (moment($date).isBefore(timeNow)) {
                        $this.html('Tournament has ended');
                    } else {
                        setInterval(function () {
                            let timeNow = moment().format('YYYY-MM-DD HH:mm:ss');
                            let diff = moment.duration(moment($date).diff(moment(timeNow)));
                            let days = diff.days();
                            let hours = diff.hours();
                            let minutes = diff.minutes();
                            let seconds = diff.seconds();
                            $this.find('span').html(days + ' days ' + hours + ' hours ' + minutes + ' minutes ' + seconds + ' seconds');
                        }, 1000);
                    }
                });
            }
            )
                .catch(function (error) {
                    console.log(error);
                });

        });

        // submit form on page load
        $('.iesf-all-tournaments-block .filter-vals').submit();
    }
});

function tournament_card_component(bannerImgS, bannerImgM, bannerImgL, title, startTime, endTime, link) {

    let prettyStartTime = moment(startTime).format('DD MMM');
    let prettyEndTime = moment(endTime).format('DD MMM');

    let endTimeMarkup = '';
    if (endTime) {
        endTimeMarkup = ` - <span class="single-tournament-time-end">${prettyEndTime}</span> `;
    }

    let imgMarkup = '';
    let srcset = '';
    let sizes = '';
    if (bannerImgS) {
        srcset += `${bannerImgS} 300w,`;
        sizes += `(max-width: 600px) 300px,`;
    }
    if (bannerImgL) {
        srcset += `${bannerImgL} 1200w,`;
        sizes += `1200px,`;
    }
    if (bannerImgM) {
        srcset += `${bannerImgM} 600w,`;
        sizes += `(max-width: 600px) 600px,`;
    }



    if (bannerImgS) {
        imgMarkup = `   <img class="single-tournament-banner"
        src="${bannerImgS}"
         srcset="${srcset}"
            sizes="${sizes}"
             alt="${title}">
        `;
    }
    return `
    <div class="single-tournament single-tournament-card">
     ${imgMarkup}
            <div class="wrap">
          
                <div class="single-tournament-time">
                    <span class="single-tournament-time-start">${prettyStartTime}</span> ${endTimeMarkup}
                </div>
                <h3 class="single-tournament-title">${title}</h3>
                <p class="countdown" data-time=${startTime}"><span></span></p>
                <a href="${link}" class="btn btn-clear btn-clear-arrow">View More<span class="material-symbols-outlined">
                    arrow_right_alt
                </span></a>
        </div>
   
</div>
    `
}