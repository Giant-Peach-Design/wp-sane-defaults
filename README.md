# WP Sane Defaults

WordPress mu-plugin that fixes common insecure and annoying defaults.

## What it does

**Security:**
- Disables the Users REST API endpoint for unauthenticated requests
- Disables XML-RPC entirely
- Removes the X-Pingback header
- Disables file editing via the admin
- Obscures login error messages to prevent user enumeration
- Disables author archives (redirects to homepage)
- Removes WordPress version from scripts, styles, and meta tags

**Performance/Cleanup:**
- Removes emoji scripts and styles
- Removes oEmbed discovery links
- Removes DNS prefetch for `s.w.org`
- Only loads comment-reply JS when actually needed
- Removes unnecessary meta tags (generator, WLW manifest, RSD link, shortlink, REST API link, feed links)
- Disables self-pingbacks
- Hides update nags for non-admin users

## Installation

Drop `wp-sane-defaults.php` into `wp-content/mu-plugins/`.

## Requirements

- PHP 8.0+
- WordPress 6.0+
