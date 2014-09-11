<?php

add_shortcode( 'mcl', 'mcl_status' );

function mcl_status() {
    // Get the categories
    $categories = get_categories( "exclude=" . get_option( 'mcl_settings_status_exclude_category' ) );

    // Get the sorted data
    $data_ongoing = get_all_tags_sorted( $categories, 0 );
    $data_complete = get_all_tags_sorted( $categories, 1 );

    // Create categories navigation
    $html = "<table border=\"1\"><colgroup><col width=\"1%\">";
    $html .= "<col width=\"99%\"></colgroup>";

    foreach ( $categories as $category ) {
        $count_ongoing = count_tags_of_category( $data_ongoing, $category->term_id );
        $count_complete = count_tags_of_category( $data_complete, $category->term_id );

        if ( $count_ongoing + $count_complete == 0 ) {
            continue;
        }

        $html .= "<tr><th colspan=\"2\"><div><strong><a href=\"#mediastatus-";
        $html .= "{$category->slug}\">{$category->name}</a></strong>";
        $html .= "</th></tr>";

        if ( $count_ongoing ) {
            $html .= "<tr><td nowrap><a href=\"#mediastatus-{$category->slug}-running\">Laufend</a></td><td>";

            foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                $html .= "<a href=\"#mediastatus-{$category->slug}-";
                $html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_ongoing[$category->term_id] ) ) ) ) {
                    $html .= " | ";
                }
            }

            $html .= "</td></tr>";
        }

        if ( $count_complete ) {
            $html .= "<tr><td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">Beendet</a></td><td>";

            foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                $html .= "<a href=\"#mediastatus-{$category->slug}-complete-";
                $html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_complete[$category->term_id] ) ) ) ) {
                    $html .= " | ";
                }
            }

            $html .= "</td></tr>";
        }
    }

    $html .= "</table>";

    // Create the tables
    foreach ( $categories as $category ) {
        $count_ongoing = count_tags_of_category( $data_ongoing, $category->term_id );
        $count_complete = count_tags_of_category( $data_complete, $category->term_id );

        $count = $count_ongoing + $count_complete;

        if ( $count == 0 ) {
            continue;
        }

        // Category header
        $html .= "<h4 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $html .= " ({$count})</h4><hr />";

        if ( $count_ongoing ) {
            $html .= "<h6 id=\"mediastatus-{$category->slug}-running\">Laufend";
            $html .= " ({$count_ongoing})</h6>";

            // Create the navigation
            $html .= "<div>";
            foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                $html .= "<a href=\"#mediastatus-{$category->slug}-";
                $html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_ongoing[$category->term_id] ) ) ) ) {
                    $html .= " | ";
                }
            }

            $html .= "</div><br />";

            // Table
            $html .= "<table border=\"1\"><colgroup><col width=\"98%\">";
            $html .= "<col width=\"1%\"><col width=\"1%\"></colgroup>";
            $html .= "<tr><th>Name</th><th nowrap>#</th>";
            $html .= "<th nowrap>Kapitel/Folge</th></tr>";

            foreach ( array_keys( $data_ongoing[$category->term_id] ) as $key ) {
                $html .= "<tr><th colspan=\"3\"><div id=\"mediastatus-";
                $html .= "{$category->slug}-" . strtolower( $key ) . "\">{$key}";
                $html .= " (" . count( $data_ongoing[$category->term_id][$key] ) . ")";
                $html .= "</div></th></tr>";

                foreach ( $data_ongoing[$category->term_id][$key] as $tag ) {
                    $last_post_data = get_last_post_of_tag_in_category( $tag->tag_id, $category->term_id );

                    if ( empty( $last_post_data ) ) {
                        continue;
                    }

                    $name = htmlspecialchars( $tag->name );
                    $name = str_replace( "&amp;", "&", $name );

                    $html .= "<tr><td><a href=\"{$tag->tag_link}\" title=\"";
                    $html .= "{$name}\">{$name}</a></td><th nowrap>{$tag->count}";
                    $html .= "</th><td nowrap>{$last_post_data}</td></tr>";
                }
            }

            $html .= "</table>";
        }

        if ( $count_complete ) {
            $html .= "<h6 id=\"mediastatus-{$category->slug}-complete\">Beendet";
            $html .= " ({$count_complete})</h6>";

            // Create the navigation
            $html .= "<div>";
            foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                $html .= "<a href=\"#mediastatus-{$category->slug}-";
                $html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_complete[$category->term_id] ) ) ) ) {
                    $html .= " | ";
                }
            }

            $html .= "</div><br />";

            // Table
            $html .= "<table border=\"1\"><col width=\"98%\"><col width=\"1%\">";
            $html .= "<col width=\"1%\">";
            $html .= "<tr><th>Name</th><th nowrap>#</th>";
            $html .= "<th nowrap>Kapitel/Folge</th></tr>";

            foreach ( array_keys( $data_complete[$category->term_id] ) as $key ) {
                $html .= "<tr><th colspan=\"3\"><div id=\"mediastatus-";
                $html .= "{$category->slug}-complete-" . strtolower( $key ) . "\">{$key}";
                $html .= " (" . count( $data_complete[$category->term_id][$key] ) . ")";
                $html .= "</div></th></tr>";

                foreach ( $data_complete[$category->term_id][$key] as $tag ) {
                    $last_post_data = get_last_post_of_tag_in_category( $tag->tag_id, $category->term_id );

                    if ( empty( $last_post_data ) ) {
                        continue;
                    }

                    $name = htmlspecialchars( $tag->name );
                    $name = str_replace( "&amp;", "&", $name );

                    $html .= "<tr><td><a href=\"{$tag->tag_link}\" title=\"";
                    $html .= "{$name}\">{$name}</a></td><th nowrap>{$tag->count}";
                    $html .= "</th><td nowrap>{$last_post_data}</td></tr>";
                }
            }

            $html .= "</table>";
        }
    }

    return $html;
}

?>