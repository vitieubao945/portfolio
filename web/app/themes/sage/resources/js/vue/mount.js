import { createApp } from 'vue';

/**
 * Mounts one independent Vue app per `[data-vue-component]` element found
 * on the page. Multiple islands of the same or different components can
 * coexist on a single Blade-rendered page.
 *
 * Blade:
 *   <div data-vue-component="example" data-props='{"count":0}'></div>
 *
 * @param {Record<string, import('vue').Component>} registry
 */
export function mountVueApps(registry) {
  document.querySelectorAll('[data-vue-component]').forEach((el) => {
    const name = el.dataset.vueComponent;
    const component = registry[name];

    if (!component) {
      console.warn(`[vue] No component registered for "${name}"`);
      return;
    }

    const props = el.dataset.props ? JSON.parse(el.dataset.props) : {};

    createApp(component, props).mount(el);
  });
}
