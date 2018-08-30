<template>
  <div>
    <h1>New TransferTour</h1>

    <div v-if="loading" class="alert alert-info" role="status">Loading...</div>
    <div v-if="error" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ error }}</div>

    <TransferTourForm :handle-submit="create" :values="item" :errors="violations"></TransferTourForm>
    <router-link :to="{ name: 'TransferTourList' }" class="btn btn-default">Back to list</router-link>
  </div>
</template>

<script>
  import TransferTourForm from './Form.vue';
  import { createNamespacedHelpers } from 'vuex';

  const { mapActions, mapGetters } = createNamespacedHelpers('transfertour/create');

  export default {
    components: {
      TransferTourForm
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
        this.$store.dispatch('transfertour/create/create', item);
      }
    },
    watch: {
      created: function (created) {
        if (created) {
          this.$router.push({ name: 'TransferTourUpdate', params: { id: created['@id']} });
        }
      }
    }
  }
</script>
