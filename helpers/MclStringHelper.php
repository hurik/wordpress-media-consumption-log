<?php

class MclStringHelper {

    static function getLastConsumed( $title ) {
        // Explode the title
        $titleExploded = explode( " " . MclSettingsHelper::getOtherSeprator() . " ", $title );

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

    static function buildNextPostTitle( $last_post_title ) {
        $title = trim( $last_post_title );
        $title = preg_replace( "/[A-Z0-9]+ " . MclSettingsHelper::getOtherMclNumberTo() . " /", "", $title );
        $title = preg_replace( "/[A-Z0-9]+ " . MclSettingsHelper::getOtherMclNumberAnd() . " /", "", $title );

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

}

?>