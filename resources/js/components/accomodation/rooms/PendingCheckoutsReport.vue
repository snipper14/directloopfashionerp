<template>
    <div class="container row" data-app>
        <div class="row"  v-if="active.dashboard">
        <div class="col-2" ><RoomReportsNav /></div>
        <div class="col-10">
            <div class="row" v-if="isReadPermitted">
                <div class="col-12">
                    <div class="d-flex justify-content-around">
                       
                        <h4><b> Pending Checkout Report</b></h4>
                    </div>
                    <hr />
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
                            <input
                                type="text"
                                placeholder="Search"
                                v-model="search"
                                class="form-control"
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
                     
                    </div>
                
                   
                   

                    <div class="table-responsive">
                         <span class="badge badge-pill pending-payment"
                    >Pending Pay</span
                >
                        <table
                            class="table table-sm table-striped table-bordered  "
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Guest</th>
                                    <th>Phone</th>
                                    <th scope="col">Room</th>
                                    <th scope="col">Package</th>
                                    <th>Price</th>

                                    <th scope="col">Total</th>
                                    <th>Tax</th>
                                  
                                    
                                    <th>From</th>
                                    <th>to</th>
                                  
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in resevation_data.data"
                                    :key="i"
                                >
                                    <td
                                    :class="{
                                    'pending-payment':
                                        data.total - data.amount_paid > 0,
                                }"
                                    >
                                       {{ data.name }}
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
                                        {{ cashFormatter(data.total) }}
                                    </td>
                                     <td>
                                        {{ cashFormatter(data.tax_amount) }}
                                    </td>
                                 
                                    <td>
                                        {{ data.from }}
                                    </td>
                                    <td>
                                        {{ data.to }}
                                    </td>
                                   <td>
                                <router-link
                                    to="#"
                                    
                                    @click.native="viewGuestCheckout(data)"
                                >
                                    Checkout
                                </router-link>
                            </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        v-if="resevation_data !== null"
                        v-bind:results="resevation_data"
                        v-on:get-page="fetchPendingCheckout"
                    ></Pagination>
                    Items Count {{ resevation_data.total }}
                    <div class="row">
                        <div class="col-4 d-flex" v-if="isDownloadPermitted">
                            <export-excel
                                v-if="isDownloadPermitted"
                                class="btn btn-default btn-export ml-2 "
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
                                class="btn btn-default btn-export ml-2 "
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

            <div v-else>
                <unauthorized />
            </div>
        </div>
        </div>
       
            <checkout-guest
                v-if="active.checkout_guest_details"
                :checkout_data="checkout_data"
                v-on:dashboard-active="setActiveRefresh"
            />
        <notifications group="foo" />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../guest/CreateGuest.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";

import _ from "lodash";
import RoomReportsNav from "./RoomReportsNav.vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";
import CheckoutGuest from './CheckoutGuest.vue';

export default {
    data: () => ({
        active: {
            dashboard: true,
            edit_bookings: false,
             checkout_guest_details: false,
        },
        checkout_data:null,
        edit_data: null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        resevation_data: [],
        room_select_data: null,
sum_total:0,
               sum_tax_amount:0,
        form_data: {
            guest_id: null
        },
        search: "",
        search_params: {
            room_id: null,
            guest_id: null,
            clear_status: null
        },
        params: {
            checkin_to: null,
            checkin_from: null,
            checkout_to:null,
            checkout_from:null,
        }
    }),
    components: {
        CreateGuest,
        Treeselect,
        Unauthorized,
      
        Pagination,
        RoomReportsNav,
        VueCtkDateTimePicker,
        CheckoutGuest
    },
    created() {
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function(val, old) {
                // this.search_params.to=this.formatDateTime( this.search_params.to)
                // this.search_params.from=this.formatDateTime( this.search_params.from)

                this.concurrentApiReq();
            }, 1000)
        },
        search: {
            handler: _.debounce(function(val, old) {
                this.isLoading = false;
                this.fetchPendingCheckout(1);
            }, 500)
        }
    },
    methods: {
          viewGuestCheckout(data) {
            this.checkout_data = data;
            this.setActive(this.active, "checkout_guest_details");
        },

        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq()
        },
        editBooking(data) {
            this.edit_data = data;
            this.setActive(this.active, "edit_bookings");
        },
        async fetchExcel() {
             this.showLoader() 
            const res = await this.getApi("data/reservation/fetchPendingCheckouts", {
                params: {
                    // status: "occupied",
                    ...this.search_params,
                    ...this.params,
                    search:this.search
                }
            });
          this.hideLoader() 
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
            const res = await Promise.all([this.fetchPendingCheckout(1)]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();
        },

        async fetchPendingCheckout(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/reservation/fetchPendingCheckouts", {
                params: {
                    page: page,

                    ...this.search_params,
                    search: this.search,
                    ...this.params
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.resevation_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
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
        }
    }
};
</script>
<style scoped>
table {
    font-family: "signika-light", sans-serif !important;
}
.pending-payment {
    background: red !important;
}
</style>
