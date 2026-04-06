=== Simple Course Creator ===
Contributors: sdavis2702
Tags: course, series, lesson, taxonomy, posts
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.7
Stable tag: 2.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Organize WordPress posts into courses and display a course listing within each post.

== Description ==

Simple Course Creator lets you group WordPress posts into courses using a custom taxonomy, then automatically displays a linked list of all posts in the same course within each post's content.

**Features**

* Create unlimited courses from the Posts menu — just like categories and tags
* Assign posts to a course from the edit post screen or the manage posts screen
* Display the course listing above content, below, or both
* Choose between numbered list, bullet list, or no list indicator
* Sort the listing by date, title, author, last modified, comment count, or random
* Style the current post in the listing as bold, italic, or strikethrough
* Optionally collapse the listing behind a toggle link (JavaScript)

**Post Meta**

Show author and publish date beneath each item in the course listing. Both are enabled by default and can be toggled from the settings page.

**Front Display**

On the blog home, archive pages, and search results, indicate that a post belongs to a course. Enabled by default and can be toggled from the settings page.

**Customization**

Style the course box, post meta output, and front display indicator directly from the WordPress Customizer — no custom CSS required. For deeper customization, override the plugin's template files in your active theme.

Create a directory called `scc_templates` in the root of your active theme and copy any files from the plugin's `includes/scc_templates/` directory into it. Theme files take priority over plugin files.

== Installation ==

1. Upload `simple-course-creator` to the `/wp-content/plugins/` directory
2. Activate the plugin through the Plugins menu in WordPress
3. Create courses under Posts > Courses
4. Assign posts to a course from the edit post screen or the manage posts screen
5. Optionally configure display settings under Settings > Course Settings

**Upgrading from v1.x with add-on plugins**

If you were using the separate SCC Customizer, SCC Front Display, or SCC Post Meta plugins, deactivate and delete them after updating to v2.0.0. All functionality is now built into this plugin. Your existing Customizer settings and display preferences carry over automatically.

== Frequently Asked Questions ==

= Can a post be assigned to more than one course? =

Yes. If a post belongs to multiple courses, a separate course listing is displayed for each one. The position setting (above, below, both) applies to all listings.

= Can I customize the course listing output? =

Yes, several ways.

**Hooks** — Add actions in your theme's functions.php to insert content at specific points in the output. Available hooks, in order of appearance:

* `scc_before_container`
* `scc_container_top`
* `scc_below_title`
* `scc_below_description`
* `scc_before_toggle`
* `scc_after_toggle`
* `scc_above_list`
* `scc_before_list_item` — receives `$post_id`
* `scc_after_list_item` — receives `$post_id`
* `scc_below_list`
* `scc_container_bottom`
* `scc_after_container`

The toggle link text is filterable via the `course_toggle` filter (default: "full course").

The front display leading and trailing text are filterable via `course_leading_text` and `course_trailing_text`.

The post meta label text is filterable via `written_by` and `written_on`.

**Template override** — Create an `scc_templates/` directory in your active theme and copy any files from `includes/scc_templates/` into it. Your theme versions will take priority.

**Customizer** — Use the Simple Course Creator Design section in Appearance > Customize to adjust colors, borders, padding, and typography for all three output components.

**Custom CSS** — Write CSS in your theme targeting `.scc-post-list`, `.scc-post-meta`, and `.scc-front-display`. Each listing has a `data-course-id` attribute if you need to target a specific course box (`[data-course-id="5"]`). When a post belongs to multiple courses, each listing also carries the `scc-multiple-courses` class and all listings are wrapped in a `.scc-course-group` container.

= Can I add my own styles to the Customizer output? =

Yes. The `scc_add_to_styles` action fires inside the generated `<style>` block. Hook into it to append additional CSS without opening a new style tag.

== Screenshots ==

1. Settings page with display, post meta, and front display options
2. Create and manage courses from Posts > Courses
3. Assign a post to a course from the block editor sidebar
4. Filter and manage posts by course from the manage posts screen
5. Customize the course box, post meta, and front display styles in the Customizer
6. Course listing collapsed
7. Course listing expanded with author and date post meta

== Changelog ==

= 2.1.0 =
* Added: Multiple course support — a post can now belong to more than one course; a separate listing is rendered for each
* Added: `scc_post_types` filter — register the course taxonomy on custom post types without touching the plugin
* Added: `scc-single-course` class on course boxes that belong to a single-course post; `scc-multiple-courses` class on boxes that belong to a multi-course post
* Added: `.scc-course-group` wrapper around all listings when a post belongs to more than one course
* Added: Customizer "Bottom Margin" control for the course box; applies to both standalone boxes and the group wrapper
* Changed: Customizer reorganized into a panel with three sections — Course Box, Post Meta, and Front Display
* Changed: Course listing container changed from `#scc-wrap` to `.scc-post-list` (breaking change — update any custom CSS targeting `#scc-wrap`)
* Changed: Each listing carries a `data-course-id="{term_id}"` attribute for targeting individual courses with CSS (`[data-course-id="5"]`)
* Changed: `scc.css` restructured as a self-documenting reference — every selector the plugin outputs is listed with plain-language comments
* Fixed: Duplicate element ID when `display_position` was set to "both" — IDs are not used on the container element
* Fixed: Front display sentence grammar — "course." vs. "courses." now matches the actual count
* Fixed: JS toggle addClass/removeClass was targeting all toggle links on the page instead of only the clicked one
* Fixed: `is_single()` replaced with `is_singular()` throughout so the plugin works correctly on custom post type singular views
* Fixed: Toggle element changed from `<a href="#">` to `<button type="button">` for correct semantics and accessibility; CSS reset in `scc.css` neutralizes theme button styles
* Fixed: `return false` in toggle JS replaced with `e.preventDefault()` — avoids unintended `stopPropagation()` side effect
* Changed: Toggle button now displays a chevron indicator (▾) that rotates when the list is open; style or remove via `.scc-toggle-post-list::after`
* Changed: `scc-output.php` and `scc-post-listing.js` fully documented — hook reference, template variables, inline comments explaining each conditional block

= 2.0.0 =
* Consolidated: SCC Customizer, SCC Front Display, and SCC Post Meta are now built into this plugin
* Added: Post Meta settings (show/hide author and date) on the settings page
* Added: Front Display toggle on the settings page
* Added: Unified Customizer section — all settings stored in a single scc_customizer option, independent of the active theme
* Added: Upgrade routine to migrate all settings from the former add-on plugins
* Added: Auto-deactivation of retired add-on plugins (SCC Customizer, SCC Front Display, SCC Post Meta) with admin notice on first admin load after upgrade
* Changed: All plugin option keys prefixed with scc_ (course_display_settings → scc_display_settings, taxonomy_{id} → scc_term_{id})
* Fixed: Customizer integer settings were stored as theme_mods — now stored as options so they persist across theme switches
* Fixed: Missing nonce verification and input sanitization on term meta save
* Fixed: Unescaped output throughout the frontend template and display classes
* Fixed: Missing sanitize_callback on all Customizer color settings
* Fixed: Whitelist validation on all settings page select fields
* Fixed: Incorrect WP_Query orderby value ('random' → 'rand')
* Fixed: Duplicate element ID on current post style select field
* Fixed: Logic bug in columns() causing undefined variable on first load
* Updated: Requires at least WordPress 5.0, PHP 7.4

= 1.0.7 =
* Added: Setting for ordering the post listing using the "order" parameter (ascending or descending)
* Removed: "scc_order" filter, replaced by the above setting
* Tweaked: Default border and padding on the post listing display

= 1.0.6 =
* Added: Setting for ordering the post listing using the "orderby" parameter
* Added: "scc_order" filter to control ascending or descending order
* Fixed: PHP notices
* Fixed: Database settings value inconsistencies

= 1.0.5 =
* Tweaked: Code formatting

= 1.0.4 =
* Tweaked: Improved translation strings and updated .pot file

= 1.0.3 =
* Added: Option to select the current post text style (bold, strikethrough, or italic)
* Removed: Current post default bold font weight

= 1.0.2 =
* Fixed: PHP warnings from settings sanitization

= 1.0.1 =
* Added: Disable JavaScript setting to show the course listing without a toggle

= 1.0.0 =
* First stable release
