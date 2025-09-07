/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");
require("./assets/custom.css");
import Vue from "vue";
import store from "./store";
import vuetify from "./plugins/vuetify.js";
import common from "./commons.js";
import VueSweetalert2 from "vue-sweetalert2";
import "sweetalert2/dist/sweetalert2.min.css";
import Notifications from "vue-notification";
import Loading from "vue-loading-overlay";
import excel from "vue-excel-export";
import DatePicker from "vue2-datepicker";
import "vue2-datepicker/index.css";
import Multiselect from "vue-multiselect";
import "vue-loading-overlay/dist/vue-loading.css";
import { BootstrapVue, IconsPlugin } from "bootstrap-vue";
import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue/dist/bootstrap-vue.css";

import ViewUI from "view-design";
import "view-design/dist/styles/iview.css";
import locale from "view-design/dist/locale/en-US";
import VueAutosuggest from "vue-autosuggest";
import VueTouchKeyboard from "vue-touch-keyboard";
import style from "vue-touch-keyboard/dist/vue-touch-keyboard.css";
import VueLazyload from "vue-lazyload";
import vueCountryRegionSelect from 'vue-country-region-select'
 import VueHtmlToPaper from "vue-html-to-paper";

 import '@mdi/font/css/materialdesignicons.css';
 Vue.use(require('vue-shortkey'))
window.Vue = require("vue");
const loadimage = require("./assets/images/delfirm_loading.gif");
const errorimage = require("./assets/images/delfirm_error.gif");

Vue.use(VueLazyload, {
    preLoad: 1.3,
    error: errorimage,
    loading: loadimage,
    attempt: 1
});
const options = {
    name: "_blank",
    specs: ["fullscreen=no", "titlebar=yes", "scrollbars=yes"],
    styles: [
        "https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css",
        "https://unpkg.com/kidlat-css/css/kidlat.css"
    ],
    timeout: 1000, // default timeout before the print window appears
    autoClose: true, // if false, the window will not close after printing
    windowTitle: window.document.title // override the window title
};
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Toasted from 'vue-toasted';
Vue.use(Toasted);
Vue.component('Treeselect', Treeselect)
 Vue.use(VueHtmlToPaper, options);
Vue.use(vueCountryRegionSelect)
Vue.use(VueAutosuggest);
Vue.use(VueTouchKeyboard);
Vue.use(ViewUI, { locale: locale });
Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(DatePicker);
Vue.use(Loading);
Vue.use(VueSweetalert2);
Vue.use(Notifications);
Vue.use(excel);
Vue.use(require("vue-moment"));
Vue.mixin(common);
Vue.component("multiselect", Multiselect);
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
import router from "./router";
Vue.component("posmenu", require("./components/pos/posmenu.vue").default);
Vue.component("mainapp", require("./components/mainapp.vue").default);
Vue.component("login", require("./components/login.vue").default);

Vue.component("landing_page", require("./components/LandingPage.vue").default);

Vue.component('login_external', require('./components/loginExternal.vue').default);
//Vue.component('poslogin', require('./components/pos/poslogin.vue').default);
Vue.component("login_pos", require("./components/LoginPos.vue").default);
Vue.component(
    "waiter_logout",
    require("./components/pos/PosLogout.vue").default
);
Vue.component("online", require("./components/online.vue").default);
Vue.component("fronthome", require("./components/fronthome.vue").default);
Vue.component('orderhome',require('./components/restaurant/order/OrderHome.vue').default)
Vue.component('checkout',require('./components/restaurant/Checkout.vue').default)
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    vuetify,
    el: "#app",
    router,
    store
});
