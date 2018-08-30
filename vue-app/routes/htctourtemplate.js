import HTCTourTemplateList from '../components/htctourtemplate/List.vue';
import HTCTourTemplateCreate from '../components/htctourtemplate/Create.vue';
import HTCTourTemplateUpdate from '../components/htctourtemplate/Update.vue';

export default [
  { name: 'HTCTourTemplateList', path: '/h_t_c_tour_templates/', component: HTCTourTemplateList },
  { name: 'HTCTourTemplateCreate', path: '/h_t_c_tour_templates/create', component: HTCTourTemplateCreate },
  { name: 'HTCTourTemplateUpdate', path: "/h_t_c_tour_templates/edit/:id", component: HTCTourTemplateUpdate }
];
