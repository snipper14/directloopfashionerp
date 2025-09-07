<template>
    <div class="container" data-app>
        <div class="row d-flex justify-content-between mr-2">
            <v-btn
                class="ma-2 v-btn-primary ml-2"
                @click="$router.push('room_select')"
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
                        room_data.door_name
                    }}</span>
                </h5>
            </div>

            <div>
                <h5>
                    Floor
                    <span class="badge badge-success">{{
                        room_data.floor_no
                    }}</span>
                </h5>
            </div>
            <div>
                <h5>
                    Bed
                    <span class="badge badge-success">{{
                        room_data.qty_bed
                    }}</span>
                </h5>
            </div>
            <div>
                <h5>
                    Room Type
                    <span class="badge badge-success">{{
                        room_data.room_standard.name
                    }}</span>
                </h5>
            </div>
        </div>
        <div class="row">
            <div class="card">
                <div class="col-12">
                    <div class="row">
                        <div class="col-3">
                            <button
                                class="btn btn-info btn-sm center"
                                @click="modal_guest = true"
                            >
                                <Icon type="md-add" />Add Guest
                            </button>
                        </div>

                        <div class="col-3">
                            <button
                                class="btn btn-info btn-sm center"
                                @click="modal_guest_co = true"
                            >
                                <Icon type="md-add" />Add Company
                            </button>
                        </div>

                        <div class="col-3">
                            <button
                                @click="
                                    $router.push(
                                        'current_reservations_calendar'
                                    )
                                "
                                class="btn btn-info btn-sm center"
                            >
                                <Icon type="md-time" />View Reservation
                            </button>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-3">
                            <div class="form-group">
                                <label for="">Select Package</label>
                                <Select
                                    v-model="form_data.room_rate_id"
                                    @on-change="changePackage"
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
                            <search-guest-component
                                v-on:guestSearchSelected="guestSearchSelected"
                            />
                            <SearchGuestCO
                                ref="SearchGuestCO"
                                v-on:guestCOSearchSelected="
                                    guestCOSearchSelected
                                "
                            />

                            <div class="form-group">
                                <label for="">Company Name</label>
                                <input
                                    type="text"
                                    autocomplete="offdfdf-fdfdf"
                                    disabled
                                    v-model="form_data.company_name"
                                    class="form-control"
                                />
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
                                    type="text"
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
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <label>From Date *</label
                                ><input
                                    v-model="form_data.from"
                                    type="text"
                                    disabled
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label>To Date *</label
                                ><input
                                    v-model="form_data.to"
                                    disabled
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Day/s</label>
                                <input
                                    type="number"
                                    autocomplete="offdfdf-fdfdf"
                                    v-model="form_data.qty"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group">
                                <label for="">Unit price</label>
                                <input
                                    type="number"
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
                                <label for="">Tax Rate</label>
                                <Select v-model="form_data.tax_rate">
                                    <Option
                                        v-for="(value, i) in tax_data"
                                        :key="i"
                                        :value="value.tax_rate"
                                    >
                                        {{ value.tax_rate }}--{{
                                            value.tax_code
                                        }}
                                    </Option>
                                </Select>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border p-2">
                                <b>Payment Details</b>
                                <div class="form-group">
                                    <label for="">Cash Pay</label>
                                    <input
                                        type="number"
                                        v-model="form_data.cash_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Mobile Money</label>
                                    <input
                                        type="number"
                                        v-model="form_data.mobile_money_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Credit Card</label>
                                    <input
                                        type="number"
                                        v-model="form_data.card_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Bank Transfer</label>
                                    <input
                                        type="number"
                                        v-model="form_data.bank_transfer_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Cheque</label>
                                    <input
                                        type="number"
                                        v-model="form_data.cheque_paid"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Online Pay</label>
                                    <input
                                        type="number"
                                        v-model="form_data.online_paid"
                                        class="form-control"
                                    />
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="">Reference No</label>
                                <input
                                    type="text"
                                    placeholder="i.e Cheque No, Mpesa Code"
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
                                        :auto-load-root-options="false"
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
                                        :auto-load-root-options="false"
                                        v-model="form_data.waiter_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Waiter"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <button
                            class="btn btn-primary"
                            v-if="isWritePermitted"
                            :disabled="isSubmitBtnDisabled"
                            @click="reserveRoom()"
                        >
                            SAVE RESERVATION
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
        <Modal v-model="modal_guest" width="860">
            <create-guest v-on:onSuccess="onSuccess" />
        </Modal>
        <Modal v-model="modal_guest_co" width="860">
            <CreateCOGuest v-on:onAddCompanySuccess="onAddCompanySuccess" />
        </Modal>
        <print-room-receipt-component
            ref="PrintRoomReceiptComponent"
            :form_data="form_data"
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
import SearchGuestComponent from "./room_components/SearchGuestComponent.vue";
import PrintRoomReceiptComponent from "./room_components/PrintRoomReceiptComponent.vue";
import CreateCOGuest from "../guest/CreateCOGuest.vue";
import SearchGuestCO from "./room_components/SearchGuestCO.vue";

export default {
    data: () => ({
        guest_id: "",
        isSubmitBtnDisabled: false,

        modal_guest: false,
        modal_guest_co: false,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_id: null,
        guest_select_data: null,
        hsekeeper_select_data: null,
        department_data: [],
        waiter_select_data: null,
        reservation_data: null,
        branchInfo: "",
        userInfo: "",
        type: "month",
        tax_data: [],
        rates_data: [],

        form_data: {
            company_name: "",
            guest_company_id: null,
            room_rate_id: null,
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
            room_standard_id: null,
            room_id: null,
            room_package: "",
            pay_method: "",
            ref_no: "",
            tax_rate: null,
            tax_amount: 0,
            amount_paid: 0,

            cash_paid: 0,
            mobile_money_paid: 0,
            card_paid: 0,
            cheque_paid: 0,
            bank_transfer_paid: 0,
            online_paid: 0,
        },
    }),
    components: {
        CreateGuest,
        Treeselect,
        PrintRoomReceipt,
        ReceiptHeader,
        SearchGuestComponent,
        PrintRoomReceiptComponent,
        CreateCOGuest,
        SearchGuestCO,
    },
    created() {
        this.scrollTop();
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.setRoomDetails();

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
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.setFormParams();
            }, 500),
        },
    },
    methods: {
        async guestCOSearchSelected(val) {
            this.form_data.guest_company_id = val;
            const res = await this.callApi(
                "get",
                "data/guest_co/getCompanyName/" + val
            );
            if (res.status == 200) {
                this.form_data.company_name = res.data.name;
            } else {
                this.form_data.company_name = "";
            }
        },
        guestSearchSelected(val) {
            this.guest_id = val;
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
        setRoomDetails() {
            this.room_data = this.$store.state.room_data;

            this.form_data.price = this.room_data.rate;
            this.form_data.room_id = this.room_data.id;
            this.form_data.room_standard_id = this.room_data.room_standard_id;
        },
        async reserveRoom() {
            if (this.form_data.amount_paid < this.form_data.total) {
                this.errorNotif("Amount paid is less than room cost");
                return;
            }

            this.showLoader();
            this.isSubmitBtnDisabled = true;
            this.form_data.from = this.formatDateTime(this.form_data.from);
            this.form_data.to = this.formatDateTime(this.form_data.to);

            var row_vat = this.calculateTax(
                this.form_data.tax_rate,
                this.form_data.total
            );
            this.form_data.tax_amount = row_vat;
            const res = await this.callApi(
                "POST",
                "data/reservation/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Successfully booked");
                this.reservation_data = this.form_data;

                setTimeout(() => {
                    this.$refs.PrintRoomReceiptComponent.initPrintReceipt();
                    this.$router.push("room_select");
                }, 500);
            } else {
                this.isSubmitBtnDisabled = false;
                this.form_error(res);
            }
        },
        setFormParams() {
            this.form_data.total = this.form_data.price * this.form_data.qty;
            this.form_data.amount_paid =
                parseFloat(this.form_data.cash_paid) +
                parseFloat(this.form_data.mobile_money_paid) +
                parseFloat(this.form_data.online_paid) +
                parseFloat(this.form_data.card_paid) +
                parseFloat(this.form_data.cheque_paid) +
                parseFloat(this.form_data.bank_transfer_paid);

            if (this.form_data.qty > 0) {
                this.form_data.from = this.getDateTime();
                var startdate = this.$moment(this.getCurrentDate());
                var time_10 = "10:00";
                var date_from = this.formatDate(this.form_data.from);
                var dateTime_at_10 = this.$moment(
                    date_from + " " + time_10,
                    "YYYY-MM-DD HH:mm"
                ).format("YYYY-MM-DD HH:mm");

                var date_time = this.form_data.from;

                var is_today = this.$moment(date_time).diff(
                    this.$moment(dateTime_at_10),
                    "minutes"
                );

                if (is_today >= 0) {
                    this.form_data.to = this.formatDateTime(
                        this.$moment(startdate).add(
                            this.form_data.qty * 24 + 10,
                            "hours"
                        )
                    );
                } else {
                    var time_6 = "06:00";
                    var dateTime_at_6 = this.$moment(
                        date_from + " " + time_6,
                        "YYYY-MM-DD HH:mm"
                    ).format("YYYY-MM-DD HH:mm");
                    var is_6 = this.$moment(date_time).diff(
                        this.$moment(dateTime_at_6),
                        "minutes"
                    );

                    if (is_6 >= 0) {
                        this.form_data.to = this.formatDateTime(
                            this.$moment(startdate).add(
                                this.form_data.qty * 24 + 10,
                                "hours"
                            )
                        );
                    } else {
                        if (this.form_data.qty <= 1) {
                            this.form_data.to = dateTime_at_10;
                        } else {
                            this.form_data.to = this.formatDateTime(
                                this.$moment(dateTime_at_10).add(
                                    (this.form_data.qty - 1) * 24,
                                    "hours"
                                )
                            );
                        }
                    }
                }
            }
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
                for (var key in this.tax_data) {
                    if (this.tax_data[key].tax_code == "A") {
                        this.form_data.tax_rate = this.tax_data[key].tax_rate;
                    }
                }
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchDept(),
                this.getTax(),
                this.fetchRates(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
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
                this.form_data.guest_company_id = res.data.guest_company_id;
                this.form_data.company_name = res.data.guest_company.name;
            } else {
                this.servo();
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
        },
        onAddCompanySuccess() {
            this.modal_guest_co = false;
            this.isLoading = true;
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
.printer-header {
    font-size: 1.8rem !important;
}
</style>
