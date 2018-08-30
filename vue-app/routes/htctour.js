import HTCTourList from '../components/htctour/List.vue';
import HTCTourCreate from '../components/htctour/Create.vue';
import HTCTourUpdate from '../components/htctour/Update.vue';

export default [
  { name: 'HTCTourList', path: '/h_t_c_tours', component: HTCTourList },
  { name: 'HTCTourCreate', path: '/h_t_c_tours/create', component: HTCTourCreate },
  { name: 'HTCTourUpdate', path: "/h_t_c_tours/edit/:id", component: HTCTourUpdate }
];
