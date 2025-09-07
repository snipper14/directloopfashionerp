<template>
    <div class="container row" data-app>
        <div class="col-2"><RoomReportsNav /></div>
        <div class="col-10">
            <div class="row" v-if="isReadPermitted">
                <div class="col-12" v-if="active.dashboard">
                    <div class="d-flex justify-content-around">
                        <h4><b>Current Occupation Report</b></h4>
                    </div>
                    <hr />

                    <div class="d-flex justify-content around mt-3">
                        <span class="badge badge-secondary totals-badge"
                            >Total Amount: {{ cashFormatter(sum_total) }}</span
                        >
                        
                        <div class="form-group ml-4">
                            <label> Date *</label
                            ><date-picker
                                v-model="curr_date"
                                type="datetime"
                            ></date-picker>
                            <button
                                class="btn btn-primary btn-sm"
                                @click="concurrentApiReq()"
                            >
                                filter
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Guest</th>
                                    <th>No Of Guests</th>
                                    <th>Phone</th>
                                    <th scope="col">Room</th>
                                    <th>Room Type</th>
                                    <th scope="col">Package</th>
                                    <th>From</th>
                                    <th>Days</th>
                                    <th scope="col">Amount</th>

                                    <th>Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in resevation_data"
                                    :key="i"
                                    :class="{
                                        'pending-payment':
                                            data.total - data.amount_paid > 0,
                                    }"
                                >
                                    <td>
                                       {{ data.name }}
                                    </td>
                                    <td>
                                        {{data.no_guest}}
                                    </td>
                                    <td>{{ data.phone }}</td>

                                    <td>
                                        {{ data.room.door_name }}
                                    </td>
                                    <td>
                                        {{ data.room.room_standard.name }}
                                    </td>
                                    <td>
                                        {{ data.room_package.name }}
                                    </td>
                                    <td>
                                        {{ data.guest_company.name }}
                                    </td>
                                    
                                    <td>
                                        {{data.qty}}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.price) }}
                                    </td>

                                    <td>{{ data.from + " - " + data.to }}</td>
                                    <td>{{ data.clear_status }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-8 d-flex" v-if="isDownloadPermitted">
                            <export-excel
                                v-if="isDownloadPermitted"
                                class="btn btn-default btn-export ml-2"
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                name="filename.xls"
                            >
                                <button class="btn btn-primary btn-sm">
                                    Export Excel
                                </button>
                            </export-excel>

                            <export-excel
                                v-if="isDownloadPermitted"
                                class="btn btn-default btn-export ml-2"
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="filename.xls"
                            >
                                <button class="btn btn-success btn-sm">
                                    Export CSV
                                </button>
                            </export-excel>
                            <button
                                class="btn btn-secondary btn-sm mr-2"
                                @click="downLoadPdf()"
                            >
                                Generate Daily Report
                            </button>
                            <button
                                class="btn btn-info btn-sm"
                                @click="downLoadPdf('house_keeping')"
                            >
                                House Keeping Report
                            </button>
                              <button
                                        class="btn btn-primary btn-sm ml-2"
                                        @click="downloadIncomeReport"
                                    >
                                        Daily Income Report
                                    </button>
                        </div>
                    </div>
                </div>
                <update-room-booking
                    v-if="active.edit_bookings"
                    :edit_data="edit_data"
                    v-on:dashboard-active="setActiveRefresh"
                />
            </div>

            <div v-else>
                <unauthorized />
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../guest/CreateGuest.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import UpdateRoomBooking from "./UpdateRoomBooking.vue";
import _ from "lodash";
import RoomReportsNav from "./RoomReportsNav.vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";

export default {
    data: () => ({
        active: {
            dashboard: true,
            edit_bookings: false,
        },
        curr_date: null,
        edit_data: null,
        house_keeping_report: null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        resevation_data: [],
        room_select_data: null,
        sum_total: 0,
        sum_tax_amount: 0,
        form_data: {
            guest_id: null,
        },
        format: "YYYY-MM-DD HH:mm:ss",
        search: "",
        search_params: {
            room_id: null,
            guest_id: null,
            clear_status: null,
        },
        params: {
            checkin_to: null,
            checkin_from: null,
            checkout_to: null,
            checkout_from: null,
        },
    }),
    components: {
        CreateGuest,
        Treeselect,
        Unauthorized,
        UpdateRoomBooking,
        Pagination,
        RoomReportsNav,
        VueCtkDateTimePicker,
    },
    created() {
        //   this.curr_date = this.getCurrentDate();
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                // this.search_params.to=this.formatDateTime( this.search_params.to)
                // this.search_params.from=this.formatDateTime( this.search_params.from)

                this.concurrentApiReq();
            }, 1000),
        },
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.fetchReservations(1);
            }, 500),
        },
    },
    methods: {
        async downloadIncomeReport(){
     this.showLoader();
            axios({
                url: "data/reservation/downLoadAccommodationDailyIncomeReport",
                params: {
                    curr_date: this.formatDateTime(this.curr_date),
                 
                },
                method: "GET",
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    "payment" + this.getTimeStamp() + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
},
        async downLoadPdf(house_keeping) {
            this.showLoader();
            axios({
                url: "data/reservation/downLoadDailyPdf",
                params: {
                    curr_date: this.formatDateTime(this.curr_date),
                    house_keeping_report: house_keeping,
                },
                method: "GET",
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    "daily accommodation" + this.getTimeStamp() + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },

        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        editBooking(data) {
            this.edit_data = data;
            this.setActive(this.active, "edit_bookings");
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/reservation/fetchDailyAccomodation",
                {
                    params: {
                        // status: "occupied",
                        curr_date: this.formatDateTime( this.curr_date),
                        ...this.search_params,
                        ...this.params,
                        search: this.search,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].house_keeper =
                        data_array[i].house_keeper.first_name +
                        " " +
                        data_array[i].house_keeper.last_name;

                    data_array[i].waiter = data_array[i].waiter.name;

                    data_array[i].room = data_array[i].room.door_name;
                    data_array[i].room_package =
                        data_array[i].room_package.name;
                    data_array[i].guest = data_array[i].guest.name;
                }

                return data_array;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchReservations(1),
                this.fetchReservationsTotals(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },

        async fetchReservations() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/reservation/fetchDailyAccomodation",
                {
                    params: {
                        curr_date:  this.formatDateTime(this.curr_date),
                        ...this.search_params,
                        search: this.search,
                        ...this.params,
                    },
                }
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.resevation_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

        async fetchReservationsTotals() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/reservation/fetchDailyAccomodationTotals",
                {
                    params: {
                        curr_date: this.formatDateTime( this.curr_date),
                        ...this.search_params,
                        search: this.search,
                        ...this.params,
                    },
                }
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.sum_total = res.data.sum_total;
                this.sum_tax_amount = res.data.sum_tax_amount;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

        modifyGuestKey() {
            let modif = this.guest_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        modifyRoomKey(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.door_name,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.small-tr > td {
    font-family: "signika-light", sans-serif !important;
}
.pending-payment {
    background: red !important;
}
</style>
