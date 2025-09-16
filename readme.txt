=== Media Consumption Log ===
Contributors: hurik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W3KL56CXEGRTN
Tags: media consumption log, track, tv shows, movies, books, comics, games, serials, media
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 2.8.0
License: GPLv2 or later

This plugin helps you to keep track of the tv shows, movies, books, comics, games and other things you are consuming.


== Description ==

This plugin helps you to keep track of the tv shows, movies, books, comics, games and other things you are consuming.

**This plugin uses categories and tags to track the serials and non serials. So please read the full description to learn how to use it.**

**Here is an video, which gives you an quick overview of how to use this plugin:** [Media Consumption Log - Quick overview](https://www.youtube.com/watch?v=G961wXE22qQ)

There are two kind of tracked categories:

* **Serials** are for tv shows, mangas and other things which have more than one part.
* **Non serials** are for games or books which have only one part.

Different serials and non serials are separated by the tag.

= Status =
The status page shows a list of all the the serials and non serials. Serials are separated in running, complete or abandoned serials.

To create a status page make a new site and add the shortcode "[mcl]" to it.

= Statistics =
Contains the following statistics:

* Daily consumption
* Hourly consumption
* Monthly consumption
* Yearly consumption
* Total consumption
* Average consumption
* Average consumption development
* Consumption amount
* Most consumed
* Milestones

To create a statistics page make a new site and add the shortcode "[mcl-stats]" to it.

= Quick Post =
In Quick post you can easily create a new post for a running serial. Check the screenshots for example.

= Serials Status =
Here you can change the status of a serial to running, complete or abandoned. Complete and abandoned serials will not show up in Quick post and also will be separated on the status page.

= Forgotten =
Shows running serials which haven't got a new post in the last 91 days (The number of days can be changed in the settings).

= Settings =
Here you can change the options of the plugin. You also can rebuild the data and see if there is a post without mcl number in the monitored categories.

= How to use? =
Here is an example how to use this plugin:

1. Create the category "TV Shows".
1. Add the created category in the Site Admin -> MCL -> Settings -> Monitored categories -> Series (You must enter the ID of the category).
1. Create a new post in the new category, with the title "Boston Legal - Episode S01E01" and the tag "Boston Legal".

When you watched the second episode, you can go to the Site Admin -> MCL -> Quick Post and there you can see that their is an entry for Boston Legal. Also an link to post "Boston Legal - Episode S01E02". When you click it, it automatically creates an new empty post in the "TV Shows" category with the title "Boston Legal - Episode S01E02" and the tag "Boston Legal". When you want to add some text to the post you can click on "Edit before posting" and you are forwarded to the new post page where the title, tag and the category are already set.

The post title must contains the following parts:

Boston Legal - Episode S01E01

* **Name**: "Boston Legal", should be the same as the tag.
* **Separator**: "-", can be changed in the MCL Settings.
* **Status unit**: "Episode"
* **Status**: "S01E01"

In Quick Post you also can create a new serial. For each category at the beginning there are two text fields. Title and Text. When you set the title "Dexter - Episode S01E01" and publish it a new post is created with the set title, "Dexter" will be set as tag and the category will also be set.
 
= mcl_number =
When a post is created in a monitored category, the custom field "mcl_number" is added. You can set the mcl_number manually or it is set automatically for you.

Here some examples how the automatically setting works:

* Boston Legal - Episode S01E01 -> mcl_number set to 1
* Boston Legal - Episode S01E01 and S01E02 -> mcl_number set to 2, because of the "and" keyword
* Boston Legal - Episode S01E01 to S01E05 -> mcl_number set to 5, calculated because of the "to" keyword

When you create a post like "Boston Legal - Season 1" you must manually set the mcl_number to 17 (Season 1 of Boston Legal has 17 episodes).

The keywords "and" and "to" can be changed in the MCL Settings.

You can also set it to 0. So the post will not be visible in the statistics. This is useful when you want to add something what you consumed before you started using the plugin.

= Other features =
The plugin also support comma in tags. When you create a new tag with a comma, replace ", " with "--". It will automatically will be replaced in the frontend.

= Support =
If you have a problem, question or suggestion please post it on the [plugins github page](https://github.com/hurik/wordpress-media-consumption-log) or write me an [email](mailto:andreas@giemza.net).


== Installation ==

You can install this plugin directly from your WordPress dashboard:

1. Go to the **Plugins** menu and click **Add New**.
2. Search for **Media Consumption Log**.
3. Click **Install Now** next to the Media Consumption Log plugin.
4. Activate the plugin.

Alternatively, see the guide to [Manually Installing Plugins](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).


== Frequently Asked Questions ==

No questions at the moment.


== Screenshots ==

1. Status
2. Statistics
3. Quick Post - Serials
4. Quick Post - Non Serials
5. Serials Status
6. Forgotten
7. Settings


== Changelog ==

= 2.8.0 =
* Cleaned-up Quick Post page
* Added edit links to last posts and Recently Published

= 2.7.0 =
* Added Recently Published in Quick Post

= 2.6.0 =
* Improved Quick Post for Serials

= 2.5.3 =
* Minor fixes
* Text improvements

= 2.5.2 =
* Fixed annotations in Google Charts

= 2.5.1 =
* A lot of small improvements

= 2.5.0 =
* Added mcl_number column in admin posts view
* Added mcl_number calculating support for chapters which have three or more segments (for example B4C01P02 to B4C01P04)

= 2.4.1 =
* Minor fixes and improvements
* Tested up to 4.9

= 2.4.0 =
* Added Yearly consumption in statistics
* Improved Quick Post
* Tested up to 4.8

= 2.3.0 =
* Added consumed in Status
* Removed german language files, now using: https://translate.wordpress.org/projects/wp-plugins/media-consumption-log

= 2.2.4 =
* Fixed little date bug with timezones in milestones
* Tested up to 4.6

= 2.2.3 =
* Improved bar graphs in Statistics

= 2.2.2 =
* Improved Rename serial, now using wordpress function and also renames the post_name (slug)

= 2.2.1 =
* Update MclData after renaming serial

= 2.2.0 =
* Added Rename serial in Settings
* Tested up to 4.5

= 2.1.4 =
* Fixed problem with times

= 2.1.3 =
* Improved Statistics when the plugin is freshly installed

= 2.1.2 =
* Fixed some possible bugs when the plugin is freshly installed

= 2.1.1 =
* Fixed annoying message when activating the plugin

= 2.1.0 =
* Added years and posts milestones
* Improved MclData again! Improved size of mcl_data, which is saved in the options table (In my blog is was reduced by half, from 1 MB to 0,5 MB)
* Fixed bugs when you install the plugin and there is no data

Statistics of the improvements:

* **Version 2.0.0:** 15 Queries, 2,337 seconds, 23,67 MB memory, 43,19 MB peak memory
* **Version 2.1.0:** 15 Queries, 2,137 seconds, 20,04 MB memory, 40,67 MB peak memory

= 2.0.0 =
* Improved MclData! Query count and time highly reduced!
* Added Milestones
* Status, Quick Post and Forgotten now showing post with biggest number instead of last post
* A lot of other improvements in the background

My blog has 6153 post with 513 tags, in 10 monitored categories. 7 categories are for serials with 428 tags and 3 categories are for non serials with 106 tags (428 + 106 = 534 > 513, because some tags are used in more than one category!).

Statistics of the improvements:

* **Version 1.7.1:** 1021 Queries, 4,323 seconds, 30,22 MB memory, 33,16 MB peak memory
* **Version 2.0.0:** 15 Queries, 2,337 seconds, 23,67 MB memory, 43,19 MB peak memory

= 1.7.1 =
* Added option for Average consumption development
* Fixed bug with dates in monthly graph
* Tested up to 4.4 (Beta 3)

= 1.7.0 =
* Added Average consumption development
* Improvements and fixes in the background 

= 1.6.1 =
* Tested up to 4.3
* Fixed annotation position in graphs when last category was zero
* Moved Quick Post button to new content node, for better access on mobile devices

= 1.6.0 =
* Added Most consumed in Statistics
* Fixed little bug in Settings

= 1.5.0 =
* Added Hourly consumption in Statistics

= 1.4.1 =
* Tables in Status, Statistics, Quick Post, Serial Status and Forgotten improved (Now better readable, and doesn't break the page width on mobile devices)
* Data rebuild query count reduced (A little bit)
* Settings improved
* A lot of improvements and fixes in the background 

= 1.4.0 =
* Removed "Remove postmeta orphans" from Data, use the plugin [WP-Sweep](https://wordpress.org/plugins/wp-sweep/)
* Removed Data from admin menu and moved "Rebuild Data" and "Posts without mcl_number" to settings
* Added nice statistics to "Rebuild data", used code from the plugin [WP Page Load Stats](https://wordpress.org/plugins/wp-page-load-stats/)
* Data is rebuild on a new day in Statistics and Forgotten (when opened)
* Data is rebuild when a monitored category is renamed
* Some improvements and fixes in the background

= 1.3.0 =
* Fixed and improved Forgotten (The "Minimal count of days" setting wasn't working and it now uses MclData)
* Moved Units to Settings
* Some improvements and fixes in the background

= 1.2.2 =
* Readme and screenshots improved
* Tested up to 4.2
* Default time format for Daily consumption changed

= 1.2.1 =
* Database wasn't updated to version 2

= 1.2.0 =
* Renamed Complete to Serial Status and added abandoned
* Added count to Forgotten
* Fixed navigation in Status
* Fixed possible PHP errors when category wasn't set in a new post

= 1.1.0 =
* Added Forgotten
* Data is now rebuild after updating to prevent errors

= 1.0.2 =
* Simplified Quick Post and Complete
* A lot of improvements in the background

= 1.0.1 =
* Fixed readme.txt

= 1.0.0 =
* First release
