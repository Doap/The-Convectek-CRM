=== PHP Code for posts ===
Contributors: the.missing.code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SFQZ3KDJ4LQBA
Tags: PHP, allow php, exec php, execute php, php shortcode, php in posts, use php, embed html
Requires at least: 3.3.1
Tested up to: 4.2.1
Stable tag: 1.3.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add PHP code to your WordPress posts, pages, custom post types and even sidebars using shortcodes

== Description ==

PHP Code for posts allows you to add your own PHP code to posts, pages, custom post types (posts) and even sidebars without the need for custom templates

[[ Have you got a feature request? Email Me! ]]

The plugin enables a shortcode and options page so you can add your code to the admin options page and then output it in your post using shortcodes

Multiple code snippets can be used on a post, and multiple posts can use the same code snippet, allowing you to re-use code.

The shortcodes can be used to also display plain HTML content, allowing you to add in iframe, objects, areas and other tags that are removed by the post editor

The plugin also contains a variable array which you can add variables to for use between snippets called $_var and is available though the global variable $PHPPC which is an object, so its $PHPPC->_vars[]

= New for 1.1.1 =
The plugin's shortcode can also accept parameters using the param attribute, the value should be a string of name=value pairs, separated by &s, for example `[php snippet=2 param="var1=val1&var2=val2"]`.  Within your snippet, the parameters are assigned a $_parameters array, for example `echo $_parameters["var1"]; //outputs "val1"`

= New for 1.2.0 =
The plugin's snippet editor now has better formatting, and supports AJAX saving for snippet updates (request by eneasgesing)

== Installation ==
1. Download the plugin to your computer
1. Extract the contents
1. Upload (via FTP) to a sub folder of the WordPress plugins directory
1. Activate the plugin in the admin dashboard.

== Frequently Asked Questions ==

= You mentioned a variable array to assign variables to for use between snippets, how do I do this? =

To assign variables to the array, use the following code:
` global $PHPPC;
  $PHPPC->$_vars["myvaridentifier"] = $myvar;`

and to read a variable from the array use this code:
` global $PHPPC;
	$myvar = $PHPPC->$_vars["myvaridentifer"];`
Simples!

= AJAX update keeps failing =

One of the main causes of a failed AJAX update is because nothing has actually changed in any of the fields.

= My snippet doesn't work =

One common error is an error in the eval'ed code, this is more down to a syntax / parse error in the php snippet, rather than the plugin it self.

= No other questions yet! =

:)

== ChangeLog ==

= 1.0 =
* Initial Release
* No changes
= 1.1 =
* Added missing functionality for delete single snippet
* Tested for WP 3.6
* Added class functions for getting and setting shared variables (get_variable and set_variable)
= 1.1.1 =
* added parameters for snippits
= 1.1.2 =
* Fix for php warning handle_extra_shortcode (thanks to paul_martin)
= 1.1.3 =
* Fix for the table not being created in a multi-site installation (thanks to dondela and mediagent)
* Fix for the parameter variables not splitting correctly because of html entity encoding (thanks to eoh1)
= 1.2.0 =
* Ajax saving for updating code snippets (ajax save for initial add still to be implemented) (request by eneasgesing)
* Richer snippet editor using Codemirror (request by eneasgesing)
= 1.3.0 =
* Confirmed support for latest version of WP (4.2.1) just in case you were wondering

== Upgrade Notice ==

= 1.0 =
* Nothing :)
= 1.1 =
* Added in missing functionality
* Tested for WP 3.6
= 1.1.3 =
* Multisite Fix
* Parameter Fix

== Screenshots ==
1. The plugin options menu
