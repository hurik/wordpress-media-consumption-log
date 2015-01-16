<?php

class MclStatus {

    static function build_status() {
        // Get the data
        $categoriesWithData = MclStatusHelper::getData();

        // Create categories navigation
        $html = "\n<table border=\"1\">"
                . "\n  <colgroup>"
                . "\n    <col width=\"1%\">"
                . "\n    <col width=\"99%\">"
                . "\n  </colgroup>";

        foreach ( $categoriesWithData as $categoryWithData ) {
            if ( $categoryWithData->mcl_count == 0 ) {
                continue;
            }

            $html .= "\n  <tr>"
                    . "\n    <th colspan=\"2\"><strong><a href=\"#mediastatus-{$categoryWithData->slug}\">{$categoryWithData->name}</a></strong></th>"
                    . "\n  </tr>";

            if ( $categoryWithData->mcl_ongoing ) {
                $html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$categoryWithData->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $categoryWithData->mcl_tags_ongoing ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_ongoing ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }

            if ( $categoryWithData->mcl_complete ) {
                $html .= "\n  <tr>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$categoryWithData->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td>"
                        . "\n    <td>";

                foreach ( array_keys( $categoryWithData->mcl_tags_complete ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_complete ) ) ) ) {
                        $html .= " | ";
                    }
                }

                $html .= "</td>"
                        . "\n  </tr>";
            }
        }

        $html .= "\n</table>";

        // Create the tables
        foreach ( $categoriesWithData as $categoryWithData ) {
            if ( $categoryWithData->mcl_count == 0 ) {
                continue;
            }

            // Category header
            $html .= "\n\n<h4 id=\"mediastatus-{$categoryWithData->slug}\">{$categoryWithData->name} ({$categoryWithData->mcl_count})</h4><hr />";

            if ( $categoryWithData->mcl_ongoing ) {
                $html .= "\n<h6 id=\"mediastatus-{$categoryWithData->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . " ({$categoryWithData->mcl_ongoing})</h6>";

                // Create the navigation
                $html .= "\n<div>"
                        . "\n  ";
                foreach ( array_keys( $categoryWithData->mcl_tags_ongoing ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_ongoing ) ) ) ) {
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

                foreach ( array_keys( $categoryWithData->mcl_tags_ongoing ) as $key ) {
                    $first = true;

                    foreach ( $categoryWithData->mcl_tags_ongoing[$key] as $tag ) {
                        $href_tag_title = htmlspecialchars( htmlspecialchars_decode( $tag->tag_data->name ) );
                        $href_post_title = htmlspecialchars( htmlspecialchars_decode( $tag->post_data->post_title ) );
                        $lastConsumed = MclStringHelper::getLastConsumed( $tag->post_data->post_title );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $categoryWithData->mcl_tags_ongoing[$key] ) . "\"><div id=\"mediastatus-{$categoryWithData->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $categoryWithData->mcl_tags_ongoing[$key] ) . ")</div></th>"
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

            if ( $categoryWithData->mcl_complete ) {
                $html .= "\n<h6 id=\"mediastatus-{$categoryWithData->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . " ({$categoryWithData->mcl_complete})</h6>";

                // Create the navigation
                $html .= "\n<div>"
                        . "\n  ";
                foreach ( array_keys( $categoryWithData->mcl_tags_complete ) as $key ) {
                    $html .= "<a href=\"#mediastatus-{$categoryWithData->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                    if ( $key != end( (array_keys( $categoryWithData->mcl_tags_complete ) ) ) ) {
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

                foreach ( array_keys( $categoryWithData->mcl_tags_complete ) as $key ) {
                    $first = true;

                    foreach ( $categoryWithData->mcl_tags_complete[$key] as $tag ) {
                        $href_tag_title = htmlspecialchars( $tag->tag_data->name );

                        if ( $first ) {
                            $html .= "\n  <tr>"
                                    . "\n    <th nowrap rowspan=\"" . count( $categoryWithData->mcl_tags_complete[$key] ) . "\"><div id=\"mediastatus-{$categoryWithData->slug}-complete-" . strtolower( $key ) . "\">{$key} (" . count( $categoryWithData->mcl_tags_complete[$key] ) . ")</div></th>"
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