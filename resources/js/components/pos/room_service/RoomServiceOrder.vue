<template>
    <div class="container-fluid mt-3">
        <div v-if="isReadPermitted">
            <div class="row top-nav">
                <div class="col-3">
                    <h4 v-if="room_service_data">
                        Room Name: {{ room_service_data.room.door_name }}
                    </h4>
                </div>
                <div class="col-3">
                    <h4 v-if="room_service_data">
                        Floor: {{ room_service_data.room.floor_no }}
                    </h4>
                </div>
                <div class="col-3">
                    <h4 v-if="room_service_data">
                        Package: {{ room_service_data.room_package.name }}
                    </h4>
                </div>
            </div>

            <div class="row">
                <div class="col-4">
                    <div>
                        <h4><b style="color: #0288d1">Room Service</b></h4>
                    </div>
                    <div>Customer: {{ form_data.guest_name }}</div>
                    <div>Order No: {{ form_data.order_no }}</div>

                    <label for="">Service Charge Rate(%): </label>
                    <input
                        type="number"
                        v-model="form_data.service_charge_rate"
                    />
                    <br />

                    <div
                        class="d-flex flex-column"
                        style="
                            height: 90%;
                            overflow-x: hidden;
                            overflow-y: auto;
                        "
                    >
                        <div
                            style="
                                height: 300px;
                                overflow-x: hidden;
                                overflow-y: auto;
                            "
                        >
                            <div
                                :class="[
                                    value.id,
                                    cart_current === value.id
                                        ? 'cart-active'
                                        : '',
                                    value.orderPrinted == 1
                                        ? 'order-printed'
                                        : '',
                                ]"
                                class="d-flex flex-column mt-2 mb-2"
                                v-for="(value, i) in order_data"
                                :key="i"
                                @click="cartClck(value)"
                            >
                                <div class="row">
                                    <div class="col-5">
                                        <item-particulars-component
                                            :data="value"
                                        />
                                    </div>
                                    <div class="col-2">
                                        <b class="cart-items"> X </b>
                                        <b class="cart-items">
                                            {{ value.qty }}</b
                                        >
                                    </div>
                                    <div class="col-1">
                                        <b class="cart-items">
                                            {{
                                                cashFormatter(value.sub_total)
                                            }}</b
                                        >
                                    </div>
                                    <div
                                        class="col-4 d-flex justify-content-around"
                                    >
                                        <span>
                                            <v-icon
                                                v-if="isUpdatePermitted"
                                                @click="
                                                    crementOrder('add', value)
                                                "
                                                style="color: green"
                                                medium
                                                >{{
                                                    icons.mdiPlusThick
                                                }}</v-icon
                                            >
                                        </span>
                                        <span>
                                            <v-icon
                                                v-if="isUpdatePermitted"
                                                @click="
                                                    crementOrder('minus', value)
                                                "
                                                style="color: black"
                                                medium
                                                >{{
                                                    icons.mdiMinusThick
                                                }}</v-icon
                                            >
                                        </span>
                                        <b>
                                            <v-icon
                                                v-if="isDeletePermitted"
                                                @click="
                                                    deleteRecord(value.id, i)
                                                "
                                                style="color: red"
                                                medium
                                                >{{
                                                    icons.mdiTrashCanOutline
                                                }}</v-icon
                                            ></b
                                        >
                                    </div>
                                </div>
                                <div class="d-flex justify-content-start">
                                    <p class="mr-2 cart-items">Unit Price /</p>

                                    <p>{{ cashFormatter(value.price) }}</p>
                                    <p class="ml-2 cart-items">
                                        Guest
                                        <span class="badge badge-secondary">{{
                                            value.no_guest
                                        }}</span>
                                    </p>
                                    <p class="ml-2 cart-items">
                                        {{ value.notes }}
                                    </p>
                                </div>
                                <hr />
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <span>
                                    <b>
                                        TOTAL:
                                        {{ cashFormatter(total_order) }}</b
                                    >
                                </span>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <span>
                                    <b>
                                        SUB TOTAL:
                                        {{ cashFormatter(sub_total) }}</b
                                    >
                                </span>
                            </div>
                            <div class="d-flex justify-content-end mt-1">
                                <span>
                                    <b> TAX: {{ cashFormatter(total_tax) }}</b>
                                </span>
                            </div>
                            <div class="d-flex justify-content-end mt-1">
                                <span>
                                    <b>
                                        Service Charges:
                                        {{
                                            cashFormatter(service_charge_total)
                                        }}</b
                                    >
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 widget-wrapper">
                            <div
                                class="row d-flex"
                                style="width: 200px; margin-left: 2rem"
                            >
                                <label for="">Qty Ordered</label>
                                <div class="d-flex">
                                    <input
                                        type="number"
                                        v-model="form_data.qty"
                                        class="form-control"
                                    />
                                    <button
                                        class="btn btn-secondary btn-sm"
                                        @click="printOrder()"
                                    >
                                        Complete Order
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <custom-board
                                        @pressed="form_data.qty = $event"
                                        :selfValue="form_data.qty"
                                    />
                                </div>
                                <div class="col-4">
                                    <button
                                        @click="addNoteModal()"
                                        class="btn btn-outline-secondary btn-lg"
                                        style="width: 100%"
                                    >
                                        <v-icon medium>{{
                                            icons.mdiNotebookEdit
                                        }}</v-icon>
                                        Note
                                    </button>
                                </div>

                                <div class="col-4">
                                    <button
                                        class="btn btn-outline-primary btn-lg"
                                        style="width: 100%"
                                        @click="printBill()"
                                    >
                                        <v-icon medium>{{
                                            icons.mdiPrinter
                                        }}</v-icon>
                                        Bill
                                    </button>
                                </div>

                                <div class="col-4">
                                    <button
                                        class="btn btn-outline-primary btn-lg"
                                        style="width: 100%"
                                        @click="printOrder()"
                                    >
                                        <v-icon medium>{{
                                            icons.mdiBookmarkOutline
                                        }}</v-icon>
                                        Order
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-4">
                                    <button
                                        @click="modal.guest_modal = true"
                                        v-if="isUpdatePermitted"
                                        type="button"
                                        class="btn btn-outline-secondary btn-lg"
                                    >
                                        Add Guest
                                    </button>
                                </div>

                                <div class="col-4" v-if="false">
                                    <button
                                        @click="
                                            modal.service_charge_modal = true
                                        "
                                        class="btn btn-outline-warning"
                                    >
                                        Add S.C
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <button
                                        v-if="isApprovePermitted"
                                        @click="cashout()"
                                        class="btn btn-primary btn-lg"
                                        style="width: 100%"
                                    >
                                        <v-icon medium>{{
                                            icons.mdiCashPlus
                                        }}</v-icon>
                                        Clear Order
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <RoomOrderPrint
                        v-if="order_data.length > 0"
                        :order_data="order_data"
                        ref="childComponent"
                    />
                    <RoomServiceBillPrint
                        v-if="order_data.length > 0"
                        :order_data="order_data"
                        :bill_total="total_order"
                        :tax_total="total_tax"
                        :sub_total="sub_total"
                        :service_charge_total="service_charge_total"
                        ref="printBillComponent"
                    />
                </div>
                <div class="col-8">
                    <div class="row top-panel d-flex justify-content-between">
                        <button
                            class="btn btn-primary"
                            v-if="active.show_category"
                            @click="$router.push('room_service_select_dept')"
                        >
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </button>
                        <button class="btn btn-warning" @click="newOrder()">
                            New Order
                        </button>
                        <v-icon
                            @click="$router.push('posmenu')"
                            style="color: #000"
                            medium
                            >{{ icons.mdiHome }}</v-icon
                        >
                        <v-icon
                            @click="$router.push('posmenu')"
                            style="color: #fff"
                            medium
                            >{{ icons.mdiHome }}</v-icon
                        >

                        <div class="ml-2 d-flex flex-column align-items-center">
                            <span class="ml-2 d-flex justify-content-between">
                                <p style="color: #fff" v-if="user">
                                    {{ user.name }} >>
                                </p>
                                <p style="color: #fff">
                                    {{ form_data.order_type }} >>
                                </p>
                            </span>
                        </div>
                        <button class="btn btn-warning" @click="logoutPOS()">
                            Log out
                        </button>
                    </div>
                    <div class="row mt-3 d-flex" style="background: #cfd8dc">
                        <button
                            class="btn btn-primary"
                            @click="fetchAllCategoriesStock()"
                        >
                            Show Category
                        </button>
                        <span class="ml-3" align="center"
                            ><h6>
                                <b style="color: #43a047">{{
                                    select_category
                                }}</b>
                            </h6></span
                        >
                        <div class="input-group ml-3" style="width: 50%">
                            <div class="input-group-prepend">
                                <span
                                    class="input-group-text"
                                    id="inputGroup-sizing-default"
                                    >Search</span
                                >
                            </div>
                            <input
                                placeholder="Search Product"
                                type="text"
                                v-model="search"
                                class="form-control"
                                aria-label="Sizing example input"
                                aria-describedby="inputGroup-sizing-default"
                            />
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <room-service-categories
                                v-if="
                                    product_category_data.length > 0 &&
                                    active.show_category
                                "
                                :product_category_data="product_category_data"
                                @categoryChange="categoryChange($event)"
                            />
                        </div>

                        <div class="col-10" v-if="active.show_products">
                            <div class="row">
                                <div
                                    :class="[
                                        value.id,
                                        menu_current === value.id
                                            ? 'menu-active'
                                            : '',
                                    ]"
                                    class="col-3 card menu-item"
                                    v-for="(value, i) in stock_data.data"
                                    :key="i"
                                    @click="checkAccompaniment(value)"
                                >
                                    <div
                                        class="card-body d-flex flex-column align-items-start"
                                    >
                                        <img
                                            v-if="value.image && value.show_img"
                                            alt=" image"
                                            class="menu-image"
                                            v-lazy="value.img_url"
                                        />
                                        <img
                                            v-else
                                            src="../../../assets/images/delfirm_plate_default.jpg"
                                            class="menu-image"
                                        />

                                        <b class="menu-product">
                                            {{ value.product_name }}</b
                                        >
                                        <span class="badge badge-secondary"
                                            ><b
                                                >Ksh.
                                                {{
                                                    cashFormatter(
                                                        value.selling_price
                                                    )
                                                }}</b
                                            ></span
                                        >
                                        <div v-if="form_data.isBar">
                                            <span
                                                v-if="value.qty > 0"
                                                class="badge badge-success mt-1"
                                                ><b
                                                    >Avail.
                                                    {{
                                                        cashFormatter(value.qty)
                                                    }}</b
                                                ></span
                                            >
                                            <span
                                                v-else
                                                class="badge badge-warning mt-1"
                                                ><b>Unavailable</b></span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <Pagination
                        v-if="stock_data !== null"
                        v-bind:results="stock_data"
                        v-on:get-page="fetchStock"
                    ></Pagination>
                    Items Count {{ stock_data.total }}
                </div>
            </div>
        </div>

        <div v-else><unauthorized /></div>
        <Modal
            v-model="modal.service_charge_modal"
            title="Add/Remove Service Charge"
        >
            <div class="row">
                <div class="col-9">
                    <div class="form-group">
                        <label for=""> Service Charge</label>
                        <input
                            type="number"
                            v-model="service_charge_total"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-9 d-flex justify-content-center">
                    <button
                        class="btn btn-secondary"
                        type="secondary"
                        v-if="isApprovePermitted"
                        @click="addServiceCharge()"
                    >
                        Add Service Charge
                    </button>
                    <button
                        v-if="isApprovePermitted"
                        class="btn btn-danger"
                        type="secondary"
                        @click="addServiceCharge()"
                    >
                        Remove Service Charge
                    </button>
                </div>
            </div>
        </Modal>
        <Modal v-model="modal.guest_modal" title="Add Guest">
            <div class="row">
                <div class="col-11">
                    <div class="form-group">
                        <label for="">NO OF GUEST</label>
                        <input
                            type="number"
                            v-model="form_data.no_guest"
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="col-9 d-flex justify-content-center">
                    <button
                        class="btn btn-secondary"
                        type="secondary"
                        @click="addGuest()"
                    >
                        Add Guest
                    </button>
                </div>
            </div>
        </Modal>
        <Modal v-model="modal.notes" title="Note">
            <div class="row">
                <div class="col-11">
                    <div class="form-group">
                        <label for="">NOTE</label>
                        <input
                            type="text"
                            @focus="showNotes"
                            data-layout="normal"
                            v-model="form_data.notes"
                            class="form-control"
                        />
                    </div>
                </div>

                <div class="col-9 d-flex justify-content-center">
                    <button
                        class="btn btn-secondary"
                        type="secondary"
                        @click="addNote()"
                    >
                        Add Note
                    </button>
                </div>
                <div class="col-10">
                    <vue-touch-keyboard
                        :options="options"
                        v-if="visible"
                        :layout="layout"
                        :cancel="hide"
                        :accept="accept"
                        :input="input"
                    />
                </div>
            </div>
        </Modal>
        <Modal v-model="modal.cashout" fullscreen title="Payment">
            <bill-clearance
                v-if="modal.cashout"
                :order_no="form_data.order_no"
                :order_data="order_data"
                :total_tax="total_tax"
                :total_order="total_order"
                :service_charge_total="service_charge_total"
                :service_charge_rate="form_data.service_charge_rate"
                v-on:dismiss-diag="dismissDialog"
            />
        </Modal>
        <scroll-widget-component />
        <Modal v-model="modal.add_accompaniment" fullscreen>
            <add-accompaniment
                :accomp_data="accomp_data"
                v-if="modal.add_accompaniment"
                v-on:send-accompaniment="getAccompaniment"
            />
        </Modal>
    </div>
</template>
<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import RoomOrderPrint from "./RoomOrderPrint.vue";
import RoomServiceBillPrint from "./RoomServiceBillPrint";
import Pagination from "../../utilities/Pagination.vue";
import print from "print-js";
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
    mdiCashPlus,
    mdiAccountPlus,
    mdiArrowUpCircleOutline,
    mdiArrowDownCircleOutline,
} from "@mdi/js";
import BillClearance from "./BillClearance.vue";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
import RoomServiceCategories from "./room_service_components/RoomServiceCategories.vue";
import CustomBoard from "../menu_components/dinerscomponents/CustomBoard.vue";
import AddAccompaniment from "../menu_components/dinerscomponents/AddAccompaniment.vue";
import ItemParticularsComponent from "../menu_components/dinerscomponents/ItemParticularsComponent.vue";
export default {
    components: {
        Unauthorized,
        RoomOrderPrint,
        RoomServiceBillPrint,
        BillClearance,
        Pagination,
        ScrollWidgetComponent,
        RoomServiceCategories,
        CustomBoard,
        AddAccompaniment,
        ItemParticularsComponent,
    },
    data() {
        return {
            accomp_data: null,
            active: { show_category: true, show_products: false },
            modal: {
                guest_modal: false,
                notes: false,
                cashout: false,
                service_charge_modal: false,
                add_accompaniment: false,
            },
            visible: false,
            layout: "normal",
            input: null,

            options: {
                useKbEvents: false,
                preventClickEvent: false,
            },
            product_category_data: [],
            search: "",
            user: "",
            select_category: "All Category",
            room_service_data: null,
            order_data: [],
            stock_data: [],
            total_order: 0,
            sub_total: 0,
            total_tax: 0,
            service_charge_total: 0,
            params: {
                category_id: null,
            },
            current: "",
            menu_current: "",
            cart_current: "",
            search: "",
            form_data: {
                accompaniment_id: null,
                isServiceCharged: true,
                service_charge_rate: 0,
                service_charge_amount: 0,
                guest_id: "",
                room_id: "",
                room_package_id: "",
                stock_id: "",
                unit_id: "",
                room_reservation_id: "",
                department_id: null,
                qty: 1,
                price: 0,
                cost_price: 0,
                cost_total: 0,
                row_total: 0,
                sub_total: 0,
                row_vat: 0,
                vat_rate: 0,
                no_guest: 1,
                notes: "",
                order_date: "",
                order_no: "",
                prev_order_no: "",
                order_type: "Room Service",
                guest_name: "",
            },
            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
                mdiCashPlus,
                mdiAccountPlus,
                mdiArrowUpCircleOutline,
                mdiArrowDownCircleOutline,
            },
        };
    },
    mounted() {
        this.user = this.$store.state.user;
        this.room_service_data = this.$store.state.room_service_data;

        this.form_data.guest_id = this.room_service_data.guest_id;
        this.form_data.room_id = this.room_service_data.room_id;
        this.form_data.room_package_id = this.room_service_data.room_package_id;
        this.form_data.room_reservation_id = this.room_service_data.id;
        this.form_data.order_date = this.getCurrentDate();
        this.form_data.department_id =
            this.$store.state.roomservice_department_data.id;
        this.form_data.isBar =
            this.$store.state.roomservice_department_data.department
                .toLowerCase()
                .includes("bar");
        this.form_data.guest_name = this.room_service_data.guest.name;
        this.form_data.order_no = this.$store.state.order_no2;
        this.$store.commit("setOrderNo2", "");
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.fetchStock(1);
            }, 500),
        },
        order_data: {
            deep: true,
            handler(val, old) {
                this.total_order = this.order_data.reduce(function (sum, val) {
                    return sum + val.row_total;
                }, 0);
                this.total_tax = this.order_data.reduce(function (sum, val) {
                    return sum + val.row_vat;
                }, 0);
                this.service_charge_total = this.order_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.service_charge_amount;
                },
                0);
                this.sub_total = this.order_data.reduce(function (sum, val) {
                    return sum + val.sub_total;
                }, 0);
            },
        },
        form_data: {
            deep: true,
            handler: _.debounce(function () {
                if (this.form_data.isServiceCharged) {
                    this.form_data.service_charge_amount =
                        (this.form_data.service_charge_rate *
                            this.total_order) /
                        100;
                }
            }),
        },
    },
    methods: {
        getAccompaniment(data) {
            this.modal.add_accompaniment = false;

            if (data.data) {
                this.form_data.accompaniment_id = data.data.accompaniment_id;
            }
            this.createOrder(data.accomp_data);
        },
        async checkAccompaniment(data) {
            if (data.accompaniment.length > 0) {
                this.accomp_data = data;
                //   console.log(JSON.stringify(this.accomp_data));
                this.modal.add_accompaniment = true;
            } else {
                this.createOrder(data);
            }
        },
        async addServiceCharge() {
            // this.form_data.service_charge_amount=this.service_charge_total
            if (this.order_data.length > 0) {
                this.showLoader();
                this.calculateServiceCharge();
                const res = await this.callApi(
                    "POST",
                    "data/room_order/updateServiceCharge",
                    {
                        order_no: this.form_data.order_no,
                        service_charge_amount:
                            this.form_data.service_charge_amount,
                        service_charge_rate: this.service_charge_total,
                    }
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.successNotif("Room Service Updated");
                    this.modal.service_charge_modal = false;
                    this.order_data = res.data;
                } else {
                    this.form_error(res);
                }
            } else {
                this.errorNotif("Enter service charge after order is complete");
            }
        },

        async categoryChange(value) {
            this.search = "";
            this.select_category = value.name;
            this.current = value.name;
            this.isLoading = true;
            this.params.category_id = value.id;
            this.active.show_category = false;
            this.active.show_products = true;
            await this.fetchStock(1);
        },
        async dismissDialog() {
            this.modal.cashout = false;
            this.order_data = [];
            this.$router.push("posmenu");
            //   this.newOrder();
        },
        async cashout() {
            if (this.order_data.length > 0) {
                this.modal.cashout = true;
            } else {
                this.errorNotif("No Order available");
            }
        },
        async printBill() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_order/validateBill",
                {
                    order_no: this.form_data.order_no,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                if (res.data == "1") {
                    this.$refs.printBillComponent.printBill();
                } else {
                    this.e("Complete Order before print bill");
                }
            } else {
                this.servo();
            }
        },
        addNoteModal() {
            this.hide();
            this.modal.notes = true;
        },
        showNotes(e) {
            this.input = e.target;
            this.layout = e.target.dataset.layout;

            if (!this.visible) this.visible = true;
        },
        hide() {
            this.visible = false;
        },
        accept(text) {
            this.hide();
        },
        calculateServiceCharge() {
            this.form_data.service_charge_amount =
                (this.form_data.service_charge_rate *
                    this.form_data.sub_total) /
                100;
            // if (this.order_data.length > 0) {
            //     this.form_data.service_charge_amount = Math.round(
            //         this.service_charge_total / this.order_data.length
            //     );
            // }
        },
        async crementOrder(type, value) {
            if (value.qty < 2 && type == "minus") {
                return;
            }
            this.form_data.row_total = value.price;
            var row_vat = this.calculateTax(
                value.stock.tax_dept.tax_rate,
                this.form_data.row_total
            );
            this.form_data.cost_total = value.cost_price;
            this.calculateServiceCharge();
            this.form_data.row_total =
                this.form_data.sub_total + this.form_data.service_charge_amount;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_order/crementOrder",
                {
                    qty: 1,
                    sub_total: this.form_data.sub_total,
                    row_total: this.form_data.row_total,
                    row_vat: row_vat,
                    id: value.id,
                    order_no: this.form_data.order_no,
                    stock_id: value.stock_id,
                    update_type: type,
                    cost_total: this.form_data.cost_total,
                    service_charge_amount: this.form_data.service_charge_amount,
                    isBar: this.form_data.isBar,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async addGuest() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/room_order/updateNoGuest",
                {
                    no_guest: this.form_data.no_guest,
                    id: this.cart_current,
                    order_no: this.form_data.order_no,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("No Guest updated");
                this.modal.guest_modal = false;
                this.order_data = res.data;
            } else {
                this.servo();
            }
        },
        cartClck(value) {
            this.cart_current = value.id;
        },
        async addNote() {
            this.showLoader();
            const res = await this.callApi("post", "data/room_order/addNote", {
                id: this.cart_current,
                notes: this.form_data.notes,
                order_no: this.form_data.order_no,
            });
            this.hideLoader();

            if (res.status === 200) {
                this.successNotif("Note Added ");
                this.order_data = res.data;
                this.hide();
                this.form_data.notes = "";
                this.modal.notes = false;
            } else {
                this.form_error(res);
            }
        },
        async createOrder(data) {
            this.showLoader();

            this.menu_current = data.id;
            this.form_data.stock_id = data.id;
            this.form_data.price = data.selling_price;
            this.form_data.unit_id = data.unit_id;
            this.form_data.cost_price = data.cost_price + data.production_cost;
            var tax_rate = data.tax_dept.tax_rate;
            var amount = data.selling_price * this.form_data.qty;
            var tax_amount = this.calculateTax(tax_rate, amount);
            this.form_data.row_vat = tax_amount;

            this.form_data.vat_rate = tax_rate;
            this.form_data.cost_total =
                this.form_data.cost_price * this.form_data.qty;

            this.form_data.sub_total = amount;
            this.calculateServiceCharge();
            this.form_data.row_total =
                amount + this.form_data.service_charge_amount;
            const res = await this.callApi(
                "POST",
                "data/room_order/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.form_data.accompaniment_id = null;
                this.order_data = res.data;
                this.form_data.qty = 1;
            } else {
                this.form_error(res);
            }
        },
        async fetchStock(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/stock/fetchPos", {
                params: {
                    page: page,
                    category_id: this.params.category_id,
                    department_id: this.form_data.department_id,
                    search: this.search,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.stock_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchAllCategoriesStock() {
            this.select_category = "All Categories";
            this.params.category_id = null;
            this.current = "";
            this.active.show_category = true;
            this.active.show_products = false;
            //  await this.fetchStock(1);
        },
        async getProductCategories() {
            const res = await this.getApi("data/product_category/fetchAll", {
                params: {
                    search: this.search,
                    department_id: this.form_data.department_id,
                },
            });

            if (res.status == 200) {
                this.product_category_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getPendingOrders() {
            const res = await this.callApi(
                "POST",
                "data/room_order/getPendingOrders",
                {
                    room_id: this.form_data.room_id,
                    order_no: this.form_data.order_no,
                }
            );

            if (res.status == 200) {
                if (res.data.status == "orders") {
                    this.order_data = res.data.results;
                    //  this.form_data.order_no = this.order_data[0].order_no;
                }
                if (res.data.status == "order_no") {
                    this.order_data = [];
                    this.form_data.order_no = res.data.results;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async cancelOrder() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/room_order/destroyOrder/" + this.form_data.order_no,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.order_data = [];
                } else {
                    this.servo();
                }
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/room_order/destroy",
                    {
                        id: id,
                    }
                );

                if (res.status === 200) {
                    this.successNotif("deleted ");
                    this.order_data.splice(i, 1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async newOrder() {
            this.$router.push("room_service");
        },
        async printOrder() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/room_order/completeOrder",
                {
                    order_no: this.form_data.order_no,
                }
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully added");

                var hasNewOrder = this.order_data.some(
                    (item) => item.orderPrinted === 0
                );
                if (hasNewOrder) {
                    this.$refs.childComponent.setValue(2.0);
                } else {
                    this.s("No new items added");
                }
                this.$router.push("room_service");
            } else {
                this.servo();
            }
            //  printJS("printMe", "html");
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getPendingOrders(),
                this.getProductCategories(),
                this.fetchStock(1),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
    },
};
</script>
<style scoped>
h3,
h4,
p,
button,
.category-items {
    font-family: "signika-light", sans-serif !important;
}
.cart-active {
    background: #bec5b7 !important;
}
.top-nav {
    background: #000;
    color: #fff;
    margin-bottom: 2px;
}
.menu-item {
    max-width: 25%;
    background: #d7ccc8;
}
.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
.order-printed {
    background: #c8e6c9 !important;
}
</style>
