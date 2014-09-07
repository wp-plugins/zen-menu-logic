=== Zen Menu Logic ===
Contributors: zenofwordpress
Donate link: http://www.zenofwp.com/
Plugin Uri: http://www.zml.zenofwp.com/
Tags: custom menus, menu logic
Requires at least: 3.3.1
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later

Zen Menu Logic allows the user to select any of several custom menus to appear on a per page basis.

== Description ==

This plugin only works under the following conditions:
1. the theme has registered at least one menu location
2. user has created at least one custom menu with one or more
   menu items.
3. user has selected which menu location the plugin should work on
   in the Settings -> Zen Menu Logic options page

If those 3 conditions are met, then the edit page for every page and post
and custom post type will contain a meta box listing the custom menus with
radio buttons. All you need do is select which custom menu should display
when that page is called.

== Installation ==

1. Unzip the download package
2. Upload `zen-menu-logic` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the Zen Menu Logic options page in the Setting's menu
   and select which menu location is the one that the plugin will
   work on.

alternatively

1. upload the zip file from the Admin plugins page
2. then activate
3. Go to the Zen Menu Logic options page in the Setting's menu
   and select which menu location is the one that the plugin will
   work on.

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==


= 1.4 =
* Fixed bug in saving logic. Tested in WP 3.6

= 1.3 =
* Added uninstall functionality to delete option used by plugin

= 1.21 =
* Changed the text in the options panel.

= 1.2 =
* There is a design flaw in v1.1 where I assumed that the name of the primary menu location
* was primary.  That is the way it is in the 2011 theme.  But not all themes are like that.
* So this new version has an options page as a menu item of the Settings Menu that lists all
* the menu locations by name.  And you need to select which one is the primary, or, in other
* words, which menu location the menu logic will work on.  Please let me know if there are
* any questions.

= 1.1 =
* simplify code that tests is this plugin is supported by the theme
* replace code that implemented the change in menu to use the wp_nav_menu_args filter

= 1.0 =
* write menu logic code