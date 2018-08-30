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
                         v-show="loading || loadingDeleting || retrieveLoading || changeNotificationLoading || ajaxControl">
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
                       @click="$router.push({ name: 'LSTourCreate' })"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <span>New Tour</span>
            </v-tooltip>
            <v-card flat tile>
                <v-toolbar>
                    <v-layout wrap row>
                        <v-flex md6 px-3>
                            <v-menu v-model="menuSearch" bottom right offset-x :open-on-click="false">
                                <v-text-field
                                        slot="activator"
                                        label="Code or Contact"
                                        append-icon="search"
                                        v-model="search.text"
                                        v-on:keyup="searchCodeContact"
                                ></v-text-field>
                                <v-list>
                                    <v-list-tile v-for="(item, index) in search.codeContactResult" :key="index"
                                                 @click="showItem(item);  search.text = ''">
                                        <v-list-tile-title>{{item.code}} {{ item.name!=null?' ('+item.name+')':'' }}</v-list-tile-title>
                                    </v-list-tile>
                                </v-list>
                            </v-menu>
                            <v-menu
                                    :min-width="800"
                                    v-model="menuAdvanceSearch"
                                    :close-on-content-click="false"
                                    bottom
                                    offset-y
                            >
                                <v-icon slot="activator" v-bind:color="active_filter?'red':'teal'" class="pointer">
                                    filter_list
                                </v-icon>
                                <v-card>
                                    <v-btn icon @click.native="menuAdvanceSearch = false" class="btn-close right">
                                        <v-icon>close</v-icon>
                                    </v-btn>
                                    <v-container>
                                        <v-layout row wrap>
                                            <v-flex>
                                                <v-form ref="advanceSearchForm" lazy-validation class="mt-1 pl-3">
                                                    <v-layout row wrap mt-3>
                                                        <v-flex xs4>
                                                            <v-menu
                                                                    ref="pickerAdvanceSearchStart"
                                                                    lazy
                                                                    :close-on-content-click="false"
                                                                    v-model="pickerAdvanceSearchStart"
                                                                    transition="scale-transition"
                                                                    offset-y
                                                                    full-width
                                                                    :nudge-right="40"
                                                                    :return-value.sync="search.dateStart"
                                                            >
                                                                <v-text-field
                                                                        slot="activator"
                                                                        label="Start Date"
                                                                        v-model="search.dateStart"
                                                                        prepend-icon="event"
                                                                        readonly
                                                                ></v-text-field>
                                                                <v-date-picker v-model="search.dateStart" no-title
                                                                               scrollable
                                                                               @input="$refs.pickerAdvanceSearchStart.save(search.dateStart)"></v-date-picker>
                                                            </v-menu>
                                                        </v-flex>
                                                        <v-flex xs4>
                                                            <v-menu
                                                                    ref="pickerAdvanceSearchEnd"
                                                                    lazy
                                                                    :close-on-content-click="false"
                                                                    v-model="pickerAdvanceSearchEnd"
                                                                    transition="scale-transition"
                                                                    offset-y
                                                                    full-width
                                                                    :nudge-right="40"
                                                                    :return-value.sync="search.dateEnd"
                                                            >
                                                                <v-text-field
                                                                        slot="activator"
                                                                        label="End Date"
                                                                        v-model="search.dateEnd"
                                                                        prepend-icon="event"
                                                                        readonly
                                                                ></v-text-field>
                                                                <v-date-picker v-model="search.dateEnd" no-title
                                                                               scrollable :min="search.dateStart"
                                                                               @input="$refs.pickerAdvanceSearchEnd.save(search.dateEnd)"></v-date-picker>
                                                            </v-menu>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row wrap mt-3>
                                                        <v-flex xs6>
                                                            <v-select
                                                                    :items="lsNotifications"
                                                                    v-model="search.notifications"
                                                                    label="Select notifications"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    multiple
                                                                    chips
                                                                    max-height="auto"
                                                                    autocomplete
                                                                    color="blue"
                                                            >
                                                                <template slot="selection" slot-scope="data">
                                                                    <v-chip
                                                                            :selected="data.selected"
                                                                            :key="JSON.stringify(data.item)"
                                                                            close
                                                                            class="chip--select-multi"
                                                                            @input="data.parent.selectItem(data.item)"
                                                                    >
                                                                        <v-icon v-bind:color="search.notificationStatus?'green':'red'">
                                                                            {{ data.item.icon }}
                                                                        </v-icon>
                                                                        {{ data.item.name }}

                                                                    </v-chip>
                                                                </template>
                                                                <template slot="item" slot-scope="data">
                                                                    <template>
                                                                        <v-icon>
                                                                            {{ data.item.icon }}
                                                                        </v-icon>
                                                                        {{data.item.name}}
                                                                    </template>
                                                                </template>
                                                            </v-select>
                                                        </v-flex>
                                                        <v-flex xs6>
                                                            <v-radio-group v-model="search.notificationStatus" row
                                                                           :mandatory="false">
                                                                <v-radio
                                                                        label="Incomplete"
                                                                        :value="false"
                                                                ></v-radio>
                                                                <v-radio
                                                                        label="Completed"
                                                                        :value="true"
                                                                ></v-radio>
                                                            </v-radio-group>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row wrap mt-3>
                                                        <v-flex xs6>
                                                            <v-select
                                                                    :items="destinations"
                                                                    v-model="search.destination"
                                                                    label="Destinations"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    multiple
                                                                    chips
                                                                    max-height="auto"
                                                                    autocomplete
                                                                    color="blue"
                                                            >
                                                                <template slot="selection" slot-scope="data">
                                                                    <v-chip
                                                                            :selected="data.selected"
                                                                            :key="JSON.stringify(data.item)"
                                                                            close
                                                                            class="chip--select-multi"
                                                                            @input="data.parent.selectItem(data.item)"
                                                                    >
                                                                        <v-icon v-bind:color="search.destinationAccommodation?'green':'red'">
                                                                            done
                                                                        </v-icon>
                                                                        {{ data.item.name }}

                                                                    </v-chip>
                                                                </template>
                                                                <template slot="item" slot-scope="data">
                                                                    <template>
                                                                        <v-icon>
                                                                            done
                                                                        </v-icon>
                                                                        {{data.item.name}}
                                                                    </template>
                                                                </template>
                                                            </v-select>
                                                        </v-flex>
                                                        <v-flex xs6>
                                                            <v-checkbox class="mt-3"
                                                                        label="Accommodation Assigned"
                                                                        v-model="search.destinationAccommodation"
                                                            ></v-checkbox>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row wrap mt-3>
                                                        <v-flex xs6>
                                                            <v-select
                                                                    label="Select Template"
                                                                    :items="templates"
                                                                    v-model="search.template"
                                                                    autocomplete
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    clearable
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
                                                    </v-layout>
                                                </v-form>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn flat @click="menuAdvanceSearch = false;$refs.advanceSearchForm.reset();">
                                            Cancel
                                        </v-btn>
                                        <v-btn color="primary" flat @click="advanceSearch">Search</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-menu>
                            <v-icon slot="activator" color="teal" class="pointer" @click="refresh">refresh</v-icon>
                            <v-container class="px-0 ml-3" style="display: inline" v-if="finishbDate != null || startbDate != null">{{finishbDate == null?'From - ':''}}{{startbDate == null?'Until':startbDate}}{{finishbDate == null?'':' - '}}{{finishbDate == null?'':finishbDate}}</v-container>
                        </v-flex>
                        <v-flex md6>
                            <div class="right pa-4">
                                <v-tooltip top>
                                    <v-icon class="pointer" slot="activator" @click="exportExcel">cloud_download
                                    </v-icon>
                                    <span>Download Excel</span>
                                </v-tooltip>
                            </div>
                            <div class="right pa-4">
                                <v-menu open-on-hover bottom offset-y v-for="(item,index) in notifications"
                                        :key="index">
                                    <v-badge slot="activator" color="red" overlap>
                                        <span slot="badge">{{item.cant}}</span>
                                        <v-icon color="grey" class="px-2">{{item.icon}}</v-icon>
                                    </v-badge>
                                    <v-list>
                                        <v-list-tile v-for="item2, index2 in item.tours" :key="index2"
                                                     @click="showItem(item2)">
                                            <v-list-tile-title>{{item2.code}} {{ item2.name!=null?' ('+item2.name+')':'' }}</v-list-tile-title>
                                        </v-list-tile>
                                    </v-list>
                                </v-menu>
                            </div>
                        </v-flex>
                    </v-layout>
                </v-toolbar>
                <v-alert type="info" :value="true" v-if="items.length == 0" class="mt-2" style="width: 100%">
                    There are no iems to show.
                </v-alert>
                <v-dialog max-width="400px">

                    <v-card>
                        <v-card-title>
                            <span class="headline">Title</span>
                        </v-card-title>
                    </v-card>
                </v-dialog>
                <v-container v-show="items.length != 0">
                    <v-layout row wrap my-1 v-for="lista, key, index in items" :key="index">
                        <v-flex xs1 justify-center>
                            <v-container px-0>
                                <label class="bold" style="color: rgba(0,0,0,.54)">{{key}}</label>
                            </v-container>
                        </v-flex>
                        <v-flex xs11 justify-center>
                            <v-container class="px-0">
                                <v-chip v-bind:style="{backgroundColor: (!item.canceled)?item.color:'#ff5252'}"
                                        text-color="black"
                                        v-for="item, index2 in lista" :key="index2" @click="showItem(item)">
                                    {{item.code}}
                                    <v-chip color="white" class="ml-2" style="height: 90%"
                                            v-if="item.notifications.length">
                                        <v-tooltip top v-for="notification,index in item.notifications" :key="index">
                                            <v-icon class="pointer ml-2" slot="activator"
                                                    :color="(notification.done)?'success':'error'">{{notification.icon}}
                                            </v-icon>
                                            <span>{{notification.name}}</span>
                                        </v-tooltip>
                                    </v-chip>
                                </v-chip>
                            </v-container>
                        </v-flex>
                        <v-flex md12 v-if="rows > (index + 1)">
                            <v-divider></v-divider>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-card>
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
                         <v-icon color="warning" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                 @click="cancelTour(toShow)">cancel</v-icon>
                         <span>{{(toShow.canceled)?'Activate tour': 'Cancel tour'}}</span>
                        </v-tooltip>
                        <v-tooltip top>
                         <v-icon color="error" slot="activator" v-bind:style="{cursor: 'pointer'}"
                                 @click="deleteItem(toShow)">delete</v-icon>
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
                                                                                        <v-chip v-bind="{color: accommodation.accommodation?'green':'red'}"
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
                                                                                               v-bind:style="{backgroundColor: accommodation.accommodation?'white':'#ff5252'}">Acc: </label>{{accommodation.accommodation}}
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
                                                                                               v-bind:style="{backgroundColor: !accommodation.accommodation?'#ff5252':'white'}">Acc: </label>{{accommodation.accommodation}}
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
                                                                                        <v-chip v-bind="{color: accommodation.accommodation?'green':'red'}"
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
                                                                                               v-bind:style="{backgroundColor: accommodation.accommodation?'white':'#ff5252'}">Acc: </label>{{accommodation.accommodation}}
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
                                                                        <v-flex xs2 my-2
                                                                                slot="header">
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
                                                                                <v-icon class=" ml-2"
                                                                                        slot="activator"
                                                                                        v-bind:style="{color: item.transferIn.sent?'#5ac35e':'#ff5252'}">
                                                                                    email
                                                                                </v-icon>
                                                                                <span>Send to driver</span>
                                                                            </v-tooltip>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-bind:style="{backgroundColor: item.transferIn.canceled?'#ff5252':'white'}">
                                                                            <v-container py-0>
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
                                                                        <v-flex xs2 my-2
                                                                                slot="header">
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
                                                                                <v-icon class=" ml-2"
                                                                                        slot="activator"
                                                                                        v-bind:style="{color: item.transferOut.sent?'#5ac35e':'#ff5252'}">
                                                                                    email
                                                                                </v-icon>
                                                                                <span>Send to driver</span>
                                                                            </v-tooltip>
                                                                        </v-flex>
                                                                        <v-flex class="xs12"
                                                                                v-bind:style="{backgroundColor: item.transferOut.canceled?'#ff5252':'white'}">
                                                                            <v-container py-0>
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
                                                                                            v-if="item.transferOut.accommodation && item.transferIn.accommodation.accommodation">
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
                    <v-btn color="blue darken-1" flat @click="sendToGuide(toShow.id)">Send to Guide
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
    import {API_HOST, API_PATH} from '../../config/_entrypoint';
    import time from "../../utils/time";

    export default {
        data() {
            return {
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                flag: false,
                dialogShow: false,
                toShow: {},
                changeNotificationLoading: false,
                showBooking: false,
                showAccommodations: false,
                rows: false,
                changedTour: false,
                active_filter: false,
                downloadPath: API_HOST + API_PATH + '/download-file/',
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
                search: {search: true},
                ajaxControl: false,
                menuSearch: false,
                menuAdvanceSearch: false,
                notifications: [],
                pickerAdvanceSearchStart: false,
                pickerAdvanceSearchEnd: false,
                daysShow: "",
                startbDate: null,
                finishbDate: null


            }
        },
        computed: mapGetters({
            error: 'lstour/list/error',
            items: 'lstour/list/itemsList',
            loading: 'lstour/list/loading',
            loadingDeleting: 'lstour/del/loading',
            view: 'lstour/list/view',
            deleteError: 'lstour/del/error',
            retrieved: 'lstour/update/retrieved',
            retrieveLoading: 'lstour/update/retrieveLoading',
            lsNotifications: 'lstourtemplate/list/notifications',
            templates: 'lstourtemplate/list/items',
            destinations: 'destination/list/items',
        }),
        methods: {
            sendToGuide(id) {
                let link = API_HOST + API_PATH + '/send-to-guide'
                this.ajaxControl = true;
                fetch(link, {
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({id: id, type: 'ls'})
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response) {
                            this.ajaxControl = false;
                            this.snackbarColor = 'success';
                            this.snackbarText = 'The email was sent';
                            this.snackbar = true;
                            this.$store.dispatch('lstour/list/getItemsList', this.search).then(() => {
                                this.rows = Object.keys(this.items).length;
                                this.dialogShow = false;
                            });
                            this.notificationSatus();
                        }
                        else {
                            this.ajaxControl = false;
                            this.snackbarColor = 'error';
                            this.snackbarText = 'Error sending email. PLease try later';
                            this.snackbar = true;
                        }

                    });
            },
            exportExcel() {
                let ids = [];
                for (let key in this.items)
                    this.items[key].forEach(function (item) {
                        ids.push(item.id)
                    })
                let link = API_HOST + API_PATH + '/export-excel'
                this.ajaxControl = true;
                fetch(link, {
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({ids: ids})
                })
                    .then(response => response.json())
                    .then(response => {
                        this.ajaxControl = false;
                        window.location.href = API_HOST + API_PATH + '/download-file/' + response;
                    }).catch(e => {
                    this.errorList(time() + ' ' + e.message)
                });
            },
            refresh() {
                this.$store.dispatch('lstour/list/getItemsList').then(() => {
                    this.rows = Object.keys(this.items).length;
                    this.search = {search: true};
                    this.active_filter = false;
                    this.$refs.advanceSearchForm.reset();
                    this.startbDate = moment().format('DD/MM/YYYY');
                    this.finishbDate = moment().add(this.daysShow,'days').format('DD/MM/YYYY');
                });
            },
            advanceSearch() {
                this.$store.dispatch('lstour/list/getItemsList', this.search).then(() => {
                    this.rows = Object.keys(this.items).length;
                    if (this.search.dateStart != null){
                        this.startbDate = moment(this.search.dateStart).format('DD/MM/YYYY');
                    } else {
                        this.startbDate = null;
                    }
                    if (this.search.dateEnd != null) {
                        this.finishbDate = moment(this.search.dateEnd).format('DD/MM/YYYY');}
                    else {
                        this.finishbDate = null;
                    }
                    this.menuAdvanceSearch = false;
                    this.active_filter = true;
                });

            },
            searchCodeContact() {
                this.menuSearch = false;
                if (this.search.text != "") {
                    let link = API_HOST + API_PATH + '/search-code-contact'
                    this.ajaxControl = true;
                    fetch(link, {
                        method: 'POST',
                        credentials: "same-origin",
                        body: JSON.stringify({value: this.search.text})
                    })
                        .then(response => response.json())
                        .then(response => {
                            this.menuSearch = false;
                            if (response.length) {
                                this.search.codeContactResult = response;
                                this.menuSearch = true;
                            }
                            else this.menuSearch = false;
                            this.ajaxControl = false;
                        })
                        .catch(e => {
                            this.errorList(time() + ' ' + e.message)
                        });

                }
                return true;
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
            getDay(val) {
                if (typeof val != typeof undefined && val)
                    return this.days[val].text;
            },
            cancelTour(val) {
                if (confirm('Are you sure you want to cancel this tour?')) {
                    let link = API_HOST + API_PATH + '/cancel-ls-tour/' + val.id;
                    this.changeNotificationLoading = true;
                    fetch(link, {
                        method: 'PUT',
                        credentials: "same-origin",
                    })
                        .then(response => response.json())
                        .then(response => {
                            this.changeNotificationLoading = false;
                            this.dialogShow = false;
                            this.$store.dispatch('lstour/list/getItemsList', this.search).then(() => {
                                this.rows = Object.keys(this.items).length;
                            });
                            this.notificationSatus();
                        })
                        .catch(e => {
                            this.errorList(time() + ' ' + e.message)
                        });

                }
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
                    })
                    .catch(e => {
                        this.errorList(time() + ' ' + e.message)
                    });
            },
            getTime(val) {
                return moment(val, 'H:mm').format('hh:mm a')
            },
            showItem(item) {
                this.$store.dispatch('lstour/update/retrieve', item.id).then(() => {
                    this.showBooking = this.retrieved.bookings.length;
                    Object.assign(this.toShow, this.retrieved);
                    this.setAccommodations();
                    this.dialogShow = true;
                })
                    .catch(e => {
                        this.errorList(time() + ' ' + e.message)
                    });
            },
            errorList(message) {
                this.changeNotificationLoading = false;
                this.ajaxControl = false;
                this.$store.dispatch('lstour/list/reset');
                this.$store.dispatch('lstour/update/reset');
                this.$store.dispatch('lstour/del/reset');
                this.flag = true;
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            notificationSatus() {
                let link = API_HOST + API_PATH + '/notifications-status/ls';
                this.ajaxControl = true;
                fetch(link, {
                    method: 'GET',
                    credentials: "same-origin",
                })
                    .then(response => response.json())
                    .then(response => {
                        this.notifications = response;
                        this.ajaxControl = false;
                    })
                    .catch(e => {
                        this.errorList(time() + ' ' + e.message)
                    });
            },
            deleteItem(item) {
                if (confirm('Are you sure you want to delete?')) {
                    this.$store.dispatch('lstour/del/deleteItem', item.id).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.dialogShow = false;
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            this.$store.dispatch('lstour/list/getItemsList', this.search).then(() => {
                                this.rows = Object.keys(this.items).length;
                            });
                            this.notificationSatus();
                        })
                        .catch(e => {
                            this.errorList(time() + ' ' + e.message)
                        });
                }
            }
        },
        watch: {
            dialogShow(show) {
                if (!show && this.changedTour) {
                    this.$store.dispatch('lstour/list/getItemsList').then(() => {
                        this.rows = Object.keys(this.items).length;
                    });
                    this.notificationSatus();
                    this.changedTour = false;
                }
            },
            error(message) {
                this.errorList(message);
            },
            deleteError(message) {
                this.errorList(message);

            }
        },
        created() {
            this.$store.dispatch('lstour/list/getItemsList', {check: 'notification'}).then(() => {
                this.rows = Object.keys(this.items).length;
                this.notificationSatus();
            });
            this.$store.dispatch('lstourtemplate/list/getLsNotification');
            this.$store.dispatch('lstourtemplate/list/getItems');
            if (!this.destinations.length)
                this.$store.dispatch('destination/list/getItems');

            let link = API_HOST + API_PATH + '/tour-days-shows/ls';
            fetch(link, {
                method: 'GET',
                credentials: "same-origin",
            })
                .then(response => response.json())
                .then(response => {
                    this.search.daysShow = response;
                    this.daysShow = response;
                    this.startbDate = moment().format('DD/MM/YYYY');
                    this.finishbDate = moment().add(this.daysShow,'days').format('DD/MM/YYYY');
                })
                .catch(e => {
                    this.errorList(time() + ' ' + e.message)
                });
        }
    }
</script>
