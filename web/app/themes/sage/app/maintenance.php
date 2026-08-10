<?php

/**
 * Maintenance mode. Toggle with MAINTENANCE_MODE in .env — takes effect on
 * the next request, no rebuild/restart needed. Logged-in admins and REST
 * requests bypass it so the site can still be managed while it's up.
 */

namespace App;

add_action('template_redirect', function () {
    if (getenv('MAINTENANCE_MODE') !== 'true') {
        return;
    }

    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    if (is_user_logged_in() && current_user_can('manage_options')) {
        return;
    }

    status_header(503);
    nocache_headers();
    header('Retry-After: 3600');

    echo view('maintenance', [
        'targetDate' => getenv('MAINTENANCE_UNTIL') ?: null,
        'contactEmail' => getenv('MAINTENANCE_CONTACT_EMAIL') ?: get_bloginfo('admin_email'),
        'backgroundUrl' => getenv('MAINTENANCE_BACKGROUND_URL') ?: null,
    ])->render();

    exit;
});
