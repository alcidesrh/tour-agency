<template>
  <div>
    <h1>TransferCompany List</h1>

    <div v-if="loading" class="alert alert-info">Loading...</div>
    <div v-if="deletedItem" class="alert alert-success">{{ deletedItem['@id'] }} deleted.</div>
    <div v-if="error" class="alert alert-danger">{{ error }}</div>

    <span v-if="view">
      <button
        type="button"
        class="btn btn-basic btn-sm"
        @click="getPage(view['hydra:first'])"
        :disabled="!view['hydra:previous']"
      >First</button>
      &nbsp;
      <button
        type="button"
        class="btn btn-basic btn-sm"
        @click="getPage(view['hydra:previous'])"
        :disabled="!view['hydra:previous']"
      >Previous</button>
      &nbsp;
      <button
        type="button" class="btn btn-basic btn-sm"
        @click="getPage(view['hydra:next'])"
        :disabled="!view['hydra:next']"
      >Next</button>
      &nbsp;
      <button
        type="button" class="btn btn-basic btn-sm"
        @click="getPage(view['hydra:last'])"
        :disabled="view['hydra:last']"
      >Last</button>
      &nbsp;
    </span>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Id</th>
            <th>id</th>
            <th>code</th>
            <th>color</th>
            <th>name</th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items">
            <td><router-link v-if="item" :to="{name: 'TransferCompanyShow', params: { id: item['@id'] }}">{{ item['@id'] }}</router-link></td>
            <td><router-link v-if="item" :to="{name: 'TransferCompanyShow', params: { id: item['@id'] }}">{{ item['id'] }}</router-link></td>
            <td><router-link v-if="item" :to="{name: 'TransferCompanyShow', params: { id: item['@id'] }}">{{ item['code'] }}</router-link></td>
            <td><router-link v-if="item" :to="{name: 'TransferCompanyShow', params: { id: item['@id'] }}">{{ item['color'] }}</router-link></td>
            <td><router-link v-if="item" :to="{name: 'TransferCompanyShow', params: { id: item['@id'] }}">{{ item['name'] }}</router-link></td>
            <td>
              <router-link :to="{name: 'TransferCompanyShow', params: { id: item['@id'] }}">
                <span class="glyphicon glyphicon-search" aria-hidden="true"/>
                <span class="sr-only">Show</span>
              </router-link>
            </td>
            <td>
              <router-link :to="{name: 'TransferCompanyUpdate', params: { id: item['@id'] }}">
                <span class="glyphicon glyphicon-pencil" aria-hidden="true"/>
                <span class="sr-only">Edit</span>
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <router-link :to="{ name: 'TransferCompanyCreate' }" class="btn btn-default">Create</router-link>
  </div>
</template>

<script>
  import { mapActions, mapGetters } from 'vuex';

  export default {
    computed: mapGetters({
      deletedItem: 'transfercompany/del/deleted',
      error: 'transfercompany/list/error',
      items: 'transfercompany/list/items',
      loading: 'transfercompany/list/loading',
      view: 'transfercompany/list/view'
    }),
    methods: mapActions({
      getPage: 'transfercompany/list/getItems'
    }),
    created () {
      this.$store.dispatch('transfercompany/list/getItems')
    }
  }
</script>
