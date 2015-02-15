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