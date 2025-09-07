<template>
    <div class="container row" data-app>
        <div class="col-10">
            <div class="row" v-if="isReadPermitted">
                <div class="col-12" v-if="active.dashboard">
                    <div class="d-flex justify-content-around">
                        <h4><b>Conference Report</b></h4>
                    </div>
                    <hr />

                    <div class="d-flex justify-content-around mt-3">
                        <span class="badge badge-secondary totals-badge"
                            >Total Amount: {{ cashFormatter(sum_total) }}</span
                        >

                        <div class="form-group ml-4">
                            <label> Date *</label
                            ><date-picker
                                v-model="curr_date"
                                type="date-picker"
                            ></date-picker>
                            <button
                                class="btn btn-primary btn-sm"
                                @click="concurrentApiReq()"
                            >
                                filter
                            </button>
                        </div>
                        <input
                            style="width: 250px"
                            type="text"
                            placeholder="Search"
                            v-model="search"
                            class="form-control"
                        />
                    </div>

                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Conference Room</th>
                                    <th>Booking Date</th>
                                    <th scope="col">Cust Name</th>
                                    <th>Contacts/Email</th>
                                    <th scope="col">Amount Paid</th>
                                    <th>Pay method</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in booking_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.conference_room }}
                                    </td>
                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>

                                    <td>
                                        {{ data.company_name }}
                                    </td>
                                    <td>{{ data.phone }}/{{ data.email }}</td>
                                    <td>
                                        {{ cashFormatter(data.total_paid) }}
                                    </td>
                                    <td>
                                        {{data.pay_method}}
                                    </td>
                                    <td>
                                        {{ data.user.name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        v-if="booking_data !== null"
                        v-bind:results="booking_data"
                        v-on:get-page="fetchConferenceBookings"
                    ></Pagination>
                    Items Count {{ booking_data.total }}
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
                                v-if="isDownloadPermitted"
                                class="btn btn-secondary btn-sm"
                                @click="printPdf()"
                            >
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else>
                <unauthorized />
            </div>
        </div>
        <div class="col-12">
            <print-booking-component
                v-if="all_booking_data"
                :all_booking_data="all_booking_data"
                ref="printBookingComponent"
            />
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";

import _ from "lodash";

import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";
import PrintBookingComponent from "./PrintBookingComponent.vue";

export default {
    data: () => ({
        active: {
            dashboard: true,
            edit_bookings: false,
        },
        curr_date: null,
        all_booking_data: null,
        booking_data: null,
        isLoading: true,

        sum_total: 0,

        format: "YYYY-MM-DD HH:mm:ss",
        search: "",
    }),
    components: {
        Treeselect,
        Unauthorized,
        Pagination,
        VueCtkDateTimePicker,
        PrintBookingComponent,
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
                this.fetchConferenceBookings(1);
            }, 500),
        },
    },
    methods: {
        async printPdf() {
            const res = await this.fetchAllApiCall();
            if (res.status == 200) {
                this.all_booking_data = res.data;

                setTimeout(() => {
                    this.$refs.printBookingComponent.printBill();
                }, 1000);
            } else {
                this.servo();
            }
        },

        async fetchExcel() {
            const res = await this.fetchAllApiCall();
            if (res.status == 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].user = data_array[i].user.name;
                }

                return data_array;
            } else {
                this.servo();
            }
        },
        async fetchAllApiCall() {
            this.showLoader();
            const res = await this.getApi("data/conference/fetch", {
                params: {
                    // status: "occupied",
                    curr_date: this.formatDate(this.curr_date),

                    search: this.search,
                },
            });
            this.hideLoader();
            return res;
        },
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchConferenceBookings(1),
                this.fetchConferenceBookingsTotals(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },

        async fetchConferenceBookings(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/conference/fetch", {
                params: {
                    curr_date: this.formatDate(this.curr_date),
                    page: page,
                    search: this.search,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.booking_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

        async fetchConferenceBookingsTotals() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/conference/fetchTotals", {
                params: {
                    curr_date: this.formatDate(this.curr_date),

                    search: this.search,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.sum_total = res.data.sum_total_paid;
                //  this.sum_tax_amount = res.data.sum_tax_amount;
            } else {
                this.servo();
            }
        },
    },
};
</script>
<style scoped>
.small-tr > td {
    font-family: "signika-light", sans-serif !important;
}
</style>
