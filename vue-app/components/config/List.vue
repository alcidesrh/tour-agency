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
            <v-container style="position: fixed; z-index: 2001"  fill-height justify-center
                         v-show="loading">
                <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
            </v-container>
            <v-card-title>
                <v-flex headline>
                    General Settings
                </v-flex>
            </v-card-title>
            <v-container pa-3>
                <v-expansion-panel>
                    <v-expansion-panel-content>
                        <div slot="header">HTC Tours</div>
                        <v-card>
                            <v-card-text>
                                <v-container class="px-0 ml-3" style="display: inline">Days Show: </v-container>
                                <v-edit-dialog
                                        :return-value.sync="htcDaysShow"
                                        lazy
                                > {{ htcDaysShow }}
                                    <v-icon color="teal" class="pointer">edit</v-icon>
                                    <v-text-field style="max-width: 30px"
                                                  slot="input"
                                                  v-model="htcDaysShow"
                                                  label="Edit"
                                                  single-line
                                                  :rules="numberRule"
                                                  v-on:keyup="htcDaysShow = (validNumbers(htcDaysShow, $event)?htcDaysShow:'')"
                                                  required
                                                  @change="setHtcDaysShow"
                                    ></v-text-field>
                                </v-edit-dialog>
                            </v-card-text>
                        </v-card>
                    </v-expansion-panel-content>
                    <v-expansion-panel-content>
                        <div slot="header">LS Tour</div>
                        <v-card>
                            <v-card-text
                                <v-container class="px-0 ml-3" style="display: inline">Days Show: </v-container>
                                <v-edit-dialog
                                        :return-value.sync="lsDaysShow"
                                        lazy
                                > {{ lsDaysShow }}
                                    <v-icon color="teal" class="pointer">edit</v-icon>
                                    <v-text-field style="max-width: 30px"
                                                  slot="input"
                                                  v-model="lsDaysShow"
                                                  label="Edit"
                                                  single-line
                                                  :rules="numberRule"
                                                  v-on:keyup="lsDaysShow = (validNumbers(lsDaysShow, $event)?lsDaysShow:'')"
                                                  required
                                                  @change="setLsDaysShow"
                                    ></v-text-field>
                                </v-edit-dialog>
                            </v-card-text>
                        </v-card>
                    </v-expansion-panel-content>
                    <v-expansion-panel-content>
                        <div slot="header">Transfer</div>
                        <v-card>
                            <v-card-text>
                                <v-container class="px-0 ml-3" style="display: inline">Days Show:</v-container>
                                <v-edit-dialog
                                        :return-value.sync="transferDaysShow"
                                        lazy
                                > {{ transferDaysShow }} <v-icon color="teal" class="pointer">edit</v-icon>
                                    <v-text-field style="max-width: 30px"
                                                  slot="input"
                                                  v-model="transferDaysShow"
                                                  label="Edit"
                                                  single-line
                                                  :rules="numberRule"
                                                  v-on:keyup="transferDaysShow = (validNumbers(transferDaysShow, $event)?transferDaysShow:'')"
                                                  required
                                                  @change="setTransferDaysShow"
                                    ></v-text-field>
                                </v-edit-dialog>
                            </v-card-text>
                        </v-card>
                    </v-expansion-panel-content>
                </v-expansion-panel>
            </v-container>
        </v-card>
    </v-container>
</template>
<script>
    import invalidNumber from '../../utils/numberValidate';
    import {API_HOST, API_PATH} from '../../config/_entrypoint';

    export default {
        data() {
            return {
                htcDaysShow: "",
                lsDaysShow: "",
                transferDaysShow: "",
                numberRule: [
                    v => !!v || ''
                ],
                loading: false,
                snackbar: false,
                snackbarText: '',
                snackbarColor: 'success',
                flag: false,
            }
        },
        computed: {
        },
        watch: {
        },
        methods: {
            setTransferDaysShow(val){
                if( this.transferDaysShow !=0 && this.transferDaysShow != ""){
                    let link = API_HOST + API_PATH + '/change-transfer-days-show';
                    this.loading = true;
                    fetch(link, {
                        method: 'POST',
                        credentials: "same-origin",
                        body: JSON.stringify({'days': val})
                    })
                        .then(response => {
                            this.loading = false;
                        });

                }
            },
            setHtcDaysShow(val) {
                if (this.htcDaysShow != 0 && this.htcDaysShow != "") {
                    let link = API_HOST + API_PATH + '/change-days-show';
                    this.loading = true;
                    fetch(link, {
                        method: 'POST',
                        credentials: "same-origin",
                        body: JSON.stringify({'days': this.htcDaysShow})
                    }).then(() => {this.loading = false})

                }
            },
            setLsDaysShow(val) {
                if (this.lsDaysShow != 0 && this.lsDaysShow != "") {
                    let link = API_HOST + API_PATH + '/change-days-show/ls';
                    this.loading = true;
                    fetch(link, {
                        method: 'POST',
                        credentials: "same-origin",
                        body: JSON.stringify({'days': this.lsDaysShow})
                    })
                        .then(response => {
                            this.loading = false;
                        });

                }
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
        },
        created() {

            let link = API_HOST + API_PATH + '/tour-days-shows';
            this.loading = true;
            fetch(link, {
                method: 'GET',
                credentials: "same-origin",
            })
                .then(response => response.json())
                .then(response => {
                    this.loading = false;
                    this.htcDaysShow = response?response:7;
                });
            link = API_HOST + API_PATH + '/tour-days-shows/ls';
            this.loading = true;
            fetch(link, {
                method: 'GET',
                credentials: "same-origin",
            })
                .then(response => response.json())
                .then(response => {
                    this.loading = false;
                    this.lsDaysShow = response?response:7;
                });
            link = API_HOST + API_PATH + '/transfer-days-shows';
            this.loading = true;
            fetch(link,     {
                method: 'GET',
                credentials: "same-origin",
            })
                .then(response => response.json())
                .then(response => {
                    this.loading = false;
                    this.transferDaysShow = response;
                });
        }
    }
</script>
