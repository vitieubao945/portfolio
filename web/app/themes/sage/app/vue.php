<?php

/**
 * Frontend configuration bridge for Vue islands.
 *
 * Only non-sensitive, already-public WordPress data is exposed here
 * (REST base URL, a REST nonce, locale, and the current user ID when
 * logged in). Never add secrets, DB credentials, or salts to this array.
 */

namespace App;

add_action('wp_footer', function () {
    $config = [
        'restUrl' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'locale' => get_locale(),
        'userId' => is_user_logged_in() ? get_current_user_id() : null,
    ];

    printf(
        '<script type="application/json" id="theme-config">%s</script>',
        wp_json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP)
    );
}, 5);
