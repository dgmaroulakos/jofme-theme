<?php
/** php .tests/theme-updates.php */
$file = file_get_contents( dirname( __DIR__ ) . '/inc/theme-updates.php' );
assert( false !== strpos( $file, 'delete_site_transient_update_themes' ), 'Check again must clear GitHub cache' );
assert( false !== strpos( $file, 'jofme_check_updates' ), 'Force check action missing' );
assert( false !== strpos( $file, 'jofme_update_theme_slug' ), 'Theme slug helper missing' );
assert( false !== strpos( $file, 'jofme_clear_update_cache' ), 'Cache clear helper missing' );
assert( false !== strpos( $file, 'function jofme_github_release( $fresh = false )' ), 'Fresh fetch support missing' );
assert( false !== strpos( $file, 'jofme_github_release_from_api' ), 'API fetch helper missing' );
assert( false !== strpos( $file, 'jofme_github_release_from_atom' ), 'Atom fallback missing' );
assert( false !== strpos( $file, 'releases.atom' ), 'releases.atom endpoint missing' );
assert( false !== strpos( $file, 'jofme_github_release_error' ), 'Error transient missing' );
assert( false !== strpos( $file, 'Do not cache failures' ), 'Failure cache note missing' );
echo "PASS: theme updater cache + force-check + API/atom fallback wiring\n";
