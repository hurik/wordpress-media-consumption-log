<?php

add_filter( 'load-post-new.php', 'mcl_new_post_with_cat_and_tag' );

function mcl_new_post_with_cat_and_tag() {
    if ( array_key_exists( 'tag', $_REQUEST ) || array_key_exists( 'category', $_REQUEST ) ) {
        add_action( 'wp_insert_post', 'mcl_insert_cat_and_tag_in_new_post' );
    }
}

function mcl_insert_cat_and_tag_in_new_post( $post_id ) {
    wp_set_post_tags( $post_id, get_tag( $_REQUEST['tag'] )->name );
    wp_set_post_categories( $post_id, array( $_REQUEST['category'] ) );
}

function mcl_quick_post() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    // Get the categories
    $categories = get_categories( 'exclude=45,75' );

    // Get the sorted data
    $data = get_all_tags_sorted( $categories, 0 );

    // Create categories navigation
    $cat_nav_html = "";

    foreach ( array_keys( $data ) as $cat_key ) {
        $category = get_category( $cat_key );

        $cat_nav_html .= "<tr><th><div><a href=\"#mediastatus-";
        $cat_nav_html .= "{$category->slug}\">{$category->name}</a>";
        $cat_nav_html .= "</tr></th>";
        $cat_nav_html .= "<tr><td>";
        foreach ( array_keys( $data[$category->term_id] ) as $key ) {
            $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $cat_nav_html .= strtolower( $key ) . "\">{$key}</a>";
            if ( $key != end( (array_keys( $data[$category->term_id] ) ) ) ) {
                $cat_nav_html .= " | ";
            }
        }

        $cat_nav_html .= "</tr></td>";
    }

    $cats_html = "";

    // Create the tables
    foreach ( array_keys( $data ) as $cat_key ) {
        $category = get_category( $cat_key );

        $count = count_tags_of_category( $data, $category->term_id );

        // Category header
        $cats_html .= "<h3 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $cats_html .= " ({$count})</h3><hr />";

        // Create the navigation
        $cats_html .= "<div>";
        foreach ( array_keys( $data[$category->term_id] ) as $key ) {
            $cats_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $cats_html .= strtolower( $key ) . "\">{$key}</a>";
            if ( $key != end( (array_keys( $data[$category->term_id] ) ) ) ) {
                $cats_html .= " | ";
            }
        }

        $cats_html .= "</div><br />";

        // Table
        $cats_html .= "<table class=\"widefat fixed\">";
        $cats_html .= "<tr><th><strong>Next Post</strong></th><th><strong>Last Post</strong></th></tr>";
        foreach ( array_keys( $data[$category->term_id] ) as $key ) {
            $cats_html .= "<tr><th colspan=\"2\"><div id=\"mediastatus-";
            $cats_html .= "{$category->slug}-" . strtolower( $key ) . "\">{$key}";
            $cats_html .= " (" . count( $data[$category->term_id][$key] ) . ")";
            $cats_html .= "</div></th></tr>";
            foreach ( $data[$category->term_id][$key] as $tag ) {
                $last_post_data = get_last_post_of_tag_in_category_data( $tag->tag_id, $category->term_id );

                if ( empty( $last_post_data ) ) {
                    continue;
                }

                $title = trim( $last_post_data->post_title );
                $title = preg_replace( "/[A-Z0-9]+ bis /", "", $title );
                $title = preg_replace( "/[A-Z0-9]+ und /", "", $title );

                $title_explode = explode( ' ', $title );
                $number = end( $title_explode );

                if ( is_numeric( $number ) ) {
                    $number = floatval( $number );
                    $number++;
                    $number = floor( $number );
                }

                if ( preg_match( '/[SE]/', $number ) ) {
                    $number++;
                }

                $title = substr( $title, 0, strrpos( $title, " " ) );

                $title .= " {$number}";

                $title_urlencode = urlencode( $title );

                $link = get_permalink( $last_post_data->ID );

                $cats_html .= "<tr><td><a href=\"post-new.php?post_title=";
                $cats_html .= "{$title_urlencode}&tag={$tag->tag_id}";
                $cats_html .= "&category={$category->term_id}\" title=\"";
                $cats_html .= "{$title}\">{$title}</a></td><td><a ";
                $cats_html .= "href='{$link}' title='";
                $cats_html .= "{$last_post_data->post_title}'>";
                $cats_html .= "{$last_post_data->post_title}</a></td></tr>";
            }
        }

        $cats_html .= "</table>";
    }
    ?>
    <div class="wrap">
        <h2>Media Consumption Log - Quick Post</h2>

        <h3>Navigation</h3>
        <table class="widefat">
            <?php
            echo $cat_nav_html;
            ?>
        </table>

        <?php
        echo $cats_html;
        ?>
    </div>	
    <?php
}

?>