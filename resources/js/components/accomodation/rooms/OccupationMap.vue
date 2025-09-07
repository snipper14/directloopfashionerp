<template>
    <div class="container" data-app>
      <div class="row d-flex justify-content-between mr-2"><v-btn
                class="ma-2 v-btn-primary ml-2 "
                @click="$router.push('room_select')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
            <div>
             <h5 >Door <span class="badge badge-success">{{room_data.room.door_name}}</span></h5>
            </div>
            
             <div>
             <h5 >Floor <span class="badge badge-success">{{room_data.room.floor_no}}</span></h5>
            </div>
             <div>
             <h5 >Bed <span class="badge badge-success">{{room_data.room.qty_bed}}</span></h5>
            </div>
            <div>
             <h5 >Standard <span class="badge badge-success">{{room_data.room.room_standard.name}}</span></h5>
            </div>
            </div>
       <div class="row">
          <div class="col-4">
         
            <button
                class="btn btn-primary btn-sm center"
                @click="modal_guest = true"
            >
                <Icon type="md-add" />Add Guest
            </button>
            <div>
                <label for="">Select Guest</label>
                <div class="form-group">
                    <treeselect
                        v-model="form_data.guest_id"
                        :multiple="false"
                        :options="guest_select_data"
                        :show-count="true"
                        placeholder="Select "
                    />
                </div>
            </div>
            <div>
              <label for="">nAME</label>
            </div>
        </div>
        <div class="col-8">
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
                                <v-toolbar :color="selectedEvent.color" dark>
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
                                    <span v-html="selectedEvent.details"></span>
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
        <notifications group="foo" />
        <Modal v-model="modal_guest" width="860">
            <create-guest v-on:onSuccess="onSuccess" />
        </Modal>
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../guest/CreateGuest.vue";
export default {
    data: () => ({
        modal_guest: false,
        focus: "",
        room_data:null,
        isLoading:true,
        guest_select_data:null,
        type: "month",
        form_data:{
          guest_id:null,
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
            "blue",
            "indigo",
            "deep-purple",
            "cyan",
            "green",
            "orange",
            "blue-shade"
        ],
        names: [
            "Meeting",
            "Holiday",
            "PTO",
            "Travel",
            "Event",
            "Birthday",
            "Conference",
            "Party"
        ]
    }),
    components: {
        CreateGuest,
        Treeselect
    },
    created() {
      this.room_data=this.$store.state.room_data
      
        this.concurrentApiReq()
         this.$refs.calendar.checkChange();
       
    },
    methods: {
          async concurrentApiReq() {
            this.isLoading=false
            this.showLoader();
            const res = await Promise.all([
                this.fetchGuests(),
               
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
           async fetchGuests() {
          this.isLoading ?this.showLoader():"";
            const res = await this.getApi("data/guest/fetch", "");
            this.isLoading ?this.hideLoader():"";
            if(res.status==200){
            this.guest_data=res.data
            this.guest_select_data=this.modifyProductKey()
            }else{
                this.swr('Server error could not fetch employees')
            }
        },
              modifyProductKey() {
            let modif = this.guest_data.map(obj => {
                return {
                    id: obj.id,
                    label:
                        obj.name 
                        
                       
                };
            });
            return modif;
        },
        onSuccess() {
            this.modal_guest = false;
            this.isLoading=true
            this.fetchGuests()
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
            const events = [];

            const min = new Date(`${start.date}T00:00:00`);
            const max = new Date(`${end.date}T23:59:59`);
            const days = (max.getTime() - min.getTime()) / 86400000;
            const eventCount = this.rnd(days, days + 20);

            for (let i = 0; i < eventCount; i++) {
                const allDay = this.rnd(0, 3) === 0;
                const firstTimestamp = this.rnd(min.getTime(), max.getTime());
                const first = new Date(
                    firstTimestamp - (firstTimestamp % 900000)
                );
                const secondTimestamp = this.rnd(2, allDay ? 288 : 8) * 900000;
                const second = new Date(first.getTime() + secondTimestamp);

                events.push({
                    name: this.names[this.rnd(0, this.names.length - 1)],
                    start: first,
                    end: second,
                    color: this.colors[this.rnd(0, this.colors.length - 1)],
                    timed: !allDay
                });
            }

            this.events = events;
         
        },
        rnd(a, b) {
            return Math.floor((b - a + 1) * Math.random()) + a;
        }
    }
};
</script>
<style scoped></style>
