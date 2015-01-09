<?php

add_filter( 'load-post-new.php', 'mcl_load_mcl_complete_in_new_post' );

function mcl_load_mcl_complete_in_new_post() {
    add_action( 'wp_insert_post', 'mcl_insert_mcl_complete_in_new_post' );
}

function mcl_insert_mcl_complete_in_new_post( $post_id ) {
    add_post_meta( $post_id, 'mcl_complete', '', true );
}

add_action( 'save_post', 'mcl_check_complete_after_saving' );

function mcl_check_complete_after_saving( $post_id ) {
    if ( get_post_status( $post_id ) == 'publish' ) {
        $mcl_complete = get_post_meta( $post_id, 'mcl_complete', true );

        // Check if already set
        if ( !empty( $mcl_complete ) ) {
            $post = get_post( $post_id );

            $cat = get_the_category( $post_id );
            $cat_id = $cat[0]->term_id;

            $tag = wp_get_post_tags( $post_id );
            $tag_id = $tag[0]->term_id;

            change_complete_status( $tag_id, $cat_id, 1 );

            // Remove the meta data
            delete_post_meta( $post_id, 'mcl_complete' );

            return;
        }
    }
}

function change_complete_status( $tag_id, $cat_id, $complete ) {
    global $wpdb;

    if ( !empty( $complete ) ) {
        $wpdb->get_results( "INSERT INTO {$wpdb->prefix}mcl_complete SET tag_id = $tag_id, cat_id = $cat_id, complete = $complete" );
    } else {
        $wpdb->get_results( "DELETE FROM {$wpdb->prefix}mcl_complete WHERE tag_id = $tag_id AND cat_id = $cat_id" );
    }
}

function mcl_complete() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if ( isset( $_GET["tag_id"] ) && isset( $_GET["cat_id"] ) && isset( $_GET["complete"] ) ) {
        change_complete_status( $_GET["tag_id"], $_GET["cat_id"], $_GET["complete"] );
        return;
    }

    // Get the categories
    $categories = get_categories( "exclude=" . SettingsHelper::getStatusExcludeCategory() );

    // Get the sorted data
    $data_ongoing = DataHelper::getTagsOfCategorySorted( $categories, 0 );
    $data_complete = DataHelper::getTagsOfCategorySorted( $categories, 1 );

    // Create categories navigation
    $cat_nav_html = "";

    foreach ( $categories as $category ) {
        $count_ongoing = DataHelper::countTagsOfCategory( $data_ongoing, $category->term_id );
        $count_complete = DataHelper::countTagsOfCategory( $data_complete, $category->term_id );

        if ( $count_ongoing + $count_complete == 0 ) {
            continue;
        }

        $cat_nav_html .= "<tr><th colspan=\"2\"><div><strong><a href=\"#mediastatus-";
        $cat_nav_html .= "{$category->slug}\">{$category->name}</a></strong>";
        $cat_nav_html .= "</th></tr>";

        if ( $count_ongoing ) {
            $cat_nav_html .= "<tr><td nowrap><a href=\"#mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td><td>";

            foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-";
                $cat_nav_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_ongoing[$category->term_id] ) ) ) ) {
                    $cat_nav_html .= " | ";
                }
            }

            $cat_nav_html .= "</td></tr>";
        }

        if ( $count_complete ) {
            $cat_nav_html .= "<tr><td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td><td>";

            foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-complete-";
                $cat_nav_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_complete[$category->term_id] ) ) ) ) {
                    $cat_nav_html .= " | ";
                }
            }

            $cat_nav_html .= "</td></tr>";
        }
    }


    $cats_html = "";

    // Create the tables
    foreach ( $categories as $category ) {
        $count_ongoing = DataHelper::countTagsOfCategory( $data_ongoing, $category->term_id );
        $count_complete = DataHelper::countTagsOfCategory( $data_complete, $category->term_id );

        $count = $count_ongoing + $count_complete;

        if ( $count == 0 ) {
            continue;
        }

        // Category header
        $cats_html .= "<div class= \"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}";
        $cats_html .= " ({$count})</h3><hr />";

        if ( $count_ongoing ) {
            $cats_html .= "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-ongoing\"></div><h4>" . __( 'Running', 'media-consumption-log' );
            $cats_html .= " ({$count_ongoing})</h4>";

            // Create the navigation
            $cats_html .= "<div>";
            foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                $cats_html .= "<a href=\"#mediastatus-{$category->slug}-";
                $cats_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_ongoing[$category->term_id] ) ) ) ) {
                    $cats_html .= " | ";
                }
            }

            $cats_html .= "</div><br />";

            // Table
            $cats_html .= "\n<table class=\"widefat\"><colgroup><col width=\"1%\">";
            $cats_html .= "<col width=\"1%\"><col width=\"99%\"></colgroup>";
            $cats_html .= "\n<tr><th></th><th nowrap><strong>" . __( 'Change state', 'media-consumption-log' ) . "</strong></th>";
            $cats_html .= "<th><strong>" . __( 'Name', 'media-consumption-log' ) . "</strong></th></tr>";

            foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                $first = true;

                foreach ( $data_ongoing[$category->term_id][$key] as $tag ) {
                    $name = $tag->name;
                    if ( SettingsHelper::isOtherCommaInTags() ) {
                        $name = str_replace( '--', ', ', $name );
                    }
                    $name = htmlspecialchars( $name );
                    $name = str_replace( "&amp;", "&", $name );

                    if ( $first ) {
                        $cats_html .= "<tr>"
                                . "<th nowrap valign=\"top\" rowspan=\"" . count( $data_ongoing[$category->term_id][$key] ) . "\"><div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\"></div><div>{$key} (" . count( $data_ongoing[$category->term_id][$key] ) . ")</div></th>"
                                . "<td nowrap><a class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"1\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                . "<td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                . "</tr>";

                        $first = false;
                    } else {
                        $cats_html .= "<tr>"
                                . "<td nowrap><a class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"1\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                . "<td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                . "</tr>";
                    }
                }
            }

            $cats_html .= "</table>";
        }

        if ( $count_complete ) {
            $cats_html .= "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-complete\"></div><h4>" . __( 'Complete', 'media-consumption-log' );
            $cats_html .= " ({$count_complete})</h4>";

            // Create the navigation
            $cats_html .= "<div>";
            foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                $cats_html .= "<a href=\"#mediastatus-{$category->slug}-complete-";
                $cats_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_complete[$category->term_id] ) ) ) ) {
                    $cats_html .= " | ";
                }
            }

            $cats_html .= "</div><br />";

            // Table
            $cats_html .= "\n<table class=\"widefat\"><colgroup><col width=\"1%\">";
            $cats_html .= "<colgroup><col width=\"1%\"><col width=\"99%\"></colgroup>";
            $cats_html .= "\n<tr><th></th><th nowrap><strong>" . __( 'Change state', 'media-consumption-log' ) . "</strong></th><th><strong>" . __( 'Name', 'media-consumption-log' ) . "</strong></th></tr>";

            foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                $first = true;

                foreach ( $data_complete[$category->term_id][$key] as $tag ) {
                    $name = $tag->name;
                    if ( SettingsHelper::isOtherCommaInTags() ) {
                        $name = str_replace( '--', ', ', $name );
                    }
                    $name = htmlspecialchars( $name );
                    $name = str_replace( "&amp;", "&", $name );

                    if ( $first ) {
                        $cats_html .= "<tr>"
                                . "<th nowrap valign=\"top\" rowspan=\"" . count( $data_complete[$category->term_id][$key] ) . "\"><div class= \"anchor\" id=\"mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\"></div><div>{$key} (" . count( $data_complete[$category->term_id][$key] ) . ")</div></th>"
                                . "<td nowrap><a class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"0\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                . "<td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                . "</tr>";

                        $first = false;
                    } else {
                        $cats_html .= "<tr>"
                                . "<td nowrap><a class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"0\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                . "<td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                . "</tr>";
                    }
                }
            }

            $cats_html .= "</table>";
        }
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
        jQuery(document).ready(function ($) {
            $(".complete").click(function () {
                $.ajax({
                    async: false,
                    type: 'GET',
                    url: "admin.php?page=mcl-complete&tag_id=" + $(this).attr('tag-id') + "&cat_id=" + $(this).attr('cat-id') + "&complete=" + $(this).attr('set-to'),
                    success: function (data) {
                        location.reload();
                    }
                });
            });
        });
    </script>

    <div class="wrap">
        <h2>Media Consumption Log - <?php _e( 'Complete', 'media-consumption-log' ); ?></h2>

        <table class="widefat">
            <colgroup>
                <col width="1%">
                <col width="99%">
            </colgroup>
            <?php echo $cat_nav_html; ?>
        </table>

        <?php echo $cats_html; ?>
    </div>	
    <?php
}

?>