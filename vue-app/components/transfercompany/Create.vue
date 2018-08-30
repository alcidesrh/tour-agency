<template>
  <div>
    <h1>New TransferCompany</h1>

    <div v-if="loading" class="alert alert-info" role="status">Loading...</div>
    <div v-if="error" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ error }}</div>

    <TransferCompanyForm :handle-submit="create" :values="item" :errors="violations"></TransferCompanyForm>
    <router-link :to="{ name: 'TransferCompanyList' }" class="btn btn-default">Back to list</router-link>
  </div>
</template>

<script>
  import TransferCompanyForm from './Form.vue';
  import { createNamespacedHelpers } from 'vuex';

  const { mapActions, mapGetters } = createNamespacedHelpers('transfercompany/create');

  export default {
    components: {
      TransferCompanyForm
    },
    data: function() {
      return {
        item: {}
      }
    },
    computed: mapGetters([
      'error',
      'loading',
      'created',
      'violations'
    ]),
    methods: {
      create: function(item) {
        this.$store.dispatch('transfercompany/create/create', item);
      }
    },
    watch: {
      created: function (created) {
        if (created) {
          this.$router.push({ name: 'TransferCompanyUpdate', params: { id: created['@id']} });
        }
      }
    }
  }
</script>
