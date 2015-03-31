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

jQuery(document).ready(function ($) {
    $(this).scrollTop(0);

    $(".mcl_css_quick_post").click(function () {
        $("#mcl_loading").addClass("mcl_css_loading");

        $.get("admin.php", {
            page: "mcl-quick-post",
            title: $(this).attr("headline"),
            tag_id: $(this).attr("tag-id"),
            cat_id: $(this).attr("cat-id")}
        ).done(function () {
            location.reload();
        });
    });

    $(".mcl_quick_post_new_entry").click(function (e) {
        if (!$("#" + e.currentTarget.id + "-titel").val()) {
            alert("<?php _e( 'Title can\'t be empty!', 'media-consumption-log' ); ?>");
            return;
        }

        $("#mcl_loading").addClass("mcl_css_loading");

        $.get("admin.php", {
            page: "mcl-quick-post",
            title: encodeURIComponent($("#" + e.currentTarget.id + "-titel").val()),
            text: encodeURIComponent($("#" + e.currentTarget.id + "-text").val()),
            cat_id: e.currentTarget.id}
        ).done(function () {
            $("#" + e.currentTarget.id + "-titel").val("");
            $("#" + e.currentTarget.id + "-text").val("");
            location.reload();
        });
    });

    $(".mcl_css_complete").click(function () {
        $("#mcl_loading").addClass("mcl_css_loading");

        $.get("admin.php", {
            page: "mcl-complete",
            tag_id: $(this).attr("tag-id"),
            cat_id: $(this).attr("cat-id"),
            complete: $(this).attr("set-to")}
        ).done(function () {
            location.reload();
        });
    });

    var offset = 200;

    $(window).scroll(function () {
        var position = $(window).scrollTop();

        if (position > offset) {
            $(".mcl_css_back_to_top").fadeIn();
        } else {
            $(".mcl_css_back_to_top").fadeOut();
        }
    });

    $(".mcl_css_back_to_top").click(function () {
        $(window).scrollTop(0);
        $(this).fadeOut();
    });
});