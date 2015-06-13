=== Plugin Name ===
Contributors: jondor
Donate link: http://www.funsite.eu/rss-per-page/
Tags: widget,rss, per page, plugin
Requires at least: 3.0.1
Tested up to: 4.2
Stable tag: 1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin implements an widget which can show a different, page controled, rss feed.

== Description ==

This plugin implements an widget which can show a different, page controled, rss feed. What I wanted was the last x plugin reviews on the page
with the plugin info. 
For this I added an extra field to every page in which an plugin related id can be stored. This id then will be used by the widget to 
replace the @ID@ marker in the rss feed. 

= example =

* page rss id: gps-map-widget
* widget rss feed: https://wordpress.org/support/rss/view/plugin-reviews/@ID@
* result: the last posts in the feed https://wordpress.org/support/rss/view/plugin-reviews/gps-map-widget

When the page rss id is empty, there's also a default title and rss feed  to use. 

= alternative use =

The defaults are for use with the plugins. But.. You can also change the rss feed in the widget settings to only @ID@ and add a 
complete feed url to every page resulting in a complete different feed on every page.

I use this widget together WooSidebars (https://wordpress.org/plugins/woosidebars/) so I can easy controle where the widget ends up.. 

== Installation ==

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the widget to a widgetarea and adjust the fields if needed.
4. Go to the pages where you want to show an specific feed and add the rss id

== Frequently Asked Questions ==

= Why did you write this widget? =
I wanted to show the reviews together with the plugin info.

== Screenshots ==

1. Widget as the user sees it
2. The widget settings
3. the page rss id

== Changelog ==
= 1.4 =
Found out that WP has build in support for fetching feeds which is a beter way to deal with rss.
So the plugin has been converted. The cache timeout for a feed is 5 min. 
Also upgraded the plugin to my basis framework and fixed a few bugs and anoyances. 

= 1.3 =
Fixed some minor bugs

= 1.2 =
- Made the plugin translatable and added dutch languagesupport
- Rewrote the plugin partly to clean up the code and avoid clashes with my other plugins.

= 1.1 =
Nothing much. Moved the ID field block to the side where it takes less space.

= 1.0 =

* First release

== Upgrade Notice ==

Nothing yet.
