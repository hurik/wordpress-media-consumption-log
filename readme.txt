=== Media Consumption Log ===
Contributors: hurik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W3KL56CXEGRTN
Tags: media comsumption log, track, series, mangas, webtoons, animes, movies, games
Requires at least: 3.8
Tested up to: 4.1.1
Stable tag: 1.0.0
License: GPLv2 or later

This plugin helps you to keep track of the series, movies, mangas, games, books and other things you are consuming.

== Description ==

This plugin helps you to keep track of the series, movies, mangas, games, books and other things you are consuming.

Features:
* *Status*, shows and overview of all series and non series.
* *Statistics*, shows your daily consumption, monthly consumption, total consumption, average consumption and consumption amount.
* *Quick Post*, create an new post with one click.
* *Complete*, mark an serie as complete.

= How to use? =

Here is an example how to use this plugin:

1. Create the category "TV Shows".
2. Add the created category in the Site Admin -> MCL -> Settings -> Monitored categories -> Series (You must enter the ID of the category).
3. Create a new post in this category, with the title "Boston Legal - Episode S01E01" and the tag "Boston Legal".

When you watched the second episode, you can go to the Site Admin -> MCL -> Quick Post and there you can see that their is an entry for Boston Legal. Also an link to post "Boston Legal - Episode S01E02". When you click it, it automatically creates an new empty post in the "TV Shows" category with the title "Boston Legal - Episode S01E02" and the tag "Boston Legal". When you want to add some text to the post you can click on "Edit before posting" and you are forwarded to the new post page where the title, tag and the category are already set.

The post title must contains the following parts:

Boston Legal - Episode S01E01

* *Name*: "Boston Legal", should be the same as the tag.
* *Separator*: "-", can be changed in the MCL Settings.
* *Status unit*: Episode
* *Status*: S01E01


= mcl_number =

When a post is created in a monitored category, the post meta "mcl_number" is added.

Boston Legal - Episode S01E01 -> mcl_number set to 1
Boston Legal - Episode S01E01 and S01E02 -> mcl_number set to 2 
Boston Legal - Episode S01E01 to S01E05 -> mcl_number set to 5, calculated

When you create a post like "Boston Legal - Season 1" you must manually set the mcl_number to 17.

The keyword and an to can be changed in the MCL Settings.

You can also set it to 0. So the post will not be visible in the statistics. This is useful when you want to add something what you looked before you started logging your consumption.


== Installation ==

You can install this plugin directly from your WordPress dashboard:

1. Go to the *Plugins* menu and click *Add New*.
2. Search for *Media Consumption Log*.
3. Click *Install Now* next to the Media Consumption Log plugin.
4. Activate the plugin.

Alternatively, see the guide to [Manually Installing Plugins](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).


== Frequently Asked Questions ==

No questions at the moment.


== Screenshots ==

1. Status
2. Statistics - Daily consumption
3. Statistics - Monthly consumption
4. Statistics - Total consumption
5. Statistics - Average consumption
6. Statistics - Consumption amount
7. Quick Post
8. Complete
9. Unit
10. Data
11. Settings


== Changelog ==

= 1.0.0 =
* First release