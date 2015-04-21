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

class MclCommaInTags {

    public static function comma_tag_filter( $tag_arr ) {
        $tag_arr_new = $tag_arr;

        if ( property_exists( $tag_arr, "taxonomy" ) &&
                $tag_arr->taxonomy == 'post_tag' &&
                strpos( $tag_arr->name, '--' ) ) {
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
