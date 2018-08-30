import TransferTourList from '../components/transfertour/List.vue';
import TransferTourCreate from '../components/transfertour/Create.vue';
import TransferTourUpdate from '../components/transfertour/Update.vue';
import TransferTourShow from '../components/transfertour/Show.vue';

export default [
  { name: 'TransferTourList', path: '/transfer_tours/', component: TransferTourList },
  { name: 'TransferTourCreate', path: '/transfer_tours/create', component: TransferTourCreate },
  { name: 'TransferTourUpdate', path: "/transfer_tours/edit/:id", component: TransferTourUpdate },
  { name: 'TransferTourShow', path: "/transfer_tours/show/:id", component: TransferTourShow  }
];
