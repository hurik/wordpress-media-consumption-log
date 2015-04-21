=== Media Consumption Log ===
Contributors: hurik
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=W3KL56CXEGRTN
Tags: media consumption log, track, tv shows, movies, books, comics, games, serials, media
Requires at least: 3.8
Tested up to: 4.2
Stable tag: 1.2.2
License: GPLv2 or later

This plugin helps you to keep track of the tv shows, movies, books, comics, games and other things you are consuming.


== Description ==

This plugin helps you to keep track of the tv shows, movies, books, comics, games and other things you are consuming.

**This plugin uses categories and tags to track the serials and non serials. So please read the full description to learn how to use it.**

There are two kind of tracked categories:

* **Serials** are for tv shows and other things which have more than one part.
* **Non serials** are for games or books which have only one part.

Different serials and non serials are separated by the tag.

= Status =
The status page shows a list of all the the serials and non serials. Serials are separated in running, complete or abandoned serials.

To create a status page make a new site and add the shortcode "[mcl]" to it.

= Statistics =
Contains the following statistics:

* Daily consumption
* Monthly consumption
* Total consumption
* Average consumption
* Consumption amount

To create a statistics page make a new site and add the shortcode "[mcl-stats]" to it.

= Quick Post =
In Quick post you can easily create a new post for a running serial. Check the screenshots for example.

= Serial Status =
Here you can set a serial to complete or abandoned. Complete and abandoned serials will not show up in Quick post and also will be separated on the status page.

= Forgotten =
Shows serials which haven't got a new post in the last 91 days (The number of days can be changed in the settings).

= Unit =
Here you can set units for serial which will be used in the statistics.

= Data =
Sometimes it is necessary to rebuild the saved data, because you renamed a category. This is normally not necessary because every time you create a new post or edit a post the data is rebuild. The data is used for the Status, Statistics, Quick Post and Complete pages.

= Settings =
Here you can change the options of the plugin.

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
3. Quick Post
4. Serial Status
5. Forgotten
6. Units
7. Data
8. Settings


== Changelog ==

= 1.2.2 =
* Readme and screenshots improved
* Tested up to changed to 4.2
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
