import Vue from 'vue';
import Frontend from './views/Frontend.vue';
import store from './store';
import globalMixins from './mixins';
import I18n from "./I18n";

const el = document.querySelector('#vue-app');
if (el) {
    Vue.mixin(globalMixins);
    Vue.use(I18n);
    Vue.config.productionTip = false;

    new Vue({
        el,
        store,
        render: h => h(Frontend)
    });
}
