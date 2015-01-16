<?php

add_shortcode( 'mcl', array( 'MclStatus', 'build_status' ) );

class MclStatus {

    static function build_status() {
        // Get the data
        $data = MclRebuildData::get_data();

        // Create categories navigation
        $html = "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"99%\">"
                . "\n  </colgroup>";

        foreach ( $data->categories as $category ) {
            if ( in_array( $category->term_id, explode( ",", MclSettings::get_status_exclude_category() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            $html .= "\n  <tr>"
                    . "\n    <th colspan=\"2\"><strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong></th>"
                    . "\n  </tr>";

            if ( $category->mcl_tags_count_ongoing ) {
                $html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }

            if ( $category->mcl_tags_count_complete ) {
                $html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_complete ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }
        }

        $html .= "\n</table>";

        // Create the tables
        foreach ( $data->categories as $category ) {
            if ( in_array( $category->term_id, explode( ",", MclSettings::get_status_exclude_category() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            // Category header
            $html .= "\n\n<h4 id=\"mediastatus-{$category->slug}\">{$category->name} ({$category->mcl_tags_count})</h4><hr />";

            if ( $category->mcl_tags_count_ongoing ) {
                $html .= "\n<h6 id=\"mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . " ({$category->mcl_tags_count_ongoing})</h6>";

                // Create the navigation
                $html .= "\n<div>"
                        . "\n  ";
                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "\n</div><br />";

                // Table
                $html .= "\n<table border=\"1\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"98%\">"
                        . "\n    <col width=\"1%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                        . "\n    <th nowrap>" . __( 'Last', 'media-consumption-log' ) . "</th>"
                        . "\n  </tr>";

                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $first = true;

                    foreach ( $category->mcl_tags_ongoing[$key] as $tag ) {
                        $href_tag_title = htmlspecialchars( htmlspecialchars_decode( $tag->tag_data->name ) );
                        $href_post_title = htmlspecialchars( htmlspecialchars_decode( $tag->post_data->post_title ) );
                        $lastConsumed = MclStringHelper::get_last_consumed( $tag->post_data->post_title );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $category->mcl_tags_ongoing[$key] ) . "\"><div id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_ongoing[$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$href_tag_title}\">{$tag->tag_data->name}</a></td>"
                                    . "\n    <td nowrap><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$lastConsumed}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$href_tag_title}\">{$tag->tag_data->name}</a></td>"
                                    . "\n    <td nowrap><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$lastConsumed}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $html .= "\n</table>";
            }

            if ( $category->mcl_tags_count_complete ) {
                $html .= "\n<h6 id=\"mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . " ({$category->mcl_tags_count_complete})</h6>";

                // Create the navigation
                $html .= "\n<div>"
                        . "\n  ";
                foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_complete ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "\n</div><br />";

                // Table
                $html .= "\n<table border=\"1\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"99%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                        . "\n  </tr>";

                foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                    $first = true;

                    foreach ( $category->mcl_tags_complete[$key] as $tag ) {
                        $href_tag_title = htmlspecialchars( $tag->tag_data->name );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $category->mcl_tags_complete[$key] ) . "\"><div id=\"mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_complete[$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->tag_data->name}\">{$tag->tag_data->name}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->tag_data->name}\">{$tag->tag_data->name}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $html .= "\n</table>";
            }
        }

        return $html;
    }

}

?>