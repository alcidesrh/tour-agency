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
                         v-show="templatesLoading || createLoading || destinationsLoading || retrieveLoading || loadingGeneric">
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
                           @click="$router.push({name: 'LSTourList'})">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-container>
                <v-container>
                    <v-layout row wrap>
                        <v-flex md4
                                v-show="!templatesLoading && !createLoading && !destinationsLoading">
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
                                    required
                            >
                            </v-select>
                        </v-flex>

                    </v-layout>
                </v-container>
                <v-container v-show="typeof template == 'object'">
                    <v-layout row wrap>
                        <v-flex md12>
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
                                                required
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
                                                    label="Start Date"
                                                    v-model="item.startDate"
                                                    prepend-icon="event"
                                                    readonly
                                                    @click="changeStartDay"
                                                    required
                                                    :rules="nameRule"
                                            ></v-text-field>
                                            <v-date-picker v-model="item.startDate" no-title scrollable
                                                           :min="currentDate"
                                                           :allowed-dates="allowedDates"
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
                                                :disabled="template.type == 'group' || template.type == 'private'"
                                        >
                                            <v-text-field
                                                    slot="activator"
                                                    label="End Date"
                                                    v-model="item.endDate"
                                                    prepend-icon="event"
                                                    readonly
                                                    :disabled="template.type == 'group' || template.type == 'private'"
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
                    <v-layout row wrap class="mt-5">
                        <v-expansion-panel>
                            <v-expansion-panel-content>
                                <v-flex class="xs2 title" style="max-width: 131px; padding: 0px" slot="header">
                                    Notifications
                                </v-flex>
                                <v-flex md7>
                                    <v-layout row wrap>
                                        <v-flex md4 style="min-width: 260px">
                                        </v-flex>
                                        <v-flex md1>
                                            Days
                                        </v-flex>
                                    </v-layout>
                                    <v-layout row wrap v-for="(n, index) in notifications" :key="n.id">
                                        <v-flex md4 style="min-width: 260px">
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
                        <v-flex xs2 title slot="header" style="padding: 0px; max-width: 101px;">
                            Bookings
                        </v-flex>
                        <v-flex xs12 mb-3 title>
                            <v-layout row wrap>
                                <v-flex xs12>
                                    <v-form ref="bookingForm" lazy-validation>
                                        <v-layout row wrap
                                                  v-show="(item.type != 'private' && item.type != 'tailor_made') || bookings.length == 0">
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
                                            <v-flex xs12 sm1>
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
                                                       @click="setTransfer(props.item); dialogTransfer = true;">
                                                    Transfer
                                                </v-btn>
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
                                        :rules="nameRule"
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
                    <v-layout row wrap class="mt-5" v-if="item.type == 'group' || item.type == 'private'">
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
                                <v-btn color="primary" @click="$router.push({name: 'LSTourList'})">Cancel
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
                                       @click="addDestinationsTour"
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
                    <v-btn icon flat @click.native="dialogBookingAccommodation = false" class="btn-close">
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
                        <v-flex xs4 px-2>
                            <span class="subheading">Transfer In</span>
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
                                        v-model="bookingEditing.inTime">
                                    <v-spacer></v-spacer>
                                    <v-btn flat color="primary"
                                           @click="$refs.pickerTranferStartTime.save(bookingEditing.inTime)">OK
                                    </v-btn>
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
                    <v-container>
                        <v-divider></v-divider>
                    </v-container>
                    <v-layout row wrap mt-4>
                        <v-flex xs4 px-2>
                            <span class="subheading">Transfer Out</span>
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
                                        label="End Date"
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
                                        v-model="bookingEditing.outTime">
                                    <v-spacer></v-spacer>
                                    <v-btn flat color="primary"
                                           @click="$refs.pickerTranferEndTime.save(bookingEditing.outTime)">OK
                                    </v-btn>
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
                        <v-btn color="primary darken-1" flat @click="extAccommodationDialog= false"
                               v-show="!extAccommodationEdit">
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
                <v-container v-show="extention.type == 'extra_tour'">
                    <v-layout row wrap>
                        <v-flex xs12>
                            <v-form ref="extExtraTourForm" lazy-validation>
                                <v-layout row wrap>
                                    <v-flex xs4 pl-4>
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
                date: null,
                pickerTourStartDate: false,
                pickerTourEndDate: false,
                pickerTranferStartDate: false,
                pickerTranferEndDate: false,
                pickerTranferStartTime: false,
                pickerTranferEndTime: false,
                pickerExtraAccommodation: false,
                modal: false,
                hasAgent: false,
                officeForm: false,
                dialogAccommodation: false,
                dialogTransfer: false,
                dialogBookingAccommodation: false,
                valid: true,
                validBookingForm: true,
                template: false,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                deletingFile: false,
                item: {},
                destinationsTour: [],
                notificationTour: [],
                destinationItem: {},
                private: false,
                menuExt: false,
                extAccommodationDialog: false,
                extentionDialog: false,
                extAccommodation: {},
                extAccommodationEdit: false,
                extEditing: false,
                pickerExtInfo: false,
                pickerExtExraTour: false,
                extentions: [],
                extentionsToShow: [],
                extention: {type: false},
                loadingGeneric: false,
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
                currentDate: moment().format('YYYY-MM-DD')
            }
        }
        ,
        computed: {
            ...mapGetters({
                errorCreate: 'lstour/create/error',
                createLoading: 'lstour/create/loading',
                destinationsLoading: 'destination/list/loading',
                notifications: 'lstourtemplate/list/notifications',
                destinations: 'destination/list/items',
                created: 'lstour/create/created',
                code: 'lstourtemplate/list/code',
                templates: 'lstourtemplate/list/items',
                agents: 'agent/list/items',
                offices: 'touroffice/list/items',
                templatesLoading: 'lstourtemplate/list/loading',
                retrieved: 'lstourtemplate/update/retrieved',
                retrieveLoading: 'lstourtemplate/update/retrieveLoading',
                drivers: 'driver/list/items'
            })
        }
        ,
        watch: {
            template(val) {
                this.$store.dispatch('lstourtemplate/update/retrieve', decodeURIComponent(val['@id'])).then(() => {
                    this.reset();
                    if (this.retrieved.days)
                        this.item.days = this.retrieved.days;
                    if (this.retrieved.day)
                        this.item.day = this.retrieved.day;
                    this.item.template = this.retrieved.id;
                    this.item.name = this.retrieved.name;
                    this.item.code = this.retrieved.code;
                    this.item.color = this.retrieved.color;
                    this.item.type = this.retrieved.type;
                    this.private = this.retrieved.type == 'private' ? true : false;
                    this.item.description = this.retrieved.description;
                    this.setNotifications();
                    this.setDestinations();
                })
            },
            private(val) {
                if (val)
                    this.item.type = 'private';
                else
                    this.item.type = this.retrieved.type;
            },
            created() {
                this.$router.push({name: 'LSTourList'});
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
            // dialogAccommodation(val) {
            //     if (!val) {
            //         let sum = 0;
            //         this.destinationsTour.forEach(function (item) {
            //             sum = sum + parseInt(item.days);
            //         })
            //         if (sum)
            //             this.item.days = sum
            //     }
            // }

        }
        ,
        methods: {
            saveAccommodation() {
                this.bookingEditing.accommodations = [];
                this.destinationsTour.filter((item, index) => {
                    // if (typeof item.accommodation != typeof undefined && item.accommodation != '') {
                    let object = {
                        accommodation: item.accommodation,
                        destination: item.destination.id,
                        order: ++index,
                        days: item.days
                    };
                    if (typeof item.dateIn != typeof undefined)
                        object.dateIn = item.dateIn;
                    this.bookingEditing.accommodations.push(object);
                    // }
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
                        })
                    })
                } else {
                    this.destinationsTour.filter(item => item.accommodation = '');
                }
                if (typeof booking.extentions == typeof undefined)
                    booking.extentions = [];
                this.bookingEditing = booking;
                this.extentions = this.bookingEditing.extentions;
                this.dialogBookingAccommodation = true;
            },
            setTransfer(item) {
                this.bookingEditing = item;
                if (this.item.startDate)
                    this.bookingEditing.startDate = this.item.startDate;
                if (this.item.endDate)
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
            allowedDates(val) {
                if (this.item.type == 'group') {
                    return parseInt(moment(val).format('e')) + 1 == parseInt(this.item.day)
                }
                return val;
            },
            allowedDatesExt(val) {
                if (this.item.endDate) {
                    let endDate = moment(this.item.endDate).clone();
                    if (!this.extAccommodation.before) {
                        endDate = moment(this.item.endDate).subtract(1, 'days');
                    }
                    return (parseInt(moment(val).format('YYYYMMDD')) > parseInt(moment(endDate).format('YYYYMMDD')) && !this.extAccommodation.before) || (parseInt(moment(val).format('YYYYMMDD')) < parseInt(moment(this.item.startDate).format('YYYYMMDD')) && this.extAccommodation.before);
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
                else {
                    this.bookingEditing.extentions.push(Object.assign({}, this.extAccommodation));
                }
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
                if (this.item.days && this.item.type != 'tailor_made') {
                    this.item.endDate = moment(this.item.startDate).add((this.item.days - 1), 'days').format('YYYY-MM-DD');
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
            addDestinationsTour() {
                if (typeof this.destinationItem.destination != typeof undefined) {
                    this.destinationItem.accommodation = '';
                    this.destinationItem.id = this.destinationsTour.length + 1;
                    this.destinationsTour.push(this.destinationItem);
                    this.destinationItem = {};
                }
            },
            setNotifications() {
                this.notifications.filter((item, index) => {
                    let $this = this, filter = function (item2) {
                        if (item2.notification == item.id) {
                            item.days = item2.days;
                            $this.notificationTour[index] = item;
                        }
                    }
                    this.retrieved.notifications.filter(item2 => filter(item2));
                })
            },
            setDestinations() {
                let index = 1;
                this.retrieved.destinations.filter(item => {
                    this.destinationsTour.push({
                        destination: {id: item.destination, name: item.name},
                        days: item.days,
                        id: index++,
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
                if (!this.$refs.officeForm.validate()) {
                    this.$vuetify.goTo(0, {duration: 100})
                    return false;
                }
                let item = {};
                Object.assign(item, this.item);
                if (this.bookings.length)
                    item.bookings = this.bookings;
                let array = [], accommodation = false;
                this.notificationTour.forEach(function (item) {
                    if (item)
                        if (typeof item === 'object') {
                            array.push({notification: item.id, days: item.days});
                            if (item.code == 'a')
                                accommodation = true;
                        }
                });
                item.notifications = array;
                if (accommodation) {
                    array = [];
                    this.destinationsTour.forEach(function (item) {
                        if (item && typeof item === 'object')
                            array.push({destination: item.destination.id, days: item.days})
                    })
                    item.destinations = array;
                }
                else
                    this.destinationsTour = [];

                item.bookings.forEach(function (item) {
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

                this.$store.dispatch('lstour/create/create', item);
            },
            reset() {
                this.bookingEditing = {};
                this.booking = {name: '', persons: '', lp: ''};
                this.bookings = [];
                this.date = null;
                this.pickerTourStartDate = false;
                this.pickerTourEndDate = false;
                this.pickerTranferStartDate = false;
                this.pickerTranferEndDate = false;
                this.modal = false;
                this.hasAgent = false;
                this.destinationsTour = []
                this.notificationTour = []
                this.destinationItem = [];
                this.item = {
                    attachments: []
                }
            }
        }
        ,
        created() {
            if(typeof decodeURIComponent(this.$route.params.booking) != typeof undefined){

            }
            if (!this.templates.length)
                this.$store.dispatch('lstourtemplate/list/getItems');
            this.$store.dispatch('driver/list/getItems');
            this.$store.dispatch('lstourtemplate/list/getLsNotification');
            if (!this.destinations.length)
                this.$store.dispatch('destination/list/getItems');
            if (!this.code.length) {
                this.$store.dispatch('lstourtemplate/list/getCode');
            }
            if (!this.offices.length)
                this.$store.dispatch('touroffice/list/getItems');
            if (!this.agents.length)
                this.$store.dispatch('agent/list/getItems');

        }
    }
</script>
