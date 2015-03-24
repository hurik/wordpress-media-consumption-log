<?php

/*
  Copyright (C) 2014 Andreas Giemza <andreas@giemza.net>

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

MclHooks::on_start();

class MclHooks {

    public static function on_start() {
        add_action( 'init', array( get_called_class(), 'init' ) );

        if ( !is_admin() ) {
            add_filter( 'get_post_tag', array( 'MclCommaInTags', 'comma_tag_filter' ) );
            add_filter( 'get_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
            add_filter( 'get_the_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
        }

        add_action( 'admin_bar_menu', array( get_called_class(), 'admin_bar_menu' ), 75 );
    }

    public static function init() {
        load_plugin_textdomain( 'media-consumption-log', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    public static function admin_bar_menu( $wp_admin_bar ) {
        $args = array(
            'id' => 'mcl_admin_bar_button',
            'title' => __( 'Quick Post', 'media-consumption-log' ),
            'href' => admin_url( "admin.php?page=mcl-quick-post" ),
            'meta' => array(
                'class' => 'mcl_admin_bar_button_class'
            )
        );

        $wp_admin_bar->add_node( $args );
    }

}

?>