<?php

class MclQuickPost {

    private static function quick_post_next( $title, $tag_id, $cat_id ) {
        $my_post = array(
            'post_title' => urldecode( $title ),
            'post_status' => 'publish',
            'tags_input' => get_tag( $tag_id )->name,
            'post_category' => array( $cat_id )
        );

        wp_insert_post( $my_post );
    }

    private static function quick_post_new_series( $title, $text, $cat_id ) {
        $title = urldecode( $title );

        $tag = $title;

        if ( in_array( $cat_id, explode( ",", MclSettings::get_monitored_categories_series() ) ) ) {
            $title_exploded = explode( " " . MclSettings::get_other_separator() . " ", $title );
            $tag = str_replace( " " . MclSettings::get_other_separator() . " " . end( $title_exploded ), "", $title );
        }

        $tag = str_replace( ", ", "--", $tag );

        $my_post = array(
            'post_title' => $title,
            'post_content' => urldecode( $text ),
            'post_status' => 'publish',
            'tags_input' => $tag,
            'post_category' => array( $cat_id )
        );

        wp_insert_post( $my_post );
    }

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["title"] ) && isset( $_GET["tag_id"] ) && isset( $_GET["cat_id"] ) ) {
            self::quick_post_next( $_GET["title"], $_GET["tag_id"], $_GET["cat_id"] );
            return;
        }

        if ( isset( $_GET["title"] ) && isset( $_GET["text"] ) && isset( $_GET["cat_id"] ) ) {
            self::quick_post_new_series( $_GET["title"], $_GET["text"], $_GET["cat_id"] );
            return;
        }

        // Get the data
        $data = MclRebuildData::get_data();

        // Create categories navigation
        $cat_nav_html = "";

        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_series() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count_ongoing == 0 ) {
                continue;
            }

            $cat_nav_html .= "\n  <tr>"
                    . "\n    <th nowrap valign=\"top\"><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></th>"
                    . "\n    <td>";
            foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                    $cat_nav_html .= " | ";
                }
            }

            $cat_nav_html .= "</td>"
                    . "\n  </tr>";
        }

        $cat_nav_html .= "\n  <tr>"
                . "\n    <th nowrap valign=\"top\">" . __( 'Non series', 'media-consumption-log' ) . "</th>"
                . "\n    <td>";

        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_non_series() ) ) ) {
                continue;
            }

            $last_non_series = $category->term_id;
        }


        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_non_series() ) ) ) {
                continue;
            }

            $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}\">{$category->name}</a>";
            if ( $category->term_id != $last_non_series ) {
                $cat_nav_html .= " | ";
            }
        }

        $cat_nav_html .= "</td>"
                . "\n  </tr>";

        $cats_html = "";

        // Create the tables
        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_series() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count_ongoing == 0 ) {
                continue;
            }

            // Category header
            $cats_html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name} ({$category->mcl_tags_count_ongoing})</h3><hr />"
                    . "\n<table class=\"form-table\">"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __( 'Title', 'default' ) . "</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-titel\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __( 'Text', 'default' ) . "</th>"
                    . "\n    <td><textarea id=\"{$category->term_id}-text\" rows=\"4\" style=\"width:100%;\"></textarea></td>"
                    . "\n  </tr>"
                    . "\n</table>"
                    . "\n<div align=\"right\"><input id=\"{$category->term_id}\" class=\"button button-primary button-large\" value=\"" . __( 'Publish', 'default' ) . "\" type=\"submit\"></div>";

            // Create the navigation
            $cats_html .= "\n<div>";
            foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                $cats_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                    $cats_html .= " | ";
                }
            }

            $cats_html .= "</div><br />";

            // Table
            $cats_html .= "\n<table class=\"widefat\">"
                    . "\n  <colgroup>"
                    . "\n    <col width=\"2%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n  </colgroup>"
                    . "\n  <tr>"
                    . "\n    <th></th>"
                    . "\n    <th><strong>" . __( 'Next Post', 'media-consumption-log' ) . "</strong></th>"
                    . "\n    <th><strong>" . __( 'Last Post', 'media-consumption-log' ) . "</strong></th>"
                    . "\n  </tr>";

            foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                $first = true;

                foreach ( $category->mcl_tags_ongoing[$key] as $tag ) {
                    $title = self::build_next_post_title( $tag->post_title );
                    $title_urlencode = urlencode( $title );
                    $post_title = htmlspecialchars( $tag->post_title );
                    $date = DateTime::createFromFormat( "Y-m-d H:i:s", $tag->post_date );

                    if ( $first ) {
                        $cats_html .= "\n  <tr>"
                                . "\n    <th nowrap rowspan=\"" . count( $category->mcl_tags_ongoing[$key] ) . "\" valign=\"top\"><div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\"></div><div>{$key} (" . count( $category->mcl_tags_ongoing[$key] ) . ")</div></th>"
                                . "\n    <td><a class=\"quick-post cursor_pointer\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_id}&category={$category->term_id}\">" . __( 'Edit before posting', 'media-consumption-log' ) . "</a>)</td>"
                                . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$post_title}</a> ({$date->format( get_option( 'time_format' ) )}, {$date->format( MclSettings::get_statistics_daily_date_format() )})</td>"
                                . "\n  </tr>";

                        $first = false;
                    } else {
                        $cats_html .= "\n  <tr>"
                                . "\n    <td><a class=\"quick-post cursor_pointer\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_id}&category={$category->term_id}\">" . __( 'Edit before posting', 'media-consumption-log' ) . "</a>)</td>"
                                . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$post_title}</a> ({$date->format( get_option( 'time_format' ) )}, {$date->format( MclSettings::get_statistics_daily_date_format() )})</td>"
                                . "\n  </tr>";
                    }
                }
            }

            $cats_html .= "\n</table>";
        }

        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_non_series() ) ) ) {
                continue;
            }

            $cats_html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />"
                    . "\n<table class=\"form-table\">"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __( 'Title', 'default' ) . "</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-titel\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __( 'Text', 'default' ) . "</th>"
                    . "\n    <td><textarea id=\"{$category->term_id}-text\" rows=\"4\" style=\"width:100%;\"></textarea></td>"
                    . "\n  </tr>"
                    . "\n</table>"
                    . "\n<div align=\"right\"><input id=\"{$category->term_id}\" class=\"button button-primary button-large\" value=\"" . __( 'Publish', 'default' ) . "\" type=\"submit\"></div>";
        }
        ?>
        <style type="text/css">
            div.anchor { display: block; position: relative; top: -32px; visibility: hidden; }

            @media screen and (max-width:782px) {
                div.anchor { display: block; position: relative; top: -46px; visibility: hidden; }
            }

            @media screen and (max-width:600px) {
                div.anchor { display: block; position: relative; top: 0px; visibility: hidden; }
            }

            .loading {
                position:   fixed;
                z-index:    999999;
                top:        0;
                left:       0;
                height:     100%;
                width:      100%;
                background: rgba( 255, 255, 255, .8 ) 
                    url('<?php echo plugins_url() . "/media-consumption-log/admin/images/loading.gif"; ?>') 
                    50% 50% 
                    no-repeat;
            }

            .cursor_pointer {
                cursor:     pointer;
            }

            .back-to-top {
                cursor:           pointer;
                position:         fixed;
                z-index:          99999;
                bottom:           1em;
                right:            1em;
                color:            #FFFFFF;
                background-color: rgba( 51, 51, 51, 0.50 );
                padding:          1em;
                display:          none;
            }

            .back-to-top:hover {    
                background-color: rgba( 51, 51, 51, 0.80 );
            }
        </style>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $(this).scrollTop(0);

                $(".quick-post").click(function () {
                    $("#mcl_loading").addClass("loading");

                    $.get("admin.php", {
                        page: "mcl-quick-post",
                        title: $(this).attr("headline"),
                        tag_id: $(this).attr("tag-id"),
                        cat_id: $(this).attr("cat-id")}
                    ).done(function () {
                        location.reload();
                    });
                });

                $(".button").click(function (e) {
                    $("#mcl_loading").addClass("loading");

                    $.get("admin.php", {
                        page: "mcl-quick-post",
                        title: encodeURIComponent($("#" + e.currentTarget.id + "-titel").val()),
                        text: encodeURIComponent($("#" + e.currentTarget.id + "-text").val()),
                        cat_id: e.currentTarget.id}
                    ).done(function () {
                        $("#" + e.currentTarget.id + "-titel").val("");
                        $("#" + e.currentTarget.id + "-text").val("");
                        location.reload();
                    });
                });

                var offset = 200;

                $(window).scroll(function () {
                    var position = $(window).scrollTop();

                    if (position > offset) {
                        $(".back-to-top").fadeIn();
                    } else {
                        $(".back-to-top").fadeOut();
                    }
                });


                $(".back-to-top").click(function () {
                    $(window).scrollTop(0);
                    $(this).fadeOut();
                })
            });
        </script>

        <div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Quick Post', 'media-consumption-log' ); ?></h2>

            <table class="widefat">
                <colgroup>
                    <col width="1%">
                    <col width="99%">
                </colgroup>
                <?php
                echo $cat_nav_html;
                ?>
            </table>

            <?php echo $cats_html; ?>

            <div id="mcl_loading"></div><div class="back-to-top">^</div>
        </div>
        <?php
    }

    private static function build_next_post_title( $last_post_title ) {
        $title = trim( $last_post_title );
        $title = preg_replace( "/[A-Z0-9.]+ " . MclSettings::get_other_to() . " /", "", $title );
        $title = preg_replace( "/[A-Z0-9.]+ " . MclSettings::get_other_and() . " /", "", $title );

        $title_explode = explode( ' ', $title );
        $number = end( $title_explode );

        if ( is_numeric( $number ) ) {
            $number = floatval( $number );
            $number++;
            $number = floor( $number );
        }

        if ( preg_match( '/[SE]/', $number ) || preg_match( '/[VC]/', $number ) || preg_match( '/[CP]/', $number ) ) {
            $number++;
        }

        $title = substr( $title, 0, strrpos( $title, " " ) );

        $title .= " {$number}";

        return $title;
    }

}

?>