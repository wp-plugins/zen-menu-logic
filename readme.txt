=== Zen Menu Logic ===
Contributors: zenofwordpress
Donate link: http://www.zenofwp.com/zen-plugins/zen-menu-logic/
Tags: custom menus, menu logic
Requires at least: 3.3.1
Tested up to: 3.3.1
Stable tag: trunk
License: GPLv2 or later

Zen Menu Logic allows the user to select any of several custom menus to appear on a per page basis.

== Description ==

This plugin only works under the following conditions:
1. the theme supports custom menus
2. the theme has registered at least one menu location as Primary
3. user has created at least one custom menu with one or more
   menu items and designated it as being in the primary location.

If those 3 conditions are met, then the edit page for every page and post
and custom post type will contain a meta box listing the custom menus with
radio buttons. All you need do is select which custom menu should display
when that page is called.

== Installation ==

1. Unzip the download package
2. Upload `zen-menu-logic` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

alternatively

1. upload the zip file from the Admin plugins page
2. then activate

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.1 =
* simplify code that tests is this plugin is supported by the theme
* replace code that implemented the change in menu to use the wp_nav_menu_args filter

= 1.0 =
* write menu logic code