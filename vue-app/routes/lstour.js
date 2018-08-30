import LSTourList from '../components/lstour/List.vue';
import LSTourCreate from '../components/lstour/Create.vue';
import LSTourUpdate from '../components/lstour/Update.vue';

export default [
  { name: 'LSTourList', path: '/l_s_tours/', component: LSTourList },
  { name: 'LSTourCreate', path: '/l_s_tours/create', component: LSTourCreate },
  { name: 'LSTourUpdate', path: "/l_s_tours/edit/:id", component: LSTourUpdate }
];
