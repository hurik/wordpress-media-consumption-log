<?php

class MclCommaInTags {

    public static function comma_tag_filter( $tag_arr ) {
        $tag_arr_new = $tag_arr;
        if ( $tag_arr->taxonomy == 'post_tag' && strpos( $tag_arr->name, '--' ) ) {
            $tag_arr_new->name = str_replace( '--', ', ', $tag_arr->name );
        }
        return $tag_arr_new;
    }

    public static function comma_tags_filter( $tags_arr ) {
        $tags_arr_new = array();
        foreach ( $tags_arr as $tag_arr ) {
            $tags_arr_new[] = self::comma_tag_filter( $tag_arr );
        }
        return $tags_arr_new;
    }

}

?>