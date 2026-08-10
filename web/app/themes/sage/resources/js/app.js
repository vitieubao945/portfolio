import { mountVueApps } from './vue/mount';
import Example from './components/vue/Example.vue';
import Search from './components/vue/Search.vue';

mountVueApps({
  example: Example,
  search: Search,
});
