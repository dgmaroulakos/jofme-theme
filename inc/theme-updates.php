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
	delete_site_transient( 'jofme_github_release_error' );
	delete_site_transient( 'update_themes' );
}

/**
 * When Dashboard → Updates → Check again runs, also refetch GitHub.
 */
function jofme_clear_github_release_on_theme_check() {
	delete_site_transient( 'jofme_github_release' );
	delete_site_transient( 'jofme_github_release_error' );
}
add_action( 'delete_site_transient_update_themes', 'jofme_clear_github_release_on_theme_check' );

/**
 * Shared request headers for GitHub / github.com.
 *
 * @return array<string,string>
 */
function jofme_update_request_headers() {
	$ua = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ) . '; Jofme-Theme-Updater/' . ( defined( 'JOFME_VERSION' ) ? JOFME_VERSION : '0' );
	return array(
		'Accept'     => 'application/vnd.github+json, application/atom+xml;q=0.9, */*;q=0.8',
		'User-Agent' => $ua,
	);
}

/**
 * Remember why the latest release could not be read.
 *
 * @param string $message Error message.
 */
function jofme_set_github_release_error( $message ) {
	set_site_transient( 'jofme_github_release_error', (string) $message, 15 * MINUTE_IN_SECONDS );
}

/**
 * @return string
 */
function jofme_get_github_release_error() {
	$error = get_site_transient( 'jofme_github_release_error' );
	return is_string( $error ) ? $error : '';
}

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
			if ( ! empty( $release['source'] ) ) {
				echo ' <span class="description">(' . esc_html( $release['source'] ) . ')</span>';
			}
			if ( version_compare( $release['version'], $installed, '>' ) ) {
				echo ' — <a href="' . esc_url( admin_url( 'update-core.php' ) ) . '">' . esc_html__( 'Update available', 'jofme' ) . '</a>';
			} elseif ( version_compare( $release['version'], $installed, '==' ) ) {
				echo ' — ' . esc_html__( 'You are up to date.', 'jofme' );
			}
		} else {
			echo ' &nbsp;|&nbsp; ' . esc_html__( 'Could not read the latest GitHub release.', 'jofme' );
			$error = jofme_get_github_release_error();
			if ( $error ) {
				echo '<br><span class="description">' . esc_html( $error ) . '</span>';
			} else {
				echo '<br><span class="description">' . esc_html__( 'Check the repository name and that the site can reach api.github.com or github.com.', 'jofme' ) . '</span>';
			}
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
 * Build a release payload and confirm jofme.zip is downloadable.
 *
 * @param string $repo    owner/name.
 * @param string $version X.Y.Z.
 * @param string $source  api|atom.
 * @return array{version:string,package:string,url:string,source:string}|WP_Error
 */
function jofme_github_release_package( $repo, $version, $source ) {
	if ( ! preg_match( '/^\d+\.\d+\.\d+$/D', $version ) ) {
		return new WP_Error( 'bad_version', __( 'Latest release tag is not a stable X.Y.Z version.', 'jofme' ) );
	}
	$package = 'https://github.com/' . $repo . '/releases/download/v' . $version . '/jofme.zip';
	$head    = wp_remote_head(
		$package,
		array(
			'timeout'     => 12,
			'redirection' => 5,
			'headers'     => jofme_update_request_headers(),
		)
	);
	if ( is_wp_error( $head ) ) {
		return new WP_Error(
			'zip_unreachable',
			sprintf(
				/* translators: 1: zip URL, 2: error message */
				__( 'Could not verify %1$s (%2$s).', 'jofme' ),
				$package,
				$head->get_error_message()
			)
		);
	}
	$code = (int) wp_remote_retrieve_response_code( $head );
	if ( $code < 200 || $code >= 400 ) {
		return new WP_Error(
			'zip_missing',
			sprintf(
				/* translators: 1: version, 2: HTTP status */
				__( 'Release v%1$s has no downloadable jofme.zip (HTTP %2$d).', 'jofme' ),
				$version,
				$code
			)
		);
	}
	return array(
		'version' => $version,
		'package' => esc_url_raw( $package ),
		'url'     => 'https://github.com/' . $repo,
		'source'  => $source,
	);
}

/**
 * Read latest stable release via GitHub API.
 *
 * @param string $repo owner/name.
 * @return array{version:string,package:string,url:string,source:string}|WP_Error
 */
function jofme_github_release_from_api( $repo ) {
	$response = wp_remote_get(
		'https://api.github.com/repos/' . $repo . '/releases/latest',
		array(
			'timeout' => 12,
			'headers' => jofme_update_request_headers(),
		)
	);
	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'api_http',
			sprintf(
				/* translators: %s: transport error */
				__( 'api.github.com failed: %s', 'jofme' ),
				$response->get_error_message()
			)
		);
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		$body = (string) wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$hint = ( is_array( $data ) && ! empty( $data['message'] ) ) ? (string) $data['message'] : '';
		return new WP_Error(
			'api_status',
			trim(
				sprintf(
					/* translators: 1: HTTP status, 2: optional GitHub message */
					__( 'api.github.com returned HTTP %1$d. %2$s', 'jofme' ),
					$code,
					$hint
				)
			)
		);
	}
	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || empty( $data['tag_name'] ) || ! empty( $data['draft'] ) || ! empty( $data['prerelease'] ) ) {
		return new WP_Error( 'api_payload', __( 'GitHub API response was not a stable release.', 'jofme' ) );
	}
	$version = ltrim( (string) $data['tag_name'], 'v' );
	$package = '';
	foreach ( isset( $data['assets'] ) && is_array( $data['assets'] ) ? $data['assets'] : array() as $asset ) {
		if ( isset( $asset['name'], $asset['browser_download_url'] ) && 'jofme.zip' === $asset['name'] ) {
			$candidate = (string) $asset['browser_download_url'];
			if ( 0 === strpos( $candidate, 'https://github.com/' . $repo . '/releases/download/' ) ) {
				$package = $candidate;
				break;
			}
		}
	}
	if ( '' === $package ) {
		return jofme_github_release_package( $repo, $version, 'api' );
	}
	return array(
		'version' => $version,
		'package' => esc_url_raw( $package ),
		'url'     => 'https://github.com/' . $repo,
		'source'  => 'api',
	);
}

/**
 * Fallback when api.github.com is blocked: parse github.com releases.atom.
 *
 * @param string $repo owner/name.
 * @return array{version:string,package:string,url:string,source:string}|WP_Error
 */
function jofme_github_release_from_atom( $repo ) {
	$response = wp_remote_get(
		'https://github.com/' . $repo . '/releases.atom',
		array(
			'timeout' => 12,
			'headers' => jofme_update_request_headers(),
		)
	);
	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'atom_http',
			sprintf(
				/* translators: %s: transport error */
				__( 'github.com releases.atom failed: %s', 'jofme' ),
				$response->get_error_message()
			)
		);
	}
	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		return new WP_Error(
			'atom_status',
			sprintf(
				/* translators: %d: HTTP status */
				__( 'github.com releases.atom returned HTTP %d.', 'jofme' ),
				$code
			)
		);
	}
	$body = (string) wp_remote_retrieve_body( $response );
	if ( ! preg_match( '~releases/tag/v?(\d+\.\d+\.\d+)~', $body, $match ) ) {
		return new WP_Error( 'atom_parse', __( 'Could not parse a stable version from releases.atom.', 'jofme' ) );
	}
	return jofme_github_release_package( $repo, $match[1], 'atom' );
}

/**
 * Fetch the latest public GitHub release with a jofme.zip asset.
 *
 * Tries the GitHub API first, then falls back to releases.atom when the API
 * is unreachable (common on hosts that block api.github.com).
 *
 * @param bool $fresh Bypass the one-hour cache.
 * @return array{version:string,package:string,url:string,source?:string}|false
 */
function jofme_github_release( $fresh = false ) {
	$repo = get_option( 'jofme_update_repo', 'dgmaroulakos/jofme-theme' );
	if ( ! preg_match( '~^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$~D', $repo ) ) {
		jofme_set_github_release_error( __( 'Invalid GitHub repository name.', 'jofme' ) );
		return false;
	}
	if ( ! $fresh ) {
		$cached = get_site_transient( 'jofme_github_release' );
		if ( is_array( $cached ) && isset( $cached['repo'], $cached['release'] ) && $cached['repo'] === $repo && is_array( $cached['release'] ) ) {
			return $cached['release'];
		}
	}

	$api = jofme_github_release_from_api( $repo );
	if ( is_array( $api ) ) {
		delete_site_transient( 'jofme_github_release_error' );
		set_site_transient( 'jofme_github_release', array( 'repo' => $repo, 'release' => $api ), HOUR_IN_SECONDS );
		return $api;
	}

	$atom = jofme_github_release_from_atom( $repo );
	if ( is_array( $atom ) ) {
		delete_site_transient( 'jofme_github_release_error' );
		set_site_transient( 'jofme_github_release', array( 'repo' => $repo, 'release' => $atom ), HOUR_IN_SECONDS );
		return $atom;
	}

	$parts = array();
	if ( is_wp_error( $api ) ) {
		$parts[] = $api->get_error_message();
	}
	if ( is_wp_error( $atom ) ) {
		$parts[] = $atom->get_error_message();
	}
	jofme_set_github_release_error( implode( ' ', $parts ) );
	// Do not cache failures for an hour — allow a quick retry.
	return false;
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
