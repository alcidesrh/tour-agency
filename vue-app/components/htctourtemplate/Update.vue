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
                         v-show="notificationsLoading || retrieveLoading || updateLoading">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-card-title>
                <v-container py-0 px-3>
                    <span class="headline" v-for="item in code" :key="item.id">Edit Template: {{item.name}}</span>
                    <v-btn style="top: 10px; color: rgba(0,0,0,.54);"
                           icon
                           absolute
                           top
                           right
                           @click="$router.push({name: 'HTCTourTemplateList'})">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-container>
                <v-container>
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
                        <v-layout row wrap>
                            <v-flex xs3 pr-3>
                                <v-menu
                                        ref="pickerStartTime"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerStartTime"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        max-width="290px"
                                        min-width="290px"
                                        :return-value.sync="item.startTime"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Start Time"
                                            v-model="item.startTime"
                                            prepend-icon="access_time"
                                            required
                                            :rules="nameRule"
                                            readonly
                                    ></v-text-field>
                                    <v-time-picker
                                            v-model="item.startTime" >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="$refs.pickerStartTime.save(item.startTime)">OK</v-btn>
                                    </v-time-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs6 px-3>
                                <v-text-field v-if="item.type == 'group'"
                                              label="Start Place"
                                              v-model="item.startPlace"
                                              :rules="nameRule"
                                              prepend-icon="place"
                                              required
                                ></v-text-field>
                                <v-text-field v-else
                                              label="Start Place"
                                              v-model="item.startPlace"
                                              prepend-icon="place"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap>
                            <v-flex xs3 pr-3>
                                <v-menu
                                        ref="pickerEndTime"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerEndTime"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        max-width="290px"
                                        min-width="290px"
                                        :return-value.sync="item.endTime"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="End Time"
                                            v-model="item.endTime"
                                            prepend-icon="access_time"
                                            required
                                            :rules="nameRule"
                                            readonly
                                    ></v-text-field>
                                    <v-time-picker
                                            v-model="item.endTime" >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="$refs.pickerEndTime.save(item.endTime)">OK</v-btn>
                                    </v-time-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs6 px-3>
                                <v-text-field v-if="item.type == 'group'"
                                              label="Finish Place"
                                              v-model="item.finishPlace"
                                              :rules="nameRule"
                                              prepend-icon="place"
                                              required
                                ></v-text-field>
                                <v-text-field v-else
                                              label="Finish Place"
                                              v-model="item.finishPlace"
                                              prepend-icon="place"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap class="mt-5">
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex class="xs2 title" style="max-width: 131px; padding: 0px" slot="header">
                                        Notifications
                                    </v-flex>
                                    <v-flex md7>
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
                        <v-container px-0>
                            <v-container px-0 title>
                                Extra Information:
                            </v-container>
                            <wysiwyg v-model="item.description"></wysiwyg>
                        </v-container>
                        <v-container px-0>
                            <v-layout row wrap>
                                <v-flex md12 text-md-center>
                                    <v-btn color="primary" @click="$router.push({name: 'HTCTourTemplateList'})">Cancel
                                    </v-btn>
                                    <v-btn color="primary" @click="update">Save</v-btn>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-form>
                </v-container>
            </v-card-title>
        </v-card>
    </v-container>
</template>

<script>
    import Vue from 'vue'
    import {mapGetters} from 'vuex';
    import wysiwyg from "vue-wysiwyg";
    import moment from 'moment';

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
                pickerStartTime: false,
                pickerEndTime: false,
                item: {
                    notifications: [],
                    description: '',
                    name: '',
                    code: '',
                    color: ''
                },
                notificationTour: [],
                nameRule: [
                    v => !!v || 'This field is required'
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
                retrieveError: 'htctourtemplate/update/retrieveError',
                retrieveLoading: 'htctourtemplate/update/retrieveLoading',
                notificationsLoading: 'htctourtemplate/list/loading',
                updateError: 'htctourtemplate/update/updateError',
                updateLoading: 'htctourtemplate/create/loading',
                retrieved: 'htctourtemplate/update/retrieved',
                updated: 'htctourtemplate/create/created',
                notifications: 'htctourtemplate/list/notifications',
                code: 'htctourtemplate/list/code'
            })
        },
        methods: {
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
            getTime(val) {
                if(val)
                return moment(val).format('HH:mm');
                return "";
            },
            update() {
                let array = [];
                this.notificationTour.forEach(function (item) {
                    if (item)
                        if (typeof item === 'object') {
                            array.push({code: item.code, days: item.days});
                        }
                });
                this.item.notifications = array;

                this.$store.dispatch('htctourtemplate/create/create', this.item);
            }
        },
        watch: {
            updated() {
                this.$store.dispatch('htctourtemplate/list/getItems').then(
                    () => this.$router.push({name: 'HTCTourTemplateList'})
                )
            },
            errorCreate(message) {
                this.error(message);
            },
            errorUpdate(message) {
                this.error(message);
            }
            ,
            snackbar(val) {
                val || (this.snackbarColor = 'success')
            }
        },
        created() {
            let id = '/api/h_t_c_tour_templates/'+decodeURIComponent(this.$route.params.id);

            if (!this.notifications.length)
                this.$store.dispatch('htctourtemplate/list/getHTCNotification').then(() => {
                    this.$store.dispatch('htctourtemplate/update/retrieve', id).then(() => {
                        this.retrieved.startTime = this.getTime(this.retrieved.startTime);
                        this.retrieved.endTime = this.getTime(this.retrieved.endTime);
                        Object.assign(this.item, this.retrieved);
                        this.setNotifications();

                    });
                });
            else
                this.$store.dispatch('htctourtemplate/update/retrieve', id).then(() => {
                    this.retrieved.startTime = this.getTime(this.retrieved.startTime);
                    this.retrieved.endTime = this.getTime(this.retrieved.endTime);
                    Object.assign(this.item, this.retrieved);
                    this.setNotifications();
                });
            if (!this.code.length)
                this.$store.dispatch('htctourtemplate/list/getCode');
        }
    }
</script>
