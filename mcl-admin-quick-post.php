<?php

add_filter( 'load-post-new.php', 'mcl_new_post_with_cat_and_tag' );

function mcl_new_post_with_cat_and_tag() {
    if ( array_key_exists( 'tag', $_REQUEST ) || array_key_exists( 'category', $_REQUEST ) ) {
        add_action( 'wp_insert_post', 'mcl_insert_cat_and_tag_in_new_post' );
    }
}

function mcl_insert_cat_and_tag_in_new_post( $post_id ) {
    wp_set_post_tags( $post_id, get_tag( $_REQUEST['tag'] )->name );
    wp_set_post_categories( $post_id, array( $_REQUEST['category'] ) );
}

function mcl_create_post( $title, $tag_id, $cat_id ) {
    $my_post = array(
        'post_title' => urldecode( $title ),
        'post_status' => 'publish',
        'tags_input' => get_tag( $tag_id )->name,
        'post_category' => array( $cat_id )
    );

    wp_insert_post( $my_post );
}

function mcl_quick_post() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if ( isset( $_GET["title"] ) && isset( $_GET["tag_id"] ) && isset( $_GET["cat_id"] ) ) {
        mcl_create_post( $_GET["title"], $_GET["tag_id"], $_GET["cat_id"] );
        return;
    }

    // Get the categories
    $categories = get_categories( "exclude=" . MclSettingsHelper::getStatusExcludeCategory() );

    // Get the sorted data
    $data = MclDataHelper::getTagsOfCategorySorted( $categories, 0 );

    // Create categories navigation
    $cat_nav_html = "";

    foreach ( array_keys( $data ) as $cat_key ) {
        $category = get_category( $cat_key );

        $cat_nav_html .= "\n  <tr>"
                . "\n    <th nowrap valign=\"top\"><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></th>"
                . "\n    <td>";
        foreach ( array_keys( $data[$category->term_id] ) as $key ) {
            $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
            if ( $key != end( (array_keys( $data[$category->term_id] ) ) ) ) {
                $cat_nav_html .= " | ";
            }
        }

        $cat_nav_html .= "</td>"
                . "\n  </tr>";
    }

    $cats_html = "";

    // Create the tables
    foreach ( array_keys( $data ) as $cat_key ) {
        $category = get_category( $cat_key );

        $count = MclDataHelper::countTagsOfCategory( $data, $category->term_id );

        // Category header
        $cats_html .= "\n\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name} ({$count})</h3><hr />";

        // Create the navigation
        $cats_html .= "\n<div>";
        foreach ( array_keys( $data[$category->term_id] ) as $key ) {
            $cats_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $cats_html .= strtolower( $key ) . "\">{$key}</a>";
            if ( $key != end( (array_keys( $data[$category->term_id] ) ) ) ) {
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

        foreach ( array_keys( $data[$category->term_id] ) as $key ) {
            $first = true;

            foreach ( $data[$category->term_id][$key] as $tag ) {
                $last_post_data = MclDataHelper::getLastPostOfTagInCategory( $tag->tag_id, $category->term_id );

                if ( empty( $last_post_data ) ) {
                    continue;
                }

                $title = buildNextTitle( $last_post_data );

                $title_urlencode = urlencode( $title );

                $link = get_permalink( $last_post_data->ID );

                $date = DateTime::createFromFormat( "Y-m-d H:i:s", $last_post_data->post_date );

                if ( $first ) {
                    $cats_html .= "\n  <tr>"
                            . "\n    <th nowrap rowspan=\"" . count( $data[$category->term_id][$key] ) . "\" valign=\"top\"><div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\"></div><div>{$key} (" . count( $data[$category->term_id][$key] ) . ")</div></th>"
                            . "\n    <td><a href class=\"quick-post\" title=\"{$title_urlencode}\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"0\">{$title}</a> <a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_id}&category={$category->term_id}\">(" . __( 'Modify', 'media-consumption-log' ) . ")</a></td>"
                            . "\n    <td><a href='{$link}' title='{$last_post_data->post_title}'>{$last_post_data->post_title}</a> ({$date->format( get_option( 'time_format' ) )}, {$date->format( MclSettingsHelper::getStatisticsDailyDateFormat() )})</td>"
                            . "\n  </tr>";

                    $first = false;
                } else {
                    $cats_html .= "\n  <tr>"
                            . "\n    <td><a href class=\"quick-post\" title=\"{$title_urlencode}\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"0\">{$title}</a> <a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_id}&category={$category->term_id}\">(" . __( 'Modify', 'media-consumption-log' ) . ")</a></td>"
                            . "\n    <td><a href='{$link}' title='{$last_post_data->post_title}'>{$last_post_data->post_title}</a> ({$date->format( get_option( 'time_format' ) )}, {$date->format( MclSettingsHelper::getStatisticsDailyDateFormat() )})</td>"
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
    </style>

    <script type="text/javascript">
        var running = false;

        jQuery(document).ready(function ($) {
            $(".quick-post").click(function () {
                if (!running) {
                    running = true;

                    $.ajax({
                        async: false,
                        type: 'GET',
                        url: "admin.php?page=mcl-quick-post&title=" + $(this).attr('title') + "&tag_id=" + $(this).attr('tag-id') + "&cat_id=" + $(this).attr('cat-id'),
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
    <?php
}

function buildNextTitle( $last_post_data ) {
    $title = trim( $last_post_data->post_title );
    $title = preg_replace( "/[A-Z0-9]+ " . MclSettingsHelper::getOtherMclNumberTo() . " /", "", $title );
    $title = preg_replace( "/[A-Z0-9]+ " . MclSettingsHelper::getOtherMclNumberAnd() . " /", "", $title );

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

?>