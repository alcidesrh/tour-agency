<template>
    <v-app id="inspire">
        <v-snackbar
                color="error"
                :timeout="0"
                top
                left
                v-model="offline"
        >
            Your are offline
        </v-snackbar>
        <v-container style="position: fixed; z-index: 2001" fill-height justify-center
                     v-show="loading">
            <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
        </v-container>
        <v-toolbar dark color="primary" fixed clipped-right app tabs>
            <img v-show="host" v-bind:src="host+'LocallySourcedCubalogo-111x40.png'"/>
            <v-spacer></v-spacer>
            <v-tabs v-show="codeHTC.length != 0"
                    color="primary"
                    right

            >
                <v-tabs-slider color="white" style="height: 2px"></v-tabs-slider>
                <v-tab id="h_t_c_tours" flat v-for="(item, index) in codeHTC" :key="index"
                       @click="$router.push({name: 'HTCTourList'})">
                    <v-icon class="mr-1">view_list</v-icon>
                    {{item.name}} B.Schedule
                </v-tab>
                <v-tab id="l_s_tours" flat v-for="(item, index) in codeLS" :key="index+1"
                       @click="$router.push({name: 'LSTourList'})">
                    <v-icon class="mr-1">list</v-icon>
                    {{item.name}} B.Schedule
                </v-tab>
                <v-tab id="transfers" flat @click="$router.push({name: 'TransferList'})">
                    <v-icon class="mr-1">transfer_within_a_station</v-icon>
                    A.Transfers
                </v-tab>
                <v-tab id="guides-shedule" flat @click="$router.push({name: 'GuideShedule'})">
                    <v-icon class="mr-1">perm_contact_calendar</v-icon>
                    Guide Schedule
                </v-tab>
                <v-tab id="settings">
                    <v-menu bottom>
                        <v-btn flat slot="activator">
                            <v-icon class="mr-1">settings</v-icon>
                            Settings
                        </v-btn>
                        <v-list>
                            <v-list-tile :id="item.id" v-for="item in items" :key="item.title"
                                         @click="pushtRoute(item.route)">
                                <v-list-tile-title>
                                    <v-icon>{{item.icon}}</v-icon>
                                    {{ item.name }}
                                </v-list-tile-title>
                            </v-list-tile>
                        </v-list>
                    </v-menu>
                </v-tab>
            </v-tabs>
        </v-toolbar>
        <v-content>
            <v-container>
                <v-layout>
                    <v-flex>
                        <transition name="fade">
                            <router-view></router-view>
                        </transition>
                    </v-flex>
                </v-layout>
            </v-container>
        </v-content>
    </v-app>
</template>
<script>
    import {mapGetters} from 'vuex';
    import {API_HOST, API_PATH} from '../vue-app/config/_entrypoint';
    import moment from 'moment-timezone';

    export default {

        data: () => ({
            $this: this,
            drawer: true,
            drawerRight: true,
            right: null,
            left: null,
            items: [],
            tab: false,
            loading: false,
            host: false,
            offline: false
        }),
        props: {
            source: String
        },
        methods: {
            pushtRoute(route) {
                this.$router.push({name: route});
            }
        },
        computed: {
            ...mapGetters({
                codeLS: 'lstourtemplate/list/code',
                codeHTC: 'htctourtemplate/list/code'
            })
        },
        watch: {
            codeHTC() {
                let code = this.codeLS.length ? this.codeLS[0].name : '';
                let codeHTC = this.codeHTC.length ? this.codeHTC[0].name : '';
                this.items = [
                    {
                        name: codeHTC + " Tour Templates",
                        route: 'HTCTourTemplateList',
                        icon: 'view_list',
                        id: 'h_t_c_tour_templates'
                    },
                    {
                        name: code + " Tour Templates",
                        route: 'LSTourTemplateList',
                        icon: 'list',
                        id: 'l_s_tour_templates'
                    },
                    {name: "Guides", route: 'GuideList', icon: 'record_voice_over', id: 'guides'},
                    {name: "Provinces", route: 'ProvinceList', icon: 'add_location', id: 'provinces'},
                    {name: "Destinations", route: 'DestinationList', icon: 'place', id: 'destinations'},
                    {name: "Notifications", route: 'NotificationList', icon: 'notifications', id: 'notifications'},
                    {
                        name: "Transfers",
                        route: 'TransferReceptiveList',
                        icon: 'transfer_within_a_station',
                        id: 'transferreceptive'
                    },
                    {name: "Drivers", route: 'DriverList', icon: 'drive_eta', id: 'drivers'},
                    {name: "Agents", route: 'AgentList', icon: 'person', id: 'agents'},
                    {name: "Offices", route: 'TourOfficeList', icon: 'home', id: 'tour_offices'},
                    {name: "Icons", route: 'IconList', icon: 'transfer_within_a_station', id: 'icons'},
                    {name: "General Settings", route: 'ConfigList', icon: 'settings_applications', id: 'config'},
                ];
                this.$nextTick(function () {
                    if (typeof route_reload != typeof undefined) {
                        if (route_reload.length == 1) {
                            if (route_reload[0] != 'h_t_c_tours' && route_reload[0] != 'l_s_tours' && route_reload[0] != 'transfers' && route_reload[0] != 'guides-shedule')
                                document.getElementById('settings').firstChild.click();
                            document.getElementById(route_reload[0]).firstChild.click();
                        } else {
                            if (route_reload[0] == 'h_t_c_tours' || route_reload[0] == 'l_s_tours')
                                document.getElementById(route_reload[0]).firstChild.click();
                            else
                                document.getElementById('settings').firstChild.click();
                            let path = "";
                            route_reload.forEach(function (item) {
                                path = path + '/' + item;
                            });
                            this.$router.push({path: path});
                        }
                    }
                    else
                        document.getElementById('h_t_c_tours').firstChild.click();

                })

            }
        },
        created() {
            moment.tz.setDefault("America/New_York");
            let $this = this;
            window.addEventListener('load', function () {
                function updateOnlineStatus(event) {
                    $this.offline = !navigator.onLine;
                }

                window.addEventListener('online', updateOnlineStatus);
                window.addEventListener('offline', updateOnlineStatus);
            });
            this.host = API_HOST + '/';
            this.$store.dispatch('lstourtemplate/list/getCode').then(() => {
                this.$store.dispatch('htctourtemplate/list/getCode').then(() => {
                    this.loading = false;
                });
            });
        }
    }
</script>