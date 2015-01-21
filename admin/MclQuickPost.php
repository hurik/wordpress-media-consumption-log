<?php

class MclQuickPost {

    public static function create_post( $title, $tag_id, $cat_id ) {
        $my_post = array(
            'post_title' => urldecode( $title ),
            'post_status' => 'publish',
            'tags_input' => get_tag( $tag_id )->name,
            'post_category' => array( $cat_id )
        );

        wp_insert_post( $my_post );
    }

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["title"] ) && isset( $_GET["tag_id"] ) && isset( $_GET["cat_id"] ) ) {
            self::create_post( $_GET["title"], $_GET["tag_id"], $_GET["cat_id"] );
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
            $cats_html .= "\n\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name} ({$category->mcl_tags_count_ongoing})</h3><hr />";

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
                                . "\n    <td><a href class=\"quick-post\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_id}&category={$category->term_id}\">" . __( 'Edit before posting', 'media-consumption-log' ) . "</a>)</td>"
                                . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$post_title}</a> ({$date->format( get_option( 'time_format' ) )}, {$date->format( MclSettings::get_statistics_daily_date_format() )})</td>"
                                . "\n  </tr>";

                        $first = false;
                    } else {
                        $cats_html .= "\n  <tr>"
                                . "\n    <td><a href class=\"quick-post\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_id}&category={$category->term_id}\">" . __( 'Edit before posting', 'media-consumption-log' ) . "</a>)</td>"
                                . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$post_title}</a> ({$date->format( get_option( 'time_format' ) )}, {$date->format( MclSettings::get_statistics_daily_date_format() )})</td>"
                                . "\n  </tr>";
                    }
                }
            }

            $cats_html .= "\n</table>";
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
        </style>

        <script type="text/javascript">
            var running = false;

            jQuery(document).ready(function ($) {
                $(".quick-post").click(function () {
                    if (!running) {
                        running = true;

                        $("#mcl_loading").addClass('loading');
                        $.ajax({
                            async: false,
                            type: 'GET',
                            url: "admin.php?page=mcl-quick-post&title=" + $(this).attr('headline') + "&tag_id=" + $(this).attr('tag-id') + "&cat_id=" + $(this).attr('cat-id'),
                            success: function (data) {
                                location.reload();
                            }
                        });
                    }
                });
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

            <?php
            echo $cats_html;
            ?>
        </div>
        <div id="mcl_loading"></div>
        <?php
    }

    private static function build_next_post_title( $last_post_title ) {
        $title = trim( $last_post_title );
        $title = preg_replace( "/[A-Z0-9]+ " . MclSettings::get_other_to() . " /", "", $title );
        $title = preg_replace( "/[A-Z0-9]+ " . MclSettings::get_other_and() . " /", "", $title );

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