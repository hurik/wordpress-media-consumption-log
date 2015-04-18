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

    const RUNNING = 0;
    const COMPLETE = 1;
    const ABANDONED = 2;

    public static function change_complete_status() {
        global $wpdb;

        if ( isset( $_POST["tag_id"] ) && isset( $_POST["cat_id"] ) && isset( $_POST["complete"] ) ) {
            if ( !empty( $_POST["complete"] ) ) {
                $wpdb->get_results( "
                    INSERT INTO {$wpdb->prefix}mcl_status
                    SET tag_id = '{$_POST["tag_id"]}',
                        cat_id = '{$_POST["cat_id"]}',
                        status = '1'
                " );
            } else {
                $wpdb->get_results( "
                    DELETE
                    FROM {$wpdb->prefix}mcl_status
                    WHERE tag_id = '{$_POST["tag_id"]}'
                      AND cat_id = '{$_POST["cat_id"]}'
                " );
            }

            MclData::update_data();
        }

        wp_die();
    }

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
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
            if ( !MclHelper::is_monitored_serial_category( $category->term_id ) ) {
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
            if ( !MclHelper::is_monitored_serial_category( $category->term_id ) ) {
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
