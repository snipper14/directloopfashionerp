<template>
    <div class="container" data-app>
        <div class="row d-flex justify-content-between mr-2">
            <v-btn
                class="ma-2 v-btn-primary ml-2"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left> mdi-arrow-left </v-icon>
                Back
            </v-btn>
            <div>
                <h5>
                    Door
                    <span class="badge badge-success">{{
                        form_data.room.door_name
                    }}</span>
                </h5>
            </div>

            <div>
                <h5>
                    Floor
                    <span class="badge badge-success">{{
                        form_data.room.floor_no
                    }}</span>
                </h5>
            </div>
            <div>
                <h5>
                    Bed
                    <span class="badge badge-success">{{
                        form_data.room.qty_bed
                    }}</span>
                </h5>
            </div>
            <div>
                <h5>
                    Standard
                    <span class="badge badge-success">{{
                        form_data.room.room_standard.name
                    }}</span>
                </h5>
            </div>
        </div>
        <div class="row">
            <div class="card">
                <div class="col-12">
                    <div class="row mt-3">
                        <div class="col-3">
                            <div class="form-group">
                                <label for="">Select Package</label>
                                <Select
                                    v-model="form_data.room_rate_id"
                                    @on-change="changePackage"
                                    disabled
                                >
                                    <Option
                                        v-for="(value, i) in rates_data"
                                        :key="i"
                                        :value="value.id"
                                    >
                                        {{ value.room_package.name }}
                                    </Option>
                                </Select>
                            </div>
                            <div class="form-group">
                                <label for="">Name</label>
                                <input
                                    type="text"
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.name"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <label for="">ID</label>
                                <input
                                    type="number"
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.id_no"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Phone</label>
                                <input
                                    type="number"
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.phone"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Gender*</label>
                                <select
                                    class="form-control"
                                    v-model="form_data.gender"
                                    id=""
                                >
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">No of Guest</label>
                                <input
                                    type="number"
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.no_guest"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label>From Date </label>
                                <input
                                    class="form-control"
                                    type="text"
                                    v-model="form_data.from"
                                    disabled
                                />
                            </div>
                            <div class="form-group">
                                <label>To Date</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.to"
                                    disabled
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Day/s</label>
                                <input
                                    type="number"
                                    disabled
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.qty"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label for="">Add Day/s</label>
                                <input
                                    type="number"
                                    disabled
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="extra_days"
                                    class="form-control"
                                />
                            </div>

                            <div class="form-group">
                                <label for="">Unit price</label>
                                <input
                                    type="number"
                                    disabled
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.price"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Total price</label>
                                <input
                                    type="number"
                                    disabled
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.total"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label for=""> Amount Pending</label>
                                <input
                                    type="number"
                                    disabled
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="amount_pending"
                                    class="form-control"
                                />
                            </div>
                            <pay-details-component :form_data="form_data" />
                        </div>
                        <div class="col-3">
                            <div class="border p-2">
                                <b>Payment Details</b>
                                <div class="form-group">
                                    <label for="">Cash Pay</label>
                                    <input
                                        type="number"
                                        v-model="form_update_data.cash_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Mobile Money</label>
                                    <input
                                        type="number"
                                        v-model="
                                            form_update_data.mobile_money_paid
                                        "
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Credit Card</label>
                                    <input
                                        type="number"
                                        v-model="form_update_data.card_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Bank Transfer</label>
                                    <input
                                        type="number"
                                        v-model="
                                            form_update_data.bank_transfer_paid
                                        "
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Cheque</label>
                                    <input
                                        type="number"
                                        v-model="form_update_data.cheque_paid"
                                        class="form-control"
                                    />
                                </div>
                                   <div class="form-group">
                                    <label for="">Online Pay</label>
                                    <input
                                        type="number"
                                        v-model="
                                            form_update_data.online_paid
                                        "
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="">Reference No</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="form_data.ref_no"
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Total Paid</label>
                                <input
                                    type="number"
                                    disabled
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.amount_paid"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border p-2">
                                <b>Assign House Keeper</b>
                                <div class="form-group mt-2">
                                    <label for="">Dept</label>
                                    <Select
                                        v-model="form_data.department_id"
                                        @on-change="changeDepartment"
                                    >
                                        <Option
                                            v-for="(
                                                value, i
                                            ) in department_data"
                                            :key="i"
                                            :value="value.id"
                                            >{{ value.department }}</Option
                                        >
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for=""> House Keeper</label>

                                    <treeselect
                                        :load-options="fetchHseKeeper"
                                        :options="hsekeeper_select_data"
                                        v-model="form_data.house_keeper_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select "
                                    />
                                </div>
                            </div>

                            <div class="border p-2">
                                <b>Assign Service Waiter </b>
                                <div class="form-group mt-2">
                                    <label for="">Dept</label>
                                    <Select
                                        v-model="
                                            form_data.department_waitress_id
                                        "
                                        @on-change="changeWaitressDept"
                                    >
                                        <Option
                                            v-for="(
                                                value, i
                                            ) in department_data"
                                            :key="i"
                                            :value="value.id"
                                            >{{ value.department }}</Option
                                        >
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label for=""> Waiter</label>
                                    <treeselect
                                        :load-options="fetchWaiter"
                                        :options="waiter_select_data"
                                        v-model="form_data.waiter_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Waiter"
                                    />
                                </div>
                                <button
                                    v-if="
                                        isUpdatePermitted &&
                                        edit_data.clear_status == 'occupied'
                                    "
                                    @click="updateReservation()"
                                    class="btn btn-primary"
                                >
                                    Update Reservation
                                </button>
                                <button
                                    v-if="isAdmin"
                                    @click="reprintReservation()"
                                    class="btn btn-info mt-3"
                                >
                                    Reprint Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
        <ReservationReprint
            ref="reservationReprint"
            :form_data="form_data"
            :room_data="room_data"
        />
        <UpdateReprintRoomReceiptComponent
            ref="UpdateReprintRoomReceiptComponent"
            :form_data="form_data"
            :form_update_data="form_update_data"
            :room_data="room_data"
        />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../guest/CreateGuest.vue";
import PrintRoomReceipt from "./PrintRoomReceipt.vue";
import ReceiptHeader from "../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";
import PayDetailsComponent from "./room_components/PayDetailsComponent.vue";
import UpdateReprintRoomReceiptComponent from "./room_components/UpdateReprintRoomReceiptComponent.vue";

import ReservationReprint from "./room_components/ReservationReprint.vue";
export default {
    props: ["edit_data"],
    data: () => ({
        modal_guest: false,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_id: null,
        guest_select_data: null,
        hsekeeper_select_data: null,
        department_data: [],
        waiter_select_data: null,
        reservation_data: null,

        userInfo: "",
        type: "month",
        amount_pending: 0,
        tax_data: [],
        rates_data: [],

        payment_method_list: ["Cash", "Cheque", "Mobile Money"],
        extra_days: 0,
        form_data: {
            room_rate_id: null,
            room_standard_id: null,
            guest_id: null,
            name: "",
            id_no: "",
            phone: "",
            gender: "",
            no_guest: 1,
            from: null,
            to: null,
            qty: 1,

            price: 0,
            total: 0,
            paid: true,
            details: "",
            house_keeper_id: null,
            waiter_id: null,
            department_waitress_id: null,
            room_package_id: null,
            room_id: null,
            pay_method: "",
            ref_no: "",
            tax_rate: null,
            tax_amount: 0,
            amount_paid: 0,

            cash_paid: 0,
            mobile_money_paid: 0,
            online_paid:0,
            card_paid: 0,
            cheque_paid: 0,
            bank_transfer_paid: 0,

        },
        form_update_data: {
            id: null,
            no_guest: 1,
            cash_paid: 0,
            mobile_money_paid: 0,
             online_paid:0,
            card_paid: 0,
            cheque_paid: 0,
            bank_transfer_paid: 0,
            amount_paid: 0,
            price: 0,
            total: 0,
            tax_amount: 0,
            from: null,
            to: null,
            paid: true,
        },
    }),
    components: {
        CreateGuest,
        Treeselect,
        PrintRoomReceipt,
        ReceiptHeader,
        PayDetailsComponent,
        UpdateReprintRoomReceiptComponent,

        ReservationReprint,
    },
    created() {
        this.scrollTop();
        this.userInfo = this.$store.state.user;
        this.form_data = this.edit_data;
        this.form_update_data.id = this.edit_data.id;
        this.form_update_data.no_guest = this.edit_data.no_guest;
        this.form_update_data.price = this.edit_data.price;
        this.form_update_data.room_standard_id =
            this.edit_data.room.room_standard_id;

        this.concurrentApiReq();
    },
    watch: {
        guest_id: {
            handler(val, old) {
                this.form_data.guest_id = val;

                this.isLoading = true;
                this.fetchGuestDetails();
            },
        },
        extra_days: {
            handler(val, old) {
                this.setFormParams();
            },
        },
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.setFormParams();
            }, 500),
        },
        form_update_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.setFormParams();
            }, 500),
        },
    },
    methods: {
        async reprintReservation() {
            this.$refs.reservationReprint.initReprintPrintReceipt();
            //this.$emit("dashboard-active");
        },
        changePackage() {
            let index = this.rates_data.findIndex(
                (room_rate) => room_rate.id == this.form_data.room_rate_id
            );
            this.form_data.room_package =
                this.rates_data[index].room_package.name;
            this.form_data.room_package_id =
                this.rates_data[index].room_package_id;
            this.form_data.price = this.rates_data[index].rate;
        },
        async fetchRates() {
            const res = await this.callApi("post", "data/room_rate/checkRate", {
                room_standard_id: this.form_data.room_standard_id,
            });

            if (res.status === 200) {
                this.rates_data = res.data;
            } else {
                this.servo();
            }
        },
        async updateReservation() {
            if (this.amount_pending > 0) {
                this.errorNotif("Amount paid is less than room cost");
                return;
            }
            this.form_update_data.amount_paid =
                parseFloat(this.form_update_data.cash_paid) +
                parseFloat(this.form_update_data.mobile_money_paid) +
                parseFloat(this.form_update_data.online_paid) +
                parseFloat(this.form_update_data.card_paid) +
                parseFloat(this.form_update_data.cheque_paid) +
                parseFloat(this.form_update_data.bank_transfer_paid);
            this.extra_days = parseFloat(this.extra_days)
                ? parseFloat(this.extra_days)
                : 0;
            this.form_update_data.qty =
                parseFloat(this.extra_days) + parseFloat(this.form_data.qty);

            if (this.form_update_data.qty > 0) {
                var startdate = this.$moment(this.form_data.from).format(
                    "YYYY-MM-DD"
                );

                this.form_data.to = this.formatDateTime(
                    this.$moment(startdate).add(
                        this.form_update_data.qty * 24 + 10,
                        "hours"
                    )
                );
            }
            this.form_update_data.from = this.formatDateTime(
                this.form_data.from
            );
            this.form_update_data.to = this.formatDateTime(this.form_data.to);

            this.showLoader();

            var row_vat = this.calculateTax(
                this.form_data.tax_rate,
                this.form_update_data.amount_paid
            );
            this.form_update_data.tax_amount = row_vat;
            const res = await this.callApi(
                "put",
                "data/reservation/update",
                this.form_update_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Successfully booked");
                this.reservation_data = this.form_data;
                //  await printJS("printReceipt", "html");
                setTimeout(() => {
                    this.$refs.UpdateReprintRoomReceiptComponent.initPrintReceipt();
                    this.$emit("dashboard-active");
                }, 500);
            } else {
                this.form_error(res);
            }
        },
        setFormParams() {
        
            this.form_update_data.total =
                this.form_data.price * this.extra_days;
            this.amount_pending =
                parseFloat(this.form_update_data.total) +
                parseFloat(this.form_update_data.amount_paid) -
                (parseFloat(this.form_update_data.cash_paid) +
                    parseFloat(this.form_update_data.mobile_money_paid) +
                      parseFloat(this.form_update_data.online_paid) +
                    parseFloat(this.form_update_data.card_paid) +
                    parseFloat(this.form_update_data.cheque_paid) +
                    parseFloat(this.form_update_data.bank_transfer_paid));
            //   }
        },

        async changeDepartment() {
            this.isLoading = true;
            this.fetchHseKeeper();
        },
        async changeWaitressDept() {
            this.isLoading = true;
            this.fetchWaiter();
        },
        async getTax() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/tax/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.tax_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchRates(),
                this.fetchDept(),
                this.getTax(),
                //  this.fetchHseKeeper(),
                //this.fetchWaiter()
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchGuestDetails() {
            this.showLoader();
            const res = await this.getApi("data/guest/fetchGuestDetails", {
                params: {
                    guest_id: this.form_data.guest_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.form_data.name = res.data.name;
                this.form_data.id_no = res.data.id_no;
                this.form_data.phone = res.data.phone;
                this.form_data.gender = res.data.gender;
            } else {
                this.servo();
            }
        },
        async fetchGuests() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/guest/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.guest_data = res.data;
                this.guest_select_data = this.modifyProductKey();
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
        async fetchDept() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/dept/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.department_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchHseKeeper() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/employee/fetch", {
                params: {
                    department_id: this.form_data.department_id,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.hsekeeper_select_data = this.modifyHousePeer(
                    res.data.results
                );
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchWaiter() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {
                params: {
                    department_id: this.form_data.department_waitress_id,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.waiter_select_data = this.modifyWaiterSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyProductKey() {
            let modif = this.guest_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        modifyHousePeer(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.first_name + " " + obj.last_name,
                };
            });
            return modif;
        },
        modifyWaiterSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        onSuccess() {
            this.modal_guest = false;
            this.isLoading = true;
            this.fetchGuests();
        },
    },
};
</script>
<style scoped>
.form-group {
    display: flex !important;
    justify-content: space-between;
}
#printReceipt {
    visibility: hidden;
}
.receipt_header {
    display: flex;
    flex-direction: column;
}
.receipt-menu td {
    color: #000;
    font-weight: 700;
}
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
.room-details {
    font-size: 1.8rem !important;
    margin-left: 1rem !important;
}
</style>
