=== Simple Course Creator ===
Contributors: sdavis2702
Tags: series, course, lesson, taxonomy, sdavis2702
Requires at least: 3.8
Tested up to: 6.9.1
Stable tag: 1.0.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily create, output, and manage post series.

== Description ==

Simple Course Creator is designed to easily link posts together in a series and output that series list in the content of each included post. A post can only be assigned to one course that way it can display the rest of the posts in its series.

Output can be displayed above the post content, below, or both. There's also an option to display the posts as a number list, bullet list, or with no list indicator.

Extend the functionality of Simple Course Creator with these add-on plugins:

* [SCC Customizer](https://github.com/seanchdavis/simple-course-creator-customizer/) - Customizer the SCC output using the native WordPress customizer.
* [SCC Post Meta](https://github.com/seanchdavis/simple-course-creator-post-meta/) - Add author and published date to SCC post listing items.
* [SCC Front Display](https://github.com/seanchdavis/simple-course-creator-front-display/) - Indicate a post’s course on the blog home, archive pages, and search results.

== Installation ==

1. Upload `simple-course-creator` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create an unlimited number of courses under the WordPress 'Posts' menu
4. Add single posts to a course on the post edit screen or the manage posts screen

== Frequently Asked Questions ==

= Can a post be assigned to more than one course? =

No. A post should only be assigned to one course. This is because the purpose of the plugin is to display the other
posts that are in the same course as the post being viewed. In order to do that, the other posts must be pulled from a
single source (the one course you have chosen). Technically, multiple courses can be added to a posts. However, only
course will display on your post, and you will not have control of which course that is.

= Can I edit the course HTML output? =

Yes. When creating a new course, the description and course title fields are used to display an introduction to the course. If they are filled out, they will display.

From there, there are multiple ways to edit additional output.

-- The first and easiest way is to use the built-in hooks and filter to customize the course box. You'd write your actions in your active theme functions file.

Here's a list of all the hook names you can use to insert custom content.

* scc_before_container
* scc_container_top
* scc_below_title
* scc_below_description
* scc_before_toggle
* scc_after_toggle
* scc_above_list
* scc_before_list_item
* scc_after_list_item
* scc_below_list
* scc_container_bottom
* scc_after_container

The course display toggle link is also filtered. Use the following filter to change its text.

* course_toggle

-- The second way is to override the plugin display files in your active theme.

You'd create a directory in the ROOT of your active theme called `scc_templates` and in it, copy any of the files from the `includes/scc_templates` directory of the plugin. Your new theme files will override the plugin files.

Only use this method if you know your way around PHP, HTML, CSS, and JS.

-- Lastly, for minimal display tweaks, simply write CSS in your active theme that overrides the default plugin CSS, which is minimal.

== Screenshots ==

1. settings Page with output options
2. create a course just like categories and tags
3. assign a post to a course from edit post screen
4. filter course information from manage posts screen
5. course output collapsed
6. course output expanded

== Changelog ==

= 1.0.7 =
* Added: setting for ordering the post listing using the "order" parameter (ascending or descending)
* Removed: "scc_order" filter, which is now replaced by the above setting
* Tweaked: default border and padding on the post listing display

= 1.0.6 =
* Added: setting for ordering the post listing using the "orderby" parameter
* Added: "scc_order" filter to control whether the post listing displays ASC or DESC
* Fixed: PHP notices
* Fixed: database settings values inconsistencies

= 1.0.5 =
* Tweaked: Code formatting

= 1.0.4 =
* Tweaked: Improved translation strings and updated .pot file

= 1.0.3 =
* New: Added option to select the current post text style (bold, line-through, or italic)
* Removed: Current post default bold font weight

= 1.0.2 =
* Fixed: PHP warnings based on sanitization of plugin settings

= 1.0.1 =
* Added: "Disable JavaScript" (expand/collapse of post listing) setting on settings page

= 1.0.0 =
* first stable version