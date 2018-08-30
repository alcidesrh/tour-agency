import LSTourTemplateList from '../components/lstourtemplate/List.vue';
import LSTourTemplateCreate from '../components/lstourtemplate/Create.vue';
import LSTourTemplateUpdate from '../components/lstourtemplate/Update.vue';

export default [
  { name: 'LSTourTemplateList', path: '/l_s_tour_templates/', component: LSTourTemplateList },
  { name: 'LSTourTemplateCreate', path: '/l_s_tour_templates/create', component: LSTourTemplateCreate },
  { name: 'LSTourTemplateUpdate', path: "/l_s_tour_templates/edit/:id", component: LSTourTemplateUpdate }
];
