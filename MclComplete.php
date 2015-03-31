<?php

/*
  Copyright (C) 2014-2015 Andreas Giemza <andreas@giemza.net>

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class MclComplete {

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

        MclData::update_data();
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
        $data = MclData::get_data();

        if ( !$data->cat_serial_ongoing && !$data->cat_serial_complete ) {
            ?>
            <div class="wrap">
                <h2>Media Consumption Log - <?php _e( 'Complete', 'media-consumption-log' ); ?></h2>

                <p><strong><?php _e( 'Nothing here yet!', 'media-consumption-log' ); ?></strong></p>
            </div>
            <?php
            return;
        }

        // Create categories navigation
        $cat_nav_html = "";

        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_serials() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            $cat_nav_html .= "\n  <tr>"
                    . "\n    <th colspan=\"2\"><strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong></th>"
                    . "\n  </tr>";

            if ( $category->mcl_tags_count_ongoing ) {
                $cat_nav_html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td>"
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

            if ( $category->mcl_tags_count_complete ) {
                $cat_nav_html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_complete ) ) ) ) {
                        $cat_nav_html .= " | ";
                    }
                }

                $cat_nav_html .= "</td>"
                        . "\n  </tr>";
            }
        }

        $cats_html = "";

        // Create the tables
        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_serials() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            // Category header
            $cats_html .= "\n\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />";

            if ( $category->mcl_tags_count_ongoing ) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}-ongoing\"></div><h4>" . __( 'Running', 'media-consumption-log' ) . "</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $cats_html .= "<a href=\"#mediastatus-{$category->slug}-";
                    $cats_html .= strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"99%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th nowrap><strong>" . __( 'Change state', 'media-consumption-log' ) . "</strong></th>"
                        . "\n  </tr>";

                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $first = true;

                    foreach ( $category->mcl_tags_ongoing[$key] as $tag ) {
                        $tag_title = str_replace( "\"", "&quot;", $tag->name );

                        if ( $first ) {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <th nowrap valign=\"top\" rowspan=\"" . count( $category->mcl_tags_ongoing[$key] ) . "\"><div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\"></div><div>{$key}</div></th>"
                                    . "\n    <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"1\">{$tag_title}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"1\">{$tag_title}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $cats_html .= "\n</table>";
            }

            if ( $category->mcl_tags_count_complete ) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}-complete\"></div><h4>" . __( 'Complete', 'media-consumption-log' ) . "</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                    $cats_html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_complete ) ) ) ) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"99%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th nowrap><strong>" . __( 'Change state', 'media-consumption-log' ) . "</strong></th>"
                        . "\n  </tr>";

                foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                    $first = true;

                    foreach ( $category->mcl_tags_complete[$key] as $tag ) {
                        $tag_title = str_replace( "\"", "&quot;", $tag->name );

                        if ( $first ) {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <th nowrap valign=\"top\" rowspan=\"" . count( $category->mcl_tags_complete[$key] ) . "\"><div class= \"anchor\" id=\"mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\"></div><div>{$key}</div></th>"
                                    . "\n    <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$tag_title}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $cats_html .= "\n  <tr>"
                                    . "\n    <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$tag_title}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $cats_html .= "\n</table>";
            }
        }
        ?>

        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $(this).scrollTop(0);

                $(".mcl_css_complete").click(function () {
                    $("#mcl_loading").addClass("mcl_css_loading");

                    $.get("admin.php", {
                        page: "mcl-complete",
                        tag_id: $(this).attr("tag-id"),
                        cat_id: $(this).attr("cat-id"),
                        complete: $(this).attr("set-to")}
                    ).done(function () {
                        location.reload();
                    });
                });

                var offset = 200;

                $(window).scroll(function () {
                    var position = $(window).scrollTop();

                    if (position > offset) {
                        $(".mcl_css_back_to_top").fadeIn();
                    } else {
                        $(".mcl_css_back_to_top").fadeOut();
                    }
                });


                $(".mcl_css_back_to_top").click(function () {
                    $(window).scrollTop(0);
                    $(this).fadeOut();
                })
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

            <div id="mcl_loading"></div><div class="mcl_css_back_to_top">^</div>
        </div>
        <?php
    }

}
