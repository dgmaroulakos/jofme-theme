<?php
/** GitHub release updates for public repositories. @package Jofme */
defined( 'ABSPATH' ) || exit;

/**
 * Theme directory slug used for update payloads.
 *
 * Prefers an installed theme named Jofme (parent or current), not only `jofme`.
 *
 * @return string
 */
function jofme_update_theme_slug() {
 $current = wp_get_theme();
 if ( 'Jofme' === $current->get( 'Name' ) ) {
  return $current->get_stylesheet();
 }
 $parent = $current->parent();
 if ( $parent && 'Jofme' === $parent->get( 'Name' ) ) {
  return $parent->get_stylesheet();
 }
 if ( wp_get_theme( 'jofme' )->exists() ) {
  return 'jofme';
 }
 return '';
}

/**
 * Clear cached GitHub release + WordPress theme update payload.
 */
function jofme_clear_update_cache() {
 delete_site_transient( 'jofme_github_release' );
 delete_site_transient( 'update_themes' );
}

/**
 * When Dashboard → Updates → Check again runs, also refetch GitHub.
 */
function jofme_clear_github_release_on_theme_check() {
 delete_site_transient( 'jofme_github_release' );
}
add_action( 'delete_site_transient_update_themes', 'jofme_clear_github_release_on_theme_check' );

function jofme_update_repo_sanitize( $value ) {
 $value = trim( (string) $value );
 $value = preg_replace( '~^https://github\.com/~i', '', $value );
 $value = preg_replace( '~\.git$~', '', rtrim( $value, '/' ) );
 if ( '' !== $value && ! preg_match( '~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~D', $value ) ) {
  add_settings_error( 'jofme_update_repo', 'invalid_repo', __( 'Enter a public GitHub repository as owner/repository.', 'jofme' ) );
  return get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' );
 }
 jofme_clear_update_cache();
 return $value;
}

function jofme_update_settings() {
 register_setting( 'jofme_updates', 'jofme_update_repo', array( 'type' => 'string', 'sanitize_callback' => 'jofme_update_repo_sanitize', 'default' => 'dgmaroulakos/jofme-theme' ) );
}
add_action( 'admin_init', 'jofme_update_settings' );

function jofme_update_menu() {
 add_theme_page( __( 'Jofme Updates', 'jofme' ), __( 'Jofme Updates', 'jofme' ), 'manage_options', 'jofme-updates', 'jofme_update_page' );
}
add_action( 'admin_menu', 'jofme_update_menu' );

/**
 * Handle “Check for updates now” from the Jofme Updates screen.
 */
function jofme_handle_force_update_check() {
 if ( ! current_user_can( 'manage_options' ) ) {
  return;
 }
 if ( empty( $_GET['jofme_check_updates'] ) || empty( $_GET['_wpnonce'] ) ) {
  return;
 }
 if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'jofme_check_updates' ) ) {
  return;
 }
 jofme_clear_update_cache();
 wp_clean_update_cache();
 wp_update_themes();
 wp_safe_redirect( admin_url( 'update-core.php?force-check=1' ) );
 exit;
}
add_action( 'admin_init', 'jofme_handle_force_update_check', 1 );

function jofme_update_page() {
 if ( ! current_user_can( 'manage_options' ) ) {
  return;
 }
 $slug      = jofme_update_theme_slug();
 $installed = $slug ? wp_get_theme( $slug )->get( 'Version' ) : '';
 $release   = jofme_github_release( true );
 echo '<div class="wrap"><h1>' . esc_html__( 'Jofme — GitHub updates', 'jofme' ) . '</h1>';
 settings_errors();
 echo '<p>' . esc_html__( 'Connect a public GitHub repository. Stable releases must include a jofme.zip asset. Updates appear under Dashboard → Updates.', 'jofme' ) . '</p>';
 if ( $slug && $installed ) {
  echo '<p><strong>' . esc_html__( 'Installed', 'jofme' ) . ':</strong> ' . esc_html( $installed ) . ' <code>' . esc_html( $slug ) . '</code>';
  if ( is_array( $release ) && ! empty( $release['version'] ) ) {
   echo ' &nbsp;|&nbsp; <strong>' . esc_html__( 'Latest GitHub', 'jofme' ) . ':</strong> ' . esc_html( $release['version'] );
   if ( version_compare( $release['version'], $installed, '>' ) ) {
    echo ' — <a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">' . esc_html__( 'Update available', 'jofme' ) . '</a>';
   } elseif ( version_compare( $release['version'], $installed, '==' ) ) {
    echo ' — ' . esc_html__( 'You are up to date.', 'jofme' );
   }
  } else {
   echo ' &nbsp;|&nbsp; ' . esc_html__( 'Could not read the latest GitHub release. Check the repository name and that the site can reach api.github.com.', 'jofme' );
  }
  echo '</p>';
 } else {
  echo '<p>' . esc_html__( 'No installed theme named Jofme was found. The update ZIP must unpack into a folder that WordPress recognizes as this theme (usually jofme).', 'jofme' ) . '</p>';
 }
 echo '<p><a class="button button-primary" href="' . esc_url( wp_nonce_url( admin_url( 'themes.php?page=jofme-updates&jofme_check_updates=1' ), 'jofme_check_updates' ) ) . '">' . esc_html__( 'Check for updates now', 'jofme' ) . '</a></p>';
 echo '<form method="post" action="options.php">';
 settings_fields( 'jofme_updates' );
 echo '<p><label for="jofme-update-repo">' . esc_html__( 'GitHub repository (owner/repository)', 'jofme' ) . '</label></p><input class="regular-text" id="jofme-update-repo" name="jofme_update_repo" value="' . esc_attr( get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' ) ) . '">';
 submit_button();
 echo '</form><p>' . esc_html__( 'Release checks are cached for one hour. Use “Check for updates now”, or save this setting again, to clear the cache. Then open Dashboard → Updates.', 'jofme' ) . '</p></div>';
}

/**
 * Fetch the latest public GitHub release with a jofme.zip asset.
 *
 * @param bool $fresh Bypass the one-hour cache.
 * @return array{version:string,package:string,url:string}|false
 */
function jofme_github_release( $fresh = false ) {
 $repo = get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' );
 if ( ! preg_match( '~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~D', $repo ) ) {
  return false;
 }
 if ( ! $fresh ) {
  $cached = get_site_transient( 'jofme_github_release' );
  if ( is_array( $cached ) && isset( $cached['repo'] ) && $cached['repo'] === $repo ) {
   return $cached['release'];
  }
 }
 $response = wp_remote_get(
  'https://api.github.com/repos/' . $repo . '/releases/latest',
  array(
   'timeout' => 10,
   'headers' => array(
    'Accept'     => 'application/vnd.github+json',
    'User-Agent' => 'Jofme-Theme-Updater',
   ),
  )
 );
 if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
  return false;
 }
 $data = json_decode( wp_remote_retrieve_body( $response ), true );
 if ( ! is_array( $data ) || empty( $data['tag_name'] ) || ! empty( $data['draft'] ) || ! empty( $data['prerelease'] ) ) {
  return false;
 }
 $version = ltrim( $data['tag_name'], 'v' );
 if ( ! preg_match( '/^\d+\.\d+\.\d+$/D', $version ) ) {
  return false;
 }
 $release = false;
 foreach ( isset( $data['assets'] ) && is_array( $data['assets'] ) ? $data['assets'] : array() as $asset ) {
  if ( isset( $asset['name'], $asset['browser_download_url'] ) && 'jofme.zip' === $asset['name'] ) {
   $package = $asset['browser_download_url'];
   if ( 0 !== strpos( $package, 'https://github.com/' . $repo . '/releases/download/' ) ) {
    continue;
   }
   $release = array(
    'version' => $version,
    'package' => esc_url_raw( $package ),
    'url'     => 'https://github.com/' . $repo,
   );
   break;
  }
 }
 set_site_transient( 'jofme_github_release', array( 'repo' => $repo, 'release' => $release ), HOUR_IN_SECONDS );
 return $release;
}

function jofme_theme_update( $transient ) {
 if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
  return $transient;
 }
 $theme = jofme_update_theme_slug();
 if ( '' === $theme || ! wp_get_theme( $theme )->exists() ) {
  return $transient;
 }
 $release = jofme_github_release();
 if ( ! $release ) {
  return $transient;
 }
 $installed = wp_get_theme( $theme )->get( 'Version' );
 $update    = array(
  'theme'       => $theme,
  'new_version' => $release['version'],
  'url'         => $release['url'],
  'package'     => $release['package'],
 );
 if ( version_compare( $release['version'], $installed, '>' ) ) {
  $transient->response[ $theme ] = $update;
  unset( $transient->no_update[ $theme ] );
 } else {
  $transient->no_update[ $theme ] = $update;
  unset( $transient->response[ $theme ] );
 }
 return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'jofme_theme_update' );
