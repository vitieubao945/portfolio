import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin'
import { wordpressPlugin, wordpressThemeJson } from '@roots/vite-plugin';

// Set APP_URL if it doesn't exist for Laravel Vite plugin
if (! process.env.APP_URL) {
  process.env.APP_URL = 'http://example.test';
}

// Docker: bind the dev server to all interfaces and let the browser reach
// HMR through the published port on the host.
const isDocker = process.env.VITE_DOCKER === 'true';

export default defineConfig({
  base: '/app/themes/sage/public/build/',
  plugins: [
    tailwindcss(),
    vue(),
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/editor.css',
        'resources/js/editor.js',
      ],
      refresh: true,
      assets: ['resources/images/**', 'resources/fonts/**'],
    }),

    wordpressPlugin(),

    // Generate the theme.json file in the public/build/assets directory
    // based on the Tailwind config and the theme.json file from base theme folder
    wordpressThemeJson({
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
      disableTailwindBorderRadius: false,
    }),
  ],
  resolve: {
    alias: {
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
      '@components': '/resources/js/components',
    },
  },
  server: isDocker
    ? {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        origin: process.env.VITE_ORIGIN || 'http://localhost:5173',
        // Reflect the requesting origin (the WordPress site, e.g.
        // localhost:8754) instead of only allowing Vite's own origin —
        // otherwise the browser blocks @vite/client and app.js with CORS.
        cors: true,
        hmr: {
          host: process.env.VITE_HMR_HOST || 'localhost',
        },
        watch: {
          usePolling: true,
        },
      }
    : undefined,
})
