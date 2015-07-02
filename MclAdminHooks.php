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

if ( is_admin() ) {
    MclAdminHooks::on_start();
}

class MclAdminHooks {

    public static function on_start() {
        add_action( 'plugins_loaded', array( get_called_class(), 'update_db_check' ) );

        add_action( 'admin_init', array( get_called_class(), 'admin_init' ) );
        add_action( 'admin_menu', array( get_called_class(), 'admin_menu' ) );

        add_action( 'save_post', array( get_called_class(), 'save_post' ) );
        add_action( 'transition_post_status', array( get_called_class(), 'transition_post_status' ), 10, 3 );
        add_action( 'before_delete_post', array( get_called_class(), 'before_delete_post' ) );

        add_action( 'edit_term', array( get_called_class(), 'edit_term' ), 10, 3 );

        add_filter( 'load-post-new.php', array( get_called_class(), 'load_post_new_php' ) );

        add_action( 'wp_ajax_mcl_complete', array( 'MclSerialStatus', 'change_complete_status' ) );
        add_action( 'wp_ajax_mcl_quick_post_next', array( 'MclQuickPost', 'post_next' ) );
        add_action( 'wp_ajax_mcl_quick_post_new', array( 'MclQuickPost', 'post_new' ) );
        add_action( 'wp_ajax_mcl_rebuild_data', array( 'MclSettings', 'rebuild_data' ) );
    }

    public static function update_db_check() {
        global $wpdb;

        // Update DB from version 1 to 2
        if ( get_option( 'mcl_db_version' ) == 1 ) {
            $wpdb->query( "RENAME TABLE `{$wpdb->prefix}mcl_complete` TO `{$wpdb->prefix}mcl_status`" );
            $wpdb->query( "ALTER TABLE `{$wpdb->prefix}mcl_status` CHANGE `complete` `status` TINYINT(1) NOT NULL" );
            update_option( 'mcl_db_version', 2 );
        }
    }

    public static function admin_init() {
        MclSettings::register_settings();
    }

    public static function admin_menu() {
        // Add menu
        add_menu_page( 'MCL', 'MCL', 'manage_options', 'mcl-quick-post' );
        $page_hook_quick_post = add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Quick Post', 'media-consumption-log' ), __( 'Quick Post', 'media-consumption-log' ), 'manage_options', 'mcl-quick-post', array( 'MclQuickPost', 'create_page' ) );
        $page_hook_serial_status = add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Serial Status', 'media-consumption-log' ), __( 'Serial Status', 'media-consumption-log' ), 'manage_options', 'mcl-serial-status', array( 'MclSerialStatus', 'create_page' ) );
        $page_hook_forgotten = add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Forgotten', 'media-consumption-log' ), __( 'Forgotten', 'media-consumption-log' ), 'manage_options', 'mcl-forgotten', array( 'MclForgotten', 'create_page' ) );
        $page_hook_settings = add_submenu_page( 'mcl-quick-post', 'MCL - ' . __( 'Settings', 'media-consumption-log' ), __( 'Settings', 'media-consumption-log' ), 'manage_options', 'mcl-settings', array( 'MclSettings', 'create_page' ) );

        add_action( 'admin_print_scripts-' . $page_hook_quick_post, array( get_called_class(), 'enqueue_scripts_and_styles' ) );
        add_action( 'admin_print_scripts-' . $page_hook_serial_status, array( get_called_class(), 'enqueue_scripts_and_styles' ) );
        add_action( 'admin_print_scripts-' . $page_hook_forgotten, array( get_called_class(), 'enqueue_scripts_and_styles' ) );
        add_action( 'admin_print_scripts-' . $page_hook_settings, array( get_called_class(), 'enqueue_scripts_and_styles' ) );
    }

    public static function enqueue_scripts_and_styles() {
        wp_enqueue_script( 'mcl_admin_js', plugin_dir_url( __FILE__ ) . 'js/mcl_admin.js', array( 'jquery' ) );
        wp_localize_script( 'mcl_admin_js', 'mcl_js_strings', array( 'title_empty_error' => __( 'Title can\'t be empty!', 'media-consumption-log' ) ) );

        wp_enqueue_style( 'mcl_admin_css', plugin_dir_url( __FILE__ ) . 'css/mcl_admin.css' );

        wp_enqueue_script( 'mcl-back-to-top', plugin_dir_url( __FILE__ ) . 'js/mcl_back_to_top.js', array( 'jquery' ) );
        wp_enqueue_style( 'mcl-back-to-top', plugin_dir_url( __FILE__ ) . 'css/mcl_back_to_top.css' );
    }

    public static function save_post( $post_id ) {
        $cats = get_the_category( $post_id );

        if ( count( $cats ) != 0 ) {
            if ( MclHelpers::is_monitored_category( $cats[0]->term_id ) ) {
                MclNumber::check_mcl_number_after_saving( $post_id );

                if ( get_post_status( $post_id ) == 'publish' ) {
                    MclData::update_data();
                }
            } else {
                delete_post_meta( $post_id, "mcl_number" );
            }
        }
    }

    public static function transition_post_status( $new_status, $old_status, $post ) {
        $cats = get_the_category( $post->ID );

        if ( count( $cats ) != 0 ) {
            if ( MclHelpers::is_monitored_category( $cats[0]->term_id ) ) {
                if ( ($old_status == 'publish' && $new_status != 'publish' ) ) {
                    MclData::update_data();
                }
            }
        }
    }

    public static function before_delete_post( $post_id ) {
        $cats = get_the_category( $post_id );

        if ( count( $cats ) != 0 ) {
            if ( MclHelpers::is_monitored_category( $cats[0]->term_id ) ) {
                add_action( 'delete_post', array( get_called_class(), 'delete_post' ) );
            }
        }
    }

    public static function delete_post( $post_id ) {
        MclData::update_data();
    }

    public static function edit_term( $term_id, $tt_id, $taxonomy ) {
        // Check if term is a category and if it is a monitored category
        if ( $taxonomy == "category" && MclHelpers::is_monitored_category( $term_id ) ) {
            // Rebuild data to updated changed category in mcl_data
            MclData::update_data();
        }
    }

    public static function load_post_new_php( $post_id ) {
        add_action( 'wp_insert_post', array( get_called_class(), 'load_post_new_php_to_wp_insert_post' ) );
    }

    public static function load_post_new_php_to_wp_insert_post( $post_id ) {
        // Set category and tag in new post with link
        if ( isset( $_REQUEST['category'] ) ) {
            wp_set_post_categories( $post_id, array( $_REQUEST['category'] ) );
        }

        if ( isset( $_REQUEST['tag'] ) ) {
            wp_set_post_tags( $post_id, get_tag( $_REQUEST['tag'] )->name );
        }

        // Add default custom fields
        MclNumber::add_default_custom_field_in_new_post( $post_id );
    }

    public static function register_activation_hook() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mcl_status';

        if ( $wpdb->get_var( "SHOW TABLES LIKE {$table_name}" ) != $table_name ) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

            if ( !empty( $wpdb->collate ) ) {
                $charset_collate .= " COLLATE {$wpdb->collate}";
            }

            $sql = "
                CREATE TABLE {$table_name} (
                    `tag_id` bigint(20) unsigned NOT NULL,
                    `cat_id` bigint(20) unsigned NOT NULL,
                    `status` tinyint(1) NOT NULL,
                    PRIMARY KEY (`tag_id`,`cat_id`)
                ) $charset_collate;
            ";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            add_option( 'mcl_db_version', 2 );
        }
    }

}
