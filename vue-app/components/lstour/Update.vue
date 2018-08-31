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
                         v-show="updateLoading || retrieveLoading  || destinationsLoading || changeNotificationLoading || notificationsLoading || loadingGeneric">
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
                           @click="$router.push({name: 'LSTourList'})">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-container>
                <v-container>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-form v-model="valid" ref="tourForm" lazy-validation>
                                <v-layout row wrap v-if="item.type != 'tailor_made'">
                                    <v-flex xs3 pr-3>
                                        <v-text-field
                                                label="Days Number"
                                                v-model="item.days"
                                                :rules="numberRule"
                                                v-on:keyup="item.days = (validNumbers(item.days, $event)?item.days:'')"
                                                required
                                                :disabled="(item.type == 'group' || item.type=='private')?true:false"
                                        ></v-text-field>
                                    </v-flex>
                                    <v-flex xs3 px-3 v-if="item.type == 'group'">
                                        <v-select
                                                :items="days"
                                                v-model="item.day"
                                                label="Start Day"
                                                item-value="value"
                                                :disabled="(item.type == 'group' || item.type=='private')?true:false"
                                        ></v-select>
                                    </v-flex>
                                </v-layout>
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
                                                    @click="changeStartDay"
                                            ></v-text-field>

                                            <!--<v-date-picker color="grey lighten-1" v-model="item.startDate"-->
                                            <!--:min="currentDate"-->
                                            <!--:allowed-dates="allowDates"-->
                                            <!--no-title scrollable-->
                                            <!--@input="setEndDate">-->
                                            <!--<v-spacer></v-spacer>-->
                                            <!--<v-btn flat color="primary" @click="pickerTourStartDate = false">Cancel-->
                                            <!--</v-btn>-->
                                            <!--<v-btn flat color="primary"-->
                                            <!--@click="$refs.pickerTourStartDate.save(item.startDate)">OK-->
                                            <!--</v-btn>-->
                                            <!--</v-date-picker>-->
                                            <v-date-picker v-model="item.startDate" no-title scrollable
                                                           :min="currentDate"
                                                           :allowed-dates="allowDates"
                                                           @input="$refs.pickerTourStartDate.save(item.startDate);setEndDate();"></v-date-picker>
                                        </v-menu>
                                    </v-flex>
                                    <v-flex xs3 px-3>
                                        <v-menu
                                                ref="pickerTourEndDate"
                                                lazy
                                                :close-on-content-click="false"
                                                v-model="pickerTourEndDate"
                                                transition="scale-transition"
                                                offset-y
                                                full-width
                                                :nudge-right="40"
                                                :return-value.sync="item.endDate"
                                                :disabled="item.type == 'group' || item.type == 'private'"
                                        >
                                            <v-text-field
                                                    slot="activator"
                                                    label="End Date"
                                                    v-model="item.endDate"
                                                    prepend-icon="event"
                                                    readonly
                                                    :disabled="item.type == 'group' || item.type == 'private'"
                                                    required
                                                    :rules="nameRule"
                                            ></v-text-field>

                                            <!--<v-date-picker color="green lighten-1" v-model="item.endDate" no-title-->
                                            <!--scrollable-->
                                            <!--:min="item.startDate"-->
                                            <!--:readonly="typeof item.startDate == typeof undefined">-->
                                            <!--<v-spacer></v-spacer>-->
                                            <!--<v-btn flat color="primary" @click="pickerTourEndDate = false">Cancel-->
                                            <!--</v-btn>-->
                                            <!--<v-btn flat color="primary"-->
                                            <!--@click="$refs.pickerTourEndDate.save(item.endDate)">OK-->
                                            <!--</v-btn>-->
                                            <!--</v-date-picker>-->
                                            <v-date-picker v-model="item.endDate" no-title scrollable
                                                           :min="item.startDate"
                                                           :readonly="typeof item.startDate == typeof undefined"
                                                           @input="$refs.pickerTourEndDate.save(item.endDate)"></v-date-picker>
                                        </v-menu>
                                    </v-flex>
                                </v-layout>
                            </v-form>
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
                    <v-layout row wrap class="mt-5" v-if="destinationsTour.length && item.type != 'tailor_made'">
                        <v-flex xs12 title>
                            Destinations
                            <v-icon color="success" v-bind:style="{cursor: 'pointer'}"
                                    @click="dialogAccommodation = true">edit
                            </v-icon>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap class="mt-5">
                        <v-flex xs2 title slot="header" style="padding: 0px; max-width: 101px;">
                            Bookings
                        </v-flex>
                        <v-flex xs12 mb-3 title>
                            <v-layout row wrap>
                                <v-flex xs12>
                                    <v-form ref="bookingForm" lazy-validation>
                                        <v-layout row wrap v-show="item.type == 'group' || bookings.length == 0">
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
                                                        v-on:keyup="booking.persons= (validNumbersPerson(booking.persons, $event)?booking.persons:'')"
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
                                                                v-on:keyup="props.item.persons= (validNumbersPerson(props.item.persons, $event)?props.item.persons:'')"
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
                                                    <v-btn color="primary" small
                                                           @click="setAccommodation(props.item)">
                                                        Accomodation
                                                    </v-btn>
                                                    <v-btn color="primary" small
                                                           @click="setTransfer(props.item);dialogTransfer = true;">
                                                        Transfer
                                                    </v-btn>
                                                    <v-tooltip top>
                                                        <v-btn icon class="mx-0" slot="activator"
                                                               @click="changeBookingState(props.item)">
                                                            <v-icon color="warning">cancel</v-icon>
                                                        </v-btn>
                                                        <span>{{(props.item.canceled)?'Activate booking': 'Cancel booking'}}</span>
                                                    </v-tooltip>
                                                    <v-tooltip top>
                                                        <v-btn icon class="mx-0" slot="activator"
                                                               @click="deleteBooking(props.item)">
                                                            <v-icon v-bind:color="props.item.canceled?'white':'red'">
                                                                delete
                                                            </v-icon>
                                                        </v-btn>
                                                        <span>Delete booking</span>
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
                                        :rules="nameRule"
                                        required
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
                    <v-layout row wrap class="mt-5" v-if="!backTour || !item.ext">
                        <v-flex md12 title>
                            Extentions
                            <v-btn color="primary"
                                   dark
                                   fab
                                   small
                                   @click="extentionDialog = true"
                            >
                                <v-icon>add</v-icon>
                            </v-btn>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap v-show="extentionsToShow.length">
                        <v-flex xs12 sm10 title>
                            <v-data-table
                                    :headers="[
                    {text: 'Date', value: 'name', align: 'left', sortable: false},
                    {text: 'Contact', value: 'pn', align: 'left', sortable: false},
                    {text: 'Type', value: 'lp', align: 'left', sortable: false},
                    {text: 'Tour/Description', value: '', sortable: false},
                    {text: '', value: '', sortable: false}
                                        ]"
                                    :items="extentionsToShow"
                                    hide-actions
                                    class="elevation-1"
                            >
                                <template slot="items" slot-scope="props">
                                    <tr>
                                        <td class="text-md-left">
                                            {{ props.item.dateIn }}
                                        </td>
                                        <td class="text-md-left">
                                            {{props.item.booking.name}}
                                        </td>
                                        <td class="text-md-left">
                                            {{props.item.type}}
                                        </td>
                                        <td class="text-md-left py-1" style="max-width: 550px">
                                            {{props.item.type ==
                                            'extra_tour'?props.item.tour.code:props.item.description}}
                                        </td>
                                        <td class="text-md-right">
                                            <v-btn icon class="mx-0" slot="activator"
                                                   @click="extEdit(props.item)">
                                                <v-icon color="success">edit</v-icon>
                                            </v-btn>
                                            <v-btn icon class="mx-0" slot="activator"
                                                   @click="extentionsToShow.splice(extentionsToShow.indexOf(props.item), 1)">
                                                <v-icon color="red">
                                                    delete
                                                </v-icon>
                                            </v-btn>
                                        </td>
                                    </tr>
                                </template>
                            </v-data-table>
                        </v-flex>
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
                                <v-btn  v-if="!backTour" color="primary" @click="$router.push({name: 'LSTourList'})">Cancel
                                </v-btn>
                                <v-btn color="primary" @click="save">Save</v-btn>
                            </v-flex>
                        </v-layout>
                    </v-container>
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
                                       @click="(typeof destinationItem.destination == typeof undefined)?false:destinationItem.accommodation='';destinationsTour.push(destinationItem);destinationItem={};"
                                >
                                    <v-icon>add</v-icon>
                                </v-btn>
                            </v-flex>
                        </v-layout>

                        <v-container v-if="destinationsTour.length" pl-4>
                            <v-layout row wrap v-for="(item, index) in destinationsTour" :key="index">
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
        <v-dialog v-model="dialogBookingAccommodation" scrollable max-width="900">
            <v-card>
                <v-card-title>
                    <span class="headline">Accommodation for: {{bookingEditing.name}} ({{bookingEditing.persons}} Pax)</span>
                    <v-btn icon @click.native="dialogBookingAccommodation = false" class="btn-close">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-card-title>

                    <v-card-text v-if="destinationsTour.length">
                        <v-container pl-4>
                            <v-layout row wrap>
                                <v-flex xs6>
                                    <v-text-field
                                            label="Sleeping Requirement"
                                            v-model="bookingEditing.sleepRequirement"
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-container>
                        <v-container v-if="item.type == 'group' || item.type == 'private'">
                            <v-layout row wrap>
                                <v-flex md12>
                                    <span class="subheading">Extra accommodation at the beginning</span>
                                    <v-btn icon color="primary" slot="activator" class="d-inline"
                                           dark
                                           small
                                           @click="extAccommodation = {before: true}; $refs.extAccommodation.reset();extAccommodationDialog = true"
                                    >
                                        <v-icon>add</v-icon>
                                    </v-btn>

                                </v-flex>
                            </v-layout>
                            <v-layout row wrap v-for="value, index in extentions" :key="index"
                                      v-if="value.before">
                                <v-flex xs1 style="padding-top: 20px">
                                    <v-icon :color="value.accommodation?'success':'error'"
                                            style="font-size: 34px">
                                        done
                                    </v-icon>
                                </v-flex>
                                <v-flex xs2 style="padding-top: 25px">
                                    <label>{{getDestinationDaysExt(value)}}</label>
                                </v-flex>
                                <v-flex xs3 ml-3 style="padding-top: 25px">
                                    {{value.destination.name}}
                                </v-flex>
                                <v-flex xs4>
                                    <v-text-field
                                            label="Accommodation"
                                            v-model="value.accommodation"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs1 style="padding-top: 20px">
                                    <v-btn icon small class="mx-0"
                                           @click="bookingEditing.extentions.splice(bookingEditing.extentions.indexOf(value), 1);">
                                        <v-icon v-bind:color="'red'">
                                            delete
                                        </v-icon>
                                    </v-btn>
                                    <v-btn icon small class="mx-0"
                                           @click="$refs.extAccommodation.reset();extAccommodation = value;extAccommodationEdit=true;extAccommodationDialog = true">
                                        <v-icon v-bind:color="'green'">
                                            edit
                                        </v-icon>
                                    </v-btn>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap mt-2>
                                <v-flex md12>
                                    <v-divider></v-divider>
                                </v-flex>
                            </v-layout>
                        </v-container>
                        <v-container pl-4>
                            <v-layout row wrap v-for="(destination, index) in destinationsTour" :key="index">
                                <v-flex xs1 style="padding-top: 20px">
                                    <v-icon :color="destination.accommodation?'success':'error'"
                                            style="font-size: 34px">
                                        done
                                    </v-icon>
                                </v-flex>
                                <v-flex xs2 style="padding-top: 25px">
                                    <label>{{getDestinationDays(destination)}}</label>
                                </v-flex>
                                <v-flex xs3 ml-3 style="padding-top: 25px">
                                    {{destination.destination.name}}
                                </v-flex>
                                <v-flex xs4>
                                    <v-text-field
                                            label="Accommodation"
                                            v-model="destination.accommodation"
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
                        </v-container>
                        <v-container v-if="item.type == 'group' || item.type == 'private'">
                            <v-layout row wrap>
                                <v-flex md12>
                                    <v-divider></v-divider>
                                </v-flex>
                            </v-layout>
                            <v-layout row wrap mt-2>
                                <v-flex md12 body-2>
                                    <span class="subheading">Extra accommodation at the end</span>
                                    <v-btn icon color="primary" slot="activator" class="d-inline"
                                           dark
                                           small
                                           @click="extAccommodation = {before: false}; $refs.extAccommodation.reset();extAccommodationDialog = true"
                                    >
                                        <v-icon>add</v-icon>
                                    </v-btn>

                                </v-flex>
                            </v-layout>
                            <v-layout row wrap v-for="value, index in extentions" :key="index"
                                      v-if="!value.before">
                                <v-flex xs1 style="padding-top: 20px">
                                    <v-icon :color="value.accommodation?'success':'error'"
                                            style="font-size: 34px">
                                        done
                                    </v-icon>
                                </v-flex>
                                <v-flex xs2 style="padding-top: 25px">
                                    <label>{{getDestinationDaysExt(value)}}</label>
                                </v-flex>
                                <v-flex xs3 ml-3 style="padding-top: 25px">
                                    {{value.destination.name}}
                                </v-flex>
                                <v-flex xs4>
                                    <v-text-field
                                            label="Accommodation"
                                            v-model="value.accommodation"
                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs1 style="padding-top: 20px">
                                    <v-btn icon small class="mx-0"
                                           @click="bookingEditing.extentions.splice(bookingEditing.extentions.indexOf(value), 1);">
                                        <v-icon v-bind:color="'red'">
                                            delete
                                        </v-icon>
                                    </v-btn>
                                    <v-btn icon small class="mx-0"
                                           @click="$refs.extAccommodation.reset();extAccommodation = value;extAccommodationEdit=true;extAccommodationDialog = true">
                                        <v-icon v-bind:color="'green'">
                                            edit
                                        </v-icon>
                                    </v-btn>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary darken-1" flat @click="dialogBookingAccommodation = false">
                            Cancel
                        </v-btn>
                        <v-btn color="primary darken-1" flat @click.native="saveAccommodation">Save
                        </v-btn>
                    </v-card-actions>

            </v-card>
        </v-dialog>
        <v-dialog v-model="dialogTransfer" scrollable max-width="900">
            <v-card>
                <v-card-title>
                    <span class="headline">Transfers for: {{bookingEditing.name}} ({{bookingEditing.persons}} Pax)</span>
                    <v-btn icon @click.native="dialogTransfer = false" class="btn-close">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-card-title>
                <!--<v-form ref="transferForm" lazy-validation>-->
                    <v-card-text>
                        <v-layout row wrap>
                            <v-flex xs2 px-2
                                    v-bind:style="{backgroundColor: (bookingEditing.transferInCanceled)?'rgb(255, 82, 82)':'white'}">
                                <span class="subheading">Transfer In</span>
                            </v-flex>
                            <v-flex xs4 px-2 my-2>
                                <v-tooltip top>
                                    <v-icon slot="activator"
                                            v-bind:style="{color: bookingEditing.flyData?'#5ac35e':'#ff5252'}">flight
                                    </v-icon>
                                    <span>Flight Data</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <v-icon class="ml-2" slot="activator"
                                            v-bind:style="{color: bookingEditing.accommodationIn?'#5ac35e':'#ff5252'}">
                                        home
                                    </v-icon>
                                    <span>Accommodation</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <v-icon class="ml-2" slot="activator" v-bind:style="{color: bookingEditing.sentIn?'#5ac35e':'#ff5252'}">email
                                    </v-icon>
                                    <span>Send to driver</span>
                                </v-tooltip>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap>
                            <v-flex xs3 px-2>
                                <v-menu
                                        ref="pickerTranferStartDate"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerTranferStartDate"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        :return-value.sync="bookingEditing.startDate"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Date"
                                            v-model="bookingEditing.startDate"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker v-model="bookingEditing.startDate" no-title scrollable
                                                   :min="currentDate"
                                                   @input="$refs.pickerTranferStartDate.save(bookingEditing.startDate)"></v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs3 px-2>
                                <v-menu
                                        ref="pickerTranferStartTime"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerTranferStartTime"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        max-width="290px"
                                        min-width="290px"
                                        :return-value.sync="bookingEditing.inTime"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Hour"
                                            v-model="bookingEditing.inTime"
                                            prepend-icon="access_time"
                                            readonly
                                    ></v-text-field>
                                    <!--<v-time-picker v-model="bookingEditing.inTime"-->
                                                   <!--@change="$refs.pickerTranferStartTime.save(bookingEditing.inTime)"></v-time-picker>-->
                                    <v-time-picker
                                            v-model="bookingEditing.inTime" >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="$refs.pickerTranferStartTime.save(bookingEditing.inTime)">OK</v-btn>
                                    </v-time-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs5 px-2>
                                <v-text-field
                                        label="Fly Data"
                                        v-model="bookingEditing.flyData"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs6 px-2>
                                <v-text-field
                                        label="Accomodation In"
                                        v-model="bookingEditing.accommodationIn"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs4 px-2>
                                <v-select
                                        :items="drivers"
                                        v-model="bookingEditing.driverIn"
                                        label="Drive"
                                        single-line
                                        item-text="name"
                                        item-value="id"
                                        :filter="customFilter"
                                        autocomplete
                                ></v-select>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap mt-4>
                            <v-flex xs2 px-2
                                    v-bind:style="{backgroundColor: (bookingEditing.transferOutCanceled)?'rgb(255, 82, 82)':'white'}">
                                <span class="subheading">Transfer Out</span>
                            </v-flex>
                            <v-flex xs4 px-2 my-2>
                                <v-tooltip top>
                                    <v-icon slot="activator"
                                            v-bind:style="{color: bookingEditing.flyDataOut?'#5ac35e':'#ff5252'}">flight
                                    </v-icon>
                                    <span>Flight Data</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <v-icon class="ml-2" slot="activator"
                                            v-bind:style="{color: bookingEditing.accommodationOut?'#5ac35e':'#ff5252'}">
                                        home
                                    </v-icon>
                                    <span>Accommodation</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <v-icon class="ml-2" slot="activator"  v-bind:style="{color: bookingEditing.sentOut?'#5ac35e':'#ff5252'}">email
                                    </v-icon>
                                    <span>Send to driver</span>
                                </v-tooltip>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap>
                            <v-flex xs3 px-2>
                                <v-menu
                                        ref="pickerTranferEndDate"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerTranferEndDate"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        :return-value.sync="bookingEditing.endDate"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Date"
                                            v-model="bookingEditing.endDate"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker v-model="bookingEditing.endDate" no-title scrollable
                                                   :min="bookingEditing.startDate"
                                                   @input="$refs.pickerTranferEndDate.save(bookingEditing.endDate)"></v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs3 px-2>
                                <v-menu
                                        ref="pickerTranferEndTime"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerTranferEndTime"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        max-width="290px"
                                        min-width="290px"
                                        :return-value.sync="bookingEditing.outTime"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Hour"
                                            v-model="bookingEditing.outTime"
                                            prepend-icon="access_time"
                                            readonly
                                    ></v-text-field>
                                    <!--<v-time-picker v-model="bookingEditing.outTime"-->
                                                   <!--@change="$refs.pickerTranferEndTime.save(bookingEditing.outTime)"></v-time-picker>-->
                                    <v-time-picker
                                            v-model="bookingEditing.outTime" >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="$refs.pickerTranferEndTime.save(bookingEditing.outTime)">OK</v-btn>
                                    </v-time-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs5 px-2>
                                <v-text-field
                                        label="Fly Data"
                                        v-model="bookingEditing.flyDataOut"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs6 px-2>
                                <v-text-field
                                        label="Accomodation Out"
                                        v-model="bookingEditing.accommodationOut"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs4 px-2>
                                <v-select
                                        :items="drivers"
                                        v-model="bookingEditing.driverOut"
                                        label="Drive"
                                        single-line
                                        item-text="name"
                                        item-value="id"
                                        :filter="customFilter"
                                        autocomplete
                                ></v-select>
                            </v-flex>
                        </v-layout>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary darken-1" flat @click="dialogTransfer = false">
                            Cancel
                        </v-btn>
                        <v-btn color="primary darken-1" flat @click.native="dialogTransfer = false">Save</v-btn>
                    </v-card-actions>
                <!--</v-form>-->
            </v-card>
        </v-dialog>
        <v-dialog v-model="extAccommodationDialog" max-width="900" :persistent="extAccommodationEdit">
            <v-card>
                <v-card-title class="pb-0">
                    <span class="headline">Add Extention</span>
                </v-card-title>
                <v-form ref="extAccommodation" lazy-validation>
                    <v-card-text class="py-0 mt-3">
                        <v-layout row wrap>
                            <v-flex xs3 pr-3>
                                <v-menu
                                        ref="pickerExtraAccommodation"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerExtraAccommodation"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        :nudge-right="40"
                                        :return-value.sync="extAccommodation.dateIn"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Date"
                                            v-model="extAccommodation.dateIn"
                                            prepend-icon="event"
                                            readonly
                                    ></v-text-field>
                                    <v-date-picker v-model="extAccommodation.dateIn" no-title scrollable
                                                   :allowed-dates="allowedDatesExt"
                                                   :min="currentDate"
                                                   @input="$refs.pickerExtraAccommodation.save(extAccommodation.dateIn)"></v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs1 pr-3>
                                <v-text-field
                                        label="Days"
                                        v-model="extAccommodation.days"
                                        :rules="numberRule"
                                        v-on:keyup="extAccommodation.days = (validNumbers(extAccommodation.days, $event)?extAccommodation.days:'')"
                                        required
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs3 pr-3>
                                <v-select
                                        :items="destinations"
                                        v-model="extAccommodation.destination"
                                        label="Destinations"
                                        item-text="name"
                                        autocomplete
                                ></v-select>
                            </v-flex>
                            <v-flex xs3 pr-3>
                                <v-text-field
                                        label="Accommodation"
                                        v-model="extAccommodation.accommodation"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn color="primary darken-1" flat @click="extAccommodationDialog= false">
                            Cancel
                        </v-btn>
                        <v-btn color="primary darken-1" flat @click.native="addAccommodationExt">Save</v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>
        <v-dialog v-model="extentionDialog" :max-width="extention.type == 'extra_tour'?900:500"
                  :persistent="extEditing">
            <v-card>
                <v-card-title class="pb-0">
                    <span class="headline">Add Extention</span>
                </v-card-title>
                <v-container>
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-radio-group v-model="extention.type">
                                <v-radio
                                        label="Extra Tour"
                                        value="extra_tour"
                                ></v-radio>
                                <v-radio
                                        label="Extra Information"
                                        value="extra_info"
                                ></v-radio>
                            </v-radio-group>
                        </v-flex>
                    </v-layout>
                </v-container>
                <v-container class="pt-0 ml-3" v-show="extention.type == 'extra_tour'">
                    <v-layout row wrap>
                        <v-expansion-panel>
                            <v-expansion-panel-content>
                                <v-flex md12 style="max-width: 182px; padding: 0px" slot="header">
                                    Existing tour
                                </v-flex>
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-form ref="extExtraTourForm" lazy-validation>
                                            <v-layout row wrap>
                                                <v-flex xs4 pl-2>
                                                    <v-select
                                                            :items="bookings"
                                                            v-model="extention.booking"
                                                            label="Contact Name"
                                                            item-text="name"
                                                            :rules="nameRule"
                                                            required
                                                    ></v-select>
                                                </v-flex>
                                                <v-flex xs3 pl-4>
                                                    <v-menu
                                                            ref="pickerExtExraTour"
                                                            lazy
                                                            :close-on-content-click="false"
                                                            v-model="pickerExtExraTour"
                                                            transition="scale-transition"
                                                            offset-y
                                                            full-width
                                                            :nudge-right="40"
                                                            :return-value.sync="extention.dateIn"
                                                    >
                                                        <v-text-field
                                                                slot="activator"
                                                                label="Date"
                                                                v-model="extention.dateIn"
                                                                prepend-icon="event"
                                                                readonly
                                                                :rules="nameRule"
                                                                required
                                                        ></v-text-field>
                                                        <v-date-picker v-model="extention.dateIn" no-title scrollable
                                                                       :min="currentDate"
                                                                       @input="$refs.pickerExtExraTour.save(extention.dateIn);setExtTour();"></v-date-picker>
                                                    </v-menu>
                                                </v-flex>
                                                <v-flex xs4 pl-4>
                                                    <v-select
                                                            v-show="typeof extention.tours == typeof undefined || extention.tours.length"
                                                            :items="extention.tours"
                                                            v-model="extention.tour"
                                                            label="Select Tour"
                                                            item-text="name"
                                                            :rules="nameRule"
                                                            required
                                                    >
                                                        <template slot="selection" slot-scope="data">
                                                            <div style="width: 100%">
                                                                {{ data.item.name }}
                                                                <span style="font-size: 12px; margin-left: 10px">{{ data.item.code }}</span>
                                                            </div>

                                                        </template>
                                                        <template slot="item" slot-scope="data">
                                                            <template>
                                                                <label>{{ data.item.name }}</label>
                                                                <span style="font-size: 12px; margin-left: 10px">{{ data.item.code }}</span>
                                                            </template>
                                                        </template>
                                                    </v-select>
                                                    <v-container
                                                            v-show="typeof extention.tours != typeof undefined && !extention.tours.length">
                                                        No tour on this date
                                                    </v-container>
                                                </v-flex>
                                            </v-layout>
                                        </v-form>
                                    </v-flex>
                                </v-layout>
                            </v-expansion-panel-content>
                            <v-expansion-panel-content>
                                <v-flex md12 style="max-width: 182px; padding: 0px" slot="header">
                                    LS Extention
                                </v-flex>
                                <v-layout row wrap>
                                    <v-flex xs12>
                                        <v-form ref="extExtraTourForm" lazy-validation>
                                            <v-layout row wrap>
                                                <v-flex xs4 pl-2>
                                                    <v-select
                                                            :items="bookings"
                                                            v-model="extention.booking"
                                                            label="Contact Name"
                                                            item-text="name"
                                                            item-value="id"
                                                            :rules="nameRule"
                                                            required
                                                    ></v-select>
                                                </v-flex>
                                                <v-flex xs3 pl-4>
                                                    <v-menu
                                                            ref="pickerExtExraTour2"
                                                            lazy
                                                            :close-on-content-click="false"
                                                            v-model="pickerExtExraTour2"
                                                            transition="scale-transition"
                                                            offset-y
                                                            full-width
                                                            :nudge-right="40"
                                                            :return-value.sync="extention.dateIn"
                                                    >
                                                        <v-text-field
                                                                slot="activator"
                                                                label="Date"
                                                                v-model="extention.dateIn"
                                                                prepend-icon="event"
                                                                readonly
                                                                :rules="nameRule"
                                                                required
                                                        ></v-text-field>
                                                        <v-date-picker v-model="extention.dateIn" no-title scrollable
                                                                       :min="currentDate"
                                                                       @input="$refs.pickerExtExraTour2.save(extention.dateIn);"></v-date-picker>
                                                    </v-menu>
                                                </v-flex>
                                                <v-flex xs4 pl-4>
                                                    <v-btn color="primary darken-1" @click="createNewTour">
                                                        Create LS Extention
                                                    </v-btn>
                                                </v-flex>
                                            </v-layout>
                                        </v-form>
                                    </v-flex>
                                </v-layout>
                            </v-expansion-panel-content>
                        </v-expansion-panel>
                    </v-layout>
                </v-container>
                <v-container v-show="extention.type == 'extra_info'">
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-form ref="extInfoForm" lazy-validation>
                                <v-layout row wrap>
                                    <v-flex xs8 pl-4>
                                        <v-select
                                                :items="bookings"
                                                v-model="extention.booking"
                                                label="Contact Name"
                                                item-text="name"
                                                :rules="nameRule"
                                                required
                                        ></v-select>
                                    </v-flex>
                                </v-layout>
                                <v-layout row wrap v-show="extention.booking">
                                    <v-flex xs8 pl-4>
                                        <v-menu
                                                ref="pickerExtInfo"
                                                lazy
                                                :close-on-content-click="false"
                                                v-model="pickerExtInfo"
                                                transition="scale-transition"
                                                offset-y
                                                full-width
                                                :nudge-right="40"
                                                :return-value.sync="extention.dateIn"
                                        >
                                            <v-text-field
                                                    slot="activator"
                                                    label="Date"
                                                    v-model="extention.dateIn"
                                                    prepend-icon="event"
                                                    readonly
                                                    :rules="nameRule"
                                                    required
                                            ></v-text-field>
                                            <v-date-picker v-model="extention.dateIn" no-title scrollable
                                                           :min="currentDate"
                                                           @input="$refs.pickerExtInfo.save(extention.dateIn)"></v-date-picker>
                                        </v-menu>
                                    </v-flex>
                                    <v-flex xs12 ml-3>
                                        <v-text-field
                                                v-model="extention.description"
                                                label="Description"
                                                textarea
                                                :rules="nameRule"
                                                required
                                        ></v-text-field>
                                    </v-flex>
                                </v-layout>
                            </v-form>
                        </v-flex>
                    </v-layout>
                </v-container>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn v-show="!extEditing" color="primary darken-1" flat @click="extentionDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="primary darken-1" flat @click.native="saveExt">Save</v-btn>
                </v-card-actions>
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
    import moment from 'moment';
    import {API_HOST, API_PATH} from '../../config/_entrypoint';
    import time from "../../utils/time";

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
                pickerTourEndDate: false,
                pickerTranferStartDate: false,
                pickerTranferEndDate: false,
                pickerTranferStartTime: false,
                pickerTranferEndTime: false,
                pickerExtraAccommodation: false,
                modal: false,
                dialogAccommodation: false,
                dialogTransfer: false,
                dialogBookingAccommodation: false,
                menuExt: false,
                extAccommodationDialog: false,
                extentionDialog: false,
                extAccommodation: {},
                extAccommodationEdit: false,
                extEditing: false,
                pickerExtInfo: false,
                pickerExtExraTour: false,
                pickerExtExraTour2: false,
                extentions: [],
                extentionsToShow: [],
                extention: {type: false},
                valid: true,
                validBookingForm: true,
                validateAccommodationForm: false,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                deletingFile: false,
                changeNotificationLoading: false,
                item: {
                    notifications: [],
                    destinations: [],
                    bookings: [],
                    attachments: [],
                    day: '',
                    days: '',
                    type: '',
                    description: '',
                    name: '',
                    code: '',
                    color: '',
                    agent: '',
                },
                destinationsTour: [],
                notificationTour: [],
                destinationItem: {},
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
                    v => !!v || 'This Field is required'
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
                currentDate: moment().format('YYYY-MM-DD'),
                loadingGeneric: false,
                backTour: false
            }
        }
        ,
        computed: {
            ...mapGetters({
                retrieveError: 'lstour/update/retrieveError',
                errorCreate: 'lstour/create/error',
                retrieveLoading: 'lstour/update/retrieveLoading',
                updateLoading: 'lstour/create/loading',
                destinationsLoading: 'destination/list/loading',
                destinations: 'destination/list/items',
                created: 'lstour/create/created',
                code: 'lstourtemplate/list/code',
                agents: 'agent/list/items',
                retrieved: 'lstour/update/retrieved',
                drivers: 'driver/list/items',
                notifications: 'lstourtemplate/list/notifications',
                notificationsLoading: 'lstourtemplate/list/loading',
                offices: 'touroffice/list/items',
            })
        }
        ,
        watch: {
            extAccommodationDialog(val) {
                if (!val)
                    this.extAccommodation = {};
            },
            created() {
                if(this.backTour){
                    this.$router.push({ name: 'LSTourUpdate', params: {id: this.backTour} });
                    window.location.reload(true);
                }
                else
                this.$router.push({name: 'LSTourList'});
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
            dialogAccommodation(val) {
                if (!val) {
                    let sum = 0;
                    this.destinationsTour.forEach(function (item) {
                        sum = sum + parseInt(item.days);
                    })
                    if (sum)
                        this.item.days = sum
                }
            }

        }
        ,
        methods: {
            createNewTour(){
                let link = API_HOST + API_PATH + '/create-ext-tour';
                this.changeNotificationLoading = true;
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                    body: JSON.stringify({booking: this.extention.booking, startDate: this.extention.dateIn, tour_type: 'ls', tour: this.item.id, })
                })
                    .then(response => response.json())
                    .then(response => {
                        this.changeNotificationLoading = false;
                        if(typeof response.id != typeof undefined){
                            this.$router.push({ name: 'LSTourUpdate', params: {id: response.id} });
                            var d = new Date();
                            d.setTime(d.getTime() + (1 * 24 * 60 * 60 * 1000));
                            var expires = "expires=" + d.toUTCString();
                            document.cookie = "tour=" + this.item.id + ";" + expires + ";path=/";
                            window.location.reload(true);
                        }

                    });
            },
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
            saveAccommodation() {
                this.bookingEditing.accommodations = [];
                this.destinationsTour.filter((item, index) => {
                        let object = {
                            accommodation: item.accommodation,
                            destination: item.destination.id,
                            order: ++index,
                            days: item.days
                        };
                        if (typeof item.dateIn != typeof undefined)
                            object.dateIn = item.dateIn;
                        this.bookingEditing.accommodations.push(object);
                });
                this.dialogBookingAccommodation = false;
                this.setTransfer(this.bookingEditing);
            },
            getDestinationDays(destination) {
                let days = 0, days_aux = 0, date_aux = this.item.startDate, month = moment(date_aux).format('MMMM');
                this.destinationsTour.forEach(function (item) {
                    if (item == destination) {
                        destination.dateIn = moment(date_aux).format('YYYY-MM-DD');
                        for (let i = 0; destination.days > i; i++) {
                            days = days + parseInt(moment(date_aux).add(days_aux++, 'days').format('D'));
                            if (i + 1 != destination.days) {
                                if (moment(date_aux).add(days_aux - 1, 'days').format('MMMM') != moment(date_aux).add(days_aux, 'days').format('MMMM')) {
                                    days = days + ' ' + month + '; ';
                                    month = moment(date_aux).add(days_aux, 'days').format('MMMM');
                                } else
                                    days = days + ', '
                            }
                            else {
                                days = days + ' ' + month;
                            }
                        }
                        return true;
                    }
                    else {
                        date_aux = moment(date_aux).add(item.days, 'days');
                        month = moment(date_aux).format('MMMM');
                    }
                })
                return days;
            },
            getDestinationDaysExt(destination) {
                let days = 0, days_aux = 0, date_aux = moment(destination.dateIn).format('YYYY-MM-DD'),
                    month = moment(date_aux).format('MMMM');
                for (let i = 0; destination.days > i; i++) {
                    days = days + parseInt(moment(date_aux).add(days_aux++, 'days').format('D'));
                    if (i + 1 != destination.days) {
                        if (moment(date_aux).add(days_aux - 1, 'days').format('MMMM') != moment(date_aux).add(days_aux, 'days').format('MMMM')) {
                            days = days + ' ' + month + '; ';
                            month = moment(date_aux).add(days_aux, 'days').format('MMMM');
                        } else
                            days = days + ', '
                    }
                    else {
                        days = days + ' ' + month;
                    }
                }
                return days;
            },
            setAccommodation(booking) {
                if (typeof booking.accommodations != typeof undefined && booking.accommodations.length) {
                    this.destinationsTour.filter((item, index) => {
                        item.accommodation = '';
                        index++;
                        booking.accommodations.filter(accommodation => {
                            if (index == accommodation.order) {
                                item.accommodation = accommodation.accommodation;
                            }
                        });
                    })
                }
                else {
                    this.destinationsTour.filter(item => item.accommodation = '');
                }
                if (typeof booking.extentions == typeof undefined)
                    booking.extentions = [];
                this.bookingEditing = booking;
                this.extentions = this.bookingEditing.extentions;
                this.dialogBookingAccommodation = true;
            },
            assingBooking(item) {
                this.bookingEditing = {
                    name: item.name,
                    lp: item.lp,
                    persons: item.persons,
                    canceled: item.canceled,
                    id: item.id,
                };
                if (typeof item.accommodations != typeof undefined)
                    this.bookingEditing.accommodations = item.accommodations;
                if (typeof item.sleepRequirement != typeof undefined)
                    this.bookingEditing.sleepRequirement = item.sleepRequirement;
                if (item.transferIn) {
                    if (item.transferIn.startDate)
                        this.bookingEditing.startDate = item.transferIn.startDate;
                    if (item.transferIn.startTime)
                        this.bookingEditing.inTime = item.transferIn.startTime;
                    if (item.transferIn.flyData)
                        this.bookingEditing.flyData = item.transferIn.flyData;
                    if (item.transferIn.drive)
                        this.bookingEditing.driverIn = item.transferIn.drive.id
                    if (item.transferIn.accommodation)
                        this.bookingEditing.accommodationIn = item.transferIn.accommodation.accommodation;
                    this.bookingEditing.sentIn = item.transferIn.sent
                    this.bookingEditing.transferInCanceled = item.transferIn.canceled;
                }
                if (item.transferOut) {
                    if (item.transferOut.startDate)
                        this.bookingEditing.endDate = item.transferOut.startDate;

                    if (item.transferOut.startTime)
                        this.bookingEditing.outTime = item.transferOut.startTime;
                    if (item.transferOut.flyData)
                        this.bookingEditing.flyDataOut = item.transferOut.flyData;
                    if (item.transferOut.drive)
                        this.bookingEditing.driverOut = item.transferOut.drive.id
                    if (item.transferOut.accommodation)
                        this.bookingEditing.accommodationOut = item.transferOut.accommodation.accommodation
                    this.bookingEditing.sentOut = item.transferOut.sent
                    this.bookingEditing.transferOutCanceled = item.transferOut.canceled;
                }
                if (typeof item.extentions != typeof undefined) {
                    let $this = this;
                    item.extentions.forEach(function (item) {
                        $this.destinations.forEach(function (item2) {
                            if (item.destination == item2.id)
                                item.destination = item2;
                        })
                    })
                    this.bookingEditing.extentions = item.extentions;
                }
                if (typeof item.otherExtentions != typeof undefined) {
                    let $this = this;
                    item.otherExtentions.forEach(function (item2) {
                        item2.booking = $this.bookingEditing;
                        $this.extentionsToShow.push(item2)
                    });
                }
                return this.bookingEditing;
            },
            setTransfer(item) {
                this.bookingEditing = item;
                if (this.item.startDate && typeof this.bookingEditing.startDate == "undefined")
                    this.bookingEditing.startDate = this.item.startDate;
                if (this.item.endDate && typeof this.bookingEditing.endDate == "undefined")
                    this.bookingEditing.endDate = this.item.endDate;
                if (this.destinationsTour.length && typeof this.bookingEditing.accommodations != typeof undefined && this.bookingEditing.accommodations.length) {
                    if (this.destinationsTour[0].destination.id == this.bookingEditing.accommodations[0].destination)
                        this.bookingEditing.accommodationIn = this.bookingEditing.accommodations[0].accommodation;
                    if (this.destinationsTour[this.destinationsTour.length - 1].destination.id == this.bookingEditing.accommodations[this.bookingEditing.accommodations.length - 1].destination)
                        this.bookingEditing.accommodationOut = this.bookingEditing.accommodations[this.bookingEditing.accommodations.length - 1].accommodation;
                }
            },
            addBooking() {
                if (this.$refs.bookingForm.validate()) {
                    let item = Object.assign({}, this.booking);
                    this.bookingEditing = item;
                    this.setAccommodation(item);
                    this.saveAccommodation();
                    this.bookings.push(item);
                    this.setTransfer(item);
                    this.$refs.bookingForm.reset();
                }
            },
            allowDates(val) {
                if (this.item.type == 'group') {
                    return parseInt(moment(val).format('e')) + 1 == parseInt(this.item.day)
                }
                return val;
            },
            allowedDatesExt(val) {
                if (this.item.endDate){
                    let endDate = moment(this.item.endDate).clone();
                    if (!this.extAccommodation.before) {
                        endDate = moment(this.item.endDate).subtract(1, 'days');
                    }
                    return (parseInt(moment(val).format('YYYYMMDD')) > parseInt(moment(endDate).format('YYYYMMDD')) && !this.extAccommodation.before) || (parseInt(moment(val).format('YYYYMMDD')) < parseInt(moment(this.item.startDate).format('YYYYMMDD')) && this.extAccommodation.before)
                }
                else if (this.item.days){
                    let days = this.item.days;
                    if (!this.extAccommodation.before) {
                        days = days - 1
                    }
                    return (parseInt(moment(val).format('YYYYMMDD')) > parseInt(moment(this.item.startDate).add(days, 'days').format('YYYYMMDD')) && !this.extAccommodation.before) || (parseInt(moment(val).format('YYYYMMDD')) < parseInt(moment(this.item.startDate).format('YYYYMMDD')) && this.extAccommodation.before)
                }
                return val;
            },
            addAccommodationExt() {
                if (!this.$refs.extAccommodation.validate()) return;
                else if (this.extAccommodationEdit)
                    this.extAccommodationEdit = false;
                else
                    this.bookingEditing.extentions.push(Object.assign({}, this.extAccommodation));
                this.extAccommodationDialog = false;

            },
            setExtTour() {
                let link = API_HOST + API_PATH + '/get-tours-by-date';
                this.loadingGeneric = true;
                fetch(link, {
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({date: this.extention.dateIn})
                })
                    .then(response => response.json())
                    .then(response => {
                        this.loadingGeneric = false;
                        this.extention.tours = response;
                    });
            },
            changeStartDay() {

                if (this.item.type == 'group') {
                    if (typeof this.item.startDate != typeof undefined && parseInt(moment(this.item.startDate).format('e')) + 1 != parseInt(this.item.day)) {
                        let now = moment();
                        while (true) {
                            if (parseInt(now.format('e')) + 1 == parseInt(this.item.day)) {
                                this.item.startDate = now.format('YYYY-MM-DD');
                                break;
                            }
                            else
                                now.add(1, 'days');
                        }
                    }
                }
            },
            setEndDate() {
                if (this.item.days) {
                    this.item.endDate = moment(this.item.startDate).add((this.item.days - 1), 'days').format('YYYY-MM-DD');
                }
            },
            error(message) {
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
                this.changeNotificationLoading;
                this.loadingGeneric = false;
                this.$store.dispatch('lstour/create/reset');
                this.$store.dispatch('lstour/update/reset');
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
            validNumbersPerson(val, event) {

                if (invalidNumber(event.keyCode)) {
                    this.snackbarColor = 'warning';
                    this.snackbarText = val + ' is not a number';
                    this.snackbar = true;
                    return false;
                }
                // else if (val > 12) {
                //     this.snackbarColor = 'warning';
                //     this.snackbarText = 'can not exceed 12';
                //     this.snackbar = true;
                //     return false;
                // }
                return true;
            },
            customFilter: filter,
            setDestinations() {
                let index = 1;
                this.item.destinations.filter(item => {
                    this.destinationsTour.push({
                        destination: {id: item.destination, name: item.name},
                        days: item.days,
                        id: item.id,
                        accommodation: item.name
                    });
                });
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
                    let link = API_HOST + API_PATH + '/delete-file';
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
            extEdit(item) {
                this.extention = item;
                this.extEditing = true;
                this.extentionDialog = true;
            },
            saveExt() {
                if (this.extention.type == "extra_tour") {
                    if (!this.$refs.extExtraTourForm.validate())
                        return false;
                }
                else if (!this.$refs.extInfoForm.validate())
                    return false;
                if (this.extEditing) {
                    this.extEditing = false;
                    this.extentionDialog = false;
                    this.extention = {};
                    this.$refs.extInfoForm.reset();
                    this.$refs.extExtraTourForm.reset();
                    return false
                }
                this.extentionsToShow.push(Object.assign({}, this.extention));
                this.extentionDialog = false;
                this.extention = {};
                this.$refs.extInfoForm.reset();
                this.$refs.extExtraTourForm.reset();
            },
            save() {

                if (!this.$refs.tourForm.validate()) {
                    this.$vuetify.goTo(0, {duration: 500})
                    return false;
                }

                if (this.bookings.length)
                    this.item.bookings = this.bookings;

                let array = [], accommodation = false;

                if (this.item.type == 'tailor_made') {
                    this.notificationTour.forEach(function (item) {
                        if (item && typeof item === 'object') {
                            array.push({
                                days: item.days,
                                done: typeof item.done != typeof undefined ? item.done : false,
                                id: typeof item.notification != typeof undefined ? item.notification : false,
                                notification: item.id,
                            });
                            if (item.code == 'a')
                                accommodation = true;
                        }
                    });
                    this.item.notifications = array;
                }
                if (accommodation) {
                    array = [];
                    this.destinationsTour.forEach(function (item) {
                        if (item && typeof item === 'object')
                            array.push({destination: item.destination.id, days: item.days, id: item.id})
                    })
                    this.item.destinations = array;
                }
                else
                    this.destinationsTour = [];
                this.item.bookings.forEach(function (item) {
                    if (typeof item.extentions != typeof undefined && item.extentions.length) {
                        item.extentions.forEach(function (item2) {
                            item2.destination = item2.destination.id;
                            if (typeof item.accommodations == typeof undefined)
                                item.accommodations = [];
                            item.accommodations.push(item2)
                        })
                        item.extentions = [];
                    }
                });
                if (this.extentionsToShow.length) {
                    this.extentionsToShow.forEach(function (item) {
                        let extention = {type: item.type, date: item.dateIn}
                        if (item.type == 'extra_tour')
                            extention.tour = item.tour.code;
                        else
                            extention.description = item.description;
                        if (typeof item.id != typeof undefined)
                            extention.id = item.id;
                        if (typeof item.booking.extentions == typeof undefined)
                            item.booking.extentions = [];

                        item.booking.extentions.push(extention)
                    })
                }
                if(this.backTour)
                    this.item.ext = true;
                this.$store.dispatch('lstour/create/update', this.item).catch(e => {
                    this.error(time() + ' ' + e.message)
                });
            }
            ,
        }
        ,
        created() {
            this.$store.dispatch('lstour/update/retrieve', decodeURIComponent(this.$route.params.id)).then(
                () => {

                    this.$store.dispatch('touroffice/list/getItems');
                    this.$store.dispatch('driver/list/getItems');
                    if (this.retrieved.type == 'tailor_made') {
                        this.$store.dispatch('lstourtemplate/list/getLsNotification').then(
                            () => {
                                Object.assign(this.item, this.retrieved);
                                this.setNotifications();
                                this.item.bookings = [];
                                if (!this.destinations.length)
                                    this.$store.dispatch('destination/list/getItems').then(
                                        () => {
                                            this.setDestinations();
                                            if (typeof this.retrieved.bookings != typeof undefined) {
                                                let $this = this;
                                                this.retrieved.bookings.forEach(function (item) {
                                                    $this.bookings.push($this.assingBooking(item));
                                                })
                                            }
                                        })
                                else {
                                    this.setDestinations();
                                    if (typeof this.retrieved.bookings != typeof undefined) {
                                        let $this = this;
                                        this.retrieved.bookings.forEach(function (item) {
                                            $this.bookings.push($this.assingBooking(item));
                                        })
                                    }
                                }
                                if (!this.agents.length)
                                    this.$store.dispatch('agent/list/getItems');
                            })
                    }
                    else {
                        Object.assign(this.item, this.retrieved);
                        this.item.bookings = [];
                        if (!this.destinations.length)
                            this.$store.dispatch('destination/list/getItems').then(
                                () => {
                                    this.setDestinations();
                                    if (typeof this.retrieved.bookings != typeof undefined) {
                                        let $this = this;
                                        this.retrieved.bookings.forEach(function (item) {
                                            $this.bookings.push($this.assingBooking(item));
                                        })
                                    }
                                })
                        else {
                            this.setDestinations();
                            if (typeof this.retrieved.bookings != typeof undefined) {
                                let $this = this;
                                this.retrieved.bookings.forEach(function (item) {
                                    $this.bookings.push($this.assingBooking(item));
                                })
                            }
                        }
                        if (!this.agents.length)
                            this.$store.dispatch('agent/list/getItems');
                    }
                    let name = "tour=";
                    let ca = document.cookie.split(';');
                    for (var i = 0; i < ca.length; i++) {
                        var c = ca[i];
                        while (c.charAt(0) == ' ') {
                            c = c.substring(1);
                        }
                        if (c.indexOf(name) == 0) {
                            this.$vuetify.goTo(0, {duration: 500});
                            this.backTour = c.substring(name.length, c.length);
                            document.cookie = "tour=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        }
                    }
console.log(this.retrieved)
                }
            ).catch(e => {
                this.error(time() + ' ' + e.message)
            });
        }
    }
</script>
