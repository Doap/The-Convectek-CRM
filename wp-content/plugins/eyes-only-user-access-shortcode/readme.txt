=== Eyes Only: User Access Shortcode ===

Name: Eyes Only: User Access Shortcode

Contributors: thomstark, kevinB

Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MC429PEHQPWEC

Version: 1.8.2

Requires at least: 3.3

Tested up to: 3.9

Stable tag: 1.8.2

License: GPLv3

Tags: content, hide, visibility, shortcode, shortcodes, role, capabilities, restrict, BuddyPress, bbPress, Role Scoper, Press Permit



Show or hide any portion of post content based on usernames, user roles, capabilities, custom groups, or logged-in status.



== Description ==



Show or hide any portion of post content based on usernames, user roles, capabilities, custom groups, or logged-in status. Construct shortcodes manually or using a point and click UI.



= Mini Update Log = 



* v1.8.2 compat with WordPress 3.9
* v1.8.1 fixed conflict with the Tabify Edit Screen plugin

* v1.8 added new shortcode handlers for nesting eyesonly shortcodes. also added body classes for logged-in user roles and logged-in usernames. 

* v1.7.1 fixed bug with TinyMCE button where TinyMCE panel is used outside post/page editor. 

* v1.7 added multisite support for username multiselect; fixed bug with role/level dropdown in non-Mozilla browsers.

* Lots of new features in v1.6, including smooth integration with Role Scoper and Press Permit, thanks to their author Kevin Behrens, who has also now joined the Eyes Only team. Two makes a team, right?

* As of v1.5, has compatibility with BuddyPress and BBPress forums in conjunction with the *bbPress2 shortcode whitelist* plugin by Anton Channing.

* As of v1.4, includes TinyMCE Shortcode Button with Shortcode Generator Modal, with options to restrict access to the Shortcode Button, and to determine its position on the TinyMCE panel.

* As of v1.3, includes Options page, with options to restrict access to the Meta Box, and to prevent content being hidden from Administrators.

* As of v1.2, includes Shortcode Generator Meta Box

* [See full changelog](http://wordpress.org/plugins/eyes-only-user-access-shortcode/changelog/ "Right-Click to Open in New Window")



= The Shortcode = 



[eyesonly] [/eyesonly]



or 



[eyesonlier] [/eyesonlier]



or



[eyesonliest] [/eyesonliest]



* *Required attributes:* *either* logged="in|out" *or* level="anyrole, any_capability" *or* username="anyusername" *or all three*

* *Newly integrated attributes:* rs_group="any_Role_Scoper_group(s)" *or* pp_group="any_Permit_Press_group(s)"

* *Optional attribute:* hide="yes"



All shortcodes must include at least one of the three required attributes. In some cases, it makes sense to use all of them together.



* The 'logged' attribute takes one of two values: 'in' or 'out'

* The 'level' attribute takes any user role (including custom roles) or any capability (including custom capabilities). It can take multiple roles and/or capabilities, separated either by spaces or commas.

* The 'username' attribute takes any userlogin (username), or multiple, separated either by spaces or commas.

* The 'rs_group' and 'pp_group' attributes can take comma or space-separated lists just like the 'level' and 'username' attributes. If you have the latest versions of either Role Scoper or Press Permit activated, you'll get an extra menu in your shortcode generators to filter your content by those custom user groups. This attribute can also be used in combination with any of the others to fine tune your filtering. 

* The 'hide' attribute: By default, the shortcode will show the content only to the users specified by the 'username', 'level' or 'logged' attributes. Adding the 'hide' attribute will reverse that, and hide the content from the specified users. To hide content from the specified users, use hide=yes or hide=true.



Any other shortcode can be used inside this shortcode.





= Examples =



* [eyesonly level="administrator, customrole, moderate_comments"] *Any kind of content.* [/eyesonly]<br>*This will show the content only to users with the 'administrator' role, the 'customrole' role, and the 'moderate_comments' capability.*



* [eyesonly hide="yes" logged="out" level="subscriber customrole"] *Any kind of content.* [/eyesonly]<br>*This will hide the content from all logged out users, and from anyone with the roles 'subscriber' or 'customrole'.* 



* [eyesonly username="joebob, billyjean" level="administrator"] *Your content.* [/eyesonly]<br>*This will show the content to all administrators and to the specific users 'joebob' and 'billyjean,' whether they're administrators or not.*



* [eyesonly logged="in"] *Content for logged-in users.* [/eyesonly][eyesonly logged="out"] *Content for logged-out users.* [/eyesonly]<br>*This will show different content in the same location on the page depending on a user's logged-in or out status.*



* [eyesonly level="administrator"] *Content.* [/eyesonly]<br>*This will show the content only to administrators.*



* [eyesonly hide="yes" level="administrator custom_capability"] *Content.* [/eyesonly]<br>*This will hide the content only from administrators and from any role that has the 'custom_capability'.*



= Eyes Only Nesting =



* Eyes Only allows for nested user access shortcodes up to three levels deep. Example:



[eyesonly level="administrator, editor, author"] Restricted content. [eyesonlier level="administrator, editor"] Restricteder content. [eyesonliest level="administrator"] Restrictedest content. [/eyesonliest] More restricteder content. [/eyesonlier] More restricted content. [/eyesonly]



* It doesn't matter which order you use them in, just so long as you keep your nested levels straight. 



= Requirements =



* PHP 5.2+

* WordPress 3.3+









== Installation ==



1. Upload 'eyes-only-user-access-shortcode/' to the '/wp-content/plugins/' directory.

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Use the provided shortcode generator(s) and use the codes on your pages, posts, widgets, etc.





== Frequently Asked Questions ==



= Is Eyes Only compatible with Role Scoper and Press Permit? =



Sure is. As of version 1.6, *Eyes Only* is integrated with both [Role Scoper](http://wordpress.org/plugins/role-scoper/ "Right-Click to Open in New Window") and [Press Permit](http://wordpress.org/plugins/press-permit-core/ "Right-Click to Open in New Window") to allow you to filter content according to Role Scoper or Press Permit custom user groups. Just use the attributes rs_group="" or pp_group="" in your shortcode, specifying the group names in between the quotes. Or, just use one of the two shortcode generators provided, both of which have Role Scoper and Press Permit selectboxes if one of those plugins is activated.



= I developed a plugin that classifies users. Can I integrate my plugin with Eyes Only? =



Sure can. By hooking into *Eyes Only* filters, you can add your available selections to the post editing UI and implement those selections on front end access. Here's how to do it:



`<?php

add_action( 'init', '_my_eo_registration' );

function _my_eo_registration() {

	sseo_register_parameter( 'my_group_type', 'My Group Type' ); 

}



add_filter( 'eo_shortcode_matched', '_my_eo_matching', 10, 3 );

function _my_eo_matching( $matched, $params, $shortcode_content ) {

	if ( ! empty( $params['my_group_type'] ) ) {

		$shortcode_group_ids_array = $params['my_group_type'];



		/*

		if ( your-group-matching-logic-here )

			return true;

		*/

	}



	return $matched;

}



// note that this filter is named according to your sseo_register_parameter() call

add_filter( 'sseo_my_group_type_items', '_my_eo_available_groups' );

function _my_eo_available_groups( $group_labels ) {

	// Return an id => label array for your available groups

	$group_labels = array( 1 => 'My Group 1', 2 => 'My Group 2' );

	return $group_labels;

}

`



= Is Eyes Only compatible with BuddyPress and BBPress? =



Yes, for the most part, if you install an additional plugin by a different author. Please read these instructions carefully: 



*Eyes Only* has been updated for compatibility with the [bbPress2 shortcode whitelist](http://wordpress.org/plugins/bbpress2-shortcode-whitelist/ "Right-Click to Open in New Window") plugin. Here are the steps you should take:



* Make sure that your *Eyes Only: User Access Shortcode* is updated to version 1.5 or greater.

* Install and activate the [bbPress2 shortcode whitelist](http://wordpress.org/plugins/bbpress2-shortcode-whitelist/ "Right-Click to Open in New Window") plugin by Anton Channing.

* Go to Settings > Shortcode whitelist

* At the top, you should see a **Detected Plugins** header and then a checkbox next to: *Eyes Only: User Access Shortcode by Thom Stark supports the shortcodes: [eyesonly].* Check that box. 

* Very important: also type **eyesonly** in the **Manual Additions** textbox. Don't type [eyesonly]. Type it without the brackets.

* Now, the shortcode will work in BuddyPress and BBPRess forum posts, topics, comments, etc. 

* **PLEASE NOTE:** It doesn't work everywhere. For instance, it doesn't seem to work in the forum or group description posts (when a forum is created on the back end). But it DOES work in topics and comments within those forums. In other words, it works on the front-end, but not the back-end, of BP and BBP. At least that's my experience.

* Best thing to do is use the code all over the place. See where it works, and see where it doesn't.

* Also, it seemed to take a few minutes after first installing the *bbPress2 shortcode whitelist* plugin before the shortcode started working everywhere. After a few page refreshes, it kicked in, and now it works immediately on anything new I create. So don't get exasperated after the first few minutes and give up.



Follow those steps and your BP and BBP users will be able to use the [eyesonly] shortcode to their hearts' content. 









== Screenshots ==



1. The Shortcode Generator Meta Box defaults to a closed position so as to be as unobtrusive as possible.   

2. You've opened up the Meta Box. (That's so Meta.) Behold! Options.

3. So then you choose some options.

4. Then you make it official.

5. Then you share your secrets.

6. The Option page can be found under Settings > Eyes Only Options







== Changelog ==


= 1.8.2 =
* Compat with WordPress 3.9


= 1.8.1 =

* Fixed conflict with Tabify Edit Screen plugin



= 1.8 =

* Two new significant features: Shortcode Nesting, and User-Specific Body Classes for CSS Styling.

* Nesting: added [eyesonlier] and [eyesonliest] shortcode handlers for nesting user access up to three levels deep. Nesting options included in both the Modal and Metabox shortcode generators. 

* Body Classes: Eyes Only now generates user-specific body classes for logged-in users: a body class each for the user's user role(s), and for their userlogin. By default, the class is prefixed by "eyesonly-" but you have the option to specify your own prefix on the Eyes Only Options page. So if a logged-in user's role is Editor and her userlogin is "janedoe", her body classes for CSS styling will be: eyesonly-editor and eyesonly-janedoe (again, you can specify your own prefix). 



= 1.7.1 =

* v1.7.1 fixed bug with TinyMCE button where TinyMCE panel is used outside post/page editor.



= 1.7 =

* Fixed: Username multiselect in modal window/metabox now compatible with multisite.

* Fixed: Role / Type Access dropdown in modal window/metabox was ineffective on non-Mozilla browsers

* Added: New plugin links on settings page.



= 1.6 =

* Now integrated and fully compatible with Role Scoper (v.1.3.63) and Press Permit Core (v.2.1.24)

* New Feature: [eyesonly] tags will be placed around any selected text when using the modal window shortcode generator.

* New Feature: Modal window replaces any existing [eyesonly] tag(s) in selected text.

* New Feature: Option for strict role matching (based on actual role assignment rather than possession of equivalent capabilities).

* Change: Organized level selection UI (separate list for roles, Post capabilities, Page capabilities, etc.) But don't worry. You can still select multiple from each of them. Now you just don't have to scroll down as far.

* Change: Modal/metabox selection UI now resets after insertion/selection/closure.

* New Feature: API for inclusion and processing of externally defined user parameters

* Perf: Moved wp-admin code to separate file

* Perf: Various code cleanup and simplifications

* Fixed: Plugin settings were not available if plugin folder renamed

* Fixed: Plugin images were not displayed if plugin folder renamed



= 1.5 =

* Added compatibility with BuddyPress and BBPress forums if used in combination with the [bbPress2 shortcode whitelist](http://wordpress.org/plugins/bbpress2-shortcode-whitelist/ "Right-Click to Open in New Window") plugin by Anton Channing. See the [FAQ](http://wordpress.org/plugins/eyes-only-user-access-shortcode/faq/ "Right-Click to Open in New Window") for instructions.

* Optomized the shortcode code to reduce the already negligible server load. No functionality changes.



= 1.4 = 

* Major Addition: Now with TinyMCE Shortcode Button and Modal Window for shortcode generation. Like the Meta Box generator, the shortcode Modal can be disabled or its access restricted per user capability.

* Option to determine the position of the shortcode button on the TinyMCE panel. 

* Strengthened Administrator Override feature. It's now essentially fullproof.

* The "Configure Options" button in the Meta Box (and Modal window) now only shows up for Administrators. 

* Tweaked some styles.

* Added support and review links to the Options page. 



= 1.3 = 

* Major Addition: Now with Options page under the Settings menu. 

* Added option to hide the shortcode generator (meta box), or, by user capability, to control who sees it.

* Added an "Administrator Override" option (activated by default) that prevents users from hiding content from Administrators when activated. 

* Also fixed a minor issue, the "unexepected characters" warning upon plugin activation. It was harmless, but now it's gone.  



= 1.2 = 

* Major Addition: Now with shortcode generator meta box, located beneath the Visual/Text editor on your posts and pages. 



= 1.1 =

* Added the 'username' attribute to allow filtering by specific user logins.



= 1.0 =

* Initial release





== Upgrade Notice ==



= 1.8 = 

* Added nesting and user-specific body classes. 



= 1.7.1 = 

* v1.7.1 fixed bug with TinyMCE button where TinyMCE panel is used outside post/page editor.



= 1.7 =

* Fixed: Username multiselect in modal window/metabox now compatible with multisite.

* Fixed: Role / Type Access dropdown in modal window/metabox was ineffective on non-Mozilla browsers



= 1.6 =

Now integrated with Role Scoper and Press Permit. Lots of new little features and UI improvements.



= 1.5 =

Optimizes shortcode for reduced server load and adds support for BuddyPress and BBPress if used in combination with the *bbPress2 shortcode whitelist* plugin.