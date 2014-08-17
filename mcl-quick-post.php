<?php

add_filter('load-post-new.php', 'mclLoadPostWithCategoryAndTag');

function mclInsertCategoryAndTagToPost($post_id) {
    wp_set_post_tags($post_id, get_tag($_REQUEST['tag'])->name);
    wp_set_post_categories($post_id, array($_REQUEST['category']));
}

function mclLoadPostWithCategoryAndTag() {
    if (array_key_exists('tag', $_REQUEST) || array_key_exists('category', $_REQUEST)) {
        add_action('wp_insert_post', 'mclInsertCategoryAndTagToPost');
    }
}

add_action('admin_bar_menu', 'mclAdminBarButton', 75);

function mclAdminBarButton($wp_admin_bar) {
    $args = array(
        'id' => 'mcl_admin_bar_button',
        'title' => 'MCL - Quick Post',
        'href' => admin_url("edit.php?page=mcl"),
        'meta' => array(
            'class' => 'mcl_admin_bar_button_class'
        )
    );
    $wp_admin_bar->add_node($args);
}

add_action('admin_menu', 'mclQuickPostMenu');

function mclQuickPostMenu() {
    add_posts_page('MCL - Quick Post', 'MCL - Quick Post', 'manage_options', 'mcl', 'mclQuickPost');
}

/** Step 3. */
function mclQuickPost() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // Get the categories
    $categories = get_categories('exclude=45,75');

    // Get the sorted data
    $data = getAllTagsSortedByCategoryAndName($categories);

    // Create categories navigation
    $cat_nav_html = "";

    foreach ($categories as $category) {
        $cat_nav_html .= "<tr><th><div><a href=\"#mediastatus-";
        $cat_nav_html .= "{$category->slug}\">{$category->name}</a>";
        $cat_nav_html .= "</tr></th>";
        $cat_nav_html .= "<tr><td>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $cat_nav_html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id])))) {
                $cat_nav_html .= " | ";
            }
        }

        $cat_nav_html .= "</tr></td>";
    }

    $lists_html = "";

    // Create the tables
    foreach ($categories as $category) {
        $count = countTagsOfCategory($data, $category->term_id);

        // Category header
        $lists_html .= "<h3 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $lists_html .= " ({$count})</h3><hr />";

        // Create the navigation
        $lists_html .= "<div>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $lists_html .= "<a href=\"#mediastatus-{$category->slug}-";
            $lists_html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id])))) {
                $lists_html .= " | ";
            }
        }

        $lists_html .= "</div><br />";

        // Table
        $lists_html .= "<table class=\"widefat fixed\">";
        $lists_html .= "<tr><th><strong>Next Post</strong></th><th><strong>Last Post</strong></th></tr>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $lists_html .= "<tr><th colspan=\"2\"><div id=\"mediastatus-";
            $lists_html .= "{$category->slug}-" . strtolower($key) . "\">{$key}";
            $lists_html .= " (" . count($data[$category->term_id][$key]) . ")";
            $lists_html .= "</div></th></tr>";
            foreach ($data[$category->term_id][$key] as $tag) {
                $last_post_data = get_latest_post_of_tag_in_category_data($tag->tag_id, $category->term_id);

                if (empty($last_post_data)) {
                    continue;
                }

                $title = trim($last_post_data->post_title);
                $title = preg_replace("/[A-Z0-9]+ bis /", "", $title);
                $title = preg_replace("/[A-Z0-9]+ und /", "", $title);

                $title_explode = explode(' ', $title);
                $number = end($title_explode);

                if (is_numeric($number)) {
                    $number = floatval($number);
                    $number++;
                    $number = floor($number);
                }

                if (preg_match('/[SE]/', $number)) {
                    $number++;
                }

                $title = substr($title, 0, strrpos($title, " "));

                $title .= " {$number}";

                $title_get = urlencode($title);

                $link = get_permalink($last_post_data->ID);

                $lists_html .= "<tr><td><a href=\"post-new.php?post_title=";
                $lists_html .= "{$title_get}&tag={$tag->tag_id}";
                $lists_html .= "&category={$category->term_id}\" title=\"";
                $lists_html .= "{$title}\">{$title}</a></td><td><a ";
                $lists_html .= "href='{$link}' title='";
                $lists_html .= "{$last_post_data->post_title}'>";
                $lists_html .= "{$last_post_data->post_title}</a></td></tr>";
            }
        }

        $lists_html .= "</table>";
    }
    ?>
    <div class="wrap">
        <h2>Media Consumption Log - Quick Post</h2>

        <h3>Navigation</h3>
        <table class="widefat fixed">
            <?php
            echo $cat_nav_html;
            ?>
        </table>

        <?php
        echo $lists_html;
        ?>
    </div>	
    <?php
}

?>