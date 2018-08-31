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
                         v-show="templatesLoading || createLoading">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-card-title>
                <v-container py-0 px-3>
                    <span class="headline" v-for="item in code" :key="item.id">New {{item.name}} Tour</span>
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
                        <v-flex md4 v-show="!templatesLoading">
                            <v-select
                                    label="Select Template"
                                    :items="templates"
                                    v-model="template"
                                    autocomplete
                                    item-text="name"
                            >
                                <template slot="selection" slot-scope="data">
                                    <div style="width: 100%">
                                        <span v-bind:style="{backgroundColor: data.item.color, width: '25px', height: '25px', marginRight: '5px'}"></span>
                                        {{ data.item.name }}
                                        <span style="font-size: 12px; position: absolute; right: 20px">{{(data.item.type == 'tailor_made')?'tailor made':data.item.type }}</span>
                                    </div>

                                </template>
                                <template slot="item" slot-scope="data">
                                    <template v-if="typeof data.item !== 'object'">
                                        No data available
                                    </template>
                                    <template v-else>
                                        <span v-bind:style="{backgroundColor: data.item.color, width: '25px', height: '25px', marginRight: '5px'}"></span>
                                        <label>{{ data.item.name }}</label>
                                        <span style="font-size: 12px; position: absolute; right: 20px">{{(data.item.type == 'tailor_made')?'tailor made':data.item.type }}</span>
                                    </template>
                                </template>
                            </v-select>
                        </v-flex>
                        <v-flex md1 ml-4
                                v-show="template && template.type != 'private' && template.type != 'tailor_made'"
                                style="padding-top: 18px">
                            <v-checkbox
                                    label="Private"
                                    v-model="private"
                                    color="primary"
                            >
                            </v-checkbox>
                        </v-flex>
                        <v-flex md2 pl-3 v-show="template" class="pt-18" style="padding-top: 18px; padding-left: 10px">
                            <v-checkbox
                                    label="From Agent"
                                    v-model="hasAgent"
                                    color="primary"
                            >
                            </v-checkbox>
                        </v-flex>
                        <v-flex md3 v-if="hasAgent">
                            <v-select
                                    name="name"
                                    label="Select Agent"
                                    :items="agents"
                                    v-model="item.agent"
                                    item-text="name"
                                    item-value="id"
                                    :rules="fieldRule"
                                    required
                            >
                            </v-select>
                        </v-flex>

                    </v-layout>
                </v-container>
                <v-container v-show="typeof template == 'object'">
                    <v-form v-model="valid" ref="tourForm" lazy-validation>
                    <v-layout row wrap>
                        <v-flex md12>

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

                        </v-flex>
                    </v-layout>
                    <v-layout row wrap>
                        <!--<v-flex xs3 pr-3>-->
                            <!--<v-menu-->
                                    <!--ref="pickerStartTime"-->
                                    <!--lazy-->
                                    <!--:close-on-content-click="false"-->
                                    <!--v-model="pickerStartTime"-->
                                    <!--transition="scale-transition"-->
                                    <!--offset-y-->
                                    <!--full-width-->
                                    <!--:nudge-right="40"-->
                                    <!--max-width="290px"-->
                                    <!--min-width="290px"-->
                                    <!--:return-value.sync="item.startTime"-->
                            <!--&gt;-->
                                <!--<v-text-field-->
                                        <!--slot="activator"-->
                                        <!--label="Start Time"-->
                                        <!--v-model="item.startTime"-->
                                        <!--prepend-icon="access_time"-->
                                        <!--require-->
                                        <!--:rules="nameRule"-->
                                        <!--readonly-->
                                <!--&gt;</v-text-field>-->
                                <!--&lt;!&ndash;<v-time-picker v-model="item.startTime"&ndash;&gt;-->
                                               <!--&lt;!&ndash;@change="$refs.pickerStartTime.save(item.startTime)"></v-time-picker>&ndash;&gt;-->
                                <!--<v-time-picker-->
                                        <!--v-model="item.startTime" >-->
                                    <!--<v-spacer></v-spacer>-->
                                    <!--<v-btn flat color="primary" @click="$refs.pickerStartTime.save(item.startTime)">OK</v-btn>-->
                                <!--</v-time-picker>-->
                            <!--</v-menu>-->
                        <!--</v-flex>-->
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
                        <!--<v-flex xs3 pr-3>-->
                            <!--<v-menu-->
                                    <!--ref="pickerEndTime"-->
                                    <!--lazy-->
                                    <!--:close-on-content-click="false"-->
                                    <!--v-model="pickerEndTime"-->
                                    <!--transition="scale-transition"-->
                                    <!--offset-y-->
                                    <!--full-width-->
                                    <!--:nudge-right="40"-->
                                    <!--max-width="290px"-->
                                    <!--min-width="290px"-->
                                    <!--:return-value.sync="item.endTime"-->
                            <!--&gt;-->
                                <!--<v-text-field-->
                                        <!--slot="activator"-->
                                        <!--label="End Time"-->
                                        <!--v-model="item.endTime"-->
                                        <!--prepend-icon="access_time"-->
                                        <!--require-->
                                        <!--:rules="nameRule"-->
                                        <!--readonly-->
                                <!--&gt;</v-text-field>-->
                                <!--&lt;!&ndash;<v-time-picker v-model="item.endTime"&ndash;&gt;-->
                                               <!--&lt;!&ndash;@change="$refs.pickerEndTime.save(item.endTime)"></v-time-picker>&ndash;&gt;-->
                                <!--<v-time-picker-->
                                        <!--v-model="item.endTime" >-->
                                    <!--<v-spacer></v-spacer>-->
                                    <!--<v-btn flat color="primary" @click="$refs.pickerEndTime.save(item.endTime)">OK</v-btn>-->
                                <!--</v-time-picker>-->
                            <!--</v-menu>-->
                        <!--</v-flex>-->
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
                    </v-form>
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
                    <v-layout row wrap class="mt-5">
                        <v-flex xs2 title slot="header" style="padding: 0px; max-width: 101px;">
                            Bookings
                        </v-flex>
                        <v-flex xs12 mb-3 title>
                            <v-layout row wrap>
                                <v-flex xs12>
                                    <v-form ref="bookingForm" lazy-validation>
                                        <v-layout row wrap v-show="item.type== 'group' || bookings.length == 0">
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
                                                <!--<v-btn color="primary" small @click="setTransfer(props.item)">-->
                                                <!--Transfer-->
                                                <!--</v-btn>-->
                                                <v-btn icon class="mx-0"
                                                       @click="bookings.splice(bookings.indexOf(props.item), 1)">
                                                    <v-icon color="red">delete</v-icon>
                                                </v-btn>
                                            </td>
                                        </template>
                                    </v-data-table>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap class="mt-5">
                    <v-flex md3>
                        <v-form v-model="officeForm" ref="officeForm" lazy-validation>
                        <v-select
                                name="name"
                                label="Select Office"
                                :items="offices"
                                v-model="item.office"
                                item-text="name"
                                item-value="id"
                                :rules="fieldRule"
                                required
                        >
                        </v-select>
                        </v-form>
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
                            Add attached:
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
                                <v-btn color="primary" @click="$router.push({name: 'HTCTourTemplateList'})">Cancel
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
    import filter from '../../utils/filter';
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
                officeForm: false,
                valid: true,
                validBookingForm: true,
                template: false,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                deletingFile: false,
                item: {},
                notificationTour: [],
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
                fieldRule: [
                    v => !!v || 'This field is required'
                ],
                currentDate: moment().format('YYYY-MM-DD')
            }
        }
        ,
        computed: {
            ...mapGetters({
                errorCreate: 'htctour/create/error',
                createLoading: 'htctour/create/loading',
                notifications: 'htctourtemplate/list/notifications',
                created: 'htctour/create/created',
                code: 'htctourtemplate/list/code',
                templates: 'htctourtemplate/list/items',
                agents: 'agent/list/items',
                offices: 'touroffice/list/items',
                templatesLoading: 'htctourtemplate/list/loading',
            })
        }
        ,
        watch: {
            template(val) {
                this.reset();
                this.item.template = val.id;
                this.item.name = val.name;
                this.item.code = val.code;
                this.item.color = val.color;
                this.item.type = val.type;
                this.private = val.type == 'private' ? true : false;
                this.item.description = val.description;
                if (val.startTime)
                    this.item.startTime = moment(val.startTime).format('HH:mm');
                if (val.endTime)
                    this.item.endTime = moment(val.endTime).format('HH:mm');
                this.item.finishPlace = val.finishPlace;
                this.item.startPlace = val.startPlace;
                this.setNotifications();
            },
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
            }
            ,
            snackbar(val) {
                val || (this.snackbarColor = 'success')
            }
            ,

        }
        ,
        methods: {
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
            customFilter: filter,
            setNotifications() {
                this.notifications.filter((item, index) => {
                    let $this = this, filter = function (item2) {
                        if (item2.notification == item.id) {
                            item.days = item2.days;
                            $this.notificationTour[index] = item;
                        }
                    }
                    this.template.notifications.filter(item2 => filter(item2));
                })
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
                if (!this.$refs.officeForm.validate()) {
                    this.$vuetify.goTo(0, {duration: 100})
                    return false;
                }
                let item = {};
                Object.assign(item, this.item);
                if (this.bookings.length)
                    item.bookings = this.bookings;
                let array = [];
                this.notificationTour.forEach(function (item) {
                    if (item)
                        if (typeof item === 'object') {
                            array.push({notification: item.id, days: item.days});
                        }
                });
                item.notifications = array;
                this.$store.dispatch('htctour/create/create', item);
            },
            reset() {
                this.bookingEditing = {};
                this.booking = {name: '', persons: '', lp: ''};
                this.bookings = [];
                this.selectAgent = false;
                this.date = null;
                this.pickerTourStartDate = false;
                this.pickerTranferStartDate = false;
                this.pickerTranferEndDate = false;
                this.modal = false;
                this.hasAgent = false;
                this.notificationTour = [];
                this.item = {
                    attachments: []
                }
            }
        }
        ,
        created() {
            if (!this.templates.length)
                this.$store.dispatch('htctourtemplate/list/getItems');
            this.$store.dispatch('htctourtemplate/list/getHTCNotification');

            if (!this.code.length) {
                this.$store.dispatch('htctourtemplate/list/getCode');
            }
            if (!this.offices.length)
                this.$store.dispatch('touroffice/list/getItems');
            if (!this.agents.length)
                this.$store.dispatch('agent/list/getItems');

        }
    }
</script>
