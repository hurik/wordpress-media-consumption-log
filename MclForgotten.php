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

class MclForgotten {

    public static function create_page() {
        if ( !current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'media-consumption-log' ) );
        }

        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        // Get the data
        $data = MclData::get_data_up_to_date();

        $empty = true;

        foreach ( $data->categories as $category ) {
            $category->forgotten = array();

            if ( !MclHelper::is_monitored_serial_category( $category->term_id ) || $category->mcl_tags_count_ongoing < 1 ) {
                continue;
            }

            foreach ( $category->mcl_tags_ongoing as $letter ) {
                foreach ( $letter as $tag ) {
                    $date = DateTime::createFromFormat( "Y-m-d H:i:s", $tag->post_date );
                    $date_current = new DateTime( date( "Y-m-d H:i:s" ) );
                    $number_of_days = $date_current->diff( $date )->format( "%a" ) + 1;

                    if ( $number_of_days >= MclSettings::get_forgotten_min_days() ) {
                        $tag->forgotten = $number_of_days;
                        $category->forgotten[] = $tag;

                        $empty = false;
                    }
                }
            }

            usort( $category->forgotten, function( $a, $b ) {
                return $a->forgotten < $b->forgotten;
            } );
        }

        if ( $empty ) {
            ?><div class="wrap">
                <h2>Media Consumption Log - <?php _e( 'Forgotten', 'media-consumption-log' ); ?></h2>
                <p><strong><?php _e( 'Nothing here yet!', 'media-consumption-log' ); ?></strong></p>
            </div><?php
            return;
        }

        $nav = "";
        $nav_first = true;

        $html = "";

        foreach ( $data->categories as $category ) {
            if ( count( $category->forgotten ) < 1 ) {
                continue;
            }

            if ( !$nav_first ) {
                $nav .= " | ";
            } else {
                $nav_first = false;
            }

            $nav .= "<a href=\"#mediastatus-{$category->slug}\">{$category->name}</a>";

            $html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name} (" . count( $category->forgotten ) . ")</h3><hr />"
                    . "\n<table class=\"widefat\">"
                    . "\n  <colgroup>"
                    . "\n    <col width=\"49%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n    <col width=\"2%\">"
                    . "\n  </colgroup>"
                    . "\n  <tr>"
                    . "\n    <th><strong>" . __( 'Name', 'media-consumption-log' ) . "</strong></th>"
                    . "\n    <th><strong>" . __( 'Last', 'media-consumption-log' ) . "</strong></th>"
                    . "\n    <th nowrap><strong>" . __( 'Days ago', 'media-consumption-log' ) . "</strong></th>"
                    . "\n  </tr>";

            MclCommaInTags::comma_tags_filter( $category->forgotten );

            foreach ( $category->forgotten as $tag ) {
                $tag_title = htmlspecialchars( $tag->name );
                $status = MclHelper::get_last_consumed( $tag->post_title );
                $post_title = htmlspecialchars( $tag->post_title );

                $html .= "\n  <tr>"
                        . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag_title}\">{$tag_title}</a></td>"
                        . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$status}</a></td>"
                        . "\n    <td nowrap>{$tag->forgotten}</td>"
                        . "\n  </tr>";
            }

            $html .= "\n</table>";
        }
        ?><div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Forgotten', 'media-consumption-log' ); ?></h2>

            <table class="widefat">
                <tr>
                    <td>
                        <?php echo $nav; ?> 
                    </td>
                </tr>
            </table>

            <?php echo $html; ?> 

            <div class="mcl_css_back_to_top">^</div>
        </div><?php
        }

    }
    