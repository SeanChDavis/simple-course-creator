Simple Course Creator
=====================

https://scc.crispydiv.com

Organize WordPress posts into courses and display a course listing within each post.

---

## Table of Contents

- [What's Included](#whats-included)
- [How It Works](#how-it-works)
- [Settings](#settings)
- [Post Meta](#post-meta)
- [Front Display](#front-display)
- [Theme Overrides](#theme-overrides)
  - [Customizer](#customizer)
  - [Hooks & Filters](#hooks--filters)
  - [Template File Overrides](#template-file-overrides)
- [Bugs and Contributions](#bugs-and-contributions)
- [License](#license)

---

## What's Included

As of v2.0.0, Simple Course Creator is a single plugin with no add-ons required. Customizer styles, post meta output, and front display are all built in.

If you were using the separate SCC Customizer, SCC Post Meta, or SCC Front Display plugins, deactivate and delete them after upgrading. Your existing settings carry over automatically.

---

## How It Works

Once activated, a new taxonomy is added to your Posts menu called "Courses." Courses are created just like categories and tags.

When creating a new course, give it a title and a description. The description is optional but recommended — it appears above the post listing inside the course box.

![Create New Course](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-create-course.jpg)

Courses can also be created or selected while editing a post, just like adding a category.

![Edit Post — Create or Select Course](https://scc.crispydiv.com/wp-content/uploads/2026/03/Screenshot-3-Post-Edit-w-Course-scaled.png)
![All Posts — Filter by Course](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-all-posts.jpg)

Once a post is assigned to a course, a course listing appears in the post content — as long as it isn't the only post in that course. The listing shows the course title, the course description, and a toggle link. Clicking the link expands the listing to reveal all posts in the course. All posts are linked except the current one.

![Course Listing — Collapsed](https://scc.crispydiv.com/wp-content/uploads/2026/03/Screenshot-5-Included-Post-Course-Collapsed-scaled.png)
![Course Listing — Expanded](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-post-listing-expanded.jpg)

Styles are minimal. Theme styles are inherited as much as possible.

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

---

## Theme Overrides

### Customizer

The **Simple Course Creator Design** section in **Appearance > Customize** lets you adjust colors, borders, padding, and font sizes for all three output components — the course listing box, post meta, and front display — without writing CSS.

![Customizer — SCC Design Section](https://scc.crispydiv.com/wp-content/uploads/2026/04/scc-customizer.jpg)

The `scc_add_to_styles` action fires inside the generated `<style>` block if you need to append additional CSS without opening a separate style tag.

### Hooks & Filters

Add actions in your theme's `functions.php` to insert content at specific points in the course listing output. Hooks fire in this order:

| Hook | Notes |
|---|---|
| `scc_before_container` | Before the `#scc-wrap` div |
| `scc_container_top` | After the opening `#scc-wrap` div |
| `scc_below_title` | After the course title |
| `scc_below_description` | After the course description |
| `scc_before_toggle` | Before the toggle link text |
| `scc_after_toggle` | After the toggle link text |
| `scc_above_list` | Before the list opening tag |
| `scc_before_list_item` | Before each list item — receives `$post_id` |
| `scc_after_list_item` | After each list item — receives `$post_id` |
| `scc_below_list` | After the list closing tag |
| `scc_container_bottom` | Before the closing `#scc-wrap` div |
| `scc_after_container` | After the `#scc-wrap` div |

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
| `course_toggle` | `"full course"` | Toggle link text |
| `course_leading_text` | `"This post is part of the"` | Front display leading text |
| `course_trailing_text` | `"course."` | Front display trailing text |
| `written_by` | `"written by"` | Post meta author label |
| `written_on` | `"on"` | Post meta date label |

### Template File Overrides

To override the course listing template, create a directory called `scc_templates` in the root of your active theme and copy any files from the plugin's `includes/scc_templates/` directory into it. Theme files take priority over plugin files.

Copy the files — don't create empty ones. An empty file still overrides.

---

## Bugs and Contributions

If you notice any mistakes, feel free to fork the repo and submit a pull request. The same goes for features or improvements.

---

## License

This plugin, like WordPress, is licensed under the GPL. Do what you want with it.
