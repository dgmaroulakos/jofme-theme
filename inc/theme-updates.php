<?php
/** GitHub release updates for public repositories. @package Jofme */
defined( 'ABSPATH' ) || exit;
function jofme_update_repo_sanitize( $value ) {
 $value = trim( (string) $value );
 $value = preg_replace( '~^https://github\.com/~i', '', $value );
 $value = preg_replace( '~\.git$~', '', rtrim( $value, '/' ) );
 if ( '' !== $value && ! preg_match( '~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~D', $value ) ) {
  add_settings_error( 'jofme_update_repo', 'invalid_repo', __( 'Enter a public GitHub repository as owner/repository.', 'jofme' ) );
  return get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' );
 }
 delete_site_transient( 'jofme_github_release' );
 delete_site_transient( 'update_themes' );
 return $value;
}
function jofme_update_settings() {
 register_setting( 'jofme_updates', 'jofme_update_repo', array( 'type' => 'string', 'sanitize_callback' => 'jofme_update_repo_sanitize', 'default' => 'dgmaroulakos/jofme-theme' ) );
}
add_action( 'admin_init', 'jofme_update_settings' );
function jofme_update_menu() { add_theme_page( __( 'Jofme Updates', 'jofme' ), __( 'Jofme Updates', 'jofme' ), 'manage_options', 'jofme-updates', 'jofme_update_page' ); }
add_action( 'admin_menu', 'jofme_update_menu' );
function jofme_update_page() {
 if ( ! current_user_can( 'manage_options' ) ) { return; }
 echo '<div class="wrap"><h1>' . esc_html__( 'Jofme — GitHub updates', 'jofme' ) . '</h1>';
 settings_errors();
 echo '<p>' . esc_html__( 'Connect a public GitHub repository. Stable releases must include a jofme.zip asset. Updates appear under Dashboard → Updates.', 'jofme' ) . '</p><form method="post" action="options.php">';
 settings_fields( 'jofme_updates' );
 echo '<p><label for="jofme-update-repo">' . esc_html__( 'GitHub repository (owner/repository)', 'jofme' ) . '</label></p><input class="regular-text" id="jofme-update-repo" name="jofme_update_repo" value="' . esc_attr( get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' ) ) . '">';
 submit_button();
 echo '</form><p>' . esc_html__( 'After saving, use “Check again” on Dashboard → Updates. Release checks are cached for one hour. Save this setting again to clear the cache.', 'jofme' ) . '</p></div>';
}
function jofme_github_release() {
 $repo = get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' );
 if ( ! preg_match( '~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~D', $repo ) ) { return false; }
 $cached = get_site_transient( 'jofme_github_release' );
 if ( is_array( $cached ) && isset( $cached['repo'] ) && $cached['repo'] === $repo ) { return $cached['release']; }
 $response = wp_remote_get( 'https://api.github.com/repos/' . $repo . '/releases/latest', array( 'timeout' => 10, 'headers' => array( 'Accept' => 'application/vnd.github+json', 'User-Agent' => 'Jofme-Theme-Updater' ) ) );
 if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { return false; }
 $data = json_decode( wp_remote_retrieve_body( $response ), true );
 if ( ! is_array( $data ) || empty( $data['tag_name'] ) || ! empty( $data['draft'] ) || ! empty( $data['prerelease'] ) ) { return false; }
 $version = ltrim( $data['tag_name'], 'v' );
 if ( ! preg_match( '/^\d+\.\d+\.\d+$/D', $version ) ) { return false; }
 $release = false;
 foreach ( isset( $data['assets'] ) && is_array( $data['assets'] ) ? $data['assets'] : array() as $asset ) {
  if ( isset( $asset['name'], $asset['browser_download_url'] ) && 'jofme.zip' === $asset['name'] ) {
   $package = $asset['browser_download_url'];
   if ( 0 !== strpos( $package, 'https://github.com/' . $repo . '/releases/download/' ) ) { continue; }
   $release = array( 'version' => $version, 'package' => esc_url_raw( $package ), 'url' => 'https://github.com/' . $repo );
   break;
  }
 }
 set_site_transient( 'jofme_github_release', array( 'repo' => $repo, 'release' => $release ), HOUR_IN_SECONDS );
 return $release;
}
function jofme_theme_update( $transient ) {
 if ( ! is_object( $transient ) || empty( $transient->checked ) ) { return $transient; }
 $theme = 'jofme';
 if ( ! wp_get_theme( $theme )->exists() ) { return $transient; }
 $release = jofme_github_release();
 if ( ! $release ) { return $transient; }
 $installed = wp_get_theme( $theme )->get( 'Version' );
 $update = array( 'theme' => $theme, 'new_version' => $release['version'], 'url' => $release['url'], 'package' => $release['package'] );
 if ( version_compare( $release['version'], $installed, '>' ) ) { $transient->response[ $theme ] = $update; unset( $transient->no_update[ $theme ] ); }
 else { $transient->no_update[ $theme ] = $update; unset( $transient->response[ $theme ] ); }
 return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'jofme_theme_update' );
