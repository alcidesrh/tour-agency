import TransferCompanyList from '../components/transfercompany/List.vue';
import TransferCompanyCreate from '../components/transfercompany/Create.vue';
import TransferCompanyUpdate from '../components/transfercompany/Update.vue';

export default [
  { name: 'TransferCompanyList', path: '/companies/', component: TransferCompanyList },
  { name: 'TransferCompanyCreate', path: '/companies/create', component: TransferCompanyCreate },
  { name: 'TransferCompanyUpdate', path: "/companies/edit/:id", component: TransferCompanyUpdate }
];
