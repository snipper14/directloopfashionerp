<template>
    <div class="container row" data-app>
        <div class="col-2"><RoomReportsNav /></div>
        <div class="col-10">
            <div class="row">
                <div class="col-12" v-if="active.dashboard">
                    <div class="d-flex justify-content-around">
                        <h4><b> Reservation Credit Notes</b></h4>
                    </div>
                    <hr />

                    <div class="d-flex justify-content around mt-3">
                        <div class="form-group">
                            <label> from Date *</label>
                            <date-picker
                                v-model="params.from"
                                valueType="format"
                            />
                        </div>
                        <div class="">
                            <label> to date *</label>
                            <date-picker
                                v-model="params.to"
                                valueType="format"
                            />
                        </div>
                        <div>
                            <button
                                class="btn btn-primary btn-sm mt-2"
                                @click="concurrentApiReq()"
                            >
                                Filter
                            </button>
                        </div>
                        <div class="form-group ml-4">
                            <input type="text" placeholder="Search" v-model="search" class="form-control">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Credit No</th>
                                    <th>Details</th>
                                    <th scope="col">Created at</th>
                                    <th scope="col">Credit Amount</th>
                              <th>Customer</th>

                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in credit_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.credit_no }}
                                    </td>
                                    <td>{{ data.details }}</td>

                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>
                                    <td>{{ cashFormatter(data.total) }}</td>
                                    <td>{{data.room_reservation.name}}</td>
                                    <td>
                                        {{ data.user.name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        v-if="credit_data !== null"
                        v-bind:results="credit_data"
                        v-on:get-page="fetchTransfers"
                    ></Pagination>
                    Items Count {{ credit_data.total }}
                    <div class="row">
                        <div class="col-4 d-flex">
                            <export-excel
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
                        </div>
                    </div>
                </div>
              
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";

import _ from "lodash";
import RoomReportsNav from "../RoomReportsNav.vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";

export default {
    data: () => ({
        active: {
            dashboard: true,
        },
        search:'',
        details_modal: false,
        details_data: null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        credit_data: [],
        room_select_data: null,
        sum_total: 0,
        sum_tax_amount: 0,
        form_data: {
            guest_id: null,
        },
        format: "YYYY-MM-DD HH:mm:ss",
        search: "",

        params: {
            to: null,
            from: null,
        },
    }),
    components: {
        
        Treeselect,
        Unauthorized,
      
        Pagination,
        RoomReportsNav,
        VueCtkDateTimePicker,
    },
    created() {
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
      
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/reservation_credit_note/fetch", {
                params: {
                    ...this.params,
                    search: this.search,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].room_reservation = data_array[i].room_reservation.name;
               
                 data_array[i].user = data_array[i].user.name;
               }

                return data_array;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([this.fetchTransfers(1)]).then(
                function (results) {
                    return results;
                }
            );
            this.hideLoader();
        },

        async fetchTransfers(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/reservation_credit_note/fetch",
                {
                    params: {
                        page: page,

                        search: this.search,
                        ...this.params,
                    },
                }
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.credit_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
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
