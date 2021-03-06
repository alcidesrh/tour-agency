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
        <span>New Driver</span>
      </v-tooltip>
      <v-alert type="info" :value="true" v-if="items.length == 0">
        There are no iems to show.
      </v-alert>
      <div v-show="items.length != 0">
        <v-card-title>
          <v-flex headline>
            Drivers
          </v-flex>
          <v-dialog v-model="dialog" max-width="400px">
            <v-card>
              <v-card-title>
                <span class="headline">{{ formTitle }}</span>
              </v-card-title>
              <v-form v-model="valid" ref="form" lazy-validation v-on:submit.prevent="save">
                <v-card-text>
                  <v-text-field
                          label="Name"
                          v-model="item.name"
                          :rules="nameRule"
                          required
                  ></v-text-field>
                  <v-text-field
                          label="Phone"
                          v-model="item.phone"
                          :rules="nameRule"
                          required
                  ></v-text-field>
                  <v-text-field
                          label="Pax.N"
                          v-model="item.number"
                          :rules="phoneRule"
                          required
                  ></v-text-field>
                </v-card-text>
                <v-card-actions>
                  <v-spacer></v-spacer>
                  <v-btn color="primary darken-1" flat @click.native="close">Cancel</v-btn>
                  <v-btn color="primary darken-1" flat type="submit">Save</v-btn>
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
          <template slot="items" slot-scope="props" class="alcides">
            <td>{{ props.item.name}}</td>
            <td>{{ props.item.phone }}</td>
            <td>{{ props.item.number }}</td>
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
      </div>
    </v-card>
  </v-container>
</template>
<script>
    import {mapGetters} from 'vuex';

    export default {
        data() {
            return {
                valid: true,
                search: '',
                headers: [
                    {text: 'Name', value: 'name'},
                    {text: 'Phone', value: 'phone'},
                    {text: 'Pax.N', value: 'pax_n'},
                    {text: '', value: ''}
                ],
                dialog: false,
                editedIndex: -1,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                flag: false,
                item: {},
                nameRule: [
                    v => !!v || 'This field is required'
                ],
                emailRule: [
                    v => !!v || 'E-mail is required',
                    v => /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail must be valid'
                ],
                lastNameRule: [
                    v => !!v || 'Last name is required'
                ],
                phoneRule: [
                    v => !!v || 'Phone is required'
                ]
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'New Driver' : 'Edit Driver: '
            },
            ...mapGetters({
                deletedItem: 'driver/del/deleted',
                errorList: 'driver/list/error',
                errorCreate: 'driver/create/error',
                errorUpdate: 'driver/update/updateError',
                errorDelete: 'driver/del/error',
                items: 'driver/list/items',
                loading: 'driver/list/loading',
                view: 'driver/list/view',
                created: 'driver/create/created',
                deleteLoading: 'driver/del/loading',
                updateLoading: 'driver/update/updateLoading',
                createLoading: 'driver/create/loading'
            })
        },
        watch: {
            dialog(val) {
                val || this.close()
            },
            errorList(message) {
                this.error(message);
            },
            errorCreate(message) {
                this.error(message);
            },
            errorUpdate(message) {
                this.error(message);
            },
            errorDelete(message) {
                this.error(message);
            },
            snackbar(val) {
                val || (this.snackbarColor = 'success')
            }
        },
        methods: {
            error(message) {
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
            editItem(item) {
                this.open()
                Object.assign(this.item, item)
                this.editedIndex = this.items.indexOf(item);
            },
            deleteItem(item) {
                const index = this.items.indexOf(item)
                if (confirm('Are you sure you want to delete?')) {

                    this.$store.dispatch('driver/del/delete', item).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.items.splice(index, 1)
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                        })
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
                itemAux.number = parseInt(this.item.number);
                if (this.editedIndex > -1) {
                    let editedIndex = this.editedIndex;
                    this.$store.dispatch('driver/update/update', {
                        item: this.items[editedIndex],
                        values: itemAux
                    }).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            Object.assign(this.items[editedIndex], itemAux);
                            this.snackbarText = 'Has been edited';
                            this.snackbar = true;
                        });
                } else {
                    this.$store.dispatch('driver/create/create', itemAux).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.items.unshift(this.created);
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                        });
                }
                this.close()
            }
        },
        created() {
            if (this.items.length == 0) {
                this.$store.dispatch('driver/list/getItems');
            }
        }
    }
</script>
