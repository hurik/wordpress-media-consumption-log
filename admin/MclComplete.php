<?php

class MclComplete {

    public static function add_default_custom_field_in_new_post( $post_id ) {
        add_post_meta( $post_id, 'mcl_complete', '', true );
    }

    public static function check_complete_after_saving( $post_id ) {
        if ( get_post_status( $post_id ) == 'publish' ) {
            $mcl_complete = get_post_meta( $post_id, 'mcl_complete', true );

            // Check if already set
            if ( !empty( $mcl_complete ) ) {
                $cat = get_the_category( $post_id );
                $cat_id = $cat[0]->term_id;

                $tag = wp_get_post_tags( $post_id );
                $tag_id = $tag[0]->term_id;

                self::change_complete_status( $tag_id, $cat_id, 1 );
            }

            // Remove the meta data
            delete_post_meta( $post_id, 'mcl_complete' );
        }
    }

    private static function change_complete_status( $tag_id, $cat_id, $complete ) {
        global $wpdb;

        if ( !empty( $complete ) ) {
            $wpdb->get_results( "
            INSERT INTO {$wpdb->prefix}mcl_complete
            SET tag_id = '{$tag_id}',
                cat_id = '{$cat_id}',
                complete = '1'
        " );
        } else {
            $wpdb->get_results( "
            DELETE
            FROM {$wpdb->prefix}mcl_complete
            WHERE tag_id = '{$tag_id}'
              AND cat_id = '{$cat_id}'
        " );
        }

        MclAdminHooks::updateData();
    }

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        if ( isset( $_GET["tag_id"] ) && isset( $_GET["cat_id"] ) && isset( $_GET["complete"] ) ) {
            self::change_complete_status( $_GET["tag_id"], $_GET["cat_id"], $_GET["complete"] );
            return;
        }

        // Get the data
        $categoriesWithData = MclStatusHelper::getData();

        // Create categories navigation
        $cat_nav_html = "";

        foreach ( $categoriesWithData as $categoryWithData ) {
            if ( $categoryWithData->mcl_count == 0 ) {
                continue;
            }

            $cat_nav_html .= "\n  <tr>"
                    . "\n    <th colspan=\"2\"><strong><a href=\"#mediastatus-{$categoryWithData->slug}\">{$categoryWithData->name}</a></strong></th>"
                    . "\n  </tr>";

            if ( $categoryWithData->mcl_ongoing ) {
                $cat_nav_html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$categoryWithData->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $categoryWithData->mcl_tags_ongoing ) as $key ) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_ongoing ) ) ) ) {
                        $cat_nav_html .= " | ";
                    }
                }

                $cat_nav_html .= "</td>"
                        . "\n  </tr>";
            }

            if ( $categoryWithData->mcl_complete ) {
                $cat_nav_html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$categoryWithData->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $categoryWithData->mcl_tags_complete ) as $key ) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_complete ) ) ) ) {
                        $cat_nav_html .= " | ";
                    }
                }

                $cat_nav_html .= "</td>"
                        . "\n  </tr>";
            }
        }

        $cats_html = "";

        // Create the tables
        foreach ( $categoriesWithData as $categoryWithData ) {
            if ( $categoryWithData->mcl_count == 0 ) {
                continue;
            }

            // Category header
            $cats_html .= "\n\n<div class= \"anchor\" id=\"mediastatus-{$categoryWithData->slug}\"></div><h3>{$categoryWithData->name} ({$categoryWithData->mcl_count})</h3><hr />";

            if ( $categoryWithData->mcl_ongoing ) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$categoryWithData->slug}-ongoing\"></div><h4>" . __( 'Running', 'media-consumption-log' ) . " ({$categoryWithData->mcl_ongoing})</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach ( array_keys( $categoryWithData->mcl_tags_ongoing ) as $key ) {
                    $cats_html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-";
                    $cats_html .= strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_ongoing ) ) ) ) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"98%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th nowrap><strong>" . __( 'Change state', 'media-consumption-log' ) . "</strong></th>"
                        . "\n    <th><strong>" . __( 'Name', 'media-consumption-log' ) . "</strong></th>"
                        . "\n  </tr>";

                foreach ( array_keys( $categoryWithData->mcl_tags_ongoing ) as $key ) {
                    $first = true;

                    foreach ( $categoryWithData->mcl_tags_ongoing[$key] as $tag ) {
                        if ( $first ) {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <th nowrap valign=\"top\" rowspan=\"" . count( $categoryWithData->mcl_tags_ongoing[$key] ) . "\"><div class= \"anchor\" id=\"mediastatus-{$categoryWithData->slug}-" . strtolower( $key ) . "\"></div><div>{$key} (" . count( $categoryWithData->mcl_tags_ongoing[$key] ) . ")</div></th>"
                                    . "\n    <td nowrap><a href class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"1\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->tag_data->name}\">{$tag->tag_data->name}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <td nowrap><a href class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"1\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->tag_data->name}\">{$tag->tag_data->name}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $cats_html .= "\n</table>";
            }

            if ( $categoryWithData->mcl_complete ) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$categoryWithData->slug}-complete\"></div><h4>" . __( 'Complete', 'media-consumption-log' ) . " ({$categoryWithData->mcl_complete})</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach ( array_keys( $categoryWithData->mcl_tags_complete ) as $key ) {
                    $cats_html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_complete ) ) ) ) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"98%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th nowrap><strong>" . __( 'Change state', 'media-consumption-log' ) . "</strong></th>"
                        . "\n    <th><strong>" . __( 'Name', 'media-consumption-log' ) . "</strong></th>"
                        . "\n  </tr>";

                foreach ( array_keys( $categoryWithData->mcl_tags_complete ) as $key ) {
                    $first = true;

                    foreach ( $categoryWithData->mcl_tags_complete[$key] as $tag ) {
                        if ( $first ) {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <th nowrap valign=\"top\" rowspan=\"" . count( $categoryWithData->mcl_tags_complete[$key] ) . "\"><div class= \"anchor\" id=\"mediastatus-{$categoryWithData->slug}-complete-" . strtolower( $key ) . "\"></div><div>{$key} (" . count( $categoryWithData->mcl_tags_complete[$key] ) . ")</div></th>"
                                    . "\n    <td nowrap><a href class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"0\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->tag_data->name}\">{$tag->tag_data->name}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <td nowrap><a href class=\"complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$tag->cat_id}\" set-to=\"0\">" . __( 'Change!', 'media-consumption-log' ) . "</a></td>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->tag_data->name}\">{$tag->tag_data->name}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $cats_html .= "\n</table>";
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

}

?>