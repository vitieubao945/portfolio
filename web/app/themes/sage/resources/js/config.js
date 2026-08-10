let cached = null;

/**
 * Reads the non-sensitive WordPress config injected by App\vue.php
 * (`#theme-config`) into `wp_footer`. Never put secrets in that tag.
 */
export function themeConfig() {
  if (cached) {
    return cached;
  }

  const el = document.getElementById('theme-config');

  cached = el ? JSON.parse(el.textContent) : {};

  return cached;
}
