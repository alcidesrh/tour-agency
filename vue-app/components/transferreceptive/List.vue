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
                         v-show="loading || deleteLoading || updateLoading || createLoading || loading2 || deleteLoading2 || updateLoading2 || createLoading2">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-alert type="info" :value="true" v-if="transfers.length == 0">
                There are no transfer to show.
            </v-alert>
            <div v-show="transfers.length">
                <v-dialog v-model="dialog" max-width="900px">
                    <v-card>
                        <v-card-title>
                            <span class="headline">{{ formTitle }}</span>
                        </v-card-title>
                        <v-form v-model="valid" ref="form" lazy-validation v-on:submit.prevent="saveTransfer">
                            <v-card-text>
                                <v-layout row wrap>
                                    <v-flex xs1 px-2>
                                        <v-text-field
                                                label="Min Pax"
                                                v-model="transfer.paxMin"
                                                :rules="fieldRule"
                                                required
                                                v-on:keyup="transfer.paxMin= (validNumbers(transfer.paxMin, $event)?transfer.paxMin:'')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs1 px-2 v-if="editedIndex < 2">
                                        <v-text-field
                                                label="Max Pax"
                                                v-model="transfer.paxMax"
                                                :rules="fieldRule"
                                                required
                                                v-on:keyup="transfer.paxMax = (validNumbers(transfer.paxMax, $event)?transfer.paxMax:'')"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs3 px-2>
                                        <v-text-field
                                                label="Name"
                                                v-model="transfer.name"
                                                :rules="fieldRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs3 px-2>
                                        <v-text-field
                                                label="Phone"
                                                v-model="transfer.phone"
                                                :rules="fieldRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs4 px-2>
                                        <v-text-field
                                                label="Email"
                                                v-model="transfer.email"
                                                :rules="fieldRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary darken-1" flat @click.native="close('transfer')">Cancel</v-btn>
                                <v-btn color="primary darken-1" flat type="submit">Save</v-btn>
                            </v-card-actions>
                        </v-form>
                    </v-card>
                </v-dialog>
                <v-card-title >
                    <v-flex title>
                        Transfer Receptives
                    </v-flex>
                </v-card-title>
                <v-data-table
                        :headers="headers"
                        :items="transfers"
                        hide-actions
                        :disable-initial-sort="true"
                        item-key="name"
                >
                    <template slot="items" slot-scope="props">
                        <td v-if="props.item.paxMax">{{ props.item.paxMin + ' - ' + props.item.paxMax }}</td>
                        <td v-else>{{ '> ' + props.item.paxMin }}</td>
                        <td>{{ props.item.name }}</td>
                        <td>{{ props.item.phone }}</td>
                        <td>{{ props.item.email }}</td>
                        <td class="justify-center layout px-0">
                            <v-btn icon class="mx-0" @click="editItem(props.item, 'transfer')">
                                <v-icon color="teal">edit</v-icon>
                            </v-btn>
                            <!--<v-btn icon class="mx-0" @click="deleteItem(props.item)">-->
                            <!--<v-icon color="red">delete</v-icon>-->
                            <!--</v-btn>-->
                        </td>
                    </template>
                </v-data-table>
            </div>
            <div class="d-block" v-if="transfers.length < 3 && transfers.length">
                <v-tooltip top style="float: right">
                    <v-btn color="primary"
                           slot="activator"
                           dark
                           fab
                           right
                           @click="open('transfer')"
                    >
                        <v-icon>add</v-icon>
                    </v-btn>
                    <span>Add Transfer</span>
                </v-tooltip>
            </div>
            <div class="mt-5">
                <v-dialog v-model="dialogCompany" max-width="800px">
                    <v-card>
                        <v-card-title>
                            <span class="headline">{{ formTitleCompany }}</span>
                        </v-card-title>
                        <v-form v-model="validCompany" ref="form2" lazy-validation v-on:submit.prevent="saveCompany">
                            <v-card-text>
                                <v-layout row wrap>
                                    <v-flex xs4 px-2>
                                        <v-text-field
                                                label="Name"
                                                v-model="company.name"
                                                :rules="fieldRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs1>
                                        <v-text-field style="padding-bottom: 0px"
                                                      type="color"
                                                      v-model="company.color"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs2 px-2>
                                        <v-text-field
                                                label="Color"
                                                v-model="company.color"
                                                :rules="fieldRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs2 px-2>
                                        <v-text-field
                                                label="Code"
                                                v-model="company.code"
                                                :rules="fieldRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-card-text>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn color="primary darken-1" flat @click.native="close">Cancel</v-btn>
                                <v-btn color="primary darken-1" flat type="submit">Save</v-btn>
                            </v-card-actions>
                        </v-form>
                    </v-card>
                </v-dialog>
                <v-alert type="info" :value="true" v-if="companies.length == 0">
                    There are no companies to show.
                </v-alert>
                <div>
                    <v-card-title v-if="companies.length">
                        <v-flex title>
                            Transfer Companies
                        </v-flex>
                    </v-card-title>
                    <v-data-table  v-if="companies.length"
                            :headers="headersCompanies"
                            :items="companies"
                            :rows-per-page-items="[10,5,25,{'text':'All','value':-1}]"
                            no-data-text=""
                            :disable-initial-sort="true"
                            item-key="color"
                    >
                        <template slot="items" slot-scope="props">
                            <td v-bind:style="{width: '48px'}">
                                <v-container
                                        v-bind:style="{backgroundColor: props.item.color, width: '48px'}"></v-container>
                            </td>
                            <td>{{ props.item.name }}</td>
                            <td>{{ props.item.code }}</td>
                            <td class="justify-center layout px-0">
                                <v-btn icon class="mx-0" @click="editItem(props.item, 'company')">
                                    <v-icon color="teal">edit</v-icon>
                                </v-btn>
                                <v-btn icon class="mx-0" @click="deleteItem(props.item, 'company')">
                                    <v-icon color="red">delete</v-icon>
                                </v-btn>
                            </td>
                        </template>
                    </v-data-table>
                    <div class="d-block">
                        <v-tooltip top style="float: right">
                            <v-btn color="primary"
                                   slot="activator"
                                   dark
                                   fab
                                   right
                                   @click="open('company')"
                            >
                                <v-icon>add</v-icon>
                            </v-btn>
                            <span>Add Company</span>
                        </v-tooltip>
                    </div>
                </div>
            </div>
        </v-card>
    </v-container>
</template>
<script>
    import {mapGetters} from 'vuex';
    import invalidNumber from '../../utils/numberValidate';

    export default {
        data() {
            return {
                valid: true,
                validCompany: true,
                editingType: 'transfer',
                headers: [
                    {text: 'Pax. N', value: 'min_pax'},
                    {text: 'Name', value: 'name'},
                    {text: 'Phone', value: 'phone'},
                    {text: 'Email', value: 'email'},
                    {text: '', value: ''},
                ],
                headersCompanies: [
                    {text: '', value: ''},
                    {text: 'Name', value: 'name'},
                    {text: 'code', value: 'code'},
                    {text: '', value: ''},
                ],
                dialog: false,
                dialogCompany: false,
                editedIndex: -1,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                transfer: {},
                company: {},
                flag: false,
                item: {},
                fieldRule: [
                    v => !!v || 'This field is required'
                ],
                daysRule: [
                    v => !!v || 'Days is required'
                ]
            }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'New Transfer' : 'Edit Transfer'
            },
            formTitleCompany() {
                return this.editedIndex === -1 ? 'New Company' : 'Edit Company'
            },
            ...mapGetters({
                deletedItem: 'transferreceptive/del/deleted',
                errorList: 'transferreceptive/list/error',
                errorCreate: 'transferreceptive/create/error',
                errorUpdate: 'transferreceptive/update/updateError',
                errorDelete: 'transferreceptive/del/error',
                transfers: 'transferreceptive/list/items',
                loading: 'transferreceptive/list/loading',
                view: 'transferreceptive/list/view',
                created: 'transferreceptive/create/created',
                deleteLoading: 'transferreceptive/del/loading',
                updateLoading: 'transferreceptive/update/updateLoading',
                createLoading: 'transferreceptive/create/loading',
                companies: 'transfercompany/list/items',
                loading2: 'transfercompany/list/loading',
                deleteLoading2: 'transfercompany/del/loading',
                updateLoading2: 'transfercompany/update/updateLoading',
                createLoading2: 'transfercompany/create/loading',
            })
        },
        watch: {
            dialog(val) {
                val || this.close('transfer')
            },
            dialogCompany(val) {
                val || this.close('company')
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
            validNumbers(val, event) {
                if (invalidNumber(event.keyCode)) {
                    this.snackbarColor = 'warning';
                    this.snackbarText = val + ' is not a number';
                    this.snackbar = true;
                    return false;
                }
                return true;
            },
            error(message) {
                this.flag = true;
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            open(type) {
                this.editedIndex = -1;
                if (type == 'transfer') {
                    this.$refs.form.reset();
                    this.dialog = true;
                }
                else if (type == 'company') {
                    this.$refs.form2.reset();
                    this.dialogCompany = true;
                }
            },
            editItem(item, type) {
                this.open(type);
                if (type == 'company') {
                    Object.assign(this.company, item)
                    this.editedIndex = this.companies.indexOf(item);
                }
                else {
                    Object.assign(this.transfer, item)
                    this.editedIndex = this.transfers.indexOf(item);
                }

            },
            deleteItem(item, type) {
                if (confirm('Are you sure you want to delete?')) {
                    let endPoint = (type == 'company') ? 'transfercompany/del/delete' : 'transferreceptive/del/delete';
                    this.$store.dispatch(endPoint, item).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            if (type == 'company') {
                                this.$store.dispatch('transfercompany/list/getItems');
                            }
                            else
                                this.$store.dispatch('transferreceptive/list/getItems');
                        })
                }
            },
            close(type) {
                this.editedIndex = -1;
                if (type == 'transfer') {
                    this.dialog = false;
                    this.transfer = {};
                }
                else {
                    this.dialogCompany = false;
                    this.company = {};
                }
            },
            saveTransfer() {

                if (!this.$refs.form.validate()) return;
                let itemAux = {};
                Object.assign(itemAux, this.transfer);
                itemAux.paxMin = parseInt(this.transfer.paxMin);
                itemAux.paxMax = parseInt(this.transfer.paxMax);
                if (this.editedIndex > -1) {
                    let editedIndex = this.editedIndex;
                    this.$store.dispatch('transferreceptive/update/update', {
                        item: this.transfers[editedIndex],
                        values: itemAux
                    }).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            Object.assign(this.transfers[editedIndex], itemAux);
                            this.snackbarText = 'Has been edited';
                            this.snackbar = true;
                            this.$store.dispatch('transferreceptive/list/getItems');
                        });
                } else {
                    this.$store.dispatch('transferreceptive/create/create', itemAux).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.transfers.unshift(this.created);
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                            this.$store.dispatch('transferreceptive/list/getItems');
                        });
                }
                this.close('transfer');
            },
            saveCompany() {
                if (!this.$refs.form2.validate()) return;
                let itemAux = {};
                Object.assign(itemAux, this.company);
                if (this.editedIndex > -1) {
                    let editedIndex = this.editedIndex;
                    this.$store.dispatch('transfercompany/update/update', {
                        item: this.companies[editedIndex],
                        values: itemAux
                    }).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            Object.assign(this.companies[editedIndex], itemAux);
                            this.snackbarText = 'Has been edited';
                            this.snackbar = true;
                            this.$store.dispatch('transfercompany/list/getItems');
                        });
                } else {
                    this.$store.dispatch('transfercompany/create/create', itemAux).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.transfers.unshift(this.created);
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                            this.$store.dispatch('transfercompany/list/getItems');
                        });
                }
                this.close('company');
            },
        },
        created() {
            if (this.transfers.length == 0) {
                this.$store.dispatch('transferreceptive/list/getItems');
            }
            this.$store.dispatch('transfercompany/list/getItems');

        }
    }
</script>
