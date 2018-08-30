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
            <v-tooltip top>
                <v-btn color="primary"
                       slot="activator"
                       dark
                       fab
                       fixed
                       bottom
                       right
                       @click="newItem"
                >
                    <v-icon>add</v-icon>
                </v-btn>
                <label>New Transfer</label>
            </v-tooltip>
            <v-container style="position: fixed; z-index: 2001" fill-height justify-center
                         v-show="loading || loadingDeleting || retrieveLoading || creatingLoading || loadingGeneric">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <!--<v-card-title>-->
                <v-card flat tile>
                <v-toolbar>
                    <v-layout wrap row>
                        <v-flex md8 px-3>
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
                                                 @click="showItem(item.id); dialogShow = false; search.text = ''">
                                        <v-list-tile-title>
                                            <v-icon class="pointer" style="color: rgba(0,0,0,.54)">
                                            {{item.in?'flight_land':'flight_takeoff'}}
                                        </v-icon>
                                            <label class="bold" style="color: rgba(0,0,0,.54)">{{item.in?'IN':'OUT'}}</label> {{ item.bookingName+' '+item.startDate+' '+getTime(item.startDate+' '+item.startTime)}}</v-list-tile-title>
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
                                <v-icon v-bind:color="active_filter?'red':'teal'" slot="activator" class="pointer">filter_list</v-icon>
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
                                                                <v-date-picker v-model="search.dateStart" no-title scrollable
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
                                                                <v-date-picker v-model="search.dateEnd" no-title scrollable :min="search.dateStart"
                                                                               @input="$refs.pickerAdvanceSearchEnd.save(search.dateEnd)">
                                                                </v-date-picker>
                                                            </v-menu>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row wrap mt-3>
                                                        <v-flex xs6>
                                                            <v-select
                                                                    :items="transferNotifications"
                                                                    v-model="search.notifications"
                                                                    label="Select notifications"
                                                                    item-text="name"
                                                                    item-value="icon"
                                                                    multiple
                                                                    chips
                                                                    max-height="auto"
                                                                    autocomplete
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
                                                                        </v-icon> {{ data.item.name }}

                                                                    </v-chip>
                                                                </template>
                                                                <template slot="item" slot-scope="data">
                                                                    <template>
                                                                        <v-icon>
                                                                            {{ data.item.icon }}
                                                                        </v-icon> {{data.item.name}}
                                                                    </template>
                                                                </template>
                                                            </v-select>
                                                        </v-flex>
                                                        <v-flex xs6>
                                                            <v-radio-group v-model="search.notificationStatus" row :mandatory="false">
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
                                                    <v-layout row wrap mt3>
                                                        <v-flex xs4>
                                                            <v-select
                                                                    :items="typesSearch"
                                                                    v-model="search.type"
                                                                    label="Select type"
                                                                    item-text="name"
                                                                    max-height="auto"
                                                                    autocomplete
                                                            >
                                                                <template slot="selection" slot-scope="data">
                                                                    <v-icon>
                                                                        {{ data.item.icon }}
                                                                    </v-icon> {{ data.item.text }}
                                                                </template>
                                                                <template slot="item" slot-scope="data">
                                                                    <template>
                                                                        <v-icon>
                                                                            {{ data.item.icon }}
                                                                        </v-icon> {{data.item.text}}
                                                                    </template>
                                                                </template>
                                                            </v-select>
                                                        </v-flex>
                                                        <v-flex xs4 ml-3>
                                                            <v-select
                                                                    :items="companies"
                                                                    v-model="search.company"
                                                                    label="Companies"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    single-line
                                                                    autocomplete
                                                            ></v-select>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row wrap mt3>
                                                        <v-flex xs4>
                                                            <v-select
                                                                    :key="1"
                                                                    :items="drivers"
                                                                    v-model="drive"
                                                                    label="Drivers"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    single-line
                                                                    autocomplete
                                                                    @change="receptive=''"
                                                            ></v-select>
                                                        </v-flex>
                                                        <v-flex xs4 ml-3>
                                                            <v-select
                                                                    :key="2"
                                                                    :items="transfers"
                                                                    v-model="receptive"
                                                                    label="Reciptives"
                                                                    item-text="name"
                                                                    item-value="id"
                                                                    single-line
                                                                    @change="drive=''"
                                                            ></v-select>
                                                        </v-flex>
                                                    </v-layout>
                                                </v-form>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                    <v-card-actions>
                                        <v-spacer></v-spacer>
                                        <v-btn flat @click="menuAdvanceSearch = false;$refs.advanceSearchForm.reset();">Cancel</v-btn>
                                        <v-btn color="primary" flat @click="advanceSearch">Search</v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-menu>
                            <v-icon slot="activator" color="teal" class="pointer" @click="refresh">refresh</v-icon>
                            <v-container class="px-0 ml-3" style="display: inline" v-if="finishbDate != null || startbDate != null">{{finishbDate == null?'From - ':''}}{{startbDate == null?'Until':startbDate}}{{finishbDate == null?'':' - '}}{{finishbDate == null?'':finishbDate}}</v-container>
                        </v-flex>
                        <v-flex md4>
                            <div class="right pa-4">
                                <v-tooltip top>
                                    <v-icon class="pointer" slot="activator" @click="exportExcel">cloud_download</v-icon>
                                    <span>Download Excel</span>
                                </v-tooltip>
                            </div>
                            <div class="right pa-4">
                                <v-menu open-on-hover bottom offset-y v-for="(item,index) in notifications" :key="index" v-if="item.cant">
                                    <v-badge slot="activator" color="red" overlap>
                                        <span slot="badge">{{item.cant}}</span>
                                        <v-icon color="grey" class="px-2">{{item.icon}}</v-icon>
                                    </v-badge>
                                    <v-list>
                                        <v-list-tile v-for="item2, index2 in item.transfers" :key="index2" @click="showItem(item2.id)">
                                            <v-list-tile-title>
                                                <v-icon class="pointer" style="color: rgba(0,0,0,.54)">
                                                    {{item2.type=='IN'?'flight_land':'flight_takeoff'}}
                                                </v-icon>
                                                {{ item2.type+' '+item2.name }}</v-list-tile-title>
                                        </v-list-tile>
                                    </v-list>
                                </v-menu>
                            </div>
                        </v-flex>
                    </v-layout>
                </v-toolbar>
                </v-card>
                <v-alert type="info" :value="true" v-if="items.length == 0" class="mt-2" style="width: 100%">
                    There are no iems to show.
                </v-alert>
                <v-container >
                    <div v-bind:style="{width: width, display: 'inline-block'}">
                        <v-layout row wrap my-1 v-for="lista, key, index3 in items" :key="index3">
                        <v-flex xs1 justify-center style="max-width: 10%">
                            <v-container class="px-0">
                                <label class="bold" style="color: rgba(0,0,0,.54)">{{key}}</label>
                            </v-container>
                        </v-flex>
                        <v-flex xs11 justify-center>
                            <v-container class="px-0">
                                <v-chip v-for="item, index2 in lista" :key="index2" v-bind:style="{backgroundColor: (item.canceled)?'rgb(255, 82, 82)':(item.companyColor)?item.companyColor:'#00897B'}"
                                        text-color="black" @click="showItem(item.id)">
                                    <v-chip color="white" style="height: 90%; margin-left: 0px; padding: 0px">
                                        <v-icon class="pointer" style="color: rgba(0,0,0,.54)">
                                            {{item.in?'flight_land':'flight_takeoff'}}
                                        </v-icon>
                                        <label class="bold" style="color: rgba(0,0,0,.54)">{{item.in?'IN':'OUT'}}</label>
                                    </v-chip>
                                    {{item.booking}}
                                    <v-chip color="white" class="ml-2" style="height: 90%">
                                        <v-icon class="pointer"
                                                :color="(item.flyData)?'success':'error'">flight
                                        </v-icon>
                                        <v-icon class="pointer ml-2"
                                                :color="(item.accommodation)?'success':'error'">home
                                        </v-icon>
                                        <v-icon class="pointer ml-2"
                                                :color="(item.sent)?'success':'error'">email
                                        </v-icon>
                                    </v-chip>
                                    <v-tooltip top>
                                        <v-icon v-if="!item.sent && !item.driver" class="pointer" @click.stop="addToSend(item)"
                                                color="white" slot="activator">send
                                        </v-icon>
                                        <span>Send by mail</span>
                                    </v-tooltip>
                                </v-chip>
                            </v-container>
                        </v-flex>
                        <v-flex xs12 v-if="rows > (index3 + 1)">
                            <v-divider></v-divider>
                        </v-flex>
                    </v-layout>
                    </div>
                    <div v-if="mailList.length" v-bind:style="{width: '16%', display: 'inline-block', float:'right', paddingLeft: '5px'}">
                       <v-layout row wrap my-1>
                        <v-flex xs12>
                            <v-card style="margin-right:20px;margin-left:-20px;position:fixed">
                                <v-card-title primary-title style="padding-top: 12px">
                                    <div class="mb-2" style="font-size: 20px">Send to receptive</div>
                                    <v-chip v-for="item, index in mailList" :key="index" style="margin-left: 0px; padding: 0px" v-bind:style="{backgroundColor: item.companyColor?item.companyColor:'#00897B'}"
                                            text-color="white">
                                        <label class="bold">{{item.booking}}</label>
                                        <v-icon class="pointer bold" color="error" @click.stop="deleteMailList(item)" style="margin-left: 5px; margin-right: -7px; font-size: 20px">
                                            close
                                        </v-icon>
                                    </v-chip>
                                </v-card-title>
                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn flat small color="primary" @click="deleteMailList">Cancel</v-btn>
                                    <v-btn flat small color="primary" @click="sendMails">Send</v-btn>
                                </v-card-actions>
                            </v-card>
                        </v-flex>
                    </v-layout>
                    </div>
                </v-container>
            <!--</v-card-title>-->
        </v-card>
        <v-dialog v-model="dialogShow" max-width="900" :persistent="true">
            <v-card>
                <v-card-title>
                    <span class="headline" v-bind:style="{backgroundColor: (!toShow.canceled)?'':'#ff5252'}">
                        <span v-show="!editing">New</span><span v-show="editing"></span> Transfer</span>
                    <span v-show="editing" class="ml-2">
                        <v-icon class="pointer" style="color: rgba(0,0,0,.54)">
                        {{toShow.in?'flight_land':'flight_takeoff'}}
                    </v-icon>
                    <label class="bold" style="color: rgba(0,0,0,.54)">{{toShow.in?'IN':'OUT'}}</label>
                    </span>
                     <v-btn icon @click.native="dialogShow = false" class="btn-close" slot="activator">
                        <v-icon>close</v-icon>
                     </v-btn>
                </v-card-title>
                <v-form ref="transferForm" lazy-validation>
                    <v-card-text>
                        <v-layout row wrap>
                            <v-flex xs12 px-2 my-2>
                                <v-tooltip top>
                                    <v-icon class="pointer" slot="activator"
                                            :color="colorFlight">flight
                                    </v-icon>
                                    <span>Flight Data</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <v-icon class="pointer ml-2" slot="activator"
                                            :color="colorAccommodation">home
                                    </v-icon>
                                    <span>Accommodation</span>
                                </v-tooltip>
                                <v-tooltip top>
                                    <v-icon class="pointer ml-2" @click="changeSentState(toShow);" slot="activator"
                                            :color="(toShow.sent)?'success':'error'">email
                                    </v-icon>
                                    <span>Send to driver</span>
                                </v-tooltip>
                                <span class="ml-3 right">
            <v-tooltip top>
            <v-icon color="success" slot="activator" v-bind:style="{cursor: 'pointer'}"
                    @click="save">save</v-icon>
            <span>Save transfer</span>
            </v-tooltip>
            <v-tooltip top>
            <v-icon color="warning" slot="activator" v-bind:style="{cursor: 'pointer'}" @click="changeTransferState(toShow)">cancel</v-icon>
            <span>{{toShow.canceled?'Activate transfer':'Cancel transfer'}}</span>
            </v-tooltip>
            <v-tooltip top v-show="editing">
            <v-icon color="error" slot="activator" v-bind:style="{cursor: 'pointer'}"
                    @click="deleteItem(toShow.id)">delete</v-icon>
            <span>Delete transfer</span>
            </v-tooltip>
            </span>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap mb-3 v-if="toShow.tour && editing">
                            <v-flex xs12 px-2 >
                                <label class="bold" >Tour: </label>{{toShow.tour}}
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap mb-3 v-if="editing">
                            <v-flex xs12 px-2>
                                <label class="bold">Booking: </label>{{toShow.booking}}
                                <label class="bold ml-2">Pax.N: </label>{{toShow.number}}
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap v-if="!editing">
                            <v-flex xs2 px-2>
                                <v-select
                                        :items="types"
                                        v-model="toShow.type"
                                        label="Type"
                                        item-text="name"
                                        max-height="auto"
                                        required
                                >
                                    <template slot="selection" slot-scope="data">
                                        <v-icon>
                                            {{ data.item.icon }}
                                        </v-icon> {{ data.item.text }}
                                    </template>
                                    <template slot="item" slot-scope="data">
                                        <template>
                                            <v-icon>
                                                {{ data.item.icon }}
                                            </v-icon> {{data.item.text}}
                                        </template>
                                    </template>
                                </v-select>
                            </v-flex>
                            <v-flex xs4 px-2>
                                <v-select
                                        :items="companies"
                                        v-model="toShow.company"
                                        label="Companies"
                                        item-text="name"
                                        single-line
                                        autocomplete
                                        :rules="filedRule"
                                        required
                                ></v-select>
                            </v-flex>
                            <v-flex xs3 px-2 v-show="typeof toShow.company != typeof undefined && toShow.company.companyHTC">
                                <v-select
                                        :items="tours"
                                        v-model="toShow.tour"
                                        label="Tour"
                                        item-text="code"
                                        item-value="id"
                                        single-line
                                        autocomplete
                                ></v-select>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap v-if="!editing">
                            <v-layout row wrap v-show="!editing">
                                <v-flex xs6 px-2>
                                    <v-text-field
                                            label="Booking"
                                            v-model="toShow.booking"
                                            v-on:keyup="flightColor"
                                            :rules="filedRule"
                                            required

                                    ></v-text-field>
                                </v-flex>
                                <v-flex xs1 px-2>
                                    <v-text-field
                                            label="Pax.N"
                                            v-model="toShow.number"
                                            v-on:keyup="toShow.number = (validNumbers(toShow.number, $event)?toShow.number:'')"
                                            :rules="filedRule"
                                            required
                                    ></v-text-field>
                                </v-flex>
                            </v-layout>
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
                                        :return-value.sync="toShow.date"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Date"
                                            v-model="toShow.date"
                                            prepend-icon="event"
                                            readonly
                                            :rules="filedRule"
                                            required
                                    ></v-text-field>

                                    <!--<v-date-picker color="green lighten-1" v-model="toShow.date"-->
                                                   <!--:min="currentDate"-->
                                                   <!--no-title scrollable>-->
                                        <!--<v-spacer></v-spacer>-->
                                        <!--<v-btn flat color="primary" @click="pickerTranferStartDate = false">Cancel-->
                                        <!--</v-btn>-->
                                        <!--<v-btn flat color="primary"-->
                                               <!--@click="$refs.pickerTranferStartDate.save(toShow.date)">OK-->
                                        <!--</v-btn>-->
                                    <!--</v-date-picker>-->
                                    <v-date-picker v-model="toShow.date" no-title scrollable :min="currentDate"
                                                   @input="$refs.pickerTranferStartDate.save(toShow.date)">
                                    </v-date-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs3>
                                <v-menu
                                        ref="pickerTranferStartTime"
                                        lazy
                                        :close-on-content-click="false"
                                        v-model="pickerTranferStartTime"
                                        transition="scale-transition"
                                        offset-y
                                        full-width
                                        max-width="290px"
                                        min-width="290px"
                                        :return-value.sync="toShow.inTime"
                                        @click.stop="toShow.stop = true"
                                >
                                    <v-text-field
                                            slot="activator"
                                            label="Hour"
                                            v-model="toShow.inTime"
                                            prepend-icon="access_time"
                                            readonly
                                    ></v-text-field>
                                    <!--<v-time-picker v-model="toShow.inTime"-->
                                                   <!--@change="$refs.pickerTranferStartTime.save(toShow.inTime)"></v-time-picker>-->
                                    <v-time-picker
                                            v-model="toShow.inTime" >
                                        <v-spacer></v-spacer>
                                        <v-btn flat color="primary" @click="$refs.pickerTranferStartTime.save(toShow.inTime)">OK</v-btn>
                                    </v-time-picker>
                                </v-menu>
                            </v-flex>
                            <v-flex xs5 px-2>
                                <v-text-field
                                        label="Fly Data"
                                        v-model="toShow.flyData"
                                        v-on:keyup="flightColor"
                                ></v-text-field>
                            </v-flex>
                        </v-layout>
                        <v-layout row wrap>
                            <v-flex xs6 px-2>
                                <v-text-field
                                        label="Accomodation"
                                        v-model="toShow.accommodation"
                                        v-on:keyup="accomodationColor"
                                ></v-text-field>
                            </v-flex>
                            <v-flex xs4 px-3>
                                <v-select
                                        :items="drivers"
                                        v-model="toShow.driver"
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
                </v-form>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<script>
    import {mapGetters} from 'vuex';
    import moment from 'moment';
    import filter from '../../utils/filter';
    import invalidNumber from '../../utils/numberValidate';
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
                rows: false,
                pickerTranferStartDate: false,
                pickerTranferStartTime: false,
                currentDate: moment().format('YYYY-MM-DD'),
                colorFlight: 'error',
                colorAccommodation: 'error',
                editing: false,
                types: [{text: 'In', value: 'in', icon: 'flight_land'}, {text: 'Out', value: 'out', icon: 'flight_takeoff'}],
                typesSearch: [{text: 'Any type', value: 'all', icon: ''}, {text: 'In', value: 'in', icon: 'flight_land'}, {text: 'Out', value: 'out', icon: 'flight_takeoff'}],
                width: '100%',
                mailList: [],
                loadingGeneric: false,
                filedRule: [
                    v => !!v || 'This filed is required'
                ],
                search: {search: true},
                drive: false,
                receptive: false,
                menuSearch: false,
                menuAdvanceSearch: false,
                notifications: [],
                pickerAdvanceSearchStart: false,
                active_filter: false,
                pickerAdvanceSearchEnd: false,
                daysShow: "",
                startbDate: null,
                finishbDate: null,
                transferNotifications: [{icon: 'flight', name: 'Flight Data'}, {icon: 'home', name: 'Accommodation'}, {icon: 'email', name: 'Sent to Drive'}],
            }
        },
        computed: mapGetters({
            error: 'transfer/list/error',
            items: 'transfer/list/items',
            loading: 'transfer/list/loading',
            loadingDeleting: 'transfer/del/loading',
            view: 'transfer/list/view',
            deleteError: 'transfer/del/error',
            retrieveError: 'transfer/update/retrieveError',
            retrieved: 'transfer/update/retrieved',
            retrieveLoading: 'transfer/update/retrieveLoading',
            drivers: 'driver/list/items',
            updateError: 'transfer/update/updateError',
            tours: 'htctour/list/items',
            companies: 'transfercompany/list/items',
            creatingLoading: 'transfer/create/loading',
            creatingError: 'transfer/create/error',
            transfers: 'transferreceptive/list/items',
        }),
        methods: {
            exportExcel(){
                let ids = [];
                for(let key in this.items)
                    this.items[key].forEach(function(item){
                        ids.push(item.id)
                    })
                let link = API_HOST + API_PATH + '/export-transfer-excel'
                this.loadingGeneric = true;
                fetch(link, {
                    method: 'POST',
                    credentials: "same-origin",
                    body: JSON.stringify({ids: ids})
                })
                    .then(response => response.json())
                    .then(response => {
                        this.loadingGeneric = false;
                        window.location.href = API_HOST + API_PATH + '/download-file/'+response;
                    }).catch(e => {
                    this.showError(time() + ' ' + e.message)
                });
            },
            refresh(){
                this.$store.dispatch('transfer/list/getItemsList').then(() => {
                    this.rows = Object.keys(this.items).length;
                    this.search = {search: true};
                    this.$refs.advanceSearchForm.reset();
                    this.active_filter = false;
                    this.startbDate = moment().format('DD/MM/YYYY');
                    this.finishbDate = moment().add(this.daysShow,'days').format('DD/MM/YYYY');
                });
            },
            advanceSearch(){
                if(this.drive){
                    this.search.drive = this.drive;
                    this.search.receptive = false;
                }
                else if(this.receptive){
                    this.search.receptive = this.receptive;
                    this.search.drive = false;
                }
                this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                    this.rows = Object.keys(this.items).length;
                    this.menuAdvanceSearch = false;
                });               
                this.active_filter = true;
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

            },
            searchCodeContact() {
                this.menuSearch = false;
                if (this.search.text != "") {
                    let link = API_HOST + API_PATH + '/search-code-contact-transfer'
                    this.loadingGeneric = true;
                    fetch(link, {
                        method: 'POST',
                        credentials: "same-origin",
                        body: JSON.stringify({value: this.search.text, })
                    })
                        .then(response => response.json())
                        .then(response => {
                            this.menuSearch = false;
                            if (response.length) {
                                this.search.codeContactResult = response;
                                this.menuSearch = true;
                            }
                            else this.menuSearch = false;
                            this.loadingGeneric = false;
                        }).catch(e => {
                        this.showError(time() + ' ' + e.message)
                    });

                }
                return true;
            },
            changeSentState(transfer){
                if(!transfer.sent){
                    if(!transfer.driver){
                        this.snackbarColor = 'warning';
                        this.snackbarText = 'This transfer has not driver';
                        this.snackbar = true;
                        return false;
                    }
                    else if(!transfer.flyData){
                        this.snackbarColor = 'warning';
                        this.snackbarText = 'This transfer has not fly data';
                        this.snackbar = true;
                        return false;
                    }
                    else if(!transfer.accommodation){
                        this.snackbarColor = 'warning';
                        this.snackbarText = 'This transfer has not accommodation';
                        this.snackbar = true;
                        return false;
                    }
                }

                let link = API_HOST + API_PATH + '/change-sent-state/' + transfer.id;
                this.loadingGeneric = true;
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                }).then(response => {
                    this.loadingGeneric = false;
                    this.toShow.sent=!this.toShow.sent
                    this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                        this.rows = Object.keys(this.items).length;
                    });
                }).catch(e => {
                    this.showError(time() + ' ' + e.message)
                });
            },
            changeTransferState(transfer){
                if (!transfer.canceled && !confirm('Are you sure you want to cancel?')) {
                    return false;
                }
                let link = API_HOST + API_PATH + '/change-transfer-state/' + transfer.id;
                this.loadingGeneric = true;
                fetch(link, {
                    method: 'PUT',
                    credentials: "same-origin",
                }).then(response => {
                        this.loadingGeneric = false;
                        this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                            this.rows = Object.keys(this.items).length;
                        });
                        this.dialogShow = false;
                    this.notificationSatus();
                    }).catch(e => {
                    this.showError(time() + ' ' + e.message)
                });
            },
            sendMails(){
                let ids = [];
                this.mailList.filter(item=>{ids.push(item.id)});
                ids = {ids: ids};
                this.loadingGeneric = true;
                this.$store.dispatch('transfer/list/sendMails', ids).then((response) => {
                    if(response){
                        this.loadingGeneric = false;
                        this.snackbarColor = 'success';
                        this.snackbarText = 'The email was sent';
                        this.snackbar = true;
                        this.mailList = [];
                        this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                            this.rows = Object.keys(this.items).length;
                        });
                    }
                    else{
                        this.loadingGeneric = false;
                        this.snackbarColor = 'error';
                        this.snackbarText = 'Error sending email. PLease try later';
                        this.snackbar = true;
                    }
                })
            },
            deleteMailList(item){
                if(this.mailList.indexOf(item) == -1){
                    this.mailList = [];
                    this.width = '100%';
                    return;
                }
                this.mailList.splice(this.mailList.indexOf(item), 1);
                if(!this.mailList.length)
                    this.width = '100%';
            },
            addToSend(item){
                if(item.canceled){
                    this.snackbarColor = 'warning';
                    this.snackbarText = 'This transfer is canceled';
                    this.snackbar = true;
                    return false;
                }
                if(!item.accommodation || !item.flyData){
                    this.snackbarColor = 'warning';
                    this.snackbarText = 'This transfer is not complete to send';
                    this.snackbar = true;
                    return false;
                }
                else if (this.mailList.indexOf(item) != -1){
                    this.snackbarColor = 'warning';
                    this.snackbarText = 'This transfer is already added';
                    this.snackbar = true;
                    return false;
                }
                this.width = '83%';
                this.mailList.push(item);
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
            flightColor() {
                this.colorFlight = (this.toShow.flyData) ? 'success' : 'error';
            },
            accomodationColor() {
                this.colorAccommodation = (this.toShow.accommodation) ? 'success' : 'error';
            },
            showItem(id) {
                this.$store.dispatch('transfer/update/retrieve', id).then(() => {
                    Object.assign(this.toShow, this.retrieved);
                    this.toShow.inTime = this.retrieved.time;
                    if (this.toShow.driver)
                        this.toShow.driver = this.toShow.driver.id;
                    this.flightColor();
                    this.accomodationColor();
                    this.editing = true;
                    this.dialogShow = true;
                })
            },
            getTime(val) {
                return moment(moment(val)).format('H:mm a')
            },
            newItem() {
                this.flightColor();
                this.accomodationColor();
                this.editing = false;
                this.toShow = {};
                this.dialogShow = true;
            },
            customFilter: filter,
            showError(message) {
                this.flag = true;
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
                this.loadingGeneric = false;
            },
            deleteItem(id) {
                if (confirm('Are you sure you want to delete?')) {

                    this.$store.dispatch('transfer/del/deleteItem', id).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.dialogShow = false;
                            this.snackbarText = 'Has been deleted';
                            this.snackbar = true;
                            this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                                this.rows = Object.keys(this.items).length;
                            });
                            this.notificationSatus();
                        })
                }
            },
            notificationSatus(){
                let link = API_HOST + API_PATH + '/notifications-transfer-status';
                this.loadingGeneric = true;
                fetch(link, {
                    method: 'GET',
                    credentials: "same-origin",
                })
                    .then(response => response.json())
                    .then(response => {
                        this.notifications = response;
                        this.loadingGeneric = false;
                    }).catch(e => {
                    this.showError(time() + ' ' + e.message)
                });
            },
            save() {

                if(this.editing)
                this.$store.dispatch('transfer/update/update', {
                    id: this.toShow.id,
                    values: {
                        'accommodation': this.toShow.accommodation,
                        'date': this.toShow.date,
                        'driver': this.toShow.driver,
                        'flyData': this.toShow.flyData,
                        'time': this.toShow.inTime,
                    }
                }).then(
                    () => {
                        if (this.flag) {
                            this.flag = false;
                            return;
                        }
                        this.snackbarColor = 'success';
                        this.snackbarText = 'Has been edited';
                        this.snackbar = true;
                        this.dialogShow = false;

                        this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                            this.rows = Object.keys(this.items).length;
                            this.toShow = {};
                            this.editing = false;
                        });
                        this.notificationSatus();
                    });
                else{
                    if(!this.$refs.transferForm.validate())return false;
                    if(typeof this.toShow.company != typeof undefined)
                        this.toShow.company = this.toShow.company.id;
                    this.$store.dispatch('transfer/create/create', this.toShow).then(
                        () => {
                            if (this.flag) {
                                this.flag = false;
                                return;
                            }
                            this.snackbarColor = 'success';
                            this.snackbarText = 'Has been created';
                            this.snackbar = true;
                            this.dialogShow = false;

                            this.$store.dispatch('transfer/list/getItemsList', this.search).then(() => {
                                this.rows = Object.keys(this.items).length;
                                this.toShow = {};
                                this.editing = false;
                            });
                            this.notificationSatus();
                        });}
            }
        },
        watch: {
            error(message) {
                this.showError(message);
            },
            deleteError(message) {
                this.showError(message);
            },
            retrieveError(message) {
                this.showError(message);
            },
            updateError(message) {
                this.showError(message);
            },
            creatingError(message) {
                this.showError(message);
            }
        },
        created() {
            this.$store.dispatch('transfer/list/getItemsList').then(() => {
                this.rows = Object.keys(this.items).length;
            });
            this.$store.dispatch('driver/list/getItems');
            if (!this.tours.length)
                this.$store.dispatch('htctour/list/getItems');
            if (this.companies.length == 0)
                this.$store.dispatch('transfercompany/list/getItems');
            if (this.transfers.length == 0) {
                this.$store.dispatch('transferreceptive/list/getItems');
            }
            this.notificationSatus();

            let link = API_HOST + API_PATH + '/transfer-days-shows';
            fetch(link,     {
                method: 'GET',
                credentials: "same-origin",
            })
                .then(response => response.json())
                .then(response => {
                    this.search.daysShow = response;
                    this.daysShow = response;
                    this.startbDate = moment().format('DD/MM/YYYY');
                    this.finishbDate = moment().add(this.daysShow,'days').format('DD/MM/YYYY');
                }).catch(e => {
                this.showError(time() + ' ' + e.message)
            });

        }
    }
</script>
