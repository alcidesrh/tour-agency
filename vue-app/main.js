import Vue from 'vue'
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import App from './App.vue';
import Vuetify from 'vuetify';


import guide from './store/modules/guide/';
import guideRoutes from './routes/guide';

import province from './store/modules/province/';
import provinceRoutes from './routes/province';

import destination from './store/modules/destination/';
import destinationRoutes from './routes/destination';

import notification from './store/modules/notification/';
import notificationRoutes from './routes/notification';

import icon from './store/modules/icon/';
import iconRoutes from './routes/icon';

import lstourtemplate from './store/modules/lstourtemplate/';
import lstourtemplateRoutes from './routes/lstourtemplate';

import lstour from './store/modules/lstour/';
import lstourRoutes from './routes/lstour';

import htctourtemplate from './store/modules/htctourtemplate/';
import htctourtemplateRoutes from './routes/htctourtemplate';

import htctour from './store/modules/htctour/';
import htctourRoutes from './routes/htctour';

import agent from './store/modules/agent/';
import agentRoutes from './routes/agent';

import transferreceptive from './store/modules/transferreceptive/';
import transferreceptiveRoutes from './routes/transferreceptive';

import transfercompany from './store/modules/transfercompany/';
import transfercompanyRoutes from './routes/transfercompany';

import transfer from './store/modules/transfer/';
import transferRoutes from './routes/transfer';

import driver from './store/modules/driver/';
import driverRoutes from './routes/driver';

import config from './store/modules/config/';
import configRoutes from './routes/config';

import touroffice from './store/modules/touroffice/';
import tourofficeRoutes from './routes/touroffice';

Vue.use(Vuex);
Vue.use(VueRouter);
Vue.use(Vuetify, {
    theme: {
        primary: '#37474F',
        accent: '#37474F'
    }
});

const store = new Vuex.Store({
    modules: {
        guide,
        province,
        destination,
        notification,
        icon,
        lstourtemplate,
        lstour,
        htctourtemplate,
        htctour,
        agent,
        transferreceptive,
        transfercompany,
        transfer,
        driver,
        config,
        touroffice
    }
});

const router = new VueRouter({
    mode: 'history',
    routes: [
        ...guideRoutes,
        ...provinceRoutes,
        ...destinationRoutes,
        ...notificationRoutes,
        ...iconRoutes,
        ...lstourtemplateRoutes,
        ...lstourRoutes,
        ...htctourtemplateRoutes,
        ...htctourRoutes,
        ...agentRoutes,
        ...transferreceptiveRoutes,
        ...transfercompanyRoutes,
        ...transferRoutes,
        ...driverRoutes,
        ...configRoutes,
        ...tourofficeRoutes
    ]
});

new Vue({
    el: '#app',
    store,
    router,
    render: h => h(App),
});
