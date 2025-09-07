<template>
    <div class="container" data-app>
        <div class="row" v-if="isReadPermitted">
            <div class="col-10">
                <div class="d-flex justify-content-around">
                    <button
                        @click="$router.push('current_reservations_tabular')"
                        class="btn btn-primary btn-sm"
                    >
                        View Tabular Report
                    </button>
                    <h4><b> Reservations</b></h4>
                </div>
                <hr>
                <div class="d-flex justify-content-around mt-3">
                    <div class="form-group">
                        
                        <treeselect
                         style="width:300px"
                            :load-options="fetchGuests"
                            :options="guest_select_data"
                            :auto-load-root-options="false"
                            v-model="search_params.guest_id"
                            :multiple="false"
                            :show-count="true"
                            placeholder="Select Guest"
                        />
                    </div>
                     <div class="form-group">
                       
                        <treeselect
                         style="width:300px"
                            :load-options="fetchRooms"
                            :options="room_select_data"
                            :auto-load-root-options="false"
                            v-model="search_params.room_id"
                            :multiple="false"
                            :show-count="true"
                            placeholder="Select Room"
                        />
                    </div>
                     <div class="form-group">
                        <label for="">Occupation status</label>
                        <Select name=""   style="width:300px"  v-model="search_params.clear_status" id="">
                             <Option value="" selected>Select All</Option>
                            <Option value="occupied">occupied</Option>
                            <Option value="cleared">cleared</Option>
                        </Select>
                    </div>
                </div>
                <v-row class="fill-height">
                    <v-col>
                        <v-sheet height="64">
                            <v-toolbar flat>
                                <v-btn
                                    outlined
                                    class="mr-4"
                                    color="grey darken-2"
                                    @click="setToday"
                                >
                                    Today
                                </v-btn>
                                <v-btn
                                    fab
                                    text
                                    small
                                    color="grey darken-2"
                                    @click="prev"
                                >
                                    <v-icon small>
                                        mdi-chevron-left
                                    </v-icon>
                                </v-btn>
                                <v-btn
                                    fab
                                    text
                                    small
                                    color="grey darken-2"
                                    @click="next"
                                >
                                    <v-icon small>
                                        mdi-chevron-right
                                    </v-icon>
                                </v-btn>
                                <v-toolbar-title v-if="$refs.calendar">
                                    {{ $refs.calendar.title }}
                                </v-toolbar-title>
                                <v-spacer></v-spacer>
                                <v-menu bottom right>
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-btn
                                            outlined
                                            color="grey darken-2"
                                            v-bind="attrs"
                                            v-on="on"
                                        >
                                            <span>{{ typeToLabel[type] }}</span>
                                            <v-icon right>
                                                mdi-menu-down
                                            </v-icon>
                                        </v-btn>
                                    </template>
                                    <v-list>
                                        <v-list-item @click="type = 'day'">
                                            <v-list-item-title
                                                >Day</v-list-item-title
                                            >
                                        </v-list-item>
                                        <v-list-item @click="type = 'week'">
                                            <v-list-item-title
                                                >Week</v-list-item-title
                                            >
                                        </v-list-item>
                                        <v-list-item @click="type = 'month'">
                                            <v-list-item-title
                                                >Month</v-list-item-title
                                            >
                                        </v-list-item>
                                        <v-list-item @click="type = '4day'">
                                            <v-list-item-title
                                                >4 days</v-list-item-title
                                            >
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                            </v-toolbar>
                        </v-sheet>
                        <v-sheet height="600">
                            <v-calendar
                                ref="calendar"
                                v-model="focus"
                                color="primary"
                                :events="events"
                                :event-color="getEventColor"
                                :type="type"
                                @click:event="showEvent"
                                @click:more="viewDay"
                                @click:date="viewDay"
                                @change="updateRange"
                            ></v-calendar>
                            <v-menu
                                v-model="selectedOpen"
                                :close-on-content-click="false"
                                :activator="selectedElement"
                                offset-x
                            >
                                <v-card
                                    color="grey lighten-4"
                                    min-width="350px"
                                    flat
                                >
                                    <v-toolbar
                                        :color="selectedEvent.color"
                                        dark
                                    >
                                        <v-btn icon>
                                            <v-icon>mdi-pencil</v-icon>
                                        </v-btn>
                                        <v-toolbar-title
                                            v-html="selectedEvent.name"
                                        ></v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-btn icon>
                                            <v-icon>mdi-heart</v-icon>
                                        </v-btn>
                                        <v-btn icon>
                                            <v-icon>mdi-dots-vertical</v-icon>
                                        </v-btn>
                                    </v-toolbar>
                                    <v-card-text>
                                        <span
                                            v-html="selectedEvent.details"
                                        ></span>
                                    </v-card-text>
                                    <v-card-actions>
                                        <v-btn
                                            text
                                            color="secondary"
                                            @click="selectedOpen = false"
                                        >
                                            Cancel
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-menu>
                        </v-sheet>
                    </v-col>
                </v-row>
            </div>
        </div>
        <div v-else>
            <unauthorized />
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../guest/CreateGuest.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
export default {
    data: () => ({
        modal_guest: false,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        reservation_data: [],
        room_select_data:null,
        type: "month",
        form_data: {
            guest_id: null,
           
        },
        search_params: {
            room_id: null,
            guest_id:null,
            clear_status:null,
        },
        typeToLabel: {
            month: "Month",
            week: "Week",
            day: "Day",
            "4day": "4 Days"
        },
        selectedEvent: {},
        selectedElement: null,
        selectedOpen: false,
        events: [],
        colors: [
            "red",
            "green",
            "indigo",
            "deep-purple",
            "cyan",
            "orange",
            "blue-shade"
        ]
    }),
    components: {
        CreateGuest,
        Treeselect,
        Unauthorized
    },
    created() {
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch:{
        search_params:{
            deep:true,
            handler:_.debounce(function(val,old){
             this.concurrentApiReq();
            },500)
        }
    },
    methods: {
             async fetchGuests() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/guest/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.guest_data = res.data;
                this.guest_select_data = this.modifyGuestKey();
               
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
          async fetchRooms() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/rooms/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.room_select_data = this.modifyRoomKey(res.data);
               
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
             
                this.fetchReservations()
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        
        async fetchReservations() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/reservation/fetch", {
                params: {
                    //status: "occupied",
                    ...this.search_params
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.reservation_data = res.data;
                this.events = this.modifyRservationKey();
              
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
        modifyRservationKey() {
            let modif = this.reservation_data.map(obj => {
                return {
                    start: obj.from,
                    end: obj.to,
                    name: "Guest: "+obj.name+"/ Room:"+obj.room.door_name,
                    color: this.colors[
                        Math.floor(Math.random() * this.colors.length)
                    ]
                };
            });
            return modif;
        },
        modifyGuestKey() {
            let modif = this.guest_data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.name
                };
            });
            return modif;
        },
          modifyRoomKey(data) {
            let modif = data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.door_name
                };
            });
            return modif;
        },
        onSuccess() {
            this.modal_guest = false;
            this.isLoading = true;
            this.fetchGuests();
        },
        viewDay({ date }) {
            this.focus = date;
            this.type = "day";
        },
        getEventColor(event) {
            return event.color;
        },
        setToday() {
            this.focus = "";
        },
        prev() {
            this.$refs.calendar.prev();
        },
        next() {
            this.$refs.calendar.next();
        },
        showEvent({ nativeEvent, event }) {
            const open = () => {
                this.selectedEvent = event;
                this.selectedElement = nativeEvent.target;
                requestAnimationFrame(() =>
                    requestAnimationFrame(() => (this.selectedOpen = true))
                );
            };

            if (this.selectedOpen) {
                this.selectedOpen = false;
                requestAnimationFrame(() =>
                    requestAnimationFrame(() => open())
                );
            } else {
                open();
            }

            nativeEvent.stopPropagation();
        },
        updateRange({ start, end }) {
            //      this.events = this.modifyRservationKey();
        
        },
        rnd(a, b) {
            return Math.floor((b - a + 1) * Math.random()) + a;
        }
    }
};
</script>
<style scoped></style>
