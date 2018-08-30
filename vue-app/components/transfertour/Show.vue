<template>
  <div>
    <h1>Show {{ item && item['@id'] }}</h1>

    <div v-if="loading" class="alert alert-info" role="status">Loading...</div>
    <div v-if="error" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ error }}</div>
    <div v-if="deleteError" class="alert alert-danger" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> {{ deleteError }}</div>

    <div v-if="item" class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Field</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>numberPerson</td>
            <td>{{ item['numberPerson'] }}</td>
          </tr>
          <tr>
            <td>date</td>
            <td>{{ item['date'] }}</td>
          </tr>
          <tr>
            <td>contactName</td>
            <td>{{ item['contactName'] }}</td>
          </tr>
          <tr>
            <td>code</td>
            <td>{{ item['code'] }}</td>
          </tr>
          <tr>
            <td>flightData</td>
            <td>{{ item['flightData'] }}</td>
          </tr>
          <tr>
            <td>accommodation</td>
            <td>{{ item['accommodation'] }}</td>
          </tr>
          <tr>
            <td>type</td>
            <td>{{ item['type'] }}</td>
          </tr>
          <tr>
            <td>tourCode</td>
            <td>{{ item['tourCode'] }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <router-link v-if="item" :to="{ name: 'TransferTourList' }" class="btn btn-default">Back to list</router-link>
    <button @click="deleteItem(item)" class="btn btn-danger">Delete</button>
  </div>
</template>

<script>
  import { mapGetters } from 'vuex';

  export default {
    computed: mapGetters({
      deleteError: 'transfertour/del/error',
      error: 'transfertour/show/error',
      loading: 'transfertour/show/loading',
      item: 'transfertour/show/item',
    }),
    methods: {
      deleteItem (item) {
        if (window.confirm('Are you sure you want to delete this item?'))
          this.$store.dispatch('transfertour/del/delete', item).then(response => this.$router.push({ name: 'TransferTourList' }));
      }
    },
    created () {
      this.$store.dispatch('transfertour/show/retrieve', decodeURIComponent(this.$route.params.id));
    },
    beforeDestroy() {
      this.$store.dispatch('transfertour/show/reset');
    }
  }
</script>
