<?php

function change_finished_status( $tag_id, $cat_id, $finished ) {
    global $wpdb;

    $tags = $wpdb->get_results( "REPLACE INTO wp_mcl_finished SET tag_id = $tag_id, cat_id = $cat_id, finished = $finished" );
}

function mcl_finished() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    if ( isset( $_GET["tag_id"] ) && isset( $_GET["cat_id"] ) && isset( $_GET["finished"] ) ) {
        change_finished_status( $_GET["tag_id"], $_GET["cat_id"], $_GET["finished"] );
    }

    // Get the categories
    $categories = get_categories( 'exclude=45,75' );

    // Get the sorted data
    $data = get_all_tags_sorted( $categories, 0 );
    $data_finished = get_all_tags_sorted( $categories, 1 );

    // Create categories navigation
    $cat_nav_html = "";

    foreach ( $categories as $category ) {
        $count_running = count_tags_of_category( $data, $category->term_id );
        $count_finished = count_tags_of_category( $data_finished, $category->term_id );

        if ( $count_running + $count_finished == 0 ) {
            continue;
        }

        $cat_nav_html .= "<tr><th colspan=\"2\"><div><strong><a href=\"#mediastatus-";
        $cat_nav_html .= "{$category->slug}\">{$category->name}</a></strong>";
        $cat_nav_html .= "</th></tr>";

        if ( $count_running ) {
            $cat_nav_html .= "<tr><td nowrap><a href=\"#mediastatus-{$category->slug}-running\">Laufend</a></td><td>";

            foreach ( array_keys( $data[$category->term_id] ) as $key ) {
                $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-";
                $cat_nav_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data[$category->term_id] ) ) ) ) {
                    $cat_nav_html .= " | ";
                }
            }

            $cat_nav_html .= "</td></tr>";
        }

        if ( $count_finished ) {
            $cat_nav_html .= "<tr><td nowrap><a href=\"#mediastatus-{$category->slug}-finished\">Beendet</a></td><td>";

            foreach ( array_keys( $data_finished[$category->term_id] ) as $key ) {
                $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-finished-";
                $cat_nav_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_finished[$category->term_id] ) ) ) ) {
                    $cat_nav_html .= " | ";
                }
            }

            $cat_nav_html .= "</td></tr>";
        }
    }


    $cats_html = "";

    // Create the tables
    foreach ( $categories as $category ) {
        $count_running = count_tags_of_category( $data, $category->term_id );
        $count_finished = count_tags_of_category( $data_finished, $category->term_id );

        $count = $count_running + $count_finished;

        if ( $count == 0 ) {
            continue;
        }

        // Category header
        $cats_html .= "<h3 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $cats_html .= " ({$count})</h3><hr />";

        if ( $count_running ) {
            $cats_html .= "<h4 id=\"mediastatus-{$category->slug}-running\">Laufend";
            $cats_html .= " ({$count_running})</h4>";

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
            $cats_html .= "\n<table class=\"widefat\"><colgroup><col width=\"1%\">";
            $cats_html .= "<col width=\"99%\"></colgroup>";
            $cats_html .= "\n<tr><th><strong>Change</strong></th><th><strong>Name</strong></th></tr>";

            foreach ( array_keys( $data[$category->term_id] ) as $key ) {
                $cats_html .= "\n<tr><th><div id=\"mediastatus-";
                $cats_html .= "{$category->slug}-" . strtolower( $key ) . "\">{$key}";
                $cats_html .= " (" . count( $data[$category->term_id][$key] ) . ")";
                $cats_html .= "</div></th></tr>";

                foreach ( $data[$category->term_id][$key] as $tag ) {
                    $name = htmlspecialchars( $tag->name );
                    $name = str_replace( "&amp;", "&", $name );

                    $cats_html .= "<tr><td nowrap><a href=\"admin.php?page=mcl-finished&tag_id={$tag->tag_id}&cat_id={$tag->cat_id}&finished=1\" title=\"Status ändern!\">Change!</a></td><td><a href=\"{$tag->tag_link}\" title=\"";
                    $cats_html .= "{$name}\">{$name}</a></td></tr>";
                }
            }

            $cats_html .= "</table>";
        }

        if ( $count_finished ) {
            $cats_html .= "<h4 id=\"mediastatus-{$category->slug}-finished\">Beendet";
            $cats_html .= " ({$count_finished})</h4>";

            // Create the navigation
            $cats_html .= "<div>";
            foreach ( array_keys( $data_finished[$category->term_id] ) as $key ) {
                $cats_html .= "<a href=\"#mediastatus-{$category->slug}-finished-";
                $cats_html .= strtolower( $key ) . "\">{$key}</a>";
                if ( $key != end( (array_keys( $data_finished[$category->term_id] ) ) ) ) {
                    $cats_html .= " | ";
                }
            }

            $cats_html .= "</div><br />";

            // Table
            $cats_html .= "\n<table class=\"widefat\"><colgroup><col width=\"1%\">";
            $cats_html .= "<col width=\"99%\"></colgroup>";
            $cats_html .= "\n<tr><th><strong>Change</strong></th><th><strong>Name</strong></th></tr>";

            foreach ( array_keys( $data_finished[$category->term_id] ) as $key ) {
                $cats_html .= "<tr><th><div id=\"mediastatus-";
                $cats_html .= "{$category->slug}-finished-" . strtolower( $key ) . "\">{$key}";
                $cats_html .= " (" . count( $data_finished[$category->term_id][$key] ) . ")";
                $cats_html .= "</div></th></tr>";

                foreach ( $data_finished[$category->term_id][$key] as $tag ) {
                    $name = htmlspecialchars( $tag->name );
                    $name = str_replace( "&amp;", "&", $name );

                    $cats_html .= "<tr><td nowrap><a href=\"admin.php?page=mcl-finished&tag_id={$tag->tag_id}&cat_id={$tag->cat_id}&finished=0\" title=\"Status ändern!\">Change!</a></td><td><a href=\"{$tag->tag_link}\" title=\"";
                    $cats_html .= "{$name}\">{$name}</a></td></tr>";
                }
            }

            $cats_html .= "</table>";
        }
    }
    ?>
    <div class="wrap">
        <h2>Media Consumption Log - Finished</h2>

        <h3>Navigation</h3>
        <table class="widefat">
            <colgroup>
                <col width="1%">
                <col width="99%">
            </colgroup>
            <?php echo $cat_nav_html; ?>
        </table>

        <?php echo $cats_html; ?>
    </div>	
    <?php
}

?>