<?php

class MclStringHelper {

    public static function get_last_consumed( $title ) {
        // Explode the title
        $titleExploded = explode( " " . MclSettings::getOtherSeprator() . " ", $title );

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

    public static function build_next_post_title( $last_post_title ) {
        $title = trim( $last_post_title );
        $title = preg_replace( "/[A-Z0-9]+ " . MclSettings::getOtherMclNumberTo() . " /", "", $title );
        $title = preg_replace( "/[A-Z0-9]+ " . MclSettings::getOtherMclNumberAnd() . " /", "", $title );

        $title_explode = explode( ' ', $title );
        $number = end( $title_explode );

        if ( is_numeric( $number ) ) {
            $number = floatval( $number );
            $number++;
            $number = floor( $number );
        }

        if ( preg_match( '/[SE]/', $number ) || preg_match( '/[VC]/', $number ) || preg_match( '/[CP]/', $number ) ) {
            $number++;
        }

        $title = substr( $title, 0, strrpos( $title, " " ) );

        $title .= " {$number}";

        return $title;
    }

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

    public static function echo_checked_or_unchecked( $status ) {
        if ( $status ) {
            _e( 'Checked', 'media-consumption-log' );
        } else {
            _e( 'Unchecked', 'media-consumption-log' );
        }
    }

}

?>