<?php
/**
 * Uninstall Simple Course Creator
 *
 * Runs when the plugin is deleted from the WordPress admin. Removes all
 * plugin data from the database — options, term meta, course taxonomy terms,
 * and their post relationships.
 *
 * Posts themselves are not touched. They belong to the user's content.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// -------------------------------------------------------------------------
// Options
// -------------------------------------------------------------------------

delete_option( 'scc_db_version' );
delete_option( 'scc_display_settings' );
delete_option( 'scc_customizer' );

// -------------------------------------------------------------------------
// Term meta
// -------------------------------------------------------------------------

$wpdb->delete( $wpdb->termmeta, array( 'meta_key' => 'scc_post_list_title' ), array( '%s' ) );

// -------------------------------------------------------------------------
// Course taxonomy — terms, relationships, and taxonomy rows
// -------------------------------------------------------------------------

$term_taxonomy_ids = $wpdb->get_col(
	"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'course'"
);

$term_ids = $wpdb->get_col(
	"SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'course'"
);

if ( ! empty( $term_taxonomy_ids ) ) {
	$placeholders = implode( ',', array_fill( 0, count( $term_taxonomy_ids ), '%d' ) );

	// Remove post-to-course relationships.
	$wpdb->query( $wpdb->prepare(
		"DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ($placeholders)",
		...$term_taxonomy_ids
	) );

	// Remove course taxonomy rows.
	$wpdb->query(
		"DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'course'"
	);
}

// Remove the terms themselves, but only if they are not used in any other taxonomy.
foreach ( $term_ids as $term_id ) {
	$other_uses = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE term_id = %d",
		$term_id
	) );

	if ( 0 === $other_uses ) {
		$wpdb->delete( $wpdb->terms, array( 'term_id' => (int) $term_id ), array( '%d' ) );
	}
}
