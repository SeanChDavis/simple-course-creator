Simple Course Creator
=====================

https://scc.crispydiv.com

Organize WordPress posts into courses and display a course listing within each post.

---

## Table of Contents

- [What's Included](#whats-included)
- [How It Works](#how-it-works)
- [Multiple Courses](#multiple-courses)
- [Settings](#settings)
- [Post Meta](#post-meta)
- [Front Display](#front-display)
- [Theme Overrides](#theme-overrides)
  - [Customizer](#customizer)
  - [Hooks & Filters](#hooks--filters)
  - [Template File Overrides](#template-file-overrides)
  - [Custom Post Type Support](#custom-post-type-support)
- [Bugs and Contributions](#bugs-and-contributions)
- [License](#license)

---

## What's Included

As of v2.0.0, Simple Course Creator is a single plugin with no add-ons required. Customizer styles, post meta output, and front display are all built in.

If you were using the separate SCC Customizer, SCC Post Meta, or SCC Front Display plugins, deactivate and delete them after upgrading. Your existing settings carry over automatically.

If you delete the plugin, all of its data is removed from the database automatically — settings, Customizer values, course terms, and term meta. Your posts are not affected.

---

## How It Works

Once activated, a new taxonomy is added to your Posts menu called "Courses." Courses are created just like categories and tags.

When creating a new course, give it a title and a description. The description is optional but recommended — it appears above the post listing inside the course box.

![Create New Course](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-create-course.jpg)

Courses can also be created or selected while editing a post, just like adding a category.

![Edit Post — Create or Select Course](https://scc.crispydiv.com/wp-content/uploads/2026/03/Screenshot-3-Post-Edit-w-Course-scaled.png)
![All Posts — Filter by Course](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-all-posts.jpg)

Once a post is assigned to a course, a course listing appears in the post content — as long as it isn't the only post in that course. The listing shows the course title, the course description, and a toggle button. Clicking the button expands the listing to reveal all posts in the course. All posts are linked except the current one.

![Course Listing — Collapsed](https://scc.crispydiv.com/wp-content/uploads/2026/03/Screenshot-5-Included-Post-Course-Collapsed-scaled.png)
![Course Listing — Expanded](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-post-listing-expanded.jpg)

Styles are minimal. Theme styles are inherited as much as possible.

---

## Multiple Courses

A post can belong to more than one course. When it does, a separate course listing is rendered for each assigned course. All listings are wrapped in a `.scc-course-group` container, and each listing carries the `scc-multiple-courses` class. Posts that belong to only one course carry the `scc-single-course` class instead.

The front display indicator on archive pages handles multiple courses naturally — "This post is part of the Course A, Course B courses."

---

## Settings

Settings are under **Settings > Course Settings**.

![Course Settings Page](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-settings.jpg)

**Display position** — Show the course listing above post content, below it, both, or not at all (preserves course data without displaying it).

**List style** — Numbered list, bulleted list, or no list indicator.

**Sort by** — Date, title, author, last modified, comment count, or random.

**Order** — Ascending or descending.

**Current post style** — Highlight the current post in the listing as bold, italic, strikethrough, or no style.

**Disable JavaScript** — Removes the toggle and shows the post listing expanded on page load.

**Show author / Show date** — Toggle the post meta output (author name and publish date) beneath each item in the course listing. See [Post Meta](#post-meta).

**Enable front display** — Toggle the course indicator on the blog home, archives, and search results. See [Front Display](#front-display).

---

## Post Meta

When enabled, each item in the course listing shows the post author and publish date beneath the title.

![Post Meta Output](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-post-meta.jpg)

Toggle both fields independently under **Settings > Course Settings**.

The label text is filterable in your theme's `functions.php`:

```php
add_filter( 'written_by', function( $text ) {
    return 'by';
} );

add_filter( 'written_on', function( $text ) {
    return 'published';
} );
```

---

## Front Display

When enabled, a course indicator appears beneath each post excerpt on the blog home, archive pages, and search results — wherever that post belongs to a course.

![Front Display Output](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-front-display.jpg)

Toggle it under **Settings > Course Settings**. The leading and trailing text are filterable:

```php
add_filter( 'course_leading_text', function( $text ) {
    return 'Part of the';
} );

add_filter( 'course_trailing_text', function( $text ) {
    return 'series.';
} );
```

The trailing text defaults to `"course."` for one course and `"courses."` for more than one. A custom filter value is used regardless of count.

---

## Theme Overrides

### Customizer

**Appearance > Customize > Simple Course Creator** provides a panel with three sections — Course Box, Post Meta, and Front Display — for adjusting colors, borders, padding, font sizes, and spacing without writing CSS.

![Customizer — SCC Design Panel](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-customizer.jpg)

The `scc_add_to_styles` action fires inside the generated `<style>` block if you need to append additional CSS without opening a separate style tag.

### Hooks & Filters

Add actions in your theme's `functions.php` to insert content at specific points in the course listing output. Hooks fire in this order:

| Hook | Notes |
|---|---|
| `scc_before_container` | Before the `.scc-post-list` div |
| `scc_container_top` | After the opening `.scc-post-list` div |
| `scc_below_title` | After the course title |
| `scc_below_description` | After the course description |
| `scc_before_toggle` | Before the toggle button text |
| `scc_after_toggle` | After the toggle button text |
| `scc_above_list` | Before the list opening tag |
| `scc_before_list_item` | Before each list item — receives `$post_id` |
| `scc_after_list_item` | After each list item — receives `$post_id` |
| `scc_below_list` | After the list closing tag |
| `scc_container_bottom` | Before the closing `.scc-post-list` div |
| `scc_after_container` | After the closing `.scc-post-list` div |

Example:

```php
add_action( 'scc_container_top', function() {
    echo '<p>Now reading a course post.</p>';
} );
```

For hooks that receive `$post_id`:

```php
add_action( 'scc_before_list_item', function( $post_id ) {
    echo '<span class="my-label">' . esc_html( get_the_category_list( ', ', '', $post_id ) ) . '</span>';
} );
```

**Filters:**

| Filter | Default | Notes |
|---|---|---|
| `course_toggle` | `"full course"` | Toggle button label |
| `course_leading_text` | `"This post is part of the"` | Front display leading text |
| `course_trailing_text` | `"course."` / `"courses."` | Front display trailing text; defaults match singular/plural count |
| `written_by` | `"written by"` | Post meta author label |
| `written_on` | `"on"` | Post meta date label |
| `scc_post_types` | `array( 'post' )` | Post types registered to the course taxonomy — see [Custom Post Type Support](#custom-post-type-support) |

**CSS selectors:**

See `includes/scc_templates/scc.css` for a full annotated reference of every selector the plugin outputs, with comments describing each one and the conditions under which it appears.

Key selectors:

| Selector | Notes |
|---|---|
| `.scc-post-list` | Course listing container — always present |
| `.scc-single-course` | Added when post belongs to one course |
| `.scc-multiple-courses` | Added when post belongs to more than one course |
| `.scc-course-group` | Wraps all listings on a multi-course post |
| `[data-course-id="N"]` | Targets a specific course box by term ID |
| `.scc-post-list-title` | Course title heading |
| `.scc-course-description` | Course description wrapper |
| `.scc-toggle-post-list` | Toggle button |
| `.scc-toggle-post-list.scc-opened` | Toggle button when list is expanded |
| `.scc-post-container` | Post list wrapper (hidden by default) |
| `.scc-show-posts` | Post list wrapper when JS is disabled (always visible) |
| `.scc-post-item` | Each `<li>` in the listing |
| `.scc-list-item` | Span wrapping each link or current-post span |
| `.scc-current-post` | Span on the currently viewed post (no link) |
| `.scc-post-meta` | Author/date output beneath each item |
| `.scc-front-display` | Front display indicator on archives/home/search |

### Template File Overrides

To override the course listing template, create a directory called `scc_templates` in the root of your active theme and copy any files from the plugin's `includes/scc_templates/` directory into it. Theme files take priority over plugin files.

Copy the files — don't create empty ones. An empty file still overrides.

The template file (`scc-output.php`) is fully documented — every hook, filter, template variable, and conditional block is explained inline.

### Custom Post Type Support

By default the course taxonomy is registered on `post` only. To add support for a custom post type, filter `scc_post_types`:

```php
add_filter( 'scc_post_types', function( $types ) {
    $types[] = 'lesson';
    return $types;
} );
```

This registers the taxonomy on the CPT, adds the Course column to its admin list table, and includes CPT posts in course listings.

---

## Upgrading from v2.0.x

**v2.1.0 contains one breaking CSS change.**

The course listing container changed from `#scc-wrap` to `.scc-post-list`. If you have custom CSS targeting `#scc-wrap`, update it to `.scc-post-list`. Each listing also carries a `data-course-id="{term_id}"` attribute for targeting a specific course box.

---

## Bugs and Contributions

If you notice any mistakes, feel free to fork the repo and submit a pull request. The same goes for features or improvements.

---

## License

This plugin, like WordPress, is licensed under the GPL. Do what you want with it.
