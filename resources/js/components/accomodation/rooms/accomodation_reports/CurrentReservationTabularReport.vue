<template>
    <div class="container row" data-app>
        <div class="row" v-if="active.dashboard">
            <div class="col-2"><RoomReportsNav /></div>
            <div class="col-10">
                <div class="row" v-if="isReadPermitted && isDisplayPermitted">
                    <div class="col-12">
                        <div class="d-flex justify-content-around">
                            <button
                                @click="
                                    $router.push(
                                        'current_reservations_calendar'
                                    )
                                "
                                class="btn btn-primary btn-sm"
                            >
                                View Calendar Report
                            </button>
                            <h4><b> Reservations</b></h4>
                        </div>
                        <hr />
                        <div class="d-flex justify-content around mt-3">
                            <div class="form-group">
                                <label> From Datetime *</label>

                                <datetime
                                    format="YYYY-MM-DD H:i:s"
                                    width="300px"
                                    v-model="params.from"
                                ></datetime>
                            </div>
                            <div class="">
                                <label> To Datetime *</label>
                                <datetime
                                    format="YYYY-MM-DD H:i:s"
                                    width="300px"
                                    v-model="params.to"
                                ></datetime>
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm mt-3"
                                    @click="concurrentApiReq()"
                                >
                                    Filter
                                </button>
                            </div>
                        </div>
                        <br />
                        <button
                            class="btn btn-info"
                            @click="toggleFilterOptions"
                        >
                            More filter options
                        </button>
                        <button
                            class="btn btn-info"
                            @click="clearFilterOptions"
                        >
                            Fetch All
                        </button>

                        <div class="row mt-3" v-if="show_filter_options">
                            <div class="col-3">
                                <search-guest-component
                                    v-on:guestSearchSelected="
                                        guestSearchSelected
                                    "
                                />
                            </div>
                            <div class="col-3">
                                <label for="">Search All</label>
                                <input
                                    type="text"
                                    placeholder="Search"
                                    v-model="search"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-3">
                                <label for=""> Search Rooms</label>
                                <treeselect
                                    style="width: 200px"
                                    :load-options="fetchRooms"
                                    :options="room_select_data"
                                    :auto-load-root-options="false"
                                    v-model="search_params.room_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select Room"
                                />
                            </div>

                            <div class="col-3">
                                <label for="">Occupation Status</label>
                                <Select
                                    name=""
                                    style="width: 200px"
                                    v-model="search_params.clear_status"
                                    id=""
                                >
                                    <Option value="" selected
                                        >Select All</Option
                                    >
                                    <Option value="occupied">occupied</Option>
                                    <Option value="cleared">cleared</Option>
                                </Select>
                            </div>
                        </div>

                        <div class="row mt-3" v-if="show_filter_options">
                            <div class="col-3">
                                <div class="d-flex flex-column">
                                    <label>Checkin From Date *</label>
                                    <date-picker
                                        v-model="params.checkin_from"
                                        valueType="format"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <label>Checkin to date *</label>
                                <date-picker
                                    v-model="params.checkin_to"
                                    valueType="format"
                                />
                            </div>
                            <div class="col-2 center">
                                <button
                                    class="btn btn-primary btn-sm"
                                    @click="concurrentApiReq()"
                                >
                                    Filter
                                </button>
                            </div>
                            <div class="col-3">
                                <SearchGuestCO
                                    ref="SearchGuestCO"
                                    v-on:guestCOSearchSelected="
                                        guestCOSearchSelected
                                    "
                                />
                            </div>
                        </div>
                        <div class="row mt-3" v-if="show_filter_options">
                            <div class="col-3">
                                <div class="form-group">
                                    <label>Checkout from Date *</label>
                                    <date-picker
                                        v-model="params.checkout_from"
                                        valueType="format"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <label>Checkout to date *</label>
                                <date-picker
                                    v-model="params.checkout_to"
                                    valueType="format"
                                />
                            </div>
                            <div class="col-2 center">
                                <button
                                    class="btn btn-primary btn-sm"
                                    @click="concurrentApiReq()"
                                >
                                    Filter
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content around mt-3">
                            <span class="badge badge-secondary totals-badge"
                                >Gross Amount:
                                {{ cashFormatter(sum_total) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Tax:
                                {{ cashFormatter(sum_tax_amount) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Credit Note:
                                {{ cashFormatter(sum_credit_note_total) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Net Amount:
                                {{
                                    cashFormatter(
                                        sum_total - sum_credit_note_total
                                    )
                                }}</span
                            >
                        </div>
                        <span class="badge badge-pill pending-payment"
                            >Pending Pay</span
                        >
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Guest</th>
                                        <th>Phone</th>
                                        <th scope="col">Room</th>
                                        <th scope="col">Package</th>
                                        <th>Price</th>
                                        <th>Tax</th>
                                        <th scope="col">Total</th>

                                        <th scope="col">Amt Paid</th>
                                        <th scope="col">Amt Pending</th>
                                        <th>Credit N.</th>
                                        <th>From</th>
                                        <th>to</th>
                                        <th>Checkout Date</th>
                                        <th>From</th>
                                        <th>Receptionist</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in resevation_data.data"
                                        :key="i"
                                        :class="{
                                            'pending-payment':
                                                data.total - data.amount_paid >
                                                0,
                                        }"
                                    >
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editBooking(data)
                                                "
                                                >{{ data.name }}</router-link
                                            >
                                        </td>
                                        <td>{{ data.phone }}</td>

                                        <td>
                                            {{ data.room.door_name }}
                                        </td>
                                        <td>
                                            {{ data.room_package.name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.tax_amount) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total) }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(data.amount_paid)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total -
                                                        data.amount_paid
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.credit_note_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ data.from }}
                                        </td>
                                        <td>
                                            {{ data.to }}
                                        </td>
                                        <td>
                                            {{ data.checkout_date }}
                                        </td>
                                        <td>
                                            {{ data.guest_company.name }}
                                        </td>
                                        <td>
                                            {{ data.user.name }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="resevation_data !== null"
                            v-bind:results="resevation_data"
                            v-on:get-page="fetchReservations"
                        ></Pagination>
                        Items Count {{ resevation_data.total }}
                        <div class="row">
                            <div
                                class="col-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
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
                                <div
                                    class="d-flex mt-2 justify-content-between"
                                >
                                <div>
                                    <button
                                        class="btn btn-primary btn-sm"
                                        @click="downloadReport"
                                    >
                                        Generate Report
                                    </button>
                                    </div>
                                    <button
                                        class="btn btn-info btn-sm"
                                        @click="fetchGroupedInhouseSummary"
                                    >
                                        Grouped Report
                                    </button>
                                     <button
                                        class="btn btn-primary btn-sm"
                                        @click="downloadPaymentReport"
                                    >
                                        Payments Report
                                    </button>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <notifications group="foo" />
        <update-room-booking
            v-if="active.edit_bookings"
            :edit_data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        />
        <print-reservation-grouped-report
            v-if="grouped_data && active.grouped_data"
            v-on:dashboard-active="setActiveRefresh"
            ref="PrintReservationGroupedReport"
            :to="params.to"
            :from="params.from"
            :grouped_data="grouped_data"
        />
    </div>
</template>
<script>
import datetime from "vuejs-datetimepicker";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../../guest/CreateGuest.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import UpdateRoomBooking from "../UpdateRoomBooking.vue";
import _ from "lodash";
import RoomReportsNav from "../RoomReportsNav.vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";
import SearchGuestComponent from "../room_components/SearchGuestComponent.vue";
import SearchGuestCO from "../room_components/SearchGuestCO.vue";
import PrintReservationGroupedReport from "./PrintReservationGroupedReport.vue";

export default {
    data: () => ({
        active: {
            dashboard: true,
            edit_bookings: false,
            grouped_data: false,
        },
        grouped_data: null,
        show_filter_options: false,
        edit_data: null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        resevation_data: [],
        room_select_data: null,
        sum_total: 0,
        sum_tax_amount: 0,
        sum_credit_note_total: 0,
        form_data: {
            guest_id: null,
        },
        format: "YYYY-MM-DD HH:mm:ss",
        search: "",
        search_params: {
            room_id: null,
            guest_id: null,
            clear_status: null,
            guest_company_id: null,
        },
        params: {
            to: null,
            from: null,
            checkin_to: null,
            checkin_from: null,
            checkout_to: null,
            checkout_from: null,
        },
    }),
    components: {
        datetime,

        CreateGuest,
        Treeselect,
        Unauthorized,
        UpdateRoomBooking,
        Pagination,
        RoomReportsNav,
        VueCtkDateTimePicker,
        SearchGuestComponent,
        SearchGuestCO,
        PrintReservationGroupedReport,
    },
    created() {
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq();
            }, 1000),
        },
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async fetchGroupedInhouseSummary() {
            this.showLoader();
            const res = await this.getApi(
                "data/reservation/fetchGroupedInhouseSummary",
                {
                    params: {
                        ...this.search_params,
                        ...this.params,
                        search: this.search,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.grouped_data = res.data;
                this.setActive(this.active, "grouped_data");
                setTimeout(() => {
                    //   this.$refs.PrintReservationGroupedReport.printDocument();
                }, 1000);
            } else {
                this.servo();
            }
        },
        guestCOSearchSelected(val) {
            this.search_params.guest_company_id = val;
        },
        toggleFilterOptions() {
            this.show_filter_options = !this.show_filter_options;
        },
        clearFilterOptions() {
            this.search = "";
            Object.keys(this.search_params).forEach(
                (key) => (this.search_params[key] = null)
            );
            Object.keys(this.params).forEach(
                (key) => (this.params[key] = null)
            );

            this.concurrentApiReq();
        },
        async downloadReport() {
            this.showLoader();
            axios({
                url: "data/reservation/downLoadAccommodationReport",
                params: {
                    ...this.search_params,
                    ...this.params,
                    search: this.search,
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
                    "accommodation" + this.getTimeStamp() + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },
async downloadPaymentReport(){
    this.showLoader();
            axios({
                url: "data/reservation/downLoadAccommodationPaymentsReport",
                params: {
                    ...this.search_params,
                    ...this.params,
                    search: this.search,
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

        guestSearchSelected(val) {
            this.search_params.guest_id = val;
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
            const res = await this.getApi("data/reservation/fetch", {
                params: {
                    // status: "occupied",
                    ...this.search_params,
                    ...this.params,
                    search: this.search,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].house_keeper =
                        data_array[i].house_keeper.first_name +
                        " " +
                        data_array[i].house_keeper.last_name;

                    data_array[i].waiter = data_array[i].waiter.name;
                    data_array[i].user = data_array[i].user.name;

                    data_array[i].room = data_array[i].room.door_name;
                    data_array[i].room_package =
                        data_array[i].room_package.name;
                    data_array[i].guest = data_array[i].guest.name;
                    data_array[i].guest_company =
                        data_array[i].guest_company.name;
                }

                return data_array;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
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
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchReservations(1),
                this.fetchReservationsTotals(),
            ]).then(function (results) {
                return results;
            });
            this.isLoading ? this.hideLoader() : "";
        },

        async fetchReservations(page) {
            const res = await this.getApi("data/reservation/fetch", {
                params: {
                    page: page,

                    ...this.search_params,
                    search: this.search,
                    ...this.params,
                },
            });

            if (res.status == 200) {
                this.resevation_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

        async fetchReservationsTotals() {
            const res = await this.getApi("data/reservation/fetchTotals", {
                params: {
                    ...this.search_params,
                    search: this.search,
                    ...this.params,
                },
            });

            if (res.status == 200) {
                this.sum_total = res.data.sum_total;
                this.sum_tax_amount = res.data.sum_tax_amount;
                this.sum_credit_note_total = res.data.sum_credit_note_total;
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
table {
    font-family: "signika-light", sans-serif !important;
}
.pending-payment {
    background: red !important;
}
td {
    font-size: 0.8rem !important;
}
</style>
