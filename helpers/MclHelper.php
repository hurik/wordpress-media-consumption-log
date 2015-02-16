<?php

class MclHelper {

    public static function build_all_categories_string( $categories, $with_id = false ) {
        $categories_string = "";

        $forelast_cat = $categories[count( $categories ) - 2];
        $last_cat = end( $categories );

        foreach ( $categories as $category ) {
            if ( $category != $last_cat ) {
                $categories_string .= "{$category->name}";

                if ( $with_id ) {
                    $categories_string .= " ({$category->term_id})";
                }

                if ( $category != $forelast_cat ) {
                    $categories_string .= ", ";
                }
            } else {
                $categories_string .= " " . __( 'and', 'media-consumption-log' ) . " {$category->name}";

                if ( $with_id ) {
                    $categories_string .= " ({$category->term_id})";
                }
            }
        }

        return $categories_string;
    }

    public static function parse_last_post_title( $last_post_title ) {
        $title = trim( $last_post_title );
        $title_exploded = explode( MclSettings::get_other_separator(), $title );

        $status = end( $title_exploded );
        $status_exploded = explode( " ", $status );

        $first_part = str_replace( $status, "", $title );

        if ( count( $status_exploded ) < 2 ) {
            return [ $first_part, $status ];
        }

        $status_first_part = $status_exploded[0];
        $status_last_part = end( $status_exploded );

        return [ $first_part, $status_first_part, $status_last_part ];
    }

    public static function is_monitored_category( $cat_id ) {
        $monitored_categories_series = explode( ",", MclSettings::get_monitored_categories_series() );
        $monitored_categories_non_series = explode( ",", MclSettings::get_monitored_categories_non_series() );

        if ( in_array( $cat_id, array_merge( $monitored_categories_series, $monitored_categories_non_series ) ) ) {
            return true;
        } else {
            return false;
        }
    }

}

?>