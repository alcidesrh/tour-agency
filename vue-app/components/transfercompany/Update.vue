<template>
  <div>
    <h1>Edit {{ item && item['@id'] }}</h1>

    <div v-if="created" class="alert alert-success" role="status">{{ created['@id'] }} created.</div>
    <div v-if="updated" class="alert alert-success" role="status">{{ updated['@id'] }} updated.</div>
    <div v-if="retrieveLoading || updateLoading || deleteLoading"class="alert alert-info" role="status">Loading...</div>
    <div v-if="retrieveError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ retrieveError }}</div>
    <div v-if="updateError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ updateError }}</div>
    <div v-if="deleteError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ deleteError }}</div>

    <TransferCompanyForm v-if="item" :handle-submit="update" :values="item" :errors="violations" :initialValues="retrieved"></TransferCompanyForm>
    <router-link v-if="item" :to="{ name: 'TransferCompanyList' }" class="btn btn-default">Back to list</router-link>
    <button @click="del" class="btn btn-danger">Delete</button>
  </div>
</template>

<script>
  import TransferCompanyForm from './Form.vue';
  import { mapGetters } from 'vuex';

  export default {
    created () {
      this.$store.dispatch('transfercompany/update/retrieve', decodeURIComponent(this.$route.params.id));
    },
    components: {
      TransferCompanyForm
    },
    computed: {
      ...mapGetters({
        retrieveError: 'transfercompany/update/retrieveError',
        retrieveLoading: 'transfercompany/update/retrieveLoading',
        updateError: 'transfercompany/update/updateError',
        updateLoading: 'transfercompany/update/updateLoading',
        deleteError: 'transfercompany/del/error',
        deleteLoading: 'transfercompany/del/loading',
        created: 'transfercompany/create/created',
        deleted: 'transfercompany/del/deleted',
        retrieved: 'transfercompany/update/retrieved',
        updated: 'transfercompany/update/updated',
        violations: 'transfercompany/update/violations'
      })
    },
    data: function() {
      return {
        item: {}
      }
    },
    methods: {
      update (values) {
        this.$store.dispatch('transfercompany/update/update', {item: this.retrieved, values: values });
      },
      del () {
        if (window.confirm('Are you sure you want to delete this item?'))
          this.$store.dispatch('transfercompany/del/delete', this.retrieved);
      },
      reset () {
        this.$store.dispatch('transfercompany/update/reset');
        this.$store.dispatch('transfercompany/del/reset');
        this.$store.dispatch('transfercompany/create/reset');

      }
    },
    watch: {
      deleted: function (deleted) {
        if (deleted) {
          this.$router.push({ name: 'TransferCompanyList' });
        }
      }
    },
    beforeDestroy() {
      this.reset();
    }
  }
</script>
