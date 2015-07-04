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

MclHooks::on_start();

class MclHooks {

    public static function on_start() {
        add_action( 'init', array( get_called_class(), 'init' ) );

        if ( !is_admin() ) {
            add_filter( 'get_post_tag', array( 'MclCommaInTags', 'comma_tag_filter' ) );
            add_filter( 'get_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );
            add_filter( 'get_the_terms', array( 'MclCommaInTags', 'comma_tags_filter' ) );

            add_filter( 'the_posts', array( get_called_class(), 'conditionally_add_style' ) );
        }

        add_action( 'admin_bar_menu', array( get_called_class(), 'admin_bar_menu' ), 75 );
    }

    public static function init() {
        load_plugin_textdomain( 'media-consumption-log', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }

    public static function admin_bar_menu( $wp_admin_bar ) {
        $wp_admin_bar->add_menu( array(
            'parent' => 'new-content',
            'id' => 'mcl_admin_bar_button',
            'title' => __( 'Quick Post', 'media-consumption-log' ),
            'href' => admin_url( "admin.php?page=mcl-quick-post" ),
        ) );
    }

    public static function conditionally_add_style( $posts ) {
        if ( empty( $posts ) ) {
            return $posts;
        }

        $shortcode_mcl = false;
        $shortcode_mcl_stats = false;

        foreach ( $posts as $post ) {
            if ( !$shortcode_mcl && stripos( $post->post_content, '[mcl]' ) !== false ) {
                $shortcode_mcl = true;
            }

            if ( !$shortcode_mcl_stats && stripos( $post->post_content, '[mcl-stats]' ) !== false ) {
                $shortcode_mcl_stats = true;
            }
        }

        if ( $shortcode_mcl ) {
            wp_enqueue_script( 'mcl-back-to-top', plugin_dir_url( __FILE__ ) . 'js/mcl_back_to_top.js', array( 'jquery' ) );
            wp_enqueue_style( 'mcl-back-to-top', plugin_dir_url( __FILE__ ) . 'css/mcl_back_to_top.css' );
            wp_enqueue_style( 'mcl-table', plugin_dir_url( __FILE__ ) . 'css/mcl_table.css' );
        }

        if ( $shortcode_mcl_stats ) {
            wp_enqueue_style( 'mcl-table', plugin_dir_url( __FILE__ ) . 'css/mcl_table.css' );
        }

        return $posts;
    }

}
