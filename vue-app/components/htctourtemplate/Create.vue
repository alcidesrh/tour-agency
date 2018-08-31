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
                   v-show="notificationsLoading || createLoading">
        <v-progress-circular indeterminate :size="70" :width="3" color="success"></v-progress-circular>
      </v-container>
      <v-card-title>
        <v-container py-0 px-3>
          <span class="headline" v-for="item in code" :key="item.id">New {{item.name}} Tour Template</span>
          <v-btn style="top: 10px; color: rgba(0,0,0,.54);"
                 icon
                 absolute
                 top
                 right
                 @click="$router.push({name: 'HTCTourTemplateList'})">
            <v-icon>close</v-icon>
          </v-btn>
          <v-radio-group v-model="item.type">
            <v-radio
                    :key="1"
                    :label="'Group'"
                    value="group"
            ></v-radio>
            <v-radio
                    :key="2"
                    :label="'Private'"
                    value="private"
            ></v-radio>
            <v-radio
                    :key="3"
                    :label="'Tailor Made'"
                    value="tailor_made"
            ></v-radio>
          </v-radio-group>
        </v-container>
        <v-container py-0 v-show="item.type != ''">
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
                  <!--<v-text-field v-if="item.type == 'group'"-->
                          <!--slot="activator"-->
                          <!--label="Start Time"-->
                          <!--v-model="item.startTime"-->
                          <!--prepend-icon="access_time"-->
                          <!--required-->
                          <!--:rules="nameRule"-->
                          <!--readonly-->
                  <!--&gt;</v-text-field>-->
                  <!--<v-text-field v-else-->
                                <!--slot="activator"-->
                                <!--label="Start Time"-->
                                <!--v-model="item.startTime"-->
                                <!--prepend-icon="access_time"-->
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
                  <!--<v-text-field v-if="item.type == 'group'"-->
                          <!--slot="activator"-->
                          <!--label="End Time"-->
                          <!--v-model="item.endTime"-->
                          <!--prepend-icon="access_time"-->
                          <!--required-->
                          <!--:rules="nameRule"-->
                          <!--readonly-->
                  <!--&gt;</v-text-field>-->
                  <!--<v-text-field v-else-->
                                <!--slot="activator"-->
                                <!--label="End Time"-->
                                <!--v-model="item.endTime"-->
                                <!--prepend-icon="access_time"-->
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
                    Notifications:
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
            <v-layout row wrap class="mt-5">
              <v-expansion-panel>
                <v-expansion-panel-content>
                  <v-flex md12 title  style="max-width: 182px; padding: 0px" slot="header">
                    Extra Information
                  </v-flex>
                  <v-flex md12>
                    <wysiwyg v-model="item.description"></wysiwyg>
                  </v-flex>
                </v-expansion-panel-content>
              </v-expansion-panel>
            </v-layout>
            <v-container px-0>
              <v-layout row wrap>
                <v-flex md12 text-md-center>
                  <v-btn color="primary" @click="$router.push({name: 'HTCTourTemplateList'})">Cancel
                  </v-btn>
                  <v-btn color="primary" @click="save">Save</v-btn>
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
                    type: '',
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
        }
        ,
        computed: {
            ...mapGetters({
                errorCreate: 'htctourtemplate/create/error',
                createLoading: 'htctourtemplate/create/loading',
                notificationsLoading: 'htctourtemplate/list/loading',
                notifications: 'htctourtemplate/list/notifications',
                created: 'htctourtemplate/create/created',
                code: 'htctourtemplate/list/code'
            })
        }
        ,
        watch: {
            created(){
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

        }
        ,
        methods: {
            error(message) {
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            error(message) {
                this.snackbarColor = 'error';
                this.snackbarText = message;
                this.snackbar = true;
            },
            save() {
                if (!this.$refs.tourForm.validate()) {
                    this.$vuetify.goTo(0, {duration: 500})
                    return false;
                }

                let array = [], accommodation = false;
                this.notificationTour.forEach(function (item) {
                    if (item && typeof item === 'object')
                        array.push({code: item.code, days: item.days});
                    if(item.code == 'a')
                        accommodation = true;
                });
                this.item.notifications = array;

                this.$store.dispatch('htctourtemplate/create/create', this.item);
            }
        }
        ,
        created() {
                this.$store.dispatch('htctourtemplate/list/getHTCNotification');
            if(!this.code.length)
                this.$store.dispatch('htctourtemplate/list/getCode');

        }
    }
</script>
