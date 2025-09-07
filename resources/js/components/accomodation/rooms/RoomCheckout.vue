<template>
    <div class="container" data-app>
        <div class="row" v-if="isReadPermitted">
            <div class="col-12" v-if="active.dashboard">
                <div class="d-flex justify-content-around">
                    <h4><b>Current Reservations Checkout</b></h4>
                </div>
                <hr />
                <div class="d-flex justify-content-around mt-3">
                    <div class="form-group">
                        <treeselect
                            style="width: 300px"
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
                            style="width: 300px"
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
                <span class="badge badge-pill pending-payment"
                    >Pending Pay</span
                >
                <table class="table table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Guest</th>
                            <th>Phone</th>
                            <th>ID</th>
                            <th scope="col">Room</th>
                            <th scope="col">Package</th>
                            <th>Price</th>

                            <th scope="col">Total</th>
                            <th scope="col">Paid Amount</th>
                            <th scope="col">Amt Pending</th>
                            <th>Credit</th>
                            <th>Days</th>
                            <th>From</th>
                            <th>to</th>
                            <th>User</th>
                            <th>options</th>
                            <th>Checkout</th>
                            <th>Cancel Reservation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="small-tr"
                            v-for="(data, i) in resevation_data.data"
                            :key="i"
                        >
                            <td
                               
                            >
                                {{ data.name }}
                            </td>
                            <td>
                                <p>{{ data.phone }}</p>
                            </td>
                            <td>{{ data.id_no }}</td>

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
                                {{ cashFormatter(data.amount_paid) }}
                            </td>
                            <td  :class="{
                                    'pending-payment':
                                        data.total - data.amount_paid > 0,
                                }">
                                {{
                                    cashFormatter(data.total - data.amount_paid)
                                }}
                            </td>
                            <td>
                                {{cashFormatter(data.credit_note_total)}}
                            </td>
                            <td>{{ data.qty }}</td>
                            <td>
                                {{ data.from }}
                            </td>
                            <td>
                                {{ data.to }}
                            </td>
                            <td>
                                {{ data.user.name }}
                            </td>
                            <td>
                                <div class="btn-group dropleft">
                                    <button
                                        type="button"
                                        class="btn btn-warning btn-sm dropdown-toggle"
                                        data-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false"
                                        v-if="isUpdatePermitted"
                                    >
                                        Options
                                    </button>
                                    <div class="dropdown-menu">
                                        <router-link
                                            class="dropdown-item"
                                            to="#"
                                            v-if="isUpdatePermitted"
                                            @click.native="
                                                extendDays(data)
                                            "
                                            >Update/Extend Days</router-link
                                        >
                                        <router-link
                                            class="dropdown-item"
                                            to="#"
                                            v-if="isUpdatePermitted"
                                            @click.native="
                                                transferOccupance(data)
                                            "
                                            >Transfer</router-link
                                        >

                                        <router-link
                                            class="dropdown-item"
                                            to="#"
                                            v-if="isAdmin"
                                            @click.native="
                                                addCreditNote(data)
                                            "
                                            >Add Credit Note</router-link
                                        >
                                    </div>
                                </div>
                            </td>
                            <td>
                                <router-link
                                    to="#"
                                    
                                    @click.native="viewGuestCheckout(data)"
                                >
                                    Checkout
                                </router-link>
                            </td>
                            <td>
                                <router-link
                                    to="#"
                                    v-if="isAdmin"
                                    class="danger"
                                    @click.native="
                                        deleteReservation(data.id, i)
                                    "
                                >
                                    Cancel
                                </router-link>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="row">
                    <div class="col-4 d-flex" v-if="isDownloadPermitted">
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
                    </div>
                </div>
            </div>

            <checkout-guest
                v-if="active.checkout_guest_details"
                :checkout_data="checkout_data"
                v-on:dashboard-active="setActiveRefresh"
            />
            <transfer-guest
                :checkout_data="checkout_data"
                v-on:dashboard-active="setActiveRefresh"
                v-if="active.transfer_guest"
            />
            <add-reservation-credit-note
             :checkout_data="checkout_data"
                v-on:dashboard-active="setActiveRefresh"
                v-if="active.credit_note"
            />
             <update-room-booking
                    v-if="active.edit_bookings"
                    :edit_data="edit_data"
                    v-on:dashboard-active="setActiveRefresh"
                />
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
import CheckoutGuest from "./CheckoutGuest.vue";
import _ from "lodash";
import TransferGuest from "./TransferGuest.vue";
import AddReservationCreditNote from './reservation_creditnote/AddReservationCreditNote.vue';
import UpdateRoomBooking from './UpdateRoomBooking.vue';
export default {
    data: () => ({
        active: {
            dashboard: true,
            checkout_guest: false,
            checkout_guest_details: false,
            transfer_guest: false,
            credit_note:false,
            edit_bookings:false,
        },
        edit_data:null,
        checkout_data: null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        resevation_data: [],
        room_select_data: null,

        search: "",
        form_data: {
            guest_id: null,
        },
        search_params: {
            room_id: null,
            guest_id: null,
            clear_status: null,
        },
    }),
    components: {
        CreateGuest,
        Treeselect,
        Unauthorized,

        CheckoutGuest,
        TransferGuest,
        AddReservationCreditNote,
        UpdateRoomBooking,
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
            }, 500),
        },
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.fetchReservations(1);
            }),
        },
    },
    methods: {
        extendDays(data){
              this.edit_data = data;
            this.setActive(this.active, "edit_bookings");
        },
        async transferOccupance(data) {
            this.checkout_data = data;
            this.setActive(this.active, "transfer_guest");
        },
        addCreditNote(data){
             this.checkout_data = data;
            this.setActive(this.active, "credit_note");
        },
        async deleteReservation(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "delete",
                    "data/reservation/destroy/" + id,
                    ""
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.successNotif("Records deleted");
                    this.resevation_data.data.splice(i, 1);
                } else {
                    this.servo();
                }
            }
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        viewGuestCheckout(data) {
            this.checkout_data = data;
            this.setActive(this.active, "checkout_guest_details");
        },

        async fetchExcel() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/reservation/fetch", {
                params: {
                    status: "occupied",
                    ...this.search_params,
                },
            });
            this.isLoading ? this.hideLoader() : "";
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
            const res = await Promise.all([this.fetchReservations(1)]).then(
                function (results) {
                    return results;
                }
            );
            this.hideLoader();
        },

        async fetchReservations(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/reservation/fetch", {
                params: {
                    page: page,
                    status: "occupied",
                    ...this.search_params,
                    search: this.search,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.resevation_data = res.data;
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
.pending-payment {
    background: red !important;
}
.dropdown-menu {
    background: #fbc02d !important;
}
td{
    font-size: 0.8rem  !important;
     font-family: "yanone-kaffeesatz", sans-serif !important;
}

</style>
