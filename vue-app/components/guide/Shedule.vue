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
        <v-container style="position: fixed; z-index: 2001" fill-height justify-center
                     v-show="loading || loadingGeneric || loadingTour">
            <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
        </v-container>
        <v-card>
            <v-container text-xs-center>
                <v-layout wrap row>
                    <v-flex xs4 offset-xs4 text-xs-center v-show="month">
                        <v-icon class="pointer mr-3 bold" @click="prev">navigate_before</v-icon>
                        <label class="headline">{{month}}</label>
                        <v-icon class="pointer ml-3 bold" @click="next">navigate_next</v-icon>
                    </v-flex>
                    <v-flex xs4 v-show="month">
                        <v-menu style="display: inline"
                                :close-on-content-click="false"
                                :close-on-click="false"
                                :nudge-width="200"
                                top left offset-x
                                v-model="menu"
                        >

                            <v-btn id="btn" slot="activator" v-show="toursNG.length"
                                   style="float: right; display: inline">Assign
                                Guide
                                <v-badge color="red" class="ml-1">
                                    <span slot="badge">{{toursNG.length}}</span>
                                </v-badge>
                            </v-btn>
                            <v-card>
                                <v-form v-model="valid" ref="assingForm" lazy-validation>
                                    <v-list>
                                        <v-list-tile>
                                            <v-list-tile-action>
                                                <v-select
                                                        label="Select Tour"
                                                        :items="toursNG"
                                                        v-model="tour"
                                                        autocomplete
                                                        item-text="code"
                                                        required
                                                        :rules="fieldRule"
                                                >
                                                    <template slot="selection" slot-scope="data">
                                                        <div style="width: 100%">
                                                            <span v-bind:style="{backgroundColor: data.item.color, width: '25px', height: '25px', marginRight: '5px'}"></span>
                                                            {{ data.item.code }}
                                                            <span style="font-size: 12px; position: absolute; right: 20px">{{data.item.date}}</span>
                                                        </div>

                                                    </template>
                                                    <template slot="item" slot-scope="data">
                                                        <template v-if="typeof data.item !== 'object'">
                                                            No data available
                                                        </template>
                                                        <template v-else>
                                                            <span v-bind:style="{backgroundColor: data.item.color, width: '25px', height: '25px', marginRight: '5px'}"></span>
                                                            <label>{{ data.item.code }}</label>
                                                        </template>
                                                    </template>
                                                </v-select>
                                            </v-list-tile-action>
                                        </v-list-tile>
                                        <v-list-tile>
                                            <v-list-tile-action>
                                                <v-select v-if="availableGuide"
                                                          label="Guides"
                                                          :items="avaibleGuides"
                                                          v-model="guideSelected"
                                                          autocomplete
                                                          item-text="name"
                                                          required
                                                          :rules="fieldRule"
                                                >
                                                </v-select>
                                                <label v-else class="error">No available guide for this tour</label>
                                            </v-list-tile-action>
                                        </v-list-tile>
                                    </v-list>
                                </v-form>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn flat @click="cancelAssing">Cancel</v-btn>
                                    <v-btn color="primary" flat @click="assingGuide" v-show="availableGuide">Save
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-menu>
                        <v-menu style="display: inline"
                                ref="pickerDate"
                                lazy
                                :close-on-content-click="false"
                                v-model="pickerDate"
                                transition="scale-transition"
                                offset-y
                                full-width
                                :nudge-right="25"
                                :return-value.sync="filterDate"

                        >
                            <v-text-field style="width: 200px; float: right; display: inline"
                                          slot="activator"
                                          label="Date"
                                          v-model="filterDate"
                                          prepend-icon="event"
                                          readonly
                                          v-show="!loading"
                            ></v-text-field>
                            <v-date-picker color="primary" v-model="filterDate" no-title scrollable
                                           @input="$refs.pickerDate.save(filterDate);filterPerDate();"></v-date-picker>
                        </v-menu>
                    </v-flex>
                    <v-flex xs12>
                        <div style="margin: auto">
                            <div class="wrap" style="height: 25px">
                                <v-layout wrap>
                                    <v-flex id="first-row-guides" class="d-inline"
                                            style="width: 150px; max-width: 150px; overflow:visible;padding:0;position:relative;"></v-flex>
                                    <v-flex id="first-row-dates" class="d-inline"
                                            style="overflow:visible;padding:0;position:relative;"></v-flex>
                                </v-layout>
                            </div>
                            <div v-bind:style="{maxHeight: maxHeight, overflow: 'auto'}">
                                <v-layout wrap>
                                    <v-flex id="guides-container" class="d-inline"
                                            v-bind:style="{maxHeight: maxHeight, width: '150px', maxWidth: '150px', padding: 0, position: 'relative'}"></v-flex>
                                    <v-flex id="container" class="d-inline"
                                            v-bind:style="{maxHeight: maxHeight, padding: 0, position: 'relative'}"></v-flex>

                                </v-layout>
                            </div>
                        </div>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-card>
        <v-dialog v-model="dialogShow" max-width="800">
            <v-card>

                <v-card-title>
                    <span class="headline" v-if="toShow.canceled"
                          v-bind:style="{backgroundColor: (!toShow.canceled)?toShow.color:'#ff5252'}">{{toShow.code}}</span>
                    <span class="headline" v-else>{{toShow.code}}</span>
                    <v-btn icon @click.native="dialogShow = false" class="btn-close">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap mb-3>
                        <v-flex xs12 px-2>
                            <v-tooltip top v-if="toShow.notifications"
                                       v-for="notification,index in toShow.notifications" :key="index">
                                <v-icon v-if="index == 0" class="pointer" slot="activator"
                                        :color="(notification.done)?'success':'error'"
                                        @click="changeNotificationState(notification)">{{notification.icon}}
                                </v-icon>
                                <v-icon v-else class="pointer ml-2" slot="activator"
                                        :color="(notification.done)?'success':'error'"
                                        @click="changeNotificationState(notification)">{{notification.icon}}
                                </v-icon>
                                <span>{{notification.name}}</span>
                            </v-tooltip>
                            <span class="right">
                            <v-tooltip top>
                            <v-icon color="success" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="$router.push({ name: 'LSTourUpdate', params: {id: toShow.id} })">edit</v-icon>
                            <span>Edit tour</span>
                            </v-tooltip>
                            <v-tooltip top>
                            <v-icon color="primary" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="relocateTour(toShow)">refresh</v-icon>
                            <span>Relocate tour</span>
                            </v-tooltip>
                            <v-tooltip top>
                            <v-icon color="warning" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="cancelTour(toShow)">cancel</v-icon>
                            <span>Cancel tour guide</span>
                            </v-tooltip>
                            <v-tooltip top>
                         <v-icon color="error" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                 @click="deleteItem(toShow, 'ls')">delete</v-icon>
                         <span>Delete tour</span>
                        </v-tooltip>
                            </span>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2>
                        <v-flex xs12 px-2>
                            <label class="bold">Name: </label>{{toShow.name}}
                            <label class="bold ml-2">Type: </label>
                            {{(toShow.type == 'tailor_made'?'tailor made':toShow.type)}}
                            <span v-if="toShow.agent"><label
                                    class="bold ml-2">Agent: </label>{{toShow.agent}}</span>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2>
                        <v-flex xs12 px-2>
                            <label class="bold">Start Date: </label>{{toShow.startDate}}
                            <label class="bold ml-2" v-if="toShow.endDate">End Date: </label>{{toShow.endDate}}
                            <label class="bold ml-2" v-if="toShow.day">Start Day: </label>{{getDay(toShow.day)}}
                        </v-flex>
                    </v-layout>
                    <v-layout row mb-2 v-if="toShow.guide">
                        <v-flex xs12>
                            <label class="bold ml-2">Guide: </label>{{toShow.guide}}
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="showBooking">
                        <v-flex xs12 px-2 v-if="showBooking">
                            <label class="bold">Group Size: </label>{{ toShow.persons }}
                            <span class="bold"
                                  v-if="toShow.type == 'group'">{{toShow.persons>15?'(Full)':'(Open)'}}</span>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="toShow.office">
                        <v-flex xs12 px-2>
                            <label class="bold">Booking Source : </label>{{ toShow.office.name }}
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="showBooking">
                        <v-flex xs12 px-2>
                            <v-layout row wrap>
                                <v-flex class="xs2" style="max-width: 131px; padding: 0px" slot="header">
                                    <label class="bold">Bookings</label>
                                </v-flex>
                                <v-flex class="xs12">
                                    <v-container py-0>
                                        <v-layout row wrap>
                                            <v-expansion-panel>
                                                <v-expansion-panel-content v-for="item, index in toShow.bookings"
                                                                           :key="index">
                                                    <v-flex class="xs8" style="padding: 0px"
                                                            slot="header"
                                                            v-bind:style="{backgroundColor: (item.canceled)?'rgb(255, 82, 82)':'white'}">
                                                        <label><span class="bold">{{index + 1}}</span> {{ '- ' +
                                                            item.name}}</label>
                                                        <label class="bold ml-2">Pax. N: </label>{{item.persons}}
                                                        <label class="bold ml-2">Lp: </label>{{item.lp}}
                                                    </v-flex>
                                                    <v-flex class="xs12">
                                                        <v-container py-0>
                                                            <v-layout row wrap mb-2>
                                                                <v-flex xs12>
                                                                    <label class="bold" v-if="item.sleepRequirement">Sleeping
                                                                        Requirement: </label>{{item.sleepRequirement}}
                                                                </v-flex>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.accommodations.length">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Accommodations: </label>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-for="accommodation,index2 in item.extentions"
                                                                                v-if="accommodation.before"
                                                                                :key="accommodation.dateIn+index2">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap my-2>
                                                                                    <v-flex xs1>
                                                                                        <v-chip v-bind="{color: accommodation.accommodation == null?'red':'green'}"
                                                                                                text-color="white"
                                                                                                style="height: 20px;">
                                                                                            Ext
                                                                                        </v-chip>
                                                                                    </v-flex>
                                                                                    <v-flex xs4>
                                                                                        {{getDestinationDays(accommodation)}}
                                                                                    </v-flex>
                                                                                    <v-flex xs3>
                                                                                        {{accommodation.destinationName}}
                                                                                    </v-flex>
                                                                                    <v-flex xs4>
                                                                                        <label class="bold"
                                                                                               v-bind:style="{backgroundColor: !accommodation.accommodation?'#ff5252':'white'}">Acc: </label>{{accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                            </v-container>
                                                                            <v-divider></v-divider>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-for="accommodation,index2 in item.accommodations"
                                                                                :key="accommodation.dateIn+index2">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap my-2>
                                                                                    <v-flex xs1>
                                                                                        <v-icon :color="accommodation.accommodation?'success':'error'"
                                                                                                style="font-size: 25px">
                                                                                            done
                                                                                        </v-icon>
                                                                                    </v-flex>
                                                                                    <v-flex xs4>
                                                                                        {{getDestinationDays(accommodation)}}
                                                                                    </v-flex>
                                                                                    <v-flex xs3>
                                                                                        {{accommodation.destinationName}}
                                                                                    </v-flex>
                                                                                    <v-flex xs4>
                                                                                        <label class="bold"
                                                                                               v-bind:style="{backgroundColor: accommodation.accommodation == ''?'#ff5252':'white'}">Acc: </label>{{accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                            </v-container>
                                                                            <v-divider></v-divider>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-for="accommodation,index2 in item.extentions"
                                                                                v-if="!accommodation.before"
                                                                                :key="accommodation.dateIn+index2">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap my-2>
                                                                                    <v-flex xs1>
                                                                                        <v-chip v-bind="{color: accommodation.accommodation == null?'red':'green'}"
                                                                                                text-color="white"
                                                                                                style="height: 20px;">
                                                                                            Ext
                                                                                        </v-chip>
                                                                                    </v-flex>
                                                                                    <v-flex xs4>
                                                                                        {{getDestinationDays(accommodation)}}
                                                                                    </v-flex>
                                                                                    <v-flex xs3>
                                                                                        {{accommodation.destinationName}}
                                                                                    </v-flex>
                                                                                    <v-flex xs4>
                                                                                        <label class="bold"
                                                                                               v-bind:style="{backgroundColor: accommodation.accommodation == null?'#ff5252':'white'}">Acc: </label>{{accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                </v-layout>

                                                                            </v-container>
                                                                            <v-divider></v-divider>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.transferIn">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                v-bind:style="{backgroundColor: item.transferIn.canceled?'#ff5252':'white'}"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Transfer In: </label>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-bind:style="{backgroundColor: item.transferIn.canceled?'#ff5252':'white'}">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap>
                                                                                    <v-flex xs12 px-2 my-2>
                                                                                        <v-tooltip top>
                                                                                            <v-icon slot="activator"
                                                                                                    v-bind:style="{color: item.transferIn.flyData?'#5ac35e':'#ff5252'}">
                                                                                                flight
                                                                                            </v-icon>
                                                                                            <span>Flight Data</span>
                                                                                        </v-tooltip>
                                                                                        <v-tooltip top>
                                                                                            <v-icon class=" ml-2"
                                                                                                    slot="activator"
                                                                                                    v-bind:style="{color: item.transferIn.accommodation && item.transferIn.accommodation.accommodation?'#5ac35e':'#ff5252'}">
                                                                                                home
                                                                                            </v-icon>
                                                                                            <span>Accommodation</span>
                                                                                        </v-tooltip>
                                                                                        <v-tooltip top>
                                                                                            <v-icon class="ml-2"
                                                                                                    slot="activator"
                                                                                                    :color="(toShow.sent)?'success':'error'">
                                                                                                email
                                                                                            </v-icon>
                                                                                            <span>Send to driver</span>
                                                                                        </v-tooltip>
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferIn.startDate">
                                                                                        <label class="bold">Date: </label>{{item.transferIn.startDate
                                                                                        + ' ' +
                                                                                        getTime(item.transferIn.startTime)}}
                                                                                    </v-flex>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferIn.flyData">
                                                                                        <label class="bold">Fly
                                                                                            Data: </label>{{item.transferIn.flyData}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferIn.accommodation && item.transferIn.accommodation.accommodation">
                                                                                        <label class="bold">Accommodation: </label>{{item.transferIn.accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferIn.drive">
                                                                                        <label class="bold">Drive: </label>{{item.transferIn.drive.name}}
                                                                                    </v-flex>

                                                                                </v-layout>
                                                                            </v-container>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.transferOut">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                v-bind:style="{backgroundColor: item.transferOut.canceled?'#ff5252':'white'}"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Transfer Out: </label>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-bind:style="{backgroundColor: item.transferOut.canceled?'#ff5252':'white'}">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap>
                                                                                    <v-flex xs12 px-2 my-2>
                                                                                        <v-tooltip top>
                                                                                            <v-icon slot="activator"
                                                                                                    v-bind:style="{color: item.transferOut.flyData?'#5ac35e':'#ff5252'}">
                                                                                                flight
                                                                                            </v-icon>
                                                                                            <span>Flight Data</span>
                                                                                        </v-tooltip>
                                                                                        <v-tooltip top>
                                                                                            <v-icon class=" ml-2"
                                                                                                    slot="activator"
                                                                                                    v-bind:style="{color: item.transferOut.accommodation && item.transferOut.accommodation.accommodation?'#5ac35e':'#ff5252'}">
                                                                                                home
                                                                                            </v-icon>
                                                                                            <span>Accommodation</span>
                                                                                        </v-tooltip>
                                                                                        <v-tooltip top>
                                                                                            <v-icon class="ml-2"
                                                                                                    slot="activator"
                                                                                                    :color="(toShow.sent)?'success':'error'">
                                                                                                email
                                                                                            </v-icon>
                                                                                            <span>Send to driver</span>
                                                                                        </v-tooltip>
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs5 v-if="item.transferOut">
                                                                                        <label class="bold">Date: </label>{{item.transferOut.startDate
                                                                                        + ' ' +
                                                                                        getTime(item.transferOut.startTime)}}
                                                                                    </v-flex>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferOut.flyData">
                                                                                        <label class="bold">Fly
                                                                                            Data: </label>{{item.transferOut.flyData}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferOut.accommodation && item.transferOut.accommodation.accommodation">
                                                                                        <label class="bold">Accommodation: </label>{{item.transferOut.accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferOut.drive">
                                                                                        <label class="bold">Drive: </label>{{item.transferOut.drive.name}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                            </v-container>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.otherExtentions.length">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                v-bind:style="{backgroundColor: item.transferOut.canceled?'#ff5252':'white'}"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Extentions: </label>
                                                                        </v-flex>
                                                                        <v-flex xs12 title>
                                                                            <v-data-table
                                                                                    :headers="[
                    {text: 'Date', value: 'name', align: 'left', sortable: false},
                    {text: 'Contact', value: 'pn', align: 'left', sortable: false},
                    {text: 'Type', value: 'lp', align: 'left', sortable: false},
                    {text: 'Tour/Description', value: '', sortable: false}
                                        ]"
                                                                                    :items="item.otherExtentions"
                                                                                    hide-actions
                                                                                    class="elevation-1"
                                                                            >
                                                                                <template slot="items"
                                                                                          slot-scope="props">
                                                                                    <tr>
                                                                                        <td class="text-md-left">
                                                                                            {{ props.item.dateIn }}
                                                                                        </td>
                                                                                        <td class="text-md-left">
                                                                                            {{item.name}}
                                                                                        </td>
                                                                                        <td class="text-md-left">
                                                                                            {{props.item.type}}
                                                                                        </td>
                                                                                        <td class="text-md-left py-1"
                                                                                            style="max-width: 550px">
                                                                                            {{props.item.type ==
                                                                                            'extra_tour'?props.item.tour.code:props.item.description}}
                                                                                        </td>
                                                                                    </tr>
                                                                                </template>
                                                                            </v-data-table>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                        </v-container>
                                                    </v-flex>
                                                </v-expansion-panel-content>
                                            </v-expansion-panel>
                                        </v-layout>
                                    </v-container>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="toShow.description!=''">
                        <v-flex xs12>
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex class="xs8" style="padding: 0px"
                                            slot="header">
                                        <label class="bold ml-2">Extra information</label>
                                    </v-flex>
                                    <v-flex class="xs12">
                                        <v-container py-0>
                                            <div v-html="toShow.description"></div>
                                        </v-container>
                                    </v-flex>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="toShow.attachments && toShow.attachments.length">
                        <v-flex xs12>
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex class="xs8" style="padding: 0px"
                                            slot="header">
                                        <label class="bold ml-2">Attachments</label>
                                    </v-flex>
                                    <v-flex class="xs12">
                                        <v-container py-0 v-for="item, index in toShow.attachments" :key="index">
                                            <label>{{item.name}} <a v-bind:href="downloadPath+item.id"
                                                                    style="text-decoration: none">
                                                <v-icon class="pointer">archive</v-icon>
                                            </a></label>
                                        </v-container>
                                    </v-flex>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions v-if="toShow.guide">
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" flat @click="sendToGuide(toShow.id, 'ls')">Send to Guide
                        <v-icon class="px-2">send</v-icon>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <v-dialog v-model="dialogShow2" max-width="650">
            <v-card>

                <v-card-title>
                    <span class="headline" v-if="toShow2.canceled"
                          v-bind:style="{backgroundColor: (!toShow.canceled)?toShow.color:'#ff5252'}">{{toShow2.code}}</span>
                    <span class="headline" v-else>{{toShow2.code}}</span>
                    <v-btn icon @click.native="dialogShow2 = false" class="btn-close">
                        <v-icon>close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text>
                    <v-layout row wrap mb-3>
                        <v-flex xs12 px-2>
                            <v-tooltip top v-if="toShow2.notifications"
                                       v-for="notification,index in toShow2.notifications" :key="index">
                                <v-icon v-if="index == 0" class="pointer" slot="activator"
                                        :color="(notification.done)?'success':'error'"
                                        @click="changeNotificationState(notification)">{{notification.icon}}
                                </v-icon>
                                <v-icon v-else class="pointer ml-2" slot="activator"
                                        :color="(notification.done)?'success':'error'"
                                        @click="changeNotificationState(notification)">{{notification.icon}}
                                </v-icon>
                                <span>{{notification.name}}</span>
                            </v-tooltip>
                            <span class="right">
                            <v-tooltip top>
                            <v-icon color="success" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="$router.push({ name: 'HTCTourUpdate', params: {id: toShow2.id} })">edit</v-icon>
                            <span>Edit tour</span>
                            </v-tooltip>
                            <v-tooltip top>
                            <v-icon color="primary" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="relocateTour(toShow2)">refresh</v-icon>
                            <span>Relocate tour</span>
                            </v-tooltip>
                            <v-tooltip top>
                            <v-icon color="warning" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="cancelTour(toShow2)">cancel</v-icon>
                            <span>Cancel tour guide</span>
                            </v-tooltip>
                            <v-tooltip top>
                            <v-icon color="error" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                    @click="deleteItem(toShow2)">delete</v-icon>
                            <span>Delete tour</span>
                            </v-tooltip>
                            </span>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2>
                        <v-flex xs12 px-2>
                            <label class="bold">Name: </label>{{toShow2.name}}
                            <label class="bold ml-2">Type: </label>
                            {{(toShow2.type == 'tailor_made'?'tailor made':toShow2.type)}}
                            <span v-if="toShow2.agent"><label
                                    class="bold ml-2">Agent: </label>{{toShow2.agent}}</span>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2>
                        <v-flex xs12>
                            <label class="bold ml-2">Date: </label>{{toShow2.startDate}}
                        </v-flex>
                        <v-flex xs12 px-2>
                            <label class="bold">Start Place: </label>{{toShow2.startPlace + ' ' +
                            getTime(toShow2.startTime)}}
                        </v-flex>
                        <v-flex xs12>
                            <label class="bold ml-2">End Place: </label>{{toShow2.finishPlace + ' ' +
                            getTime(toShow2.endTime)}}
                        </v-flex>
                    </v-layout>
                    <v-layout row mb-2 v-if="toShow2.guide">
                        <v-flex xs12>
                            <label class="bold ml-2">Guide: </label>{{toShow2.guide}}
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="showBooking">
                        <v-flex xs12 px-2>
                            <label class="bold">Group Size: </label>{{ toShow2.persons }}
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="toShow2.office">
                        <v-flex xs12 px-2>
                            <label class="bold">Booking Source : </label>{{ toShow2.office.name }}
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="showBooking">
                        <v-flex xs12 px-2>
                            <v-layout row wrap>
                                <v-flex class="xs2" style="max-width: 131px; padding: 0px" slot="header">
                                    <label class="bold">Bookings</label>
                                </v-flex>
                                <v-flex class="xs12">
                                    <v-container py-0>
                                        <v-layout row wrap>
                                            <v-expansion-panel>
                                                <v-expansion-panel-content v-for="item, index in toShow2.bookings"
                                                                           :key="index">
                                                    <v-flex class="xs8" style="padding: 0px"
                                                            slot="header"
                                                            v-bind:style="{backgroundColor: (item.canceled)?'rgb(255, 82, 82)':'white'}">
                                                        <label><span class="bold">{{index + 1}}</span> {{ '- ' +
                                                            item.name}}</label>
                                                        <label class="bold ml-2">Pax. N: </label>{{item.persons}}
                                                        <label class="bold ml-2">Lp: </label>{{item.lp}}
                                                    </v-flex>
                                                    <v-flex class="xs12">
                                                        <v-container py-0>
                                                            <v-layout row wrap mb-2>
                                                                <v-flex xs12>
                                                                    <label class="bold" v-if="item.sleepRequirement">Sleeping
                                                                        Requirement: </label>{{item.sleepRequirement}}
                                                                </v-flex>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.accommodations.length">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Accommodations: </label>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-for="accommodation,index2 in item.accommodations"
                                                                                :key="index2">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap my-2>
                                                                                    <v-flex xs6>
                                                                                        <label class="bold">Location: </label>{{accommodation.destinationName}}
                                                                                    </v-flex>
                                                                                    <v-flex xs6>
                                                                                        <label class="bold">Accommodation: </label>{{accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>

                                                                                    <v-flex xs6>
                                                                                        <label class="bold">Days: </label>{{accommodation.days}}
                                                                                    </v-flex>
                                                                                    <v-flex xs6>
                                                                                        <label class="bold">Date
                                                                                            In: </label>{{accommodation.dateIn}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                            </v-container>
                                                                            <v-divider
                                                                                    v-if="item.accommodations.length > (index2 + 1)"></v-divider>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.transferIn">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Transfer In: </label>
                                                                        </v-flex>
                                                                        <v-flex class="xs12">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferIn.flyData">
                                                                                        <label class="bold">Fly
                                                                                            Data: </label>{{item.transferIn.flyData}}
                                                                                    </v-flex>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferIn.startDate">
                                                                                        <label class="bold">Date: </label>{{item.transferIn.startDate
                                                                                        + ' ' +
                                                                                        getTime(item.transferIn.startTime)}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferIn.drive">
                                                                                        <label class="bold">Drive: </label>{{item.transferIn.drive.name}}
                                                                                    </v-flex>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferIn.accommodation">
                                                                                        <label class="bold">Accommodation: </label>{{item.transferIn.accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                            </v-container>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                            <v-layout row wrap mb-2 v-if="item.transferOut">
                                                                <v-expansion-panel>
                                                                    <v-expansion-panel-content>
                                                                        <v-flex class="xs2"
                                                                                style="max-width: 131px; padding: 0px"
                                                                                slot="header">
                                                                            <label class="bold">Transfer Out: </label>
                                                                        </v-flex>
                                                                        <v-flex class="xs12">
                                                                            <v-container py-0>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferOut.flyData">
                                                                                        <label class="bold">Fly
                                                                                            Data: </label>{{item.transferOut.flyData}}
                                                                                    </v-flex>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferOut.startDate">
                                                                                        <label class="bold">Date: </label>{{item.transferOut.startDate
                                                                                        + ' ' +
                                                                                        getTime(item.transferOut.startTime)}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                                <v-layout row wrap mb-2>
                                                                                    <v-flex xs6
                                                                                            v-if="item.transferOut.drive">
                                                                                        <label class="bold">Drive: </label>{{item.transferOut.drive.name}}
                                                                                    </v-flex>
                                                                                    <v-flex xs5
                                                                                            v-if="item.transferOut.accommodation">
                                                                                        <label class="bold">Accommodation: </label>{{item.transferOut.accommodation.accommodation}}
                                                                                    </v-flex>
                                                                                </v-layout>
                                                                            </v-container>
                                                                        </v-flex>
                                                                    </v-expansion-panel-content>
                                                                </v-expansion-panel>
                                                            </v-layout>
                                                        </v-container>
                                                    </v-flex>
                                                </v-expansion-panel-content>
                                            </v-expansion-panel>
                                        </v-layout>
                                    </v-container>
                                </v-flex>
                            </v-layout>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="toShow2.description">
                        <v-flex xs12>
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex class="xs8" style="padding: 0px"
                                            slot="header">
                                        <label class="bold ml-2">Extra information</label>
                                    </v-flex>
                                    <v-flex class="xs12">
                                        <v-container py-0>
                                            <div v-html="toShow2.description"></div>
                                        </v-container>
                                    </v-flex>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-flex>
                    </v-layout>
                    <v-layout row wrap mb-2 v-if="toShow2.attachments && toShow2.attachments.length">
                        <v-flex xs12>
                            <v-expansion-panel>
                                <v-expansion-panel-content>
                                    <v-flex class="xs8" style="padding: 0px"
                                            slot="header">
                                        <label class="bold ml-2">Attachments</label>
                                    </v-flex>
                                    <v-flex class="xs12">
                                        <v-container py-0 v-for="item, index in toShow2.attachments" :key="index">
                                            <label>{{item.name}} <a v-bind:href="downloadPath+item.id"
                                                                    style="text-decoration: none">
                                                <v-icon class="pointer">archive</v-icon>
                                            </a></label>
                                        </v-container>
                                    </v-flex>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-flex>
                    </v-layout>
                </v-card-text>
                <v-card-actions v-if="toShow2.guide">
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" flat @click="sendToGuide(toShow2.id, 'htc')">Send to Guide
                        <v-icon class="px-2">send</v-icon>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
<script>
    import {mapGetters} from 'vuex';
    import moment from 'moment';
    import time from "../../utils/time";

    import $ from "jquery";

    import {API_HOST, API_PATH} from '../../config/_entrypoint';

    export default {
        data() {
            return {
                month: false,
                date: false,
                calendarEnd: false,
                calendarRows: 0,
                calendarCols: 0,
                date: false,
                pickerDate: false,
                filterDate: "",
                dialogShow: false,
                toShow: {},
                dialogShow2: false,
                toShow2: {},
                showBooking: false,
                days: [
                    {},
                    {text: 'Sunday', value: 1},
                    {text: 'Monday', value: 2},
                    {text: 'Tuesday', value: 3},
                    {text: 'Wednesday', value: 4},
                    {text: 'Thursday', value: 5},
                    {text: 'Friday', value: 6},
                    {text: 'Saturday', value: 7}
                    ,

                ],
                toursG: [],
                toursNG: [],
                tour: false,
                locate: false,
                avaibleGuides: [],
                guideSelected: false,
                menu: false,
                valid: false,
                availableGuide: true,
                maxHeight: '0px',
                loadingGeneric: false,
                fieldRule: [
                    v => !!v || 'This field is required'
                ],
                snackbarColor: 'success',
                snackbarText: 'The email was sent',
                snackbar: false
        }
        },
        computed: {
            ...mapGetters({
                loading: 'guide/list/loading',
                tours: 'lstour/list/forShedule',
                guides: 'guide/list/items',
                retrieved: 'lstour/update/retrieved',
                retrieved2: 'htctour/update/retrieved',
                loadingTour: 'lstour/list/loading',
            })
        },
        methods: {
            relocateTour(val) {
                let $this = this, link = API_HOST + API_PATH + '/remove-guide', tourTemp = false;
                this.loadingGeneric = true;
                return fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                    body: JSON.stringify({id: val.id, type: val.ls})
                })
                    .then(response => response.json())
                    .then(response => {
                        $this.loadingGeneric = false;
                        $this.dialogShow = false;
                        $this.dialogShow2 = false;
                        this.toursG = [];
                        this.toursNG = [];
                        $this.$store.dispatch('lstour/list/getForShedule').then(() => {
                            this.tours.forEach(function (item) {
                                if (item.guide)
                                    $this.toursG.push(item);
                                else {
                                    $this.toursNG.push(item);
                                    if (item.code == val.code)
                                        tourTemp = item;
                                }

                            });
                            this.date.subtract(this.calendarCols, 'day');
                            this.init();
                            this.tour = tourTemp;
                            $('#btn').click();
                            // this.menu = true;
                        })
                    }).catch(e => {
                        this.error(time() + ' ' + e.message)
                    });
            },
            assingGuide() {
                if (!this.$refs.assingForm.validate()) return;
                this.loadingGeneric = true;
                let link = API_HOST + API_PATH + '/assing-guide';
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                    body: JSON.stringify({tour: this.tour.id, guide: this.guideSelected.id, ls: this.tour.ls})
                })
                    .then(response => {
                        this.loadingGeneric = false;
                        this.toursG = [];
                        this.toursNG = [];
                        let $this = this;
                        this.$store.dispatch('lstour/list/getForShedule').then(() => {
                            this.tours.forEach(function (item) {
                                if (item.guide)
                                    $this.toursG.push(item);
                                else
                                    $this.toursNG.push(item);
                            });
                            this.date = moment(this.tour.date);
                            let rest = parseInt(this.calendarCols / 2) - parseInt(this.tour.days / 2) - 2;
                            this.date.subtract(rest, 'days');
                            this.init();
                        });
                        this.menu = false;
                    }).catch(e => {
                    this.error(time() + ' ' + e.message)
                });
            },
            cancelAssing() {
                this.date = moment();
                this.init();
                this.availableGuide = true;
                this.menu = false;
            },
            filterPerDate() {
                this.date = moment(this.filterDate);
                this.updateCalendar();
            },
            next() {
                this.updateCalendar();
            },
            prev() {
                this.date.subtract(this.calendarCols * 2, 'day');
                this.updateCalendar();
            },
            colummGuides() {
                if (this.maxHeight == '0px')
                    this.maxHeight = ($(window).height() - $('#guides-container').offset().top - 100) + 'px';
                let $container = $("#guides-container"), $firstRow = $('#first-row-guides'),
                    gridWidth = 150,
                    gridHeight = 25,
                    y, x;
                $container.html("");
                $firstRow.html("");
                for (let i = 0; i < this.guides.length + 1; i++) {
                    y = (i) ? (i * gridHeight) - gridHeight : 0;
                    x = (i * gridWidth) % gridWidth;
                    let div = $("<div/>").css({
                        position: "absolute",
                        border: "1px solid grey",
                        width: gridWidth - 1,
                        height: gridHeight - 1,
                        top: y,
                        left: x,
                        boxSizing: 'content-box'
                    });
                    if (i == 0) {
                        $('<label/>').html('Guides').css({
                            paddingTop: '3px',
                            fontWeight: 'bold',
                            color: 'grey'
                        }).prependTo(div);
                        div.css({textAlign: 'center', border: '0px', borderLeft: '1px solid grey', paddingTop: '3px'});
                        div.prependTo($firstRow);
                    }
                    else {
                        $('<div/>').html(this.guides[i - 1].name).css({
                            textAlign: 'center',
                            paddingTop: '3px',
                            fontWeight: 'bold',
                            width: '150px'
                        }).prependTo(div);
                        if (this.locate) {
                            if (this.avaibleGuides.length && this.avaibleGuides.filter(item => item.id == this.guides[i - 1].id).length)
                                div.css({backgroundColor: '#4caf50'});
                            else
                                div.css({backgroundColor: '#ff5252'});
                        }
                        div.prependTo($container);
                    }

                }
            },
            updateCalendar() {
                let $container = $("#container"), $firstRow = $('#first-row-dates'),
                    gridWidth = 30,
                    gridHeight = 25,
                    i, x, y, guides = 1, month = false, month2 = false, tourOnDate = [], colDates = [], ids = [],
                    addDays;
                this.calendarCols = parseInt($container.width() / gridWidth);
                $container.html("");
                $firstRow.html("");
                for (i = 0; i < (this.calendarRows + 1) * this.calendarCols; i++) {
                    y = (i > this.calendarCols) ? (Math.floor(i / this.calendarCols) * gridHeight) - gridHeight : 0;
                    x = (i * gridWidth) % (this.calendarCols * gridWidth);
                    let div = $("<div/>").css({
                        position: "absolute",
                        border: "1px solid grey",
                        width: gridWidth - 1,
                        height: gridHeight - 1,
                        top: y,
                        left: x,
                        boxSizing: 'content-box'
                    });
                    if (i < this.calendarCols) {
                        let aux = this.toursG.filter(item => {
                            return this.date.format('DD-MM-YYYY') == moment(item.date).format('DD-MM-YYYY');
                        });
                        if (i == 0) {
                            let $this = this;
                            this.toursG.forEach(function (item) {
                                addDays = (item.days > 1) ? item.days : 0
                                if (moment(item.date) < $this.date && moment(item.date).add(addDays, 'days') >= $this.date) {
                                    let newItem = Object.assign({}, item);
                                    newItem.days = (item.days - moment($this.date).diff(moment(item.date), 'days'));
                                    newItem.date = $this.date.format('YYYY-MM-DD');
                                    newItem.inPart = true;
                                    aux.push(newItem)
                                }
                            })
                        }
                        tourOnDate.push(aux);
                        let label = $('<label/>');
                        $('<div/>').css({
                            fontSize: '12px',
                            fontWeight: 'bold',
                            color: 'grey'
                        }).prependTo(label);
                        $('<div/>').html(this.date.format('DD')).prependTo(label);
                        $(label).css({paddingTop: '0px', fontWeight: 'bold', color: 'grey'}).prependTo(div);
                        if (month != this.date.format('MMMM')) {
                            month = this.date.format('MMMM')
                            month2 = (month2) ? month2 + '-' + this.date.format('MMMM') : month;
                        }
                        colDates.push(this.date.format('YYYY-MM-DD'));
                        this.date.add(1, 'day');
                        div.css({textAlign: 'center', border: '0px', borderLeft: '1px solid grey'});
                        if (i == this.calendarCols - 1)
                            div.css({borderRight: '1px solid grey'});
                        div.prependTo($firstRow);
                    }
                    else {
                        if ((i - guides * this.calendarCols) == 0) {
                            guides++;
                        }
                        if (tourOnDate[i % this.calendarCols].length) {
                            let $this = this;
                            tourOnDate[(this.calendarCols + i) % this.calendarCols].forEach(function (item) {
                                if (item.guide == $this.guides[guides - 2].id && item.date == colDates[i % $this.calendarCols] && !ids.includes(item.code)) {
                                    ids.push(item.code);
                                    let divTour = $('<div/>'), col = i % $this.calendarCols,
                                        restCol = $this.calendarCols - col;
                                    divTour.addClass('box tour elevation-5 pointer').attr('data-id', item.id).attr('data-type', item.ls).css({
                                        width: item.days == 1 && typeof item.inPart == typeof undefined ? item.days * gridWidth : (item.days + 1 > restCol) ? restCol * gridWidth : (item.days + 1) * gridWidth,
                                        height: '21px',
                                        marginTop: '2px',
                                        left: col * gridWidth,
                                        backgroundColor: item.color,
                                        top: (guides - 2) * 25 + 'px',
                                        lineHeight: gridHeight + "px",
                                        zIndex: 1
                                    });
                                    let code = (!item.ls) ? item.templateCode : (item.days <= 2 || restCol < 4) ? "" : item.code;
                                    divTour.html('<div class="bold" style="font-size:12px;margin-top:-1px">' + code + '</div>');
                                    $(divTour).prependTo($container);
                                }
                            })
                        }
                        if (this.locate) {
                            addDays = (this.tour.days > 1) ? this.tour.days : 0
                            let startDate = moment(this.tour.date),
                                endDate = moment(this.tour.date).add(addDays, 'days');
                            if (startDate <= moment(colDates[i % this.calendarCols]) && endDate >= moment(colDates[i % this.calendarCols])) {
                                if (this.avaibleGuides.length && this.avaibleGuides.filter(item => item.id == this.guides[guides - 2].id).length)
                                    div.css({backgroundColor: '#4caf50'});
                                else
                                    div.css({backgroundColor: '#ff5252'});
                            }
                        }
                        div.prependTo($container);
                    }

                }

                this.month = month2;
            },
            getDay(val) {
                if (typeof val != typeof undefined && val)
                    return this.days[val].text;
            },
            cancelTour(val) {
                let $this = this, link = API_HOST + API_PATH + '/remove-guide';
                this.loadingGeneric = true;
                return fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                    body: JSON.stringify({id: val.id, type: val.ls})
                })
                    .then(response => response.json())
                    .then(response => {
                        $this.loadingGeneric = false;
                        $this.dialogShow = false;
                        $this.dialogShow2 = false;
                        this.toursG = [];
                        this.toursNG = [];
                        $this.$store.dispatch('lstour/list/getForShedule').then(() => {
                            this.tours.forEach(function (item) {
                                if (item.guide)
                                    $this.toursG.push(item);
                                else
                                    $this.toursNG.push(item);
                            });
                            this.date.subtract(this.calendarCols, 'day');
                            this.init();
                        })
                    }).catch(e => {
                        this.error(time() + ' ' + e.message)
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
                        this.changedTour = true;
                    }).catch(e => {
                    this.error(time() + ' ' + e.message)
                });
            },
            getTime(val) {
                return moment(val, 'H:mm').format('hh:mm a')
            },
            sendToGuide(id, type) {
                let link = API_HOST + API_PATH + '/send-to-guide';
                this.loadingGeneric = true;
                fetch(link, {
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({id: id, type: type})
                })
                    .then(response => response.json())
                    .then(response => {
                        this.loadingGeneric = false;
                        if(response){
                            this.snackbarColor = 'success';
                            this.snackbarText = 'The email was sent';
                            this.snackbar = true;
                            this.dialogShow = false;
                            this.dialogShow2 = false;
                        }
                        else{
                            this.snackbarColor = 'error';
                            this.snackbarText = 'Error sending email. PLease try later';
                            this.snackbar = true;
                        }
                    }).catch(e => {
                    this.error(time() + ' ' + e.message)
                });
            },
            getDestinationDays(item) {
                let accomodation = {};
                Object.assign(accomodation, item);
                let days = 0, days_aux = 0, date_aux = accomodation.dateIn, month = moment(date_aux).format('MMMM');
                accomodation.dateIn = moment(date_aux).format('YYYY-MM-DD');
                for (let i = 0; accomodation.days > i; i++) {
                    days = days + parseInt(moment(date_aux).add(days_aux++, 'days').format('D'));
                    if (i + 1 != accomodation.days) {
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
            setAccommodations() {
                if (this.toShow.destinations != typeof undefined && this.toShow.destinations.length && typeof this.toShow.bookings != typeof undefined && this.toShow.bookings.length) {
                    for (let i = 0; i < this.toShow.bookings.length; i++) {
                        let accommodations = [], booking = this.toShow.bookings[i];
                        for (let j = 0, index = 0; j < this.toShow.destinations.length; j++) {
                            if (typeof booking.accommodations != typeof undefined && typeof booking.accommodations[index] != typeof undefined && booking.accommodations[index].destination == this.toShow.destinations[j].destination && booking.accommodations[index].order == j + 1) {
                                accommodations.push(booking.accommodations[index++])
                            }
                            else {
                                let object = {
                                    destinationName: this.toShow.destinations[j].name,
                                    accommodation: "",
                                    days: this.toShow.destinations[j].days
                                };
                                if (accommodations.length == 0) {
                                    object.dateIn = this.toShow.startDate;
                                }
                                else {
                                    object.dateIn = moment(accommodations[accommodations.length - 1].dateIn).add(accommodations[accommodations.length - 1].days, 'days').format('YYYY-MM-DD');
                                }
                                accommodations.push(object)
                            }
                        }
                        booking.accommodations = accommodations;
                    }
                }
            },
            showItem(id) {
                this.loadingGeneric = true;
                this.$store.dispatch('lstour/update/retrieve', id).then(() => {
                    this.loadingGeneric = false;
                    this.showBooking = this.retrieved.bookings.length;
                    Object.assign(this.toShow, this.retrieved);
                    this.setAccommodations();
                    this.toShow.ls = true;
                    this.dialogShow = true;

                }).catch(e => {
                    this.error(time() + ' ' + e.message)
                });
            },
            showItem2(id) {
                this.loadingGeneric = true;
                this.$store.dispatch('htctour/update/retrieve', id).then(() => {
                    this.loadingGeneric = false;
                    this.showBooking = this.retrieved2.bookings.length;
                    Object.assign(this.toShow2, this.retrieved2);
                    this.toShow2.ls = false;
                    this.dialogShow2 = true;
                }).catch(e => {
                    this.error(time() + ' ' + e.message)
                });
            },
            init() {
                this.$refs.assingForm.reset();
                this.locate = false;
                this.avaibleGuides = [];
                this.guideSelected = {};
                this.menu = false;
                this.colummGuides();
                this.updateCalendar();
            },
            error(message) {
                this.flag = true;
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
                this.loadingGeneric = false;
            },
            deleteItem(item, type) {
                if (confirm('Are you sure you want to delete?')) {
                    let link = type == 'ls' ? 'lstour/del/deleteItem' : 'htctour/del/deleteItem';
                    this.loadingGeneric = true;
                    this.$store.dispatch(link, item.id).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.dialogShow = false;
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            this.loadingGeneric = false;
                            this.dialogShow = false;
                            this.dialogShow2 = false;
                            this.toursG = [];
                            this.toursNG = [];
                            this.$store.dispatch('lstour/list/getForShedule').then(() => {
                                let $this = this;
                                this.tours.forEach(function (item) {
                                    if (item.guide)
                                        $this.toursG.push(item);
                                    else
                                        $this.toursNG.push(item);
                                });
                                this.date.subtract(this.calendarCols, 'day');
                                this.init();
                                this.loadingGeneric = false;
                            })
                        }).catch(e => {
                        this.error(time() + ' ' + e.message)
                    });
                }
            }
        },
        watch: {
            tour(val) {
                if (!val) return;
                this.avaibleGuides = [];
                this.locate = true;
                this.date = moment(this.tour.date);
                let rest = parseInt(this.calendarCols / 2) - parseInt(this.tour.days / 2) - 2;
                this.date.subtract(rest, 'days');
                let addDays = (this.tour.days > 1) ? this.tour.days : 0;
                let startDate = moment(this.tour.date),
                    endDate = (this.tour.days > 1) ? moment(this.tour.date).add(addDays, 'days') : moment(this.tour.date),
                    guides = [], $this = this;

                this.toursG.forEach(function (item) {
                    let addDays = (item.days > 1) ? item.days : 0;
                    let w = moment(item.date).add(addDays, 'days');
                    if ((((startDate >= moment(item.date) && startDate <= w) || (endDate >= moment(item.date) && endDate <= w)) || (startDate <= moment(item.date) && endDate >= w)) && !guides.includes(item.guide)) {
                        guides.push(item.guide);
                    }
                });
                this.guides.forEach(function (item) {
                    if (guides.filter(item2 => item2 == item.id).length == 0)
                        $this.avaibleGuides.push(item);
                });
                this.availableGuide = this.avaibleGuides.length;
                this.colummGuides();
                this.updateCalendar();
            }
        },
        created() {
            this.$store.dispatch('lstour/list/getForShedule').then(() => {
                this.$store.dispatch('guide/list/getItems').then(() => {
                    this.date = moment();
                    this.calendarRows = this.guides.length;
                    let $this = this;
                    this.tours.forEach(function (item) {
                        if (item.guide)
                            $this.toursG.push(item);
                        else
                            $this.toursNG.push(item);
                    });
                    this.colummGuides();
                    this.updateCalendar();
                });
            })
            let $this = this;
            $('body').on('click', '.tour', function () {
                if ($(this).data('type'))
                    $this.showItem($(this).data('id'));
                else
                    $this.showItem2($(this).data('id'));
            })
        }
    }
</script>
