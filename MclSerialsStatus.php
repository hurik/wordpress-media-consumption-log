<?php
/*
  Copyright (C) 2014-2018 Andreas Giemza <andreas@giemza.net>

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

class MclSerialsStatus
{

    const RUNNING = 0;
    const COMPLETE = 1;
    const ABANDONED = 2;

    public static function change_complete_status()
    {
        global $wpdb;

        if (isset($_POST["tag_id"]) && isset($_POST["cat_id"]) && isset($_POST["complete"])) {
            if (!empty($_POST["complete"])) {
                $wpdb->get_results("
                    REPLACE INTO {$wpdb->prefix}mcl_status
                    SET tag_id = '{$_POST["tag_id"]}',
                        cat_id = '{$_POST["cat_id"]}',
                        status = '{$_POST["complete"]}'
                ");
            } else {
                $wpdb->get_results("
                    DELETE
                    FROM {$wpdb->prefix}mcl_status
                    WHERE tag_id = '{$_POST["tag_id"]}'
                      AND cat_id = '{$_POST["cat_id"]}'
                ");
            }

            MclData::update_data();
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

        if (!$data->cat_serial_ongoing && !$data->cat_serial_complete && !$data->cat_serial_abandoned) {
            ?>
            <div class="wrap">
                <h2>Media Consumption Log - <?php _e('Serials Status', 'media-consumption-log'); ?></h2>

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

            if ($category->mcl_tags_count == 0) {
                continue;
            }

            $first = true;

            if ($category->mcl_tags_count_ongoing) {
                $cat_nav_html .= "\n  <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                        . "\n    <th nowrap valign=\"top\">" . ($first ? "<strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong>" : "") . "</th>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-ongoing\">" . __('Running', 'media-consumption-log') . "</a></td>"
                        . "\n    <td>";

                foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-" . strtolower($key) . "\">{$key}</a>";
                    $ak_on = array_keys($category->mcl_tags_ongoing);
                    if ($key != end($ak_on)) {
                        $cat_nav_html .= " | ";
                    }
                }

                $cat_nav_html .= "</td>"
                        . "\n  </tr>";

                $first = false;
                $alternate = !$alternate;
            }

            if ($category->mcl_tags_count_complete) {
                $cat_nav_html .= "\n  <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                        . "\n    <th nowrap>" . ($first ? "<strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong>" : "") . "</th>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-complete\">" . __('Complete', 'media-consumption-log') . "</a></td>"
                        . "\n    <td>";

                foreach (array_keys($category->mcl_tags_complete) as $key) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower($key) . "\">{$key}</a>";
                    $ak_co = array_keys($category->mcl_tags_complete);
                    if ($key != end($ak_co)) {
                        $cat_nav_html .= " | ";
                    }
                }

                $cat_nav_html .= "</td>"
                        . "\n  </tr>";

                $first = false;
                $alternate = !$alternate;
            }

            if ($category->mcl_tags_count_abandoned) {
                $cat_nav_html .= "\n  <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                        . "\n    <th nowrap>" . ($first ? "<strong><a href=\"#mediastatus-{$category->slug}\">{$category->name}</a></strong>" : "") . "</th>"
                        . "\n    <td nowrap><a href=\"#mediastatus-{$category->slug}-abandoned\">" . __('Abandoned', 'media-consumption-log') . "</a></td>"
                        . "\n    <td>";

                foreach (array_keys($category->mcl_tags_abandoned) as $key) {
                    $cat_nav_html .= "<a href=\"#mediastatus-{$category->slug}-abandoned-" . strtolower($key) . "\">{$key}</a>";
                    $ak_ab = array_keys($category->mcl_tags_abandoned);
                    if ($key != end($ak_ab)) {
                        $cat_nav_html .= " | ";
                    }
                }

                $cat_nav_html .= "</td>"
                        . "\n  </tr>";

                $first = false;
                $alternate = !$alternate;
            }
        }

        $cats_html = "";

        // Create the tables
        foreach ($data->categories as $category) {
            if (!MclHelpers::is_monitored_serial_category($category->term_id)) {
                continue;
            }

            if ($category->mcl_tags_count == 0) {
                continue;
            }

            // Category header
            $cats_html .= "\n\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}\"></div><h3>{$category->name}</h3><hr />";

            if ($category->mcl_tags_count_ongoing) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}-ongoing\"></div><h4>" . __('Running', 'media-consumption-log') . "</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                    $cats_html .= "<a href=\"#mediastatus-{$category->slug}-";
                    $cats_html .= strtolower($key) . "\">{$key}</a>";
                    $ak_on = array_keys($category->mcl_tags_ongoing);
                    if ($key != end($ak_on)) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"98%\">"
                        . "\n    <col width=\"1%\">"
                        . "\n  </colgroup>"
                        . "\n  <thead>"
                        . "\n    <tr>"
                        . "\n      <th></th>"
                        . "\n      <th nowrap><strong>" . __('Name', 'media-consumption-log') . "</strong></th>"
                        . "\n      <th nowrap><strong>" . __('Change State', 'media-consumption-log') . "</strong></th>"
                        . "\n    </tr>"
                        . "\n  </thead>"
                        . "\n  <tbody>";

                $alternate = false;

                foreach (array_keys($category->mcl_tags_ongoing) as $key) {
                    $first = true;

                    foreach ($category->mcl_tags_ongoing[$key] as $tag) {
                        $tag_title = htmlspecialchars($data->tags[$tag->tag_term_id]->tag_name);

                        $cats_html .= "\n    <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                                . "\n      <th nowrap valign=\"top\">" . ($first ? "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-" . strtolower($key) . "\"></div><div>{$key}</div>" : "") . "</th>"
                                . "\n      <td><a href=\"{$data->tags[$tag->tag_term_id]->tag_link}\" title=\"{$tag_title}\">{$tag_title}</a></td>"
                                . "\n      <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"1\">" . __('Complete', 'media-consumption-log') . "</a> - <a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"2\">" . __('Abandoned', 'media-consumption-log') . "</a></td>"
                                . "\n    </tr>";

                        $first = false;
                        $alternate = !$alternate;
                    }
                }

                $cats_html .= "\n  </tbody>"
                        . "\n</table>";
            }

            if ($category->mcl_tags_count_complete) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}-complete\"></div><h4>" . __('Complete', 'media-consumption-log') . "</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach (array_keys($category->mcl_tags_complete) as $key) {
                    $cats_html .= "<a href=\"#mediastatus-{$category->slug}-complete-" . strtolower($key) . "\">{$key}</a>";
                    $ak_co = array_keys($category->mcl_tags_complete);
                    if ($key != end($ak_co)) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"98%\">"
                        . "\n    <col width=\"1%\">"
                        . "\n  </colgroup>"
                        . "\n  <thead>"
                        . "\n    <tr>"
                        . "\n      <th></th>"
                        . "\n      <th nowrap><strong>" . __('Name', 'media-consumption-log') . "</strong></th>"
                        . "\n      <th nowrap><strong>" . __('Change State', 'media-consumption-log') . "</strong></th>"
                        . "\n    </tr>"
                        . "\n  </thead>"
                        . "\n  <tbody>";

                $alternate = false;

                foreach (array_keys($category->mcl_tags_complete) as $key) {
                    $first = true;

                    foreach ($category->mcl_tags_complete[$key] as $tag) {
                        $tag_title = htmlspecialchars($data->tags[$tag->tag_term_id]->tag_name);

                        $cats_html .= "\n    <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                                . "\n      <th nowrap valign=\"top\">" . ($first ? "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-complete-" . strtolower($key) . "\"></div><div>{$key}</div>" : "") . "</th>"
                                . "\n      <td><a href=\"{$data->tags[$tag->tag_term_id]->tag_link}\" title=\"{$tag_title}\">{$tag_title}</a></td>"
                                . "\n      <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">" . __('Running', 'media-consumption-log') . "</a> - <a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"2\">" . __('Abandoned', 'media-consumption-log') . "</a></td>"
                                . "\n    </tr>";

                        $first = false;
                        $alternate = !$alternate;
                    }
                }

                $cats_html .= "\n  </tbody>"
                        . "\n</table>";
            }

            if ($category->mcl_tags_count_abandoned) {
                $cats_html .= "\n<div class= \"anchor\" id=\"mediastatus-{$category->slug}-abandoned\"></div><h4>" . __('Abandoned', 'media-consumption-log') . "</h4>";

                // Create the navigation
                $cats_html .= "\n<div>";
                foreach (array_keys($category->mcl_tags_abandoned) as $key) {
                    $cats_html .= "<a href=\"#mediastatus-{$category->slug}-abandoned-" . strtolower($key) . "\">{$key}</a>";
                    $ak_ab = array_keys($category->mcl_tags_abandoned);
                    if ($key != end($ak_ab)) {
                        $cats_html .= " | ";
                    }
                }

                $cats_html .= "</div><br />";

                // Table
                $cats_html .= "\n<table class=\"widefat\">"
                        . "\n  <colgroup>"
                        . "\n    <col width=\"1%\">"
                        . "\n    <col width=\"98%\">"
                        . "\n    <col width=\"1%\">"
                        . "\n  </colgroup>"
                        . "\n  <thead>"
                        . "\n    <tr>"
                        . "\n      <th></th>"
                        . "\n      <th nowrap><strong>" . __('Name', 'media-consumption-log') . "</strong></th>"
                        . "\n      <th nowrap><strong>" . __('Change State', 'media-consumption-log') . "</strong></th>"
                        . "\n    </tr>"
                        . "\n  </thead>"
                        . "\n  <tbody>";

                $alternate = false;

                foreach (array_keys($category->mcl_tags_abandoned) as $key) {
                    $first = true;

                    foreach ($category->mcl_tags_abandoned[$key] as $tag) {
                        $tag_title = htmlspecialchars($data->tags[$tag->tag_term_id]->tag_name);

                        $cats_html .= "\n    <tr" . ($alternate ? " class=\"alternate\"" : "") . ">"
                                . "\n      <th nowrap valign=\"top\">" . ($first ? "<div class= \"anchor\" id=\"mediastatus-{$category->slug}-abandoned-" . strtolower($key) . "\"></div><div>{$key}</div>" : "") . "</th>"
                                . "\n      <td><a href=\"{$data->tags[$tag->tag_term_id]->tag_link}\" title=\"{$tag_title}\">{$tag_title}</a></td>"
                                . "\n      <td nowrap><a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"0\">" . __('Running', 'media-consumption-log') . "</a> - <a class=\"mcl_css_complete\" tag-id=\"{$tag->tag_term_id}\" cat-id=\"{$category->term_id}\" set-to=\"1\">" . __('Complete', 'media-consumption-log') . "</a></td>"
                                . "\n    </tr>";

                        $first = false;
                        $alternate = !$alternate;
                    }
                }

                $cats_html .= "\n  </tbody>"
                        . "\n</table>";
            }
        }
        ?>

        <div class="wrap">
            <h2>Media Consumption Log - <?php _e('Serials Status', 'media-consumption-log'); ?></h2><br />

            <table class="widefat">
                <colgroup>
                    <col width="1%">
                    <col width="1%">
                    <col width="98%">
                </colgroup>
                <thead>
                    <tr>
                        <th><strong><?php _e('Category', 'media-consumption-log'); ?></strong></th>
                        <th><strong><?php _e('State', 'media-consumption-log'); ?></strong></th>
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
}
