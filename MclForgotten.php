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
        // Set the default timezone
        date_default_timezone_set( get_option( 'timezone_string' ) );

        $max_date = date( 'Y-m-d H:i:s', strtotime( "-100 day", strtotime( date( 'Y-m-d H:i:s' ) ) ) );

        $serials = MclSettings::get_monitored_categories_serials();

        if ( empty( $serials ) ) {
            self::nothing_here_yet();
            return;
        }

        $categories = get_categories( "include=" . $serials );

        $nav = "";
        $nav_first = true;

        $html = "";

        foreach ( $categories as $category ) {
            $tags = self::get_serials( $category, $max_date );

            if ( count( $tags ) < 1 ) {
                continue;
            }

            if ( !$nav_first ) {
                $nav .= " | ";
            } else {
                $nav_first = false;
            }

            $nav .= "<a href=\"#mediastatus-{$category->slug}\">{$category->name}</a>";

            $html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />"
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

            MclCommaInTags::comma_tags_filter( $tags );

            foreach ( $tags as $tag ) {
                $tag_link = get_tag_link( $tag->tag_id );
                $tag_title = htmlspecialchars( $tag->name );
                $status = MclHelper::get_last_consumed( $tag->post_title );
                $post_title = htmlspecialchars( $tag->post_title );
                $status_link = get_permalink( $tag->post_id );
                $date = DateTime::createFromFormat( "Y-m-d H:i:s", $tag->post_date );
                $date_current = new DateTime( date( "Y-m-d H:i:s" ) );
                $number_of_days = $date_current->diff( $date )->format( "%a" ) + 1;

                $html .= "\n  <tr>"
                        . "\n    <td><a href=\"{$tag_link}\" title=\"{$tag_title}\">{$tag_title}</a></td>"
                        . "\n    <td><a href=\"{$status_link}\" title=\"{$post_title}\">{$status}</a></td>"
                        . "\n    <td nowrap>{$number_of_days}</td>"
                        . "\n  </tr>";
            }

            $html .= "\n</table>";
        }

        if ( empty( $nav ) ) {
            self::nothing_here_yet();
            return;
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

    private static function nothing_here_yet() {
        ?><div class="wrap">
            <h2>Media Consumption Log - <?php _e( 'Forgotten', 'media-consumption-log' ); ?></h2>
            <p><strong><?php _e( 'Nothing here yet!', 'media-consumption-log' ); ?></strong></p>
        </div><?php
    }

    private static function get_serials( $category, $max_date ) {
        global $wpdb;

        $tags = $wpdb->get_results( "
            Select
                temp.tag_id,
                temp.taxonomy,
                temp.name,
                temp.post_id,
                temp.post_date,
                temp.post_title
            FROM
		(
                    SELECT
                        terms2.term_id AS tag_id,
                        t2.taxonomy AS taxonomy,
                        terms2.name AS name,
                        t1.term_id AS cat_id,
                        p2.ID AS post_id,
                        p2.post_date,
                        p2.post_title
                    FROM
                        {$wpdb->prefix}posts AS p1
                        LEFT JOIN {$wpdb->prefix}term_relationships AS r1 ON p1.ID = r1.object_ID
                        LEFT JOIN {$wpdb->prefix}term_taxonomy AS t1 ON r1.term_taxonomy_id = t1.term_taxonomy_id
                        LEFT JOIN {$wpdb->prefix}terms AS terms1 ON t1.term_id = terms1.term_id,
                        {$wpdb->prefix}posts AS p2
                        LEFT JOIN {$wpdb->prefix}term_relationships AS r2 ON p2.ID = r2.object_ID
                        LEFT JOIN {$wpdb->prefix}term_taxonomy AS t2 ON r2.term_taxonomy_id = t2.term_taxonomy_id
                        LEFT JOIN {$wpdb->prefix}terms AS terms2 ON t2.term_id = terms2.term_id
                    WHERE
                        t1.taxonomy = 'category'
                        AND p1.post_status = 'publish'
                        AND terms1.term_id = '{$category->term_id}'
                        AND t2.taxonomy = 'post_tag'
                        AND p2.post_status = 'publish'
                        AND p1.ID = p2.ID
                        AND p2.post_date < '{$max_date}'
                        AND p2.post_date = (
                            SELECT
                                MAX(dp1.post_date)
                            FROM
                                {$wpdb->prefix}posts AS dp1
                                LEFT JOIN {$wpdb->prefix}term_relationships AS dr1 ON dp1.ID = dr1.object_ID
                                LEFT JOIN {$wpdb->prefix}term_taxonomy AS dt1 ON dr1.term_taxonomy_id = dt1.term_taxonomy_id,
                                {$wpdb->prefix}posts AS dp2
                                LEFT JOIN {$wpdb->prefix}term_relationships AS dr2 ON dp2.ID = dr2.object_ID
                                LEFT JOIN {$wpdb->prefix}term_taxonomy AS dt2 ON dr2.term_taxonomy_id = dt2.term_taxonomy_id
                            WHERE
                                dp1.ID = dp2.ID
                                AND dt1.taxonomy = 'category'
                                AND dp1.post_status = 'publish'
                                AND dt2.taxonomy = 'post_tag'
                                AND dp2.post_status = 'publish'
                                AND dt1.term_id = t1.term_id
                                AND dt2.term_id = t2.term_id)
                    ORDER BY post_date
                ) AS temp
            LEFT JOIN {$wpdb->prefix}mcl_complete AS mcl ON temp.tag_id = mcl.tag_id AND temp.cat_id = mcl.cat_id
            WHERE IFNULL(complete, 0) = 0
	" );

        return $tags;
    }

}
