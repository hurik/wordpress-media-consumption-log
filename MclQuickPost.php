<?php
/*
  Copyright (C) 2014-2025 Andreas Giemza <andreas@giemza.net>

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

class MclQuickPost
{

    public static function post_next()
    {
        if (isset($_POST["title"]) && isset($_POST["tag_id"]) && isset($_POST["cat_id"])) {
            $my_post = array(
                'post_title' => urldecode($_POST["title"]),
                'post_status' => 'publish',
                'tags_input' => get_tag($_POST["tag_id"])->name,
                'post_category' => array($_POST["cat_id"])
            );

            wp_insert_post($my_post);
        }

        wp_die();
    }

    public static function post_new()
    {
        if (isset($_POST["title"]) && isset($_POST["text"]) && isset($_POST["tag"]) && isset($_POST["cat_id"])) {
            $title = urldecode($_POST["title"]);

            $tag = urldecode($_POST["tag"]);

            if (empty($tag)) {
                $tag = $title;

                if (MclHelpers::is_monitored_serial_category($_POST["cat_id"])) {
                    $title_exploded = explode(MclSettings::get_other_separator(), $title);
                    $tag = str_replace(MclSettings::get_other_separator() . end($title_exploded), "", $title);
                }

                $tag = str_replace(", ", "--", $tag);
            }

            $my_post = array(
                'post_title' => $title,
                'post_content' => urldecode($_POST["text"]),
                'post_status' => 'publish',
                'tags_input' => $tag,
                'post_category' => array($_POST["cat_id"])
            );

            wp_insert_post($my_post);
        }

        wp_die();
    }

    public static function create_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'media-consumption-log'));
        }

        // Get the data
        $data = MclData::get_data();

        if (empty($data->categories)) {
            ?>
            <div class="wrap">
                <h2>Media Consumption Log - <?php _e('Quick Post', 'media-consumption-log'); ?></h2>

                <p><strong><?php _e('Nothing here yet!', 'media-consumption-log'); ?></strong></p>
            </div>
            <?php
            return;
        }

        // Create categories navigation
        $cat_nav_html = "";
        $alternate = false;

        foreach ($data->categories as $category) {
            if (!MclHelpers::is_monitored_serial_category($category->term_id)) {
                continue;
            }

            $cat_nav_html .= "\n  <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                    . "\n    <th nowrap valign=\"top\"><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></th>"
                    . "\n    <td>";

            if ($category->mcl_tags_count_ongoing == 0) {
                $cat_nav_html .= "-";
            } else {
                foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower($key) . "\">{$key}</a>";
                    $ak_on = array_keys($category->mcl_tags_ongoing);
                    if ($key != end($ak_on)) {
                        $cat_nav_html .= " | ";
                    }
                }
            }

            $cat_nav_html .= "</td>"
                    . "\n  </tr>";

            $alternate = !$alternate;
        }

        if (!empty($cat_nav_html)) {
            $cat_nav_html = "\n  <tr class=\"alternate\">"
                    . "\n    <th colspan=\"2\"><strong>" . __('Serials', 'media-consumption-log') . "</strong></th>"
                    . "\n  </tr>"
                    . $cat_nav_html;
        }

        $recurring_html = "";
        $recurring_alternate = $alternate;

        foreach ($data->categories as $category) {
            if (!MclHelpers::is_monitored_recurring_category($category->term_id)) {
                continue;
            }

            $alternate = !$alternate;

            $recurring_html .= "\n  <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                    . "\n    <th nowrap valign=\"top\"><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></th>"
                    . "\n    <td>";

            if ($category->mcl_tags_count_ongoing == 0) {
                $recurring_html .= "-";
            } else {
                foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                    $recurring_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower($key) . "\">{$key}</a>";
                    $ak_on = array_keys($category->mcl_tags_ongoing);
                    if ($key != end($ak_on)) {
                        $recurring_html .= " | ";
                    }
                }
            }

            $recurring_html .= "</td>"
                    . "\n  </tr>";
        }

        if (!empty($recurring_html)) {
            $cat_nav_html .= "\n  <tr" . ($recurring_alternate ? " class=\"alternate\"" : "") . ">"
                    . "\n    <th colspan=\"2\"><strong>" . __('Recurring', 'media-consumption-log') . "</strong></th>"
                    . "\n  </tr>"
                    . $recurring_html;

            $alternate = !$alternate;
        }

        $monitored_categories_non_serials = MclSettings::get_monitored_categories_non_serials();

        if (!empty($monitored_categories_non_serials)) {
            $cat_nav_html .= "\n  <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                    . "\n    <th colspan=\"2\"><strong>" . __('Non serials', 'media-consumption-log') . "</strong></th>"
                    . "\n  </tr>"
                    . "\n  <tr" . (!$alternate ? " class=\"alternate\"" : "") . ">"
                    . "\n    <td colspan=\"2\">";

            foreach ($data->categories as $category) {
                if (!MclHelpers::is_monitored_non_serial_category($category->term_id)) {
                    continue;
                }

                $last_non_serials = $category->term_id;
            }


            foreach ($data->categories as $category) {
                if (!MclHelpers::is_monitored_non_serial_category($category->term_id)) {
                    continue;
                }

                $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}\">{$category->name}</a>";
                if ($category->term_id != $last_non_serials) {
                    $cat_nav_html .= " | ";
                }
            }
        }

        $cat_nav_html .= "</td>"
                . "\n  </tr>";

        $cats_html = "";

        // Create the tables
        foreach ($data->categories as $category) {
            if (!MclHelpers::is_monitored_serial_category($category->term_id)) {
                continue;
            }

            // Category header
            $cats_html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />"
                    . "\n<table class=\"form-table\">"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Title', 'media-consumption-log') . " *</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-titel\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Text', 'media-consumption-log') . "</th>"
                    . "\n    <td><textarea id=\"{$category->term_id}-text\" rows=\"4\" style=\"width:100%;\"></textarea></td>"
                    . "\n  </tr>"
                    . "\n</table>"
                    . "\n<input type=\"hidden\" id=\"{$category->term_id}-tag\" value=\"\">"
                    . "\n<div align=\"right\"><input id=\"{$category->term_id}\" class=\"mcl_quick_post_new_entry button-primary button-large\" value=\"" . __('Publish', 'media-consumption-log') . "\" type=\"submit\"></div><br />";

            if ($category->mcl_tags_count_ongoing == 0) {
                continue;
            }

            // Create the navigation
            $cats_html .= "\n<div>";
            foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                $cats_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower($key) . "\">{$key}</a>";
                $ak_on = array_keys($category->mcl_tags_ongoing);
                if ($key != end($ak_on)) {
                    $cats_html .= " | ";
                }
            }

            $cats_html .= "</div><br />";

            // Table
            $cats_html .= "\n<table class=\"widefat\">"
                    . "\n  <colgroup>"
                    . "\n    <col width=\"2%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n  </colgroup>"
                    . "\n  <thead>"
                    . "\n    <tr>"
                    . "\n      <th></th>"
                    . "\n      <th><strong>" . __('Next Post', 'media-consumption-log') . "</strong></th>"
                    . "\n      <th><strong>" . __('Last Post', 'media-consumption-log') . "</strong></th>"
                    . "\n    </tr>"
                    . "\n  </thead>"
                    . "\n  <tbody>";

            $alternate = false;

            foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                $first = true;

                foreach ($category->mcl_tags_ongoing[$key] as $tag) {
                    $post_title = htmlspecialchars($tag->post_title);
                    $date = DateTime::createFromFormat("Y-m-d H:i:s", $tag->post_date);

                    $cats_html .= "\n    <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                            . "\n      <th nowrap valign=\"top\">" . ($first ? "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower($key) . "\"></div><div>{$key}</div>" : "") . "</th>"
                            . "\n      <td>" . self::build_next_post_titles($tag, $category) . "</td>"
                            . "\n      <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$post_title}</a> ({$date->format(get_option('time_format'))}, {$date->format(MclSettings::get_statistics_daily_date_format())})</td>"
                            . "\n    </tr>";

                    $first = false;
                    $alternate = !$alternate;
                }
            }

            $cats_html .= "\n  </tbody>"
                    . "\n</table>";
        }

        // Recurring
        foreach ($data->categories as $category) {
            if (!MclHelpers::is_monitored_recurring_category($category->term_id)) {
                continue;
            }

            // Category header
            $cats_html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />"
                    . "\n<table class=\"form-table\">"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Title', 'media-consumption-log') . " *</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-titel\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Text', 'media-consumption-log') . "</th>"
                    . "\n    <td><textarea id=\"{$category->term_id}-text\" rows=\"4\" style=\"width:100%;\"></textarea></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Tag', 'media-consumption-log') . "</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-tag\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n</table>"
                    . "\n<input type=\"hidden\" id=\"{$category->term_id}-tag\" value=\"\">"
                    . "\n<div align=\"right\"><input id=\"{$category->term_id}\" class=\"mcl_quick_post_new_entry button-primary button-large\" value=\"" . __('Publish', 'media-consumption-log') . "\" type=\"submit\"></div><br />";

            if ($category->mcl_tags_count_ongoing == 0) {
                continue;
            }

            // Create the navigation
            $cats_html .= "\n<div>";
            foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                $cats_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower($key) . "\">{$key}</a>";
                $ak_on = array_keys($category->mcl_tags_ongoing);
                if ($key != end($ak_on)) {
                    $cats_html .= " | ";
                }
            }

            $cats_html .= "</div><br />";

            // Table
            $cats_html .= "\n<table class=\"widefat\">"
                    . "\n  <colgroup>"
                    . "\n    <col width=\"2%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n    <col width=\"49%\">"
                    . "\n  </colgroup>"
                    . "\n  <thead>"
                    . "\n    <tr>"
                    . "\n      <th></th>"
                    . "\n      <th><strong>" . __('Next Post', 'media-consumption-log') . "</strong></th>"
                    . "\n      <th><strong>" . __('Last Post', 'media-consumption-log') . "</strong></th>"
                    . "\n    </tr>"
                    . "\n  </thead>"
                    . "\n  <tbody>";

            $alternate = false;

            foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                $first = true;

                foreach ($category->mcl_tags_ongoing[$key] as $tag) {
                    $post_title = htmlspecialchars($tag->post_title);
                    $date = DateTime::createFromFormat("Y-m-d H:i:s", $tag->post_date);

                    $title = str_replace("--", ", ", get_tag($tag->tag_term_id)->name);
                    $title_urlencode = urlencode($title);

                    $cats_html .= "\n    <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                            . "\n      <th nowrap valign=\"top\">" . ($first ? "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower($key) . "\"></div><div>{$key}</div>" : "") . "</th>"
                            . "\n      <td><a href=\"post-new.php?post_title=" . $title_urlencode . "&tag={$tag->tag_term_id}&category={$category->term_id}\">" . $title . "</a></td>"
                            . "\n      <td><a href=\"{$tag->post_link}\" title=\"{$post_title}\">{$post_title}</a> ({$date->format(get_option('time_format'))}, {$date->format(MclSettings::get_statistics_daily_date_format())})</td>"
                            . "\n    </tr>";

                    $first = false;
                    $alternate = !$alternate;
                }
            }

            $cats_html .= "\n  </tbody>"
                    . "\n</table>";
        }

        // Non serial
        foreach ($data->categories as $category) {
            if (!MclHelpers::is_monitored_non_serial_category($category->term_id)) {
                continue;
            }

            $cats_html .= "\n\n<div class=\"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />"
                    . "\n<table class=\"form-table\">"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Title', 'media-consumption-log') . " *</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-titel\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Text', 'media-consumption-log') . "</th>"
                    . "\n    <td><textarea id=\"{$category->term_id}-text\" rows=\"4\" style=\"width:100%;\"></textarea></td>"
                    . "\n  </tr>"
                    . "\n  <tr>"
                    . "\n    <th scope=\"row\">" . __('Tag', 'media-consumption-log') . "</th>"
                    . "\n    <td><input type=\"text\" id=\"{$category->term_id}-tag\" style=\"width:100%;\" /></td>"
                    . "\n  </tr>"
                    . "\n</table>"
                    . "\n<div align=\"right\"><input id=\"{$category->term_id}\" class=\"mcl_quick_post_new_entry button-primary button-large\" value=\"" . __('Publish', 'media-consumption-log') . "\" type=\"submit\"></div>";
        }
        ?>

        <div class="wrap">
            <h2>Media Consumption Log - <?php _e('Quick Post', 'media-consumption-log'); ?></h2><br />

            <table class="widefat">
                <colgroup>
                    <col width="1%">
                    <col width="99%">
                </colgroup>
                <thead>
                    <tr>
                        <th><strong><?php _e('Category', 'media-consumption-log'); ?></strong></th>
                        <th><strong><?php _e('Quick Navigation', 'media-consumption-log'); ?></strong></th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $cat_nav_html; ?>
                </tbody>
            </table>

            <?php echo $cats_html; ?>

            <div id="mcl_loading"></div><div class="mcl_css_back_to_top">^</div>
        </div>
        <?php
    }

    private static function build_next_post_titles($tag, $category)
    {
        $prefix = array();
        $numbers = array();

        $last_post_data = MclHelpers::parse_last_post_title($tag->post_title);

        preg_match_all("/[a-zA-Z]+/", $last_post_data[2], $prefix);
        preg_match_all("/\d+\.\d+|\d+/", $last_post_data[2], $numbers);

        if (count($prefix[0]) == count($numbers[0]) && count($prefix[0]) > 1) {
            $links = "";

            for ($i = count($numbers[0]) - 1; $i >= 0; $i--) {
                $title = $last_post_data[0] . $last_post_data[1] . " <strong>";

                for ($j = 0; $j < count($numbers[0]); $j++) {
                    $title .= $prefix[0][$j];

                    if ($i == $j) {
                        $title .= str_pad($numbers[0][$j] + 1, strlen($numbers[0][$j]), '0', STR_PAD_LEFT);
                    } elseif ($i < $j) {
                        $title .= str_pad(1, strlen($numbers[0][$j]), '0', STR_PAD_LEFT);
                    } else {
                        $title .= str_pad($numbers[0][$j], strlen($numbers[0][$j]), '0', STR_PAD_LEFT);
                    }
                }

                $title .= "</strong>";

                $title_without_html = strip_tags($title);
                $title_urlencode = urlencode($title_without_html);

                $links .= "<a class=\"mcl_css_quick_post\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_term_id}&category={$category->term_id}\">" . __('Edit before posting', 'media-consumption-log') . "</a>)";

                if ($i != 0) {
                    $links .= "<br />";
                }
            }

            return $links;
        } else {
            $title = $last_post_data[0] . $last_post_data[1] . " <strong>";

            if (is_numeric($last_post_data[2])) {
                $title .= (floor($last_post_data[2]) + 1);
            } else {
                $title .= (intval($last_post_data[2]) + 1);
            }

            $title .= "</strong>";

            $title_without_html = strip_tags($title);
            $title_urlencode = urlencode($title_without_html);

            $title05 = $last_post_data[0] . $last_post_data[1] . " <strong>";

            if (is_numeric($last_post_data[2])) {
                $title05 .= (floor($last_post_data[2]) + 0.5);
            } else {
                $title05 .= (intval($last_post_data[2]) + 0.5);
            }

            $title05 .= "</strong>";

            $title05_without_html = strip_tags($title05);
            $title05_urlencode = urlencode($title05_without_html);

            $text05 = str_replace($last_post_data[0] . $last_post_data[1] . " ", '', $title05);

            if (floatval($last_post_data[2]) - floor(floatval($last_post_data[2])) == 0) {
                return "<a class=\"mcl_css_quick_post\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_term_id}&category={$category->term_id}\">" . __('Edit before posting', 'media-consumption-log') . "</a> " . __('or', 'media-consumption-log') . " <a class=\"mcl_css_quick_post\" headline=\"{$title05_urlencode}\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$text05}</a>)";
            } else {
                return "<a class=\"mcl_css_quick_post\" headline=\"{$title_urlencode}\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">{$title}</a> (<a href=\"post-new.php?post_title={$title_urlencode}&tag={$tag->tag_term_id}&category={$category->term_id}\">" . __('Edit before posting', 'media-consumption-log') . "</a>)";
            }
        }
    }
}
