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
                         v-show="updateLoading || retrieveLoading || notificationsLoading || destinationsLoading">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-card-title>
                <v-container pa-3>
                    <span v-if="retrieved != null" class="headline">Edit Template: {{retrieved.name}} </span>
                    <v-btn style="top: 10px; color: rgba(0,0,0,.54);"
                           icon
                           absolute
                           top
                           right
                           @click="$router.push({name: 'LSTourTemplateList'})">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-container>
                <v-container py-0 v-show="item.type != ''">
                    <v-form v-model="valid" ref="tourForm" lazy-validation v-on:submit.prevent="save">
                        <v-layout row wrap>
                            <v-flex xs4 pr-3>
                                <v-text-field
                                        label="Name"
                                        v-model="item.name"
                                        :rules="nameRule"
                                        required
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs2 px-3>
                                <v-text-field
                                        label="Code"
                                        v-model="item.code"
                                        :rules="codeRule"
                                        required
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs1>
                                <v-text-field style="padding-bottom: 0px"
                                              type="color"
                                              v-model="item.color"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs2 px-2>
                                <v-text-field
                                        label="Color"
                                        v-model="item.color"
                                        :rules="colorRule"
                                        disabled
                                        required
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap v-if="item.type != 'tailor_made'">
                            <v-flex xs3 pr-3>
                                <v-text-field
                                        label="Days Number"
                                        v-model="item.days"
                                        :rules="numberRule"
                                        v-on:keyup="item.days = (validNumbers(item.days, $event)?item.days:'')"
                                        required
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs3 px-3 v-if="item.type == 'group'">
                                <v-select
                                        :items="days"
                                        v-model="item.day"
                                        label="Start Day"
                                        required
                                ></v-select>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap class="mt-5">
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex class="xs2 title" style="max-width: 131px; padding: 0px" slot="header">
                                        Notifications
                                    </v-flex>
                                    <v-flex md8>
                                        <v-layout row wrap>
                                            <v-flex md4>
                                            </v-flex>
                                            <v-flex md1>
                                                Days
                                            </v-flex>
                                        </v-layout>
                                        <v-layout row wrap v-for="(n, index) in notifications" :key="n.id">
                                            <v-flex md4>
                                                <v-checkbox
                                                        v-model="notificationTour[index]"
                                                        color="primary"
                                                        hide-details
                                                        :value="n"
                                                >
                                                    <template slot="label">
                                                        <v-icon>{{n.icon}}</v-icon>
                                                        {{n.name}}
                                                        <v-icon color="success"
                                                                v-if="n.code == 'a' && notificationTour[index]"
                                                                @click.stop="dialogAccommodation = true">edit
                                                        </v-icon>
                                                    </template>
                                                </v-checkbox>
                                            </v-flex>
                                            <v-flex xs1>
                                                <v-text-field class="pt-0"
                                                              v-model="n.days"
                                                              :rules="numberRule"
                                                              v-on:keyup="n.days = (validNumbers(n.days, $event)?n.days:'')"
                                                              required
                                                ></v-text-field>
                                            </v-flex>
                                        </v-layout>
                                    </v-flex>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-layout>
                        <v-layout row wrap class="mt-5">
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex md12 title  style="max-width: 182px; padding: 0px" slot="header">
                                        Extra Information
                                    </v-flex>
                                    <v-flex md12>
                                        <wysiwyg v-model="item.description"></wysiwyg>
                                    </v-flex>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-layout>
                        <v-container px-0>
                            <v-layout row wrap>
                                <v-flex md12 text-md-center>
                                    <v-btn color="primary"
                                           @click="$router.push({name: 'LSTourTemplateList', params: { updated: true }})">
                                        Cancel
                                    </v-btn>
                                    <v-btn color="primary" @click="save">Save</v-btn>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-form>
                </v-container>
            </v-card-title>
        </v-card>
        <v-dialog v-model="dialogAccommodation" max-width="500px">
            <v-card>
                <v-card-title>
                    <span class="headline">Edit Accommodations</span>
                </v-card-title>
                <v-form ref="destinationForm" lazy-validation>
                    <v-card-text>
                        <v-layout row wrap>
                            <v-flex xs2 pl-4>
                                <v-text-field
                                        label="Days"
                                        v-model="destinationItem.days"
                                        v-on:keyup="destinationItem.days= (validNumbers(destinationItem.days, $event)?destinationItem.days:'')"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs6 ml-3>
                                <v-select
                                        :items="destinations"
                                        v-model="destinationItem.destination"
                                        label="Destinations"
                                        single-line
                                        item-text="name"
                                        :filter="customFilter"
                                        autocomplete
                                ></v-select>
                            </v-flex>
                            <v-flex xs3>
                                <v-btn color="primary"
                                       dark
                                       fab
                                       small
                                       @click="(typeof destinationItem.destination == typeof undefined)?false:destinationsTour.push(destinationItem);destinationItem={};"
                                >
                                    <v-icon>add</v-icon>
                                </v-btn>
                            </v-flex>
                        </v-layout>

                        <v-container v-if="destinationsTour.length" pl-4>
                            <v-layout row wrap v-for="(item, index) in destinationsTour"
                                      :key="index">
                                <v-flex xs1>
                                    <v-btn icon class="mx-0" @click="destinationsTour.splice(index, 1)">
                                        <v-icon color="red">delete</v-icon>
                                    </v-btn>
                                </v-flex>
                                <v-flex xs1 pl-2>
                                    <v-text-field class="pt-0"
                                                  :value="destinationsTour[index].days"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs6 justify-center>
                                    <v-container>{{item.destination.name}}</v-container>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary darken-1" flat @click="dialogAccommodation = false">
                            Cancel
                        </v-btn>
                        <v-btn color="primary darken-1" flat @click.native="dialogAccommodation = false">Save</v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
    import Vue from 'vue'
    import {mapGetters} from 'vuex';
    import filter from '../../utils/filter';
    import invalidNumber from '../../utils/numberValidate';
    import wysiwyg from "vue-wysiwyg";

    const config = {
        maxHeight: "500px"
    }
    Vue.use(wysiwyg, config);
    export default {
        data() {
            return {
                dialogAccommodation: false,
                valid: true,
                templateType: '',
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                item: {
                    notifications: [],
                    destinations: [],
                    day: '',
                    days: '',
                    type: '',
                    description: '',
                    name: '',
                    code: '',
                    color: ''
                },
                destinationsTour: [],
                notificationTour: [],
                destinationItem: {days: '', destination: {}},
                days: [
                    {text: 'Monday', value: 2},
                    {text: 'Tuesday', value: 3},
                    {text: 'Wednesday', value: 4},
                    {text: 'Thursday', value: 5},
                    {text: 'Friday', value: 6},
                    {text: 'Saturday', value: 7},
                    {text: 'Sunday', value: 1},

                ],
                nameRule: [
                    v => !!v || 'Name is required'
                ],
                codeRule: [
                    v => !!v || 'Code is required'
                ],
                colorRule: [
                    v => !!v || 'Color is required'
                ],
                numberRule: [
                    v => !!v || 'Number is required'
                ],
            }
        },
        computed: {
            ...mapGetters({
                retrieveError: 'lstourtemplate/update/retrieveError',
                retrieveLoading: 'lstourtemplate/update/retrieveLoading',
                updateError: 'lstourtemplate/update/updateError',
                updateLoading: 'lstourtemplate/create/loading',
                updated: 'lstourtemplate/create/created',
                notificationsLoading: 'lstourtemplate/list/loading',
                destinationsLoading: 'destination/list/loading',
                notifications: 'lstourtemplate/list/notifications',
                destinations: 'destination/list/items',
                retrieved: 'lstourtemplate/update/retrieved',
            })
        },
        watch: {
            updated() {
                this.$store.dispatch('lstourtemplate/list/getItems').then(
                    () => this.$router.push({name: 'LSTourTemplateList'})
                )
            },
            retrieveError(message) {
                this.error(message);
            },
            updateError(message) {
                this.error(message);
            },
            snackbar(val) {
                val || (this.snackbarColor = 'success')
            },
        },
        methods: {
            error(message) {

                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            validNumbers(val, event) {

                if (invalidNumber(event.keyCode)) {
                    this.snackbarColor = 'warning';
                    this.snackbarText = val + ' is not a number';
                    this.snackbar = true;
                    return false;
                }
                return true;
            }
            ,
            customFilter: filter,

            refresh() {
                this.dialogAccommodation = false;
                this.templateType = '';
                this.snackbar = false;
                this.snackbarText = '';
                this.snackbarColor = 'success';
                this.$store.dispatch('lstourtemplate/list/getLsNotification');
                this.$store.dispatch('destination/list/getItems');
                this.$refs.tourForm.reset();
                this.$refs.destinationForm.reset();
                this.item = {
                    notifications: [],
                    destinations: [],
                    day: '',
                    days: '',
                    type: '',
                    description: '',
                    name: '',
                    code: '',
                    color: ''
                };
            },

            save() {
                if (!this.$refs.tourForm.validate()) {
                    this.$vuetify.goTo(0, {duration: 500})
                    return false;
                }

                let array = [], accommodation = false;
                this.notificationTour.forEach(function (item) {
                    if(item)
                    if (typeof item === 'object') {
                        array.push({code: item.code, days: item.days});
                        if (item.code == 'a')
                            accommodation = true;
                    }
                });
                this.item.notifications = array;
                if (accommodation) {
                    array = [];
                    this.destinationsTour.forEach(function (item) {
                        if (item && typeof item === 'object')
                            array.push({id: item.destination.id, days: item.days})
                    })
                    this.item.destinations = array;
                }
                else
                    this.destinationsTour = [];
                this.$store.dispatch('lstourtemplate/create/create', this.item);
            },

            setNotifications() {
                this.notifications.filter((item, index) => {
                    let $this = this, filter = function (item2) {
                        if (item2.notification == item.id) {
                            item.days = item2.days;
                            $this.notificationTour[index] = item;
                        }
                    }
                    this.item.notifications.filter(item2 => filter(item2));
                })

            },
            setDestinations() {
                this.item.destinations.filter(item2 => this.destinationsTour.push({destination: {id: item2.destination, 'name': item2.name}, days: item2.days}))
            }
        }
        ,
        created() {
            let id = '/api/l_s_tour_templates/'+decodeURIComponent(this.$route.params.id);
            this.$store.dispatch('lstourtemplate/update/retrieve', id).then(
                () => {
                    Object.assign(this.item, this.retrieved);
                        this.$store.dispatch('lstourtemplate/list/getLsNotification').then(
                            () => {
                                this.setNotifications();
                            })
                    this.$store.dispatch('destination/list/getItems').then(
                        () => {
                            this.setDestinations();
                        })
                }
            );
        }
    }
</script>
