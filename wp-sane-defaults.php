<?php
/**
 * Plugin Name: WP Sane Defaults
 * Description: Fixes common insecure and annoying WordPress defaults.
 * Version: 1.0.0
 * Author: Giant Peach
 * Author URI: https://giantpeach.agency
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Disable the Users REST API endpoint for unauthenticated requests.
 */
add_filter('rest_endpoints', function (array $endpoints): array {
    if (! is_user_logged_in()) {
        unset($endpoints['/wp/v2/users']);
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

/**
 * Disable XML-RPC entirely.
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Remove the X-Pingback header.
 */
add_filter('wp_headers', function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});

/**
 * Disable file editing via the admin.
 */
if (! defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/**
 * Remove unnecessary meta tags from <head>.
 */
remove_action('wp_head', 'wp_generator');               // WordPress version
remove_action('wp_head', 'wlwmanifest_link');            // Windows Live Writer
remove_action('wp_head', 'rsd_link');                    // Really Simple Discovery
remove_action('wp_head', 'wp_shortlink_wp_head');        // Shortlink
remove_action('wp_head', 'rest_output_link_wp_head');    // REST API link
remove_action('wp_head', 'feed_links', 2);               // Feed links
remove_action('wp_head', 'feed_links_extra', 3);         // Extra feed links

/**
 * Disable emoji scripts and styles.
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

/**
 * Disable oEmbed discovery.
 */
remove_action('wp_head', 'wp_oembed_add_discovery_links');

/**
 * Disable self-pingbacks.
 */
add_action('pre_ping', function (array &$links): void {
    $home = home_url();
    foreach ($links as $i => $link) {
        if (str_starts_with($link, $home)) {
            unset($links[$i]);
        }
    }
});

/**
 * Remove the WordPress version from scripts and styles.
 */
add_filter('style_loader_src', 'wp_sane_defaults_remove_version_query', 10, 2);
add_filter('script_loader_src', 'wp_sane_defaults_remove_version_query', 10, 2);

function wp_sane_defaults_remove_version_query(string $src, string $handle): string {
    if (strpos($src, 'ver=' . get_bloginfo('version'))) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

/**
 * Obscure login error messages to prevent user enumeration.
 */
add_filter('login_errors', function (): string {
    return 'Invalid username or password.';
});

/**
 * Disable author archives and redirect to homepage.
 * Prevents username enumeration via /?author=1.
 */
add_action('template_redirect', function (): void {
    if (is_author()) {
        wp_safe_redirect(home_url(), 301);
        exit;
    }
});

/**
 * Only enqueue comment-reply JS when actually needed.
 */
add_action('wp_enqueue_scripts', function (): void {
    if (! is_singular() || ! comments_open() || ! get_option('thread_comments')) {
        wp_dequeue_script('comment-reply');
    }
});

/**
 * Remove DNS prefetch for s.w.org (emoji CDN).
 */
add_filter('wp_resource_hints', function (array $hints, string $relation): array {
    if ($relation === 'dns-prefetch') {
        $hints = array_filter($hints, function ($hint): bool {
            $url = is_array($hint) ? ($hint['href'] ?? '') : $hint;
            return ! str_contains($url, 's.w.org');
        });
    }
    return $hints;
}, 10, 2);

/**
 * Hide update nags from non-admin users.
 */
add_action('admin_init', function (): void {
    if (! current_user_can('update_core')) {
        remove_action('admin_notices', 'update_nag', 3);
    }
});

/**
 * Grant Editors access to Appearance > Menus.
 */
add_action('admin_init', function (): void {
    $editor = get_role('editor');
    if ($editor && ! $editor->has_cap('edit_theme_options')) {
        $editor->add_cap('edit_theme_options');
    }
});

/**
 * Hide the other Appearance submenus from Editors (Themes, Widgets, etc.)
 * so they only see Menus.
 */
add_action('admin_menu', function (): void {
    if (current_user_can('edit_theme_options') && ! current_user_can('manage_options')) {
        remove_submenu_page('themes.php', 'themes.php');
        remove_submenu_page('themes.php', 'widgets.php');
        remove_submenu_page('themes.php', 'customize.php');
        remove_submenu_page('themes.php', 'theme-editor.php');
    }
});
