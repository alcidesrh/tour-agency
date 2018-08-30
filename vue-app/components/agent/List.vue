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
            <v-container style="position: fixed; z-index: 2001"  fill-height justify-center
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
                <span>New Agent</span>
            </v-tooltip>
            <v-card-title>
                <v-flex headline>
                    Agents
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
                <template slot="items" slot-scope="props">
                    <td>{{ props.item.name }}</td>
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

    export default {
        data() {
            return {
                valid: true,
                search: '',
                headers: [
                    {text: 'Name', value: 'name'},
                    {text: '', value: ''}
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
                ]
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'New Agent' : 'Edit Agent: '
            },
            ...mapGetters({
                deletedItem: 'agent/del/deleted',
                errorList: 'agent/list/error',
                errorCreate: 'agent/create/error',
                errorUpdate: 'agent/update/updateError',
                errorDelete: 'agent/del/error',
                items: 'agent/list/items',
                loading: 'agent/list/loading',
                view: 'agent/list/view',
                created: 'agent/create/created',
                deleteLoading: 'agent/del/loading',
                updateLoading: 'agent/update/updateLoading',
                createLoading: 'agent/create/loading'
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
                this.item = Object.assign({}, item)
                this.editedIndex = this.items.indexOf(item);
            },
            deleteItem(item) {
                const index = this.items.indexOf(item)
                if (confirm('Are you sure you want to delete?')) {

                    this.$store.dispatch('agent/del/delete', item).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.items.splice(index, 1)
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            this.$store.dispatch('agent/list/getItems');
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
                if (this.editedIndex > -1) {
                    let editedIndex = this.editedIndex;
                    this.$store.dispatch('agent/update/update', {
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
                            this.$store.dispatch('agent/list/getItems');
                        });
                } else {
                    this.$store.dispatch('agent/create/create', itemAux).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.items.unshift(this.created);
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                            this.$store.dispatch('agent/list/getItems');
                        });
                }
                this.close()
            }
        },
        created() {
            if (this.items.length == 0) {
                this.$store.dispatch('agent/list/getItems');
            }
        }
    }
</script>
