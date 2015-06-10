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

add_shortcode( 'mcl', array( 'MclStatus', 'build_status' ) );

class MclStatus {

    static function build_status() {
        // Get the data
        $data = MclData::get_data();

        if ( !$data->cat_serial_ongoing && !$data->cat_serial_complete && !$data->cat_serial_abandoned && !$data->cat_non_serial ) {
            $html = "<p><strong>" . __( 'Nothing here yet!', 'media-consumption-log' ) . "</strong></p>";

            return $html;
        }

        // Create categories navigation
        if ( $data->cat_serial_ongoing || $data->cat_serial_complete || $data->cat_serial_abandoned ) {
            $html = "<h4><a href=\"#serials\" style=\"font-size: 120%;\">" . __( 'Serials', 'media-consumption-log' ) . "</a></h4>"
                    . "\n<table class=\"mcl_table\">"
                    . "\n  <colgroup>"
                    . "\n    <col width=\"1%\">"
                    . "\n    <col width=\"1%\">"
                    . "\n    <col width=\"98%\">"
                    . "\n  </colgroup>"
                    . "\n  <thead>"
                    . "\n    <tr>"
                    . "\n      <th nowrap><strong>" . __( 'Category', 'media-consumption-log' ) . "</strong></th>"
                    . "\n      <th nowrap><strong>" . __( 'State', 'media-consumption-log' ) . "</strong></th>"
                    . "\n      <th nowrap><strong>" . __( 'Quick Navigation', 'media-consumption-log' ) . "</strong></th>"
                    . "\n    </tr>"
                    . "\n  </thead>"
                    . "\n  <tbody>";

            foreach ( $data->categories as $category ) {
                if ( !MclHelper::is_monitored_serial_category( $category->term_id ) ) {
                    continue;
                }

                if ( $category->mcl_tags_count == 0 ) {
                    continue;
                }

                $first = true;

                if ( $category->mcl_tags_count_ongoing ) {
                    $html .= "\n    <tr>"
                            . "\n      <td nowrap>" . ($first ? "<strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong>" : "") . "</td>"
                            . "\n      <td nowrap><a href=\"#mediastatus-{$category->slug}-ongoing\">" . __( 'Running', 'media-consumption-log' ) . "</a></td>"
                            . "\n      <td>";

                    foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                        $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                        if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                            $html .= " | ";
                        }
                    }

                    $html .= "</td>"
                            . "\n    </tr>";

                    $first = false;
                }

                if ( $category->mcl_tags_count_complete ) {
                    $html .= "\n    <tr>"
                            . "\n      <td nowrap>" . ($first ? "<strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong>" : "") . "</td>"
                            . "\n      <td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . "</a></td>"
                            . "\n      <td>";

                    foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                        $html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                        if ( $key != end( (array_keys( $category->mcl_tags_complete ) ) ) ) {
                            $html .= " | ";
                        }
                    }

                    $html .= "</td>"
                            . "\n    </tr>";

                    $first = false;
                }

                if ( $category->mcl_tags_count_abandoned ) {
                    $html .= "\n    <tr>"
                            . "\n      <td nowrap>" . ($first ? "<strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong>" : "") . "</td>"
                            . "\n      <td nowrap><a href=\"#mediastatus-{$category->slug}-abandoned\">" . __( 'Abandoned', 'media-consumption-log' ) . "</a></td>"
                            . "\n      <td>";

                    foreach ( array_keys( $category->mcl_tags_abandoned ) as $key ) {
                        $html .= "<a href=\"#mediastatus-{$category->slug}-abandoned-" . strtolower( $key ) . "\">{$key}</a>";
                        if ( $key != end( (array_keys( $category->mcl_tags_abandoned ) ) ) ) {
                            $html .= " | ";
                        }
                    }

                    $html .= "</td>"
                            . "\n    </tr>";

                    $first = false;
                }
            }

            $html .= "\n  </tbody>"
                    . "\n</table>";
        }

        if ( $data->cat_non_serial ) {
            $html .= "<h4><a href=\"#non-serials\" style=\"font-size: 120%;\">" . __( 'Non serials', 'media-consumption-log' ) . "</a></h4>"
                    . "\n<table class=\"mcl_table\">"
                    . "\n  <colgroup>"
                    . "\n    <col width=\"1%\">"
                    . "\n    <col width=\"98%\">"
                    . "\n  </colgroup>"
                    . "\n  </tbody>"
                    . "\n  <thead>"
                    . "\n    <tr>"
                    . "\n      <th nowrap><strong>" . __( 'Category', 'media-consumption-log' ) . "</strong></th>"
                    . "\n      <th nowrap><strong>" . __( 'Quick Navigation', 'media-consumption-log' ) . "</strong></th>"
                    . "\n    </tr>"
                    . "\n  </thead>"
                    . "\n  <tbody>";

            foreach ( $data->categories as $category ) {
                if ( !MclHelper::is_monitored_non_serial_category( $category->term_id ) ) {
                    continue;
                }

                if ( $category->mcl_tags_count == 0 ) {
                    continue;
                }

                $html .= "\n    <tr>"
                        . "\n      <td nowrap><strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong></td>";

                if ( $category->mcl_tags_count_ongoing ) {
                    $html .= "\n      <td>";

                    foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                        $html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key}</a>";
                        if ( $key != end( (array_keys( $category->mcl_tags_ongoing ) ) ) ) {
                            $html .= " | ";
                        }
                    }

                    $html .= "</td>"
                            . "\n    </tr>";
                }
            }

            $html .= "\n  </tbody>"
                    . "\n</table>";
        }

        if ( $data->cat_serial_ongoing || $data->cat_serial_complete || $data->cat_serial_abandoned ) {
            $html .= "\n\n<h4 id=\"serials\">" . __( 'Serials', 'media-consumption-log' ) . "</h4><hr />";

            // Create the tables
            foreach ( $data->categories as $category ) {
                if ( !MclHelper::is_monitored_serial_category( $category->term_id ) ) {
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
                    $html .= "\n<table class=\"mcl_table\">"
                            . "\n  <colgroup>"
                            . "\n    <col width=\"1%\">"
                            . "\n    <col width=\"98%\">"
                            . "\n    <col width=\"1%\">"
                            . "\n  </colgroup>"
                            . "\n  <thead>"
                            . "\n    <tr>"
                            . "\n      <th></th>"
                            . "\n      <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                            . "\n      <th nowrap>" . __( 'Last', 'media-consumption-log' ) . "</th>"
                            . "\n    </tr>"
                            . "\n  </thead>"
                            . "\n  <tbody>";

                    foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                        $first = true;

                        foreach ( $category->mcl_tags_ongoing[$key] as $tag ) {
                            $href_tag_title = htmlspecialchars( htmlspecialchars_decode( $tag->name ) );
                            $href_post_title = htmlspecialchars( htmlspecialchars_decode( $tag->post_title ) );
                            $lastConsumed = MclHelper::get_last_consumed( $tag->post_title );

                            $html .= "\n    <tr>"
                                    . "\n      <th nowrap>" . ($first ? "<div id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_ongoing[$key] ) . ")</div>" : "") . "</th>"
                                    . "\n      <td><a href=\"{$tag->tag_link}\" title=\"{$href_tag_title}\">{$tag->name}</a></td>"
                                    . "\n      <td nowrap><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$lastConsumed}</a></td>"
                                    . "\n    </tr>";

                            $first = false;
                        }
                    }

                    $html .= "\n  </tbody>"
                            . "\n</table>";
                }

                if ( $category->mcl_tags_count_complete ) {
                    $html .= "\n<h6 id=\"mediastatus-{$category->slug}-complete\">" . __( 'Complete', 'media-consumption-log' ) . " ({$category->mcl_tags_count_complete})</h6>";

                    // Create the navigation
                    $html .= "\n<div>"
                            . "\n  ";
                    foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                        $html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}</a>";
                        if ( $key != end( (array_keys( $category->mcl_tags_complete ) ) ) ) {
                            $html .= " | ";
                        }
                    }

                    $html .= "\n</div><br />";

                    // Table
                    $html .= "\n<table class=\"mcl_table\">"
                            . "\n  <colgroup>"
                            . "\n    <col width=\"1%\">"
                            . "\n    <col width=\"99%\">"
                            . "\n  </colgroup>"
                            . "\n  <thead>"
                            . "\n    <tr>"
                            . "\n      <th></th>"
                            . "\n      <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                            . "\n    </tr>"
                            . "\n  </thead>"
                            . "\n  <tbody>";

                    foreach ( array_keys( $category->mcl_tags_complete ) as $key ) {
                        $first = true;

                        foreach ( $category->mcl_tags_complete[$key] as $tag ) {
                            $href_tag_title = htmlspecialchars( $tag->name );

                            $html .= "\n    <tr>"
                                    . "\n      <th nowrap>" . ($first ? "<div id=\"mediastatus-{$category->slug}-complete-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_complete[$key] ) . ")</div>" : "") . "</th>"
                                    . "\n      <td><a href=\"{$tag->tag_link}\" title=\"{$tag->name}\">{$tag->name}</a></td>"
                                    . "\n    </tr>";

                            $first = false;
                        }
                    }

                    $html .= "\n  </tbody>"
                            . "\n</table>";
                }

                if ( $category->mcl_tags_count_abandoned ) {
                    $html .= "\n<h6 id=\"mediastatus-{$category->slug}-abandoned\">" . __( 'Abandoned', 'media-consumption-log' ) . " ({$category->mcl_tags_count_abandoned})</h6>";

                    // Create the navigation
                    $html .= "\n<div>"
                            . "\n  ";
                    foreach ( array_keys( $category->mcl_tags_abandoned ) as $key ) {
                        $html .= "<a href=\"#mediastatus-{$category->slug}-abandoned-" . strtolower( $key ) . "\">{$key}</a>";
                        if ( $key != end( (array_keys( $category->mcl_tags_abandoned ) ) ) ) {
                            $html .= " | ";
                        }
                    }

                    $html .= "\n</div><br />";

                    // Table
                    $html .= "\n<table class=\"mcl_table\">"
                            . "\n  <colgroup>"
                            . "\n    <col width=\"1%\">"
                            . "\n    <col width=\"99%\">"
                            . "\n  </colgroup>"
                            . "\n  <thead>"
                            . "\n    <tr>"
                            . "\n      <th></th>"
                            . "\n      <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                            . "\n    </tr>"
                            . "\n  </thead>"
                            . "\n  <tbody>";

                    foreach ( array_keys( $category->mcl_tags_abandoned ) as $key ) {
                        $first = true;

                        foreach ( $category->mcl_tags_abandoned[$key] as $tag ) {
                            $href_tag_title = htmlspecialchars( $tag->name );

                            $html .= "\n    <tr>"
                                    . "\n      <th nowrap>" . ($first ? "<div id=\"mediastatus-{$category->slug}-abandoned-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_abandoned[$key] ) . ")</div>" : "") . "</th>"
                                    . "\n      <td><a href=\"{$tag->tag_link}\" title=\"{$tag->name}\">{$tag->name}</a></td>"
                                    . "\n    </tr>";

                            $first = false;
                        }
                    }

                    $html .= "\n  </tbody>"
                            . "\n</table>";
                }
            }
        }

        if ( $data->cat_non_serial ) {
            $html .= "\n\n<br /><h4 id=\"non-serials\">" . __( 'Non serials', 'media-consumption-log' ) . "</h4><hr />";

            // Create the tables
            foreach ( $data->categories as $category ) {
                if ( !MclHelper::is_monitored_non_serial_category( $category->term_id ) ) {
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
                    $html .= "\n<table class=\"mcl_table\">"
                            . "\n  <colgroup>"
                            . "\n    <col width=\"1%\">"
                            . "\n    <col width=\"99%\">"
                            . "\n  </colgroup>"
                            . "\n  <thead>"
                            . "\n    <tr>"
                            . "\n      <th></th>"
                            . "\n      <th>" . __( 'Name', 'media-consumption-log' ) . "</th>"
                            . "\n    </tr>"
                            . "\n  </thead>"
                            . "\n  <tbody>";

                    foreach ( array_keys( $category->mcl_tags_ongoing ) as $key ) {
                        $first = true;

                        foreach ( $category->mcl_tags_ongoing[$key] as $tag ) {
                            $href_post_title = htmlspecialchars( htmlspecialchars_decode( $tag->post_title ) );

                            $html .= "\n    <tr>"
                                    . "\n      <th nowrap>" . ($first ? "<div id=\"mediastatus-{$category->slug}-" . strtolower( $key ) . "\">{$key} (" . count( $category->mcl_tags_ongoing[$key] ) . ")</div>" : "") . "</th>"
                                    . "\n      <td><a href=\"{$tag->post_link}\" title=\"{$href_post_title}\">{$tag->name}</a></td>"
                                    . "\n    </tr>";

                            $first = false;
                        }
                    }

                    $html .= "\n  </tbody>"
                            . "\n</table>";
                }
            }
        }

        return $html;
    }

}
