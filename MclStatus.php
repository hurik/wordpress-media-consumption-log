<?php

add_shortcode( 'mcl', array( 'MclStatus', 'build_status' ) );

class MclStatus {

    static function build_status() {
        // Get the data
        $data = MclData::get_data();

        // Create categories navigation
        $html = "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"99%\">"
                . "\n  </colgroup>"
                . "\n  <tr>"
                . "\n    <th colspan=\"2\"><strong><a href=\"#series\" style=\"font-size: 130%;\">" . __( 'Series', 'media-consumption-log' ) . "</a></strong></th>"
                . "\n  </tr>";

        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_series() ) ) ) {
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

        $html .= "\n  <tr>"
                . "\n    <th colspan=\"2\"><strong><a href=\"#non-series\" style=\"font-size: 130%;\">" . __( 'Non series', 'media-consumption-log' ) . "</a></strong></th>"
                . "\n  </tr>";

        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_non_series() ) ) ) {
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
                        . "\n    <td colspan=\"2\">";

                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }
        }

        $html .= "\n</table>";

        $html .= "\n\n<h4 id=\"series\">" . __( 'Series', 'media-consumption-log' ) . "</h4><hr />";

        // Create the tables
        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_series() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            // Category header
            $html .= "\n\n<h5 id=\"mediastatus-{$category->slug}\">{$category->name} ({$category->mcl_tags_count})</h5><hr />";

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
                        $href_tag_title = htmlspecialchars( htmlspecialchars_decode( $tag->name ) );
                        $href_post_title = htmlspecialchars( htmlspecialchars_decode( $tag->post_title ) );
                        $lastConsumed = self::get_last_consumed( $tag->post_title );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $category->mcl_tags_ongoing[$key] ) . "\"><div id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_ongoing[$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$href_tag_title}\">{$tag->name}</a></td>"
                                    . "\n    <td nowrap><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$lastConsumed}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$href_tag_title}\">{$tag->name}</a></td>"
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
                        $href_tag_title = htmlspecialchars( $tag->name );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $category->mcl_tags_complete[$key] ) . "\"><div id=\"mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_complete[$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->name}\">{$tag->name}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$tag->name}\">{$tag->name}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $html .= "\n</table>";
            }
        }

        $html .= "\n\n<br /><h4 id=\"non-series\">" . __( 'Non series', 'media-consumption-log' ) . "</h4><hr />";

        // Create the tables
        foreach ( $data->categories as $category ) {
            if ( !in_array( $category->term_id, explode( ",", MclSettings::get_monitored_categories_non_series() ) ) ) {
                continue;
            }

            if ( $category->mcl_tags_count == 0 ) {
                continue;
            }

            // Category header
            $html .= "\n\n<h5 id=\"mediastatus-{$category->slug}\">{$category->name} ({$category->mcl_tags_count})</h5><hr />";

            if ( $category->mcl_tags_count_ongoing ) {
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
                        . "\n    <col width=\"99%\">"
                        . "\n  </colgroup>"
                        . "\n  <tr>"
                        . "\n    <th></th>"
                        . "\n    <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                        . "\n  </tr>";

                foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                    $first = true;

                    foreach ( $category->mcl_tags_ongoing[$key] as $tag ) {
                        $href_post_title = htmlspecialchars( htmlspecialchars_decode( $tag->post_title ) );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $category->mcl_tags_ongoing[$key] ) . "\"><div id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_ongoing[$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$tag->name}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$tag->name}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $html .= "\n</table>";
            }
        }

        return $html;
    }

    private static function get_last_consumed( $title ) {
        // Explode the title
        $titleExploded = explode( " " . MclSettings::get_other_separator() . " ", $title );

        // Get the last part, so we have the chapter/episode/...
        $status = end( $titleExploded );

        $statusExploded = explode( " ", $status );

        if ( count( $statusExploded ) == 1 ) {
            $statusText = reset( $statusExploded );
        } else {
            $first_part = reset( $statusExploded );
            $last_part = end( $statusExploded );

            $statusText = "{$first_part} {$last_part}";
        }

        return $statusText;
    }

}

?>