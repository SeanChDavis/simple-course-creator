/**
 * SCC course listing toggle
 *
 * THEME OVERRIDE: Create a folder called scc_templates/ in the root of your
 * active theme and copy this file into it. Your version takes priority over
 * the plugin's.
 *
 * Behavior
 * --------
 * Listens for clicks on .scc-toggle-post-list (the toggle button inside each
 * .scc-post-list container). On click, slides the sibling .scc-post-container
 * open or closed and adds/removes .scc-opened on the button for CSS targeting.
 *
 * Each toggle operates independently — clicking one button on a page that
 * has multiple course listings (multi-course post or "both" display position)
 * only affects the container that belongs to that specific button.
 *
 * This script is only enqueued when Disable JavaScript is off (the default).
 * When Disable JavaScript is on, .scc-show-posts is added to the container
 * server-side and the listing is always visible.
 *
 * Dependencies: jQuery (registered as 'jquery' in WordPress core)
 */
(function($){
	$('.scc-toggle-post-list').on('click', function(e){
		e.preventDefault();
		var $toggle  = $(this);
		var postList = $toggle.siblings('.scc-post-container');
		if ( postList.css('display') === 'none' ) {
			postList.slideDown();
			$toggle.addClass('scc-opened');
		} else {
			postList.slideUp();
			$toggle.removeClass('scc-opened');
		}
	});
})(jQuery);
