<template>
  <div>
    <h1>Edit {{ item && item['@id'] }}</h1>

    <div v-if="created" class="alert alert-success" role="status">{{ created['@id'] }} created.</div>
    <div v-if="updated" class="alert alert-success" role="status">{{ updated['@id'] }} updated.</div>
    <div v-if="retrieveLoading || updateLoading || deleteLoading"class="alert alert-info" role="status">Loading...</div>
    <div v-if="retrieveError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ retrieveError }}</div>
    <div v-if="updateError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ updateError }}</div>
    <div v-if="deleteError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ deleteError }}</div>

    <TransferTourForm v-if="item" :handle-submit="update" :values="item" :errors="violations" :initialValues="retrieved"></TransferTourForm>
    <router-link v-if="item" :to="{ name: 'TransferTourList' }" class="btn btn-default">Back to list</router-link>
    <button @click="del" class="btn btn-danger">Delete</button>
  </div>
</template>

<script>
  import TransferTourForm from './Form.vue';
  import { mapGetters } from 'vuex';

  export default {
    created () {
      this.$store.dispatch('transfertour/update/retrieve', decodeURIComponent(this.$route.params.id));
    },
    components: {
      TransferTourForm
    },
    computed: {
      ...mapGetters({
        retrieveError: 'transfertour/update/retrieveError',
        retrieveLoading: 'transfertour/update/retrieveLoading',
        updateError: 'transfertour/update/updateError',
        updateLoading: 'transfertour/update/updateLoading',
        deleteError: 'transfertour/del/error',
        deleteLoading: 'transfertour/del/loading',
        created: 'transfertour/create/created',
        deleted: 'transfertour/del/deleted',
        retrieved: 'transfertour/update/retrieved',
        updated: 'transfertour/update/updated',
        violations: 'transfertour/update/violations'
      })
    },
    data: function() {
      return {
        item: {}
      }
    },
    methods: {
      update (values) {
        this.$store.dispatch('transfertour/update/update', {item: this.retrieved, values: values });
      },
      del () {
        if (window.confirm('Are you sure you want to delete this item?'))
          this.$store.dispatch('transfertour/del/delete', this.retrieved);
      },
      reset () {
        this.$store.dispatch('transfertour/update/reset');
        this.$store.dispatch('transfertour/del/reset');
        this.$store.dispatch('transfertour/create/reset');

      }
    },
    watch: {
      deleted: function (deleted) {
        if (deleted) {
          this.$router.push({ name: 'TransferTourList' });
        }
      }
    },
    beforeDestroy() {
      this.reset();
    }
  }
</script>
