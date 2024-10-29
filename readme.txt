=== Plugin Name ===
Contributors: BjornW
Donate link: http://burobjorn.nl
Tags: http2https, WPMU, WordPress Mu, https
Requires at least: WordPress Mu 2.9.2
Tested up to: WordPress Mu 2.9.2
Stable tag: 1.0


bbHTTP2HTTPS is a tiny plugin for WordPress Mu which updates the siteurl, home and fileupload_url to use https instead of http 
 
== Description ==

bbHTTP2HTTPS is a tiny plugin that updates all active(!) WordPress Mu blogs in one installation to use https instead
http. It does NOT change the guid or post content with regards to the links used in there. So you might still 
have to change some links in your content after switching from http to https. As always, first make a backup of
your WordPress Mu install before using this plugin. This plugin might working in previous versions of WordPress Mu 
as well, but this has not been tested. Please contact me if you have verified the plugin on a pre 2.9.2 WordPress Mu install.      


== Installation ==

1. Unzip the plugin's zip file
2. Upload the bbHTTP2HTTPS directory to the `/wp-content/plugins/` directory
3. Make sure you a working backup of your database in case anything goes wrong!
4. Activate the plugin through the 'Plugins' menu in WordPress Mu (Do NOT activate this sitewide!)
5. Go to the plugin settings in the WordPress Mu Site Admin by pressing bbHTTP2HTTPS 
6. Press the HTTP2HTTPS button, the plugin will now process all the active blogs
and update the siteurl, home and fileupload_url to use https instead of http


== Screenshots ==

1. This screen shot shows the site admin screen and the button you need to press to see the plugin's settings 
2. This screenshot shows the plugin's interface before use
3. This screenshot shows the plugin after use with a partial log (removed all details regarding my installation)

== Changelog ==

= 1.0 =
First version and probably the last version since it does what it needs to do for WordPress Mu which has merged with
WordPress since version 3.0. However, if you encounter any bugs in it feel free to contact me and I'll try to help you
out.
