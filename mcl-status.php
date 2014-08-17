<?php

add_shortcode('mcl', 'mclStatus');

function mclStatus() {
    // Get the categories
    $categories = get_categories('exclude=45,75');

    // Get the sorted data
    $data = getAllTagsSortedByCategoryAndName($categories);

    // Create categories navigation
    $html = "<table border=\"1\">";
    foreach ($categories as $category) {
        $html .= "<tr><td><div><strong><a href=\"#mediastatus-";
        $html .= "{$category->slug}\">{$category->name}</a></strong>";
        $html .= "</tr></td>";
        $html .= "<tr><td>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<a href=\"#mediastatus-{$category->slug}-";
            $html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id])))) {
                $html .= " | ";
            }
        }

        $html .= "</tr></td>";
    }

    $html .= "</table>";

    // Create the tables
    foreach ($categories as $category) {
        $count = countTagsOfCategory($data, $category->term_id);

        // Category header
        $html .= "<h4 id=\"mediastatus-{$category->slug}\">{$category->name}";
        $html .= " ({$count})</h4><hr />";

        // Create the navigation
        $html .= "<div>";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<a href=\"#mediastatus-{$category->slug}-";
            $html .= strtolower($key) . "\">{$key}</a>";
            if ($key != end((array_keys($data[$category->term_id])))) {
                $html .= " | ";
            }
        }

        $html .= "</div><br />";

        // Table
        $html .= "<table border=\"1\"><col width=\"98%\"><col width=\"1%\">";
        $html .= "<col width=\"1%\">";
        foreach (array_keys($data[$category->term_id]) as $key) {
            $html .= "<tr><th colspan=\"3\"><div id=\"mediastatus-";
            $html .= "{$category->slug}-" . strtolower($key) . "\">{$key}";
            $html .= " (" . count($data[$category->term_id][$key]) . ")";
            $html .= "</div></th></tr>";
            $html .= "<tr><th>Name</th><th nowrap>#</th>";
            $html .= "<th nowrap>Kapitel/Folge</th></tr>";
            foreach ($data[$category->term_id][$key] as $tag) {
                $last_post_data = get_latest_post_of_tag_in_category($tag->tag_id, $category->term_id);

                if (empty($last_post_data)) {
                    continue;
                }

                $name = htmlspecialchars($tag->name);
                $name = str_replace("&amp;", "&", $name);

                $html .= "<tr><td><a href=\"{$tag->tag_link}\" title=\"";
                $html .= "{$name}\">{$name}</a></td><th nowrap>{$tag->count}";
                $html .= "</th><td nowrap>{$last_post_data}</td></tr>";
            }
        }

        $html .= "</table>";
    }

    return $html;
}

?>