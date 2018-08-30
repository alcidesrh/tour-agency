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
                         v-show="retrieveLoading || updateLoading || changeNotificationLoading || notificationsLoading">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-card-title>
                <v-container py-0 px-3>
                    <span class="headline">{{item.code}}</span>
                    <v-btn style="top: 10px; color: rgba(0,0,0,.54);"
                           icon
                           absolute
                           top
                           right
                           @click="$router.push({name: 'HTCTourList'})">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-container>
                <v-container>
                    <v-layout row wrap>
                        <v-flex md12>
                            <v-form v-model="valid" ref="tourForm" lazy-validation>
                                <v-layout row wrap>
                                    <v-flex xs3 pr-3>
                                        <v-menu
                                                ref="pickerTourStartDate"
                                                lazy
                                                :close-on-content-click="false"
                                                v-model="pickerTourStartDate"
                                                transition="scale-transition"
                                                offset-y
                                                full-width
                                                :nudge-right="40"
                                                :return-value.sync="item.startDate"
                                        >
                                            <v-text-field
                                                    slot="activator"
                                                    label="Date"
                                                    v-model="item.startDate"
                                                    prepend-icon="event"
                                                    readonly
                                                    require
                                                    :rules="nameRule"
                                            ></v-text-field>
                                            <v-date-picker v-model="item.startDate" no-title scrollable
                                                           :min="currentDate"
                                                           @input="$refs.pickerTourStartDate.save(item.startDate)"></v-date-picker>

                                        </v-menu>
                                    </v-flex>
                                </v-layout>
                            </v-form>
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
                                <!--<v-time-picker v-model="item.startTime"-->
                                               <!--@change="$refs.pickerStartTime.save(item.startTime)"></v-time-picker>-->
                                <v-time-picker
                                        v-model="item.startTime" >
                                    <v-spacer></v-spacer>
                                    <v-btn flat color="primary" @click="$refs.pickerStartTime.save(item.startTime)">OK</v-btn>
                                </v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs6 px-3>
                            <v-text-field
                                    label="Start Place"
                                    v-model="item.startPlace"
                                    :rules="nameRule"
                                    prepend-icon="place"
                                    required
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
                                <!--<v-time-picker v-model="item.endTime"-->
                                               <!--@change="$refs.pickerEndTime.save(item.endTime)"></v-time-picker>-->
                                <v-time-picker
                                        v-model="item.endTime" >
                                    <v-spacer></v-spacer>
                                    <v-btn flat color="primary" @click="$refs.pickerEndTime.save(item.endTime)">OK</v-btn>
                                </v-time-picker>
                            </v-menu>
                        </v-flex>
                        <v-flex xs6 px-3>
                            <v-text-field
                                    label="Finish Place"
                                    v-model="item.finishPlace"
                                    :rules="nameRule"
                                    prepend-icon="place"
                                    required
                            ></v-text-field>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap class="mt-5" v-if="item.type != 'tailor_made'">
                        <v-flex xs12 title>
                            Notifications
                            <v-tooltip top v-for="notification,index in item.notifications" :key="index">
                                <v-icon class="pointer ml-2" slot="activator"
                                        :color="(notification.done)?'success':'error'"
                                        @click="changeNotificationState(notification)">{{notification.icon}}
                                </v-icon>
                                <span>{{notification.name}}</span>
                            </v-tooltip>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap class="mt-5" v-if="item.type == 'tailor_made'">
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
                                                    <v-icon>{{n.icon}}</v-icon>{{n.name}}
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
                        <v-flex xs2 title slot="header" style="padding: 0px; max-width: 101px;">
                            Bookings
                        </v-flex>
                        <v-flex xs12 mb-3 title>
                            <v-layout row wrap v-show="item.type == 'group' || bookings.length == 0">
                                <v-flex xs12>
                                    <v-form ref="bookingForm" lazy-validation>
                                        <v-layout row wrap>
                                            <v-flex xs12 sm4 pr-2>
                                                <v-text-field
                                                        label="Contact Name"
                                                        v-model="booking.name"
                                                        :rules="nameRule"
                                                        required
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm1 px-2>
                                                <v-text-field
                                                        label="Pax.N"
                                                        v-model="booking.persons"
                                                        :rules="numberRule"
                                                        v-on:keyup="booking.persons= (validNumbers(booking.persons, $event)?booking.persons:'')"
                                                        required
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm1 px-2>
                                                <v-text-field
                                                        label="LP"
                                                        v-model="booking.lp"
                                                        :rules="numberRule"
                                                        v-on:keyup="booking.lp= (validNumbers(booking.lp, $event)?booking.lp:'')"
                                                        required
                                                ></v-text-field>
                                            </v-flex>
                                            <v-flex xs12 sm1 >
                                                <v-btn color="primary"
                                                       dark
                                                       fab
                                                       small
                                                       @click="addBooking"
                                                >
                                                    <v-icon>add</v-icon>
                                                </v-btn>
                                            </v-flex>
                                        </v-layout>
                                    </v-form>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap my-3 v-show="bookings.length">
                                <v-flex xs12 sm8 title>
                                    <v-data-table
                                            :headers="[
                    {text: 'Name', value: 'name', align: 'left', sortable: false},
                    {text: 'Persons Number', value: 'pn', align: 'left', sortable: false},
                    {text: 'LP', value: 'lp', align: 'left', sortable: false},
                    {text: '', value: '', sortable: false}
                                        ]"
                                            :items="bookings"
                                            hide-actions
                                            class="elevation-1"
                                    >
                                        <template slot="items" slot-scope="props">
                                            <tr v-bind:style="{backgroundColor: (props.item.canceled)?'rgb(255, 82, 82)':'white'}">
                                                <td class="text-md-left">
                                                    <v-edit-dialog
                                                            :return-value.sync="props.item.name"
                                                            large
                                                            lazy
                                                            persistent
                                                    >
                                                        <div>{{ props.item.name }}</div>
                                                        <div slot="input" class="mt-3 title">Update Name</div>
                                                        <v-text-field
                                                                slot="input"
                                                                label="Edit"
                                                                v-model="props.item.name"
                                                                single-line
                                                                counter
                                                                autofocus
                                                                :rules="nameRule"
                                                                required
                                                        ></v-text-field>
                                                    </v-edit-dialog>
                                                </td>
                                                <td class="text-md-left">
                                                    <v-edit-dialog
                                                            :return-value.sync="props.item.persons"
                                                            large
                                                            lazy
                                                            persistent
                                                    >
                                                        <div>{{ props.item.persons }}</div>
                                                        <div slot="input" class="mt-3 title">Update Pax.N</div>
                                                        <v-text-field
                                                                slot="input"
                                                                label="Edit"
                                                                v-model="props.item.persons"
                                                                single-line
                                                                counter
                                                                autofocus
                                                                :rules="numberRule"
                                                                required
                                                                v-on:keyup="props.item.persons= (validNumbers(props.item.persons, $event)?props.item.persons:'')"
                                                        ></v-text-field>
                                                    </v-edit-dialog>
                                                </td>
                                                <td class="text-md-left">
                                                    <v-edit-dialog
                                                            :return-value.sync="props.item.lp"
                                                            large
                                                            lazy
                                                            persistent
                                                    >
                                                        <div>{{ props.item.lp }}</div>
                                                        <div slot="input" class="mt-3 title">Update LP</div>
                                                        <v-text-field
                                                                slot="input"
                                                                label="Edit"
                                                                v-model="props.item.lp"
                                                                single-line
                                                                counter
                                                                autofocus
                                                                :rules="numberRule"
                                                                required
                                                                v-on:keyup="props.item.lp= (validNumbers(props.item.lp, $event)?props.item.lp:'')"
                                                        ></v-text-field>
                                                    </v-edit-dialog>
                                                </td>
                                                <td class="text-md-right">
                                                    <v-tooltip top>
                                                        <v-btn icon class="mx-0" slot="activator"
                                                               @click="changeBookingState(props.item)">
                                                            <v-icon color="warning">cancel</v-icon>
                                                        </v-btn>
                                                        <span>{{(props.item.canceled)?'activate booking': 'cancel booking'}}</span>
                                                    </v-tooltip>
                                                    <v-tooltip top>
                                                        <v-btn icon class="mx-0" slot="activator"
                                                               @click="deleteBooking(props.item)">
                                                            <v-icon v-bind:color="props.item.canceled?'white':'red'">
                                                                delete
                                                            </v-icon>
                                                        </v-btn>
                                                        <span>delete booking</span>
                                                    </v-tooltip>
                                                </td>
                                            </tr>
                                        </template>
                                    </v-data-table>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap class="mt-5">
                        <v-flex md3>
                                <v-select
                                        name="name"
                                        label="Select Office"
                                        :items="offices"
                                        v-model="item.office"
                                        item-text="name"
                                        item-value="id"
                                >
                                </v-select>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap class="mt-5">
                        <v-expansion-panel>
                            <v-expansion-panel-content>
                                <v-flex md12 title style="max-width: 182px; padding: 0px" slot="header">
                                    Extra Information
                                </v-flex>
                                <v-flex md12>
                                    <wysiwyg v-model="item.description"></wysiwyg>
                                </v-flex>
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-layout>
                    <v-layout row wrap class="mt-5">
                        <v-flex md12 title>
                            Attached:
                            <v-btn color="primary" type="file" v-show="!deletingFile"
                                   dark
                                   fab
                                   small
                                   @click="openFileInput"
                            >
                                <v-icon>add</v-icon>
                            </v-btn>
                            <v-progress-linear :indeterminate="true" v-if="deletingFile"
                                               style="max-width: 100px; display: inline-block; top: 12px"></v-progress-linear>
                            <input type="file" @change="uploadFile" style="visibility: hidden"/>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <v-flex md12 v-for="item, index in item.attachments" :key="index">
                            <label style="font-size: 16px">{{item.name}}</label>
                            <v-btn icon m-0 @click="deleteAttached(item)" v-show="item.path && !deletingFile">
                                <v-icon color="red">delete</v-icon>
                            </v-btn>
                            <v-progress-linear :indeterminate="true" v-if="typeof item.path == typeof undefined"
                                               style="max-width: 100px; display: inline-block; top: 12px"></v-progress-linear>
                        </v-flex>
                    </v-layout>
                    <v-container>
                        <v-layout row wrap>
                            <v-flex md12 text-md-center>
                                <v-btn color="primary" @click="$router.push({name: 'HTCTourList'})">Cancel
                                </v-btn>
                                <v-btn color="primary" @click="save">Save</v-btn>
                            </v-flex>
                        </v-layout>
                    </v-container>
                </v-container>

            </v-card-title>
        </v-card>
    </v-container>
</template>

<script>
    import Vue from 'vue'
    import {mapGetters} from 'vuex';
    import invalidNumber from '../../utils/numberValidate';
    import wysiwyg from "vue-wysiwyg";
    import moment from 'moment';
    import {API_HOST, API_PATH} from '../../config/_entrypoint';

    const config = {
        maxHeight: "500px"
    }
    Vue.use(wysiwyg, config);
    export default {
        data() {
            return {
                bookingEditing: {},
                booking: {name: '', persons: '', lp: ''},
                bookings: [],
                selectAgent: false,
                date: null,
                pickerTourStartDate: false,
                pickerStartTime: false,
                pickerEndTime: false,
                pickerTranferStartDate: false,
                pickerTranferEndDate: false,
                pickerTranferStartTime: false,
                pickerTranferEndTime: false,
                modal: false,
                hasAgent: false,
                // dialogTransfer: false,
                valid: true,
                validBookingForm: true,
                template: false,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                deletingFile: false,
                changeNotificationLoading: false,
                notificationTour: [],
                item: {
                    notifications: [],
                    bookings: [],
                    attachments: [],
                    type: '',
                    description: '',
                    name: '',
                    code: '',
                    color: '',
                    agent: '',
                    startDate: '',
                    inTime: '',
                    endTime: '',
                    startPlace: '',
                    finishPlace: ''
                },
                private: false,
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
                currentDate: moment().format('YYYY-MM-DD')
            }
        }
        ,
        computed: {
            ...mapGetters({
                retrieveError: 'htctour/update/retrieveError',
                retrieveLoading: 'htctour/update/retrieveLoading',
                errorCreate: 'htctour/create/error',
                updateLoading: 'htctour/create/loading',
                destinationsLoading: 'destination/list/loading',
                destinations: 'destination/list/items',
                created: 'htctour/create/created',
                code: 'htctourtemplate/list/code',
                agents: 'agent/list/items',
                retrieved: 'htctour/update/retrieved',
                notificationsLoading: 'htctourtemplate/list/loading',
                notifications: 'htctourtemplate/list/notifications',
                offices: 'touroffice/list/items',

            })
        }
        ,
        watch: {
            private(val) {
                if (val)
                    this.item.type = 'private';
                else
                    this.item.type = this.template.type;
            },
            created() {
                this.$store.dispatch('htctour/list/getItemsList').then(() => {
                    this.$router.push({name: 'HTCTourList'});
                })
            },
            errorCreate(message) {
                this.error(message);
            },
            errorUpdate(message) {
                this.error(message);
            },
            snackbar(val) {
                val || (this.snackbarColor = 'success')
            },
        },
        methods: {
            deleteBooking(item){
                if (!confirm('Are you sure you want to delete?')) {
                    return false;
                }
                this.bookings.splice(this.bookings.indexOf(item), 1)
            },
            setNotifications() {
                this.notifications.filter((item, index) => {
                    if (typeof item.done != typeof undefined)
                        item.done = false;
                    let $this = this, filter = function (item2) {
                        if (item2.notification == item.id) {
                            item.days = item2.days;
                            item.done = item2.done;
                            item.notification = item2.id
                            $this.notificationTour[index] = item;
                        }
                    }
                    this.item.notifications.filter(item2 => filter(item2));
                })

            },
            changeBookingState(booking) {
                if (!booking.canceled && !confirm('Are you sure you want to cancel?')) {
                    return false;
                }
                let link = API_HOST + API_PATH + '/change-booking-state/' + booking.id;
                this.changeNotificationLoading = true;
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                })
                    .then(response => response.json())
                    .then(response => {
                        this.changeNotificationLoading = false;
                        booking.canceled = response[0];
                    });
            },
            changeNotificationState(val) {
                let link = API_HOST + API_PATH + '/change-notification-state/' + val.id;
                this.changeNotificationLoading = true;
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                })
                    .then(response => response.json())
                    .then(response => {
                        this.changeNotificationLoading = false;
                        val.done = response[0];
                    });
            },
            assingBooking(item) {
                this.bookingEditing = {
                    name: item.name,
                    lp: item.lp,
                    persons: item.persons,
                    canceled: item.canceled,
                    id: item.id
                };
                // if(typeof item.accommodations != typeof undefined)
                //     this.bookingEditing.accommodations = item.accommodations;
                // if(typeof item.sleepRequirement != typeof undefined)
                //     this.bookingEditing.sleepRequirement = item.sleepRequirement;
                // if (item.transferIn) {
                //     if (item.transferIn.startDate)
                //         this.bookingEditing.startDate = item.transferIn.startDate;
                //     if (item.transferIn.startTime)
                //         this.bookingEditing.inTime = item.transferIn.startTime;
                //     if (item.transferIn.flyData)
                //         this.bookingEditing.flyData = item.transferIn.flyData;
                //     if (item.transferIn.drive)
                //         this.bookingEditing.driverIn = item.transferIn.drive
                //     if (item.transferIn.accommodation)
                //         this.bookingEditing.accommodationIn = item.transferIn.accommodation.accommodation
                // }
                // if (item.transferOut) {
                //     if (item.transferOut.startDate)
                //         this.bookingEditing.endDate = item.transferOut.startDate;
                //     if (item.transferOut.startTime)
                //         this.bookingEditing.outTime = item.transferOut.startTime;
                //     if (item.transferOut.flyData)
                //         this.bookingEditing.flyDataOut = item.transferOut.flyData;
                //     if (item.transferOut.drive)
                //         this.bookingEditing.driverOut = item.transferOut.drive
                //     if (item.transferOut.accommodation)
                //         this.bookingEditing.accommodationOut = item.transferOut.accommodation.accommodation
                // }
                return this.bookingEditing;
            },
            addBooking() {
                if (this.$refs.bookingForm.validate()) {
                    let item = Object.assign({}, this.booking);
                    this.bookings.push(item);
                    this.$refs.bookingForm.reset();
                }
            },
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
            },
            uploadFile() {
                let formData = new FormData();
                let fileField = document.querySelector("input[type='file']");
                formData.append('file', fileField.files[0]);
                this.item.attachments.push({name: fileField.files[0].name, deleting: false});

                let link = API_HOST + API_PATH + '/upload-file';
                fetch(link, {
                    method: 'POST',
                    credentials: "same-origin",
                    body: formData
                })
                    .then(response => response.json())
                    .then(response => {
                        this.item.attachments.splice(this.item.attachments.length - 1, 1)
                        this.item.attachments.push({name: fileField.files[0].name, path: response[0]});
                    });
            },
            deleteAttached(item) {
                if (confirm('Are you sure you want to delete this file?')) {
                    this.deletingFile = true;
                    let link = API_HOST + API_PATH + '/delete-file'
                    fetch(link, {
                        method: 'POST',
                        credentials: "same-origin",
                        body: JSON.stringify({path: item.path})
                    })
                        .then(response => response.json())
                        .catch(error => {
                            this.deletingFile = false;
                        })
                        .then(response => {
                            if (response[0] == 'success') {
                                this.item.attachments.splice(this.item.attachments.indexOf(item), 1);
                                this.deletingFile = false;
                            }
                        });
                }
            },
            openFileInput() {
                document.querySelector("input[type='file']").click()
            },
            save() {

                if (!this.$refs.tourForm.validate()) {
                    this.$vuetify.goTo(0, {duration: 500})
                    return false;
                }
                let item = {};
                Object.assign(item, this.item);
                if (this.bookings.length)
                    item.bookings = this.bookings;
                if (typeof item.agent != typeof undefined && item.agent) {
                    item.agent = item.agent.id;
                }
                if (item.type == 'tailor_made') {
                    let array = [];
                    this.notificationTour.forEach(function (item) {
                        if (item)
                            if (typeof item === 'object') {
                                array.push({
                                    days: item.days,
                                    done: typeof item.done != typeof undefined ? item.done : false,
                                    id: typeof item.notification != typeof undefined ? item.notification : false,
                                    notification: item.id,
                                });
                            }
                    });
                    item.notifications = array;
                }
                this.$store.dispatch('htctour/create/update', item);
            }
        }
        ,
        created() {
            this.$store.dispatch('htctour/update/retrieve', decodeURIComponent(this.$route.params.id)).then(
                () => {
                    this.$store.dispatch('touroffice/list/getItems');
                    this.retrieved.startTime = moment(this.retrieved.startTime).format('HH:mm');
                    this.retrieved.endTime = moment(this.retrieved.endTime).format('HH:mm');
                    if (this.retrieved.type == 'tailor_made') {
                        if (!this.notifications.length)
                            this.$store.dispatch('htctourtemplate/list/getHTCNotification').then(() => {
                                Object.assign(this.item, this.retrieved);
                                this.setNotifications();
                                this.item.bookings = [];
                                if (typeof this.retrieved.bookings != typeof undefined) {
                                    let $this = this;
                                    this.retrieved.bookings.forEach(function (item) {
                                        $this.bookings.push($this.assingBooking(item));
                                    })
                                }
                                if (!this.agents)
                                    this.$store.dispatch('agent/list/getItems');
                            });
                        else {
                            Object.assign(this.item, this.retrieved);
                            this.setNotifications();
                            this.item.bookings = [];
                            if (typeof this.retrieved.bookings != typeof undefined) {
                                let $this = this;
                                this.retrieved.bookings.forEach(function (item) {
                                    $this.bookings.push($this.assingBooking(item));
                                })
                            }
                            if (!this.agents)
                                this.$store.dispatch('agent/list/getItems');
                        }
                    }
                    else {
                        Object.assign(this.item, this.retrieved);
                        this.item.bookings = [];
                        if (typeof this.retrieved.bookings != typeof undefined) {
                            let $this = this;
                            this.retrieved.bookings.forEach(function (item) {
                                $this.bookings.push($this.assingBooking(item));
                            })
                        }
                        if (!this.agents)
                            this.$store.dispatch('agent/list/getItems');
                    }
                }
            );
        }
    }
</script>
