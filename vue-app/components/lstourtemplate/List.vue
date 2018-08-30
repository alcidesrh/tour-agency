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
            <v-container style="position: absolute; z-index: 2001" fluid fill-height justify-center
                         v-show="loading || deleteLoading">
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
                       @click="$router.push({ name: 'LSTourTemplateCreate' })"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <span v-if="code.length">New {{code[0].name}} Template</span>
            </v-tooltip>
            <v-alert type="info" :value="true" v-if="items.length == 0">
                There are no iems to show.
            </v-alert>
            <v-card-title v-show="items.length">
                <v-layout row wrap v-for="(code) in code" :key="code.id">
                    <v-flex headline>
                        LS Tour Templates
                    </v-flex>
                    <v-flex md1>
                        <v-container>Code:</v-container>
                    </v-flex>
                    <v-flex md4 v-if="!editCode">
                        <v-container mx-1>{{code.name}}
                            <v-icon color="teal" @click="editCode = true" class="pointer">edit</v-icon>
                        </v-container>
                    </v-flex>
                    <v-flex md2 v-else>
                        <v-text-field class="pl-3 pt-0"
                                      label="code"
                                      single-line
                                      hide-details
                                      v-model="code.name"
                                      :value="code.name"
                        ></v-text-field>
                    </v-flex>
                    <v-flex md2 v-if="editCode" align-content-end>
                        <v-btn small color="primary" @click="saveCode(code)">save</v-btn>
                    </v-flex>
                </v-layout>
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
                    v-show="items.length"
                    :headers="headers"
                    :items="items"
                    :search="search"
                    :rows-per-page-items="[10,5,25,{'text':'All','value':-1}]"
                    no-data-text=""
                    :disable-initial-sort="true"
            >
                <template slot="items" slot-scope="props">
                    <td v-bind:style="{width: '48px'}">
                        <v-container v-bind:style="{backgroundColor: props.item.color, width: '48px'}"></v-container>
                    </td>
                    <td>{{ props.item.name }}</td>
                    <td>{{ props.item.type }}</td>
                    <td>{{ props.item.code }}</td>
                    <td>{{ props.item.days }}</td>
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
    import {API_HOST, API_PATH} from '../../config/_entrypoint';
    export default {
        data() {
            return {
                valid: true,
                search: '',
                headers: [
                    {value: 'color'},
                    {text: 'Name', value: 'name'},
                    {text: 'Type', value: 'type'},
                    {text: 'Code', value: 'code'},
                    {text: 'Duration Days', value: 'duration'},
                    {text: '', value: ''}
                ],
                editCode: false,
                editedIndex: -1,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                flag: false,
                item: {},
                nameRule: [
                    v => !!v || 'Name is required'
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
            ...mapGetters({
                deletedItem: 'lstourtemplate/del/deleted',
                errorList: 'lstourtemplate/list/error',
                errorDelete: 'lstourtemplate/del/error',
                items: 'lstourtemplate/list/items',
                loading: 'lstourtemplate/list/loading',
                view: 'lstourtemplate/list/view',
                created: 'lstourtemplate/create/created',
                deleteLoading: 'lstourtemplate/del/loading',
                code: 'lstourtemplate/list/code'
            })
        },
        watch: {
            errorList(message) {
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
            saveCode(code) {
                let link = API_HOST + API_PATH + '/change-code/';
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                    body: JSON.stringify({code: code.name, ls: true })
                })
                    .then(response => {
                        this.editCode = false
                    });
            },
            error(message) {
                this.flag = true;
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            editItem(item) {

                this.$router.push({name: 'LSTourTemplateUpdate', params: {id: item['id']}});
            },
            deleteItem(item) {
                const index = this.items.indexOf(item)
                if (confirm('Are you sure you want to delete?')) {

                    this.$store.dispatch('lstourtemplate/del/delete', item).then(
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
            refresh() {
                this.$store.dispatch('lstourtemplate/list/getItems');
            }
        },
        created() {
            if (this.items.length == 0) {
                this.$store.dispatch('lstourtemplate/list/getItems');
                if(!this.code.length){
                    this.$store.dispatch('lstourtemplate/list/getCode');
                }
            }
        }
    }
</script>
