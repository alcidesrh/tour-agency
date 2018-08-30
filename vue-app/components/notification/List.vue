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
                <label>New Notification</label>
            </v-tooltip>
            <v-container style="position: fixed; z-index: 2001" fill-height justify-center
                         v-show="loading || deleteLoading || updateLoading || createLoading">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-card-title>
                <v-flex headline>
                    Notifications
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
                                        label="Days"
                                        v-model="item.days"
                                        :rules="daysRule"
                                        required
                                ></v-text-field>
                                <v-select
                                        :items="types"
                                        v-model="item.types"
                                        label="Type"
                                        multiple
                                        :rules="typesRule"
                                        required
                                ></v-select>
                                <v-select
                                        :items="icons"
                                        v-model="item.icon"
                                        label="Icon"
                                        single-line
                                        item-text="name"
                                        item-value="@id"
                                        :rules="iconsRule"
                                        required
                                >
                                    <template slot="selection" slot-scope="data">
                                        <v-icon
                                                @input="data.parent.selectItem(data.item)"
                                                :key="JSON.stringify(data.item)"
                                        >
                                            {{ data.item.name }}
                                        </v-icon>
                                    </template>
                                    <template slot="item" slot-scope="data" style="width: 60px !important;">
                                        <v-icon>
                                            {{data.item.name}}
                                        </v-icon>
                                    </template>
                                </v-select>
                                <v-text-field
                                        label="Order"
                                        v-model="item.orderList"
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
                    <td>{{ props.item.days }}</td>
                    <td>{{ getTypesName(props.item.types) }}</td>
                    <td>
                        <v-icon>{{ getIconName(props.item.icon) }}</v-icon>
                    </td>
                    <td>{{ props.item.orderList }}</td>
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
                    {text: 'Days', value: 'days'},
                    {text: 'Type', value: 'type'},
                    {text: 'Icon', value: 'icon'},
                    {text: 'Order', value: 'order'},
                    {text: '', value: ''}
                ],
                types: [
                    {text: 'LS Tour', value: 'ls'},
                    {text: 'HTC Tour', value: 'htc'},
                    {text: 'Transfer', value: 'transfer'}
                ],
                dialog: false,
                editedIndex: -1,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                flag: false,
                item: {name: '', days: '', types: [], icon: ''},
                nameRule: [
                    v => !!v || 'Name is required'
                ],
                daysRule: [
                    v => !!v || 'Days is required'
                ],
                typesRule: [
                    v => !!v || 'Type is required'
                ],
                iconsRule: [
                    v => !!v || 'Icon is required'
                ]
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'New Notification' : 'Edit Notification: '
            },
            ...mapGetters({
                deletedItem: 'notification/del/deleted',
                errorList: 'notification/list/error',
                errorCreate: 'notification/create/error',
                errorUpdate: 'notification/update/updateError',
                errorDelete: 'notification/del/error',
                items: 'notification/list/items',
                loading: 'notification/list/loading',
                view: 'notification/list/view',
                created: 'notification/create/created',
                deleteLoading: 'notification/del/loading',
                updateLoading: 'notification/update/updateLoading',
                createLoading: 'notification/create/loading',
                icons: 'icon/list/items'
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

                    this.$store.dispatch('notification/del/delete', item).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.items.splice(index, 1)
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            this.$store.dispatch('notification/list/getItems');
                        })
                }
            },
            close() {
                this.dialog = false;
                this.editedIndex = -1;
                this.item = {name: '', days: '', types: [], icon: ''};
            },
            save() {
                if (!this.$refs.form.validate()) return;
                let itemAux = {};
                Object.assign(itemAux, this.item);
                itemAux.days = parseInt(this.item.days);
                itemAux.orderList = parseInt(this.item.orderList);
                if (this.editedIndex > -1) {
                    let editedIndex = this.editedIndex;
                    this.$store.dispatch('notification/update/update', {
                        item: this.items[editedIndex],
                        values: itemAux
                    }).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            // Object.assign(this.items[editedIndex], itemAux);
                            this.snackbarText = 'Has been edited';
                            this.snackbar = true;
                            this.$store.dispatch('notification/list/getItems');
                        });
                } else {
                    this.$store.dispatch('notification/create/create', itemAux).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            // this.items.unshift(this.created);
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                            this.$store.dispatch('notification/list/getItems');
                        });
                }
                this.close();
            },
            refresh() {
                this.$store.dispatch('notification/list/getItems');
                if (!this.icons.length)
                    this.$store.dispatch('icon/list/getItems');
            },
            getIconName(id) {
                let icon = this.icons.filter(item => item['@id'] == id);
                if (icon.length)
                    return icon[0]['name'];
            },
            getTypesName(array) {
                let types = "";
                this.types.forEach(function (type) {
                    array.forEach(function (item) {
                        if (item == type['value'])
                            if (types == "")
                                types = type['text']
                            else
                                types = types + ', ' + type['text'];
                    })
                })
                return types
            },
        },
        created() {
            this.$store.dispatch('notification/list/getItems');

            if (!this.icons.length)
                this.$store.dispatch('icon/list/getItems');
        }
    }
</script>
