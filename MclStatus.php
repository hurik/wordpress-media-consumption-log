<?php

add_shortcode( 'mcl', array( 'MclStatus', 'mcl_status' ) );

class MclStatus {

    function mcl_status() {
        // Get the categories
        $categories = get_categories( "exclude=" . MclSettingsHelper::getStatusExcludeCategory() );

        // Get the sorted data
        $data_ongoing = MclDataHelper::getTagsOfCategorySorted( $categories, 0 );
        $data_complete = MclDataHelper::getTagsOfCategorySorted( $categories, 1 );

        // Create categories navigation
        $html = "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"99%\">"
                . "\n  </colgroup>";

        foreach ( $categories as $category ) {
            $count_ongoing = MclDataHelper::countTagsOfCategory( $data_ongoing, $category->term_id );
            $count_complete = MclDataHelper::countTagsOfCategory( $data_complete, $category->term_id );

            if ( $count_ongoing + $count_complete == 0 ) {
                continue;
            }

            $html .= "\n  <tr>"
                    . "\n    <th colspan=\"2\"><div><strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong></th>"
                    . "\n  </tr>";

            if ( $count_ongoing ) {
                $html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $data_ongoing[$category->term_id] ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }

            if ( $count_complete ) {
                $html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $data_complete[$category->term_id] ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }
        }

        $html .= "\n</table>";

        // Create the tables
        foreach ( $categories as $category ) {
            $count_ongoing = MclDataHelper::countTagsOfCategory( $data_ongoing, $category->term_id );
            $count_complete = MclDataHelper::countTagsOfCategory( $data_complete, $category->term_id );

            $count = $count_ongoing + $count_complete;

            if ( $count == 0 ) {
                continue;
            }

            // Category header
            $html .= "\n\n<h4 id=\"mediastatus-{$category->slug}\">{$category->name} ({$count})</h4><hr />";

            if ( $count_ongoing ) {
                $html .= "\n<h6 id=\"mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . " ({$count_ongoing})</h6>";

                // Create the navigation
                $html .= "\n<div>"
                        . "\n  ";
                foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $data_ongoing[$category->term_id] ) ) ) ) {
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

                foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                    $first = true;

                    foreach ( $data_ongoing[$category->term_id][$key] as $tag ) {
                        $name = htmlspecialchars( $tag->name );
                        $name = str_replace( "&amp;", "&", $name );

                        $last_consumed = self::getLastConsumed( $tag->tag_id, $category->term_id );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $data_ongoing[$category->term_id][$key] ) . "\"><div id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $data_ongoing[$category->term_id][$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                    . "\n    <td nowrap>{$last_consumed}</td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                    . "\n    <td nowrap>{$last_consumed}</td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $html .= "\n</table>";
            }

            if ( $count_complete ) {
                $html .= "\n<h6 id=\"mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . " ({$count_complete})</h6>";

                // Create the navigation
                $html .= "\n<div>"
                        . "\n  ";
                foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $data_complete[$category->term_id] ) ) ) ) {
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

                foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                    $first = true;

                    foreach ( $data_complete[$category->term_id][$key] as $tag ) {
                        $name = htmlspecialchars( $tag->name );
                        $name = str_replace( "&amp;", "&", $name );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $data_complete[$category->term_id][$key] ) . "\"><div id=\"mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key} (" . count( $data_complete[$category->term_id][$key] ) . ")</div></th>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                    . "\n  </tr>";

                            $first = false;
                        } else {
                            $html .= "\n  <tr>"
                                    . "\n    <td><a href=\"{$tag->tag_link}\" title=\"{$name}\">{$name}</a></td>"
                                    . "\n  </tr>";
                        }
                    }
                }

                $html .= "\n</table>";
            }
        }

        return $html;
    }

    private function getLastConsumed( $tag_id, $category_id ) {
        $post = MclDataHelper::getLastPostOfTagInCategory( $tag_id, $category_id );

        // Get link
        $link = get_permalink( $post->ID );

        // Explode the title
        $titleExploded = explode( " " . MclSettingsHelper::getOtherSeprator() . " ", $post->post_title );

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

        return "<a href='{$link}' title='{$post->post_title}'>{$statusText}</a>";
    }

}

?>