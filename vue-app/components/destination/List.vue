<template>
  <v-container>
    <v-snackbar
            :color="snackbarColor"
            :timeout="3000"
            :top="true"
            :multi-line="true"
            v-model="snackbar"
    >
      {{ snackbarText }}
      <v-btn flat color="white" @click.native="snackbar = false">Close</v-btn>
    </v-snackbar>
    <v-card>
      <v-container style="position: fixed; z-index: 2001" fill-height justify-center
                         v-show="loading || deleteLoading || updateLoading || createLoading">
        <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
      </v-container>
      <v-tooltip top>
        <v-btn color="primary"
               slot="activator"
               dark
               fab
               fixed
               bottom
               right
               @click="open"
        >
          <v-icon>add</v-icon>
        </v-btn>
        <span>New Destination</span>
      </v-tooltip>
      <v-card-title>
        <v-flex headline>
          Destinations
        </v-flex>
        <v-dialog v-model="dialog" max-width="400px">
          <v-card>
            <v-card-title>
              <span class="headline">{{ formTitle }}</span>
            </v-card-title>
            <v-form v-model="valid" ref="form"  lazy-validation v-on:submit.prevent="save">
              <v-card-text>
                <v-text-field
                        label="Name"
                        v-model="item.name"
                        :rules="nameRule"
                        required
                ></v-text-field>
                <v-select
                        :items="provinces"
                        v-model="item.province"
                        label="Province"
                        single-line
                        item-text="name"
                        item-value="@id"
                        :rules="provinceRule"
                        required
                        :filter="customFilter"
                        autocomplete
                ></v-select>
              </v-card-text>
              <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary darken-1" flat @click.native="close">Cancel</v-btn>
                <v-btn color="primary darken-1" flat  type="submit">Save</v-btn>
              </v-card-actions>
            </v-form>
          </v-card>
        </v-dialog>
        <v-spacer></v-spacer>
        <v-spacer></v-spacer>
        <v-text-field
                append-icon="search"
                label="Search"
                single-line
                hide-details
                v-model="search"
        ></v-text-field>
      </v-card-title>
      <v-data-table
              :headers="headers"
              :items="items"
              :search="search"
              :rows-per-page-items="[10,5,25,{'text':'All','value':-1}]"
              no-data-text=""
              :disable-initial-sort="true"
      >
        <template slot="items" slot-scope="props">
          <td>{{ props.item.name }}</td>
          <td>{{ getProvinceName(props.item.province) }}</td>
          <td class="justify-center layout px-0">
            <v-btn icon class="mx-0" @click="editItem(props.item)">
              <v-icon color="teal">edit</v-icon>
            </v-btn>
            <v-btn icon class="mx-0" @click="deleteItem(props.item)">
              <v-icon color="red">delete</v-icon>
            </v-btn>
          </td>
        </template>
        <v-alert slot="no-results" :value="true" color="error" icon="warning">
          Your search for "{{ search }}" found no results.
        </v-alert>
      </v-data-table>
    </v-card>
  </v-container>
</template>
<script>
    import {mapGetters} from 'vuex';
    import filter from '../../utils/filter';

    export default {
        data() {
            return {
                valid: true,
                search: '',
                headers: [
                    {text: 'Name', value: 'name'},
                    {text: 'Province', value: 'province'},
                    {}
                ],
                dialog: false,
                editedIndex: -1,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                flag: false,
                item: {name: ''},
                nameRule: [
                    v => !!v || 'Name is required'
                ],
                provinceRule: [
                    v => !!v || 'Province is required'
                ]
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'New Destination' : 'Edit Destination: '
            },
            ...mapGetters({
                deletedItem: 'destination/del/deleted',
                errorList: 'destination/list/error',
                errorCreate: 'destination/create/error',
                errorUpdate: 'destination/update/updateError',
                errorDelete: 'destination/del/error',
                items: 'destination/list/items',
                loading: 'destination/list/loading',
                view: 'destination/list/view',
                created: 'destination/create/created',
                deleteLoading: 'destination/del/loading',
                updateLoading: 'destination/update/updateLoading',
                createLoading: 'destination/create/loading',
                provinces: 'province/list/items'
            })
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            errorList(message){
                this.error(message);
            },
            errorCreate(message){
                this.error(message);
            },
            errorUpdate(message){
                this.error(message);
            },
            errorDelete(message){
                this.error(message);
            },
            snackbar(val){
                val || (this.snackbarColor = 'success')
            },
            provinces(){
                let provinces = [];
                this.provinces.forEach(function(province){
                    provinces.push(province['@id']);
                })
                let items = this.items.filter(function(item){
                    return provinces.indexOf(item.province) === -1;
                })
                let $this = this;
                items.forEach(function(item){
                    $this.items.splice($this.items.indexOf(item), 1)
                });
            }
        },
        methods: {
            error(message){
                this.flag = true;
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            open() {
                this.$refs.form.reset();
                this.editedIndex = -1;
                this.dialog = true;
            },
            getProvinceName(id){
                let province = this.provinces.filter(item => item['@id'] == id);
                if(province.length)
                return province[0]['name'];
            },
            editItem(item) {
                this.open()
                this.item = Object.assign({}, item)
                this.editedIndex = this.items.indexOf(item);
            },
            deleteItem(item) {
                const index = this.items.indexOf(item)
                if (confirm('Are you sure you want to delete?')) {
                    this.$store.dispatch('destination/del/delete', item).then(
                        () => {
                            if(this.flag){
                                this.flag = false;
                                return;
                            }
                            this.items.splice(index, 1)
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            this.$store.dispatch('destination/list/getItems');
                        });
                }
            },
            close() {
                this.dialog = false;
                this.editedIndex = -1;
                this.item = {};
            },
            save() {
                if (!this.$refs.form.validate()) return;
                let itemAux = {};
                Object.assign(itemAux, this.item);
                if (this.editedIndex > -1) {
                    let editedIndex = this.editedIndex;
                    this.$store.dispatch('destination/update/update', {
                        item: this.items[editedIndex],
                        values: itemAux
                    }).then(
                        () => {
                            if(this.flag){
                                this.flag = false;
                                return;
                            }
                            Object.assign(this.items[editedIndex], itemAux);
                            this.snackbarText = 'Has been edited';
                            this.snackbar = true;
                            this.$store.dispatch('destination/list/getItems');
                        });
                } else {
                    this.$store.dispatch('destination/create/create', itemAux).then(
                        () => {
                            if(this.flag){
                                this.flag = false;
                                return;
                            }
                            this.items.unshift(this.created);
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                            this.$store.dispatch('destination/list/getItems');
                        });
                }
                this.close();
            },
            customFilter: filter
        },
        created() {
            if (this.items.length == 0) {
                this.$store.dispatch('destination/list/getItems');
            }
            if(!this.provinces.length)
                this.$store.dispatch('province/list/getItems');
        }
    }
</script>
