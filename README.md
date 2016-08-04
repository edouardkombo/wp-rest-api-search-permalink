=== WP REST API Search Permalink ===
Contributors: edouardkombo
Tags: wp-rest-api-search-permalink, wp-rest-api, json-rest-api, json, search permalink, permalink
Requires at least: 3.6.0
Tested up to: 4.4.2
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extends WordPress WP REST API and allows you to search for pages and posts by link, and filter the result per fields.

== Description ==

This plugin extends the [WordPress JSON REST API](https://wordpress.org/plugins/json-rest-api/), it allows you to search for posts and pages by link and filter the result per fields.

The new routes available will be:

* `/wp-json/searchlink/v2/_PAGE_OR_POST_LINK_` GET THE CORRESPONDING PAGE OR POST.
* `/wp-json/searchlink/v2/*` GET ALL POSTS AND PAGES.

You can filter the fields the strict fields you want to retrieve like this

* `/wp-json/searchlink/v2/<YOUR_PATTERN>?fields=link`

Use a comma to add more fields

* `/wp-json/searchlink/v2/<YOUR_PATTERN>?fields=link,content`


== Installation ==

This plugin requires having [WP API](https://wordpress.org/plugins/json-rest-api/) installed and activated or it won't be of any use.

Install the plugin as you would with any WordPress plugin in your `wp-content/plugins/` directory or equivalent.

Once installed, activate  WP REST API Search Permalink from WordPress plugins dashboard page and you're ready to go, WP API will respond to new routes and endpoints.


== Frequently Asked Questions ==

= Is this an official extension of WP API? =

No, it was developed for our needs.

== Screenshots ==

No screenshots available, just request your links
