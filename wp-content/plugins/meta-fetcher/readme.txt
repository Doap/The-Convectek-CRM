=== Meta Fetcher ===
Contributors: webdeveric
Tags: meta, shortcode, post
Requires at least: 3.0.0
Tested up to: 4.4.0
Stable tag: 0.4

This plugin provides a simple [meta] shortcode that allows you to fetch meta information for the current $post.

== Description ==

This plugin provides a simple `[meta]` shortcode that allows you to fetch meta information for the current `$post`.
There are options to return a single value, multiple values joined by a user defined string, or a JSON string.

**Examples:**

`[meta name="your_meta_field"]`
This will return the value of "your_meta_field". If the value is an array, it will return a comma separated list, unless you specify an alternative join string.

`[meta name="your_meta_field" single="false"]`
By default, [meta] will return a single value. If you have multiple meta fields with the same name, you can get them all by setting single="false".

`[meta name="your_meta_field" single="false" json="true"]`
This will return the JSON encoded value of "your_meta_field".

`[meta name="your_meta_field" single="false" join="|"]`
This will return a pipe separated values of "your_meta_field".

`[meta name="your_meta_field" shortcode="false"]`
By default, the value will be passed to do_shortcode, unless you turn if off.

`[meta name="your_meta_field" filters="true"]`
There are a couple filters available if you want to filter the value. They are called after do_shortcode and before any JSON/array handling.
They are on be default, but can be turned of with filters="false". The filters are "meta_fetcher_value" and "meta_fetcher_{$name}".

== Installation ==

1. Upload `meta-fetcher` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Add shortcode to your content: `[meta name="some_name_here" default="some default content"]`

== Changelog ==

= 0.4 =
* Added option to return a JSON string.
* Added options for handling array meta values.

= 0.3 =
* Added better checking for argument values.

= 0.2 =
* Added additional arguments.

= 0.1 =
* Initial build
