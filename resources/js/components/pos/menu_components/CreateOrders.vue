<template>
    <div class="container-fluid">
        <div class="row mt-1">
            <div class="col-4">
                <div class="d-flex">
                    <div>
                        <h4><b style="color: #0288d1">Dine In</b></h4>
                    </div>
                    <div>Customer: {{ form_data.company_name }}</div>
                    <div>Order No: {{ form_data.order_no }}</div>
                    <div>
                        <v-btn
                            small
                            color="info"
                            @click="active.view_cart = true"
                        >
                            Orders
                        </v-btn>
                    </div>
                    <br />
                </div>
                <div
                    class="d-flex flex-column"
                    style="height: 90%; overflow-x: hidden; overflow-y: auto"
                >
                    <div
                        style="
                            height: 200px;
                            overflow-x: hidden;
                            overflow-y: auto;
                        "
                    >
                        <div
                            :class="[
                                value.stock_id,
                                curr_stock_id === value.stock_id
                                    ? 'cart-active'
                                    : '',
                            ]"
                            class="d-flex flex-column mt-2 mb-2"
                            v-for="(value, i) in order_data_surface"
                            :key="i"
                            @click="cartClck(value)"
                        >
                            <div class="row">
                                <div class="col-5">
                                    <item-particulars-component :data="value" />
                                </div>
                                <div class="col-2">
                                    <b class="cart-items"> X </b>
                                    <b class="cart-items"> {{ value.qty }}</b>
                                </div>
                                <div class="col-1">
                                    <b class="cart-items">
                                        {{ cashFormatter(value.row_total) }}</b
                                    >
                                </div>
                                <div
                                    class="col-4 d-flex justify-content-around"
                                >
                                    <span>
                                        <v-icon
                                            v-if="isUpdatePermitted"
                                            @click="incrementOrder(value)"
                                            style="color: green"
                                            medium
                                            >{{ icons.mdiPlusThick }}</v-icon
                                        >
                                    </span>
                                    <span>
                                        <v-icon
                                            v-if="isUpdatePermitted"
                                            @click="decrementOrder(value)"
                                            style="color: black"
                                            medium
                                            >{{ icons.mdiMinusThick }}</v-icon
                                        >
                                    </span>
                                    <b>
                                        <v-icon
                                            v-if="isDeletePermitted"
                                            @click="deleteRecord(value, i)"
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

                                <p class="cart-items">
                                    {{ cashFormatter(value.price) }}
                                </p>
                                <p class="ml-2">
                                    Guest
                                    <span
                                        class="badge badge-secondary cart-items"
                                        >{{ value.no_guest }}</span
                                    >
                                </p>
                                <p class="ml-2 cart-items">
                                    {{ value.notes }}
                                </p>
                            </div>
                            <hr />
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <span>
                                <b> TOTAL: {{ cashFormatter(total_order) }}</b>
                            </span>
                        </div>
                        <div class="d-flex justify-content-end mt-1">
                            <span>
                                <b> TAX: {{ cashFormatter(total_tax) }}</b>
                            </span>
                        </div>
                    </div>
                    <div
                        v-if="isUpdateOrder"
                        style="
                            height: 200px;
                            overflow-x: hidden;
                            overflow-y: auto;
                        "
                    >
                        <cart-item-updated
                            :order_data_surface="order_pending"
                            :total_tax="total_tax"
                            :total_order="total_order"
                        />
                    </div>
                    <div class="mt-3 widget-wrapper">
                        <div
                            class="row d-flex"
                            style="
                                max-width: 300px;
                                margin-left: 2rem;
                                background: #cfd8dc;
                            "
                        >
                            <v-btn
                                color="primary"
                                v-if="false"
                                dark
                                small
                                @click="printOrder()"
                            >
                                <v-icon medium>{{ icons.mdiPrinter }}</v-icon>
                                Order</v-btn
                            >

                            <div class="d-flex">
                                <input
                                    style="max-width: 70px"
                                    type="number"
                                    v-model="form_data.qty"
                                    class="form-control"
                                />
                                <button
                                    class="btn btn-secondary btn-sm"
                                    @click="completeOrder()"
                                >
                                    <v-icon medium>{{
                                        icons.mdiPrinter
                                    }}</v-icon>
                                    Complete Order
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <custom-board
                                v-if="false"
                                    @pressed="form_data.qty = $event"
                                    :selfValue="form_data.qty"
                                />
                            </div>
                            <div class="col-md-4 col-sm-6">
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

                            <div class="col-md-4 col-sm-6">
                                <button
                                    class="btn btn-outline-success btn-lg"
                                    style="width: 100%"
                                    @click="addGuestModal()"
                                >
                                    Guest
                                    <span class="badge badge-light">{{
                                        form_data.no_guest
                                    }}</span>
                                </button>
                            </div>
                            <div class="col-md-4 col-sm-6">
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
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <button
                                    class="btn btn-secondary btn-lg"
                                    style="width: 100%"
                                    @click="modal.add_cust = true"
                                >
                                    <v-icon style="color: #fff" medium>{{
                                        icons.mdiAccountPlus
                                    }}</v-icon>
                                    Add Customer
                                </button>
                            </div>
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
                                    Cash Out
                                </button>
                            </div>
                        </div>
                        <div class="row"></div>
                    </div>
                </div>
                <OrderPrint
                    v-if="order_data_surface.length > 0"
                    :order_data="order_data_surface"
                    ref="childComponent"
                />
                <BillPrint
                    v-if="order_data.length > 0"
                    :order_data="order_data_surface"
                    :bill_total="total_order"
                    ref="printBillComponent"
                />
            </div>
            <div class="col-8">
                <div class="row top-panel d-flex justify-content-between">
                    <v-btn
                        small
                        color="primary"
                        v-if="active.show_category"
                        @click="$router.push('dine_in')"
                    >
                        <b>
                            <v-icon medium>{{ icons.mdiBackburger }}</v-icon>
                            BACK</b
                        >
                    </v-btn>
                    <v-btn color="warning" @click="newOrder()">
                        New Order
                    </v-btn>
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
                            <p style="color: #fff">
                                <b>{{ department }} >></b>
                            </p>
                        </span>
                    </div>
                    <v-btn small color="error" @click="logout()">
                        Log out
                    </v-btn>
                </div>
                <div class="row mt-3 d-flex" style="background: #cfd8dc">
                    <button
                        class="btn btn-primary"
                        @click="fetchAllCategoriesStock()"
                    >
                        View Categories
                    </button>
                    <span class="ml-3" align="center"
                        ><h6>
                            <b style="color: #43a047">{{ select_category }}</b>
                        </h6></span
                    >
                    <div class="input-group ml-3" style="max-width: 30%">
                        <input
                            placeholder="Search Product"
                            type="text"
                            v-model="search"
                            class="form-control"
                            aria-label="Sizing example input"
                            aria-describedby="inputGroup-sizing-default"
                        />
                    </div>

                    <h3 style="font-weight: 900">
                        {{ form_data.table_name }}
                    </h3>
                    <div class="input-group ml-3" style="max-width: 20%">
                        <label>Waiter/s</label>
                        <select
                            type="text"
                            class="form-control"
                            v-model="form_data.user_id"
                            @change="onWaiterChange($event)"
                        >
                            <option value="">Select</option>
                            <option
                                v-for="(data, i) in waiter_list"
                                :key="i"
                                v-bind:value="data.id"
                            >
                                {{ data.name }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <dine-in-category
                            v-if="
                                product_category_data.length > 0 &&
                                active.show_category
                            "
                            :product_category_data="product_category_data"
                            @categoryChange="categoryChange($event)"
                        />
                    </div>

                    <div class="col-10">
                        <div
                            class="row"
                            id="order-menu"
                            v-if="active.show_products"
                        >
                            <div
                                :class="[
                                    value.id,
                                    menu_current === value.id
                                        ? 'menu-active'
                                        : '',
                                ]"
                                class="col-md-3 col-sm-6 card menu-item"
                                v-for="(value, i) in stock_data.data"
                                :key="i"
                                @click="checkAccompaniment(value)"
                            >
                                <div
                                    class="card-body d-flex flex-column align-items-start"
                                >
                                    <img
                                        v-if="value.image && value.show_img"
                                        class="menu-image"
                                        alt=" image"
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
                                    <div>
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
            <Modal v-model="modal.guest_modal" title="Add Guest">
                <div class="row">
                    <div class="col-11">
                        <div class="form-group">
                            <label for="">NO OF GUEST</label>
                            <input
                                type="number"
                                @focus="show"
                                data-layout="normal"
                                v-model="form_data.no_guest"
                                class="form-control"
                            />
                        </div>
                    </div>

                    <div class="col-9 d-flex justify-content-center">
                        <button
                            class="btn btn-secondary"
                            type="secondary"
                            @click="modal.guest_modal = false"
                        >
                            Add Guest
                        </button>
                    </div>
                    <div class="col-10">
                        <custom-board
                            @pressed="form_data.no_guest = $event"
                            :selfValue="form_data.no_guest"
                        />
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
            <Modal v-model="modal.add_cust" width="800" title="Add Cust">
                <add-customer-to-order
                    @customerSearchResult="customerSearchResult"
                />
            </Modal>
            <Modal v-model="modal.cashout" fullscreen title="Payment">
                <bill-payment
                    v-if="modal.cashout"
                    :order_no="form_data.order_no"
                    :order_data="order_data_surface"
                    :total_tax="total_tax"
                    :total_order="total_order"
                    v-on:dismiss-diag="dismissDialog"
                />
            </Modal>
            <Modal v-model="active.view_cart" fullscreen title="CART-ORDERS">
                <div class="row">
                    <div class="col-5">
                        <div
                            :class="[
                                value.stock_id,
                                curr_stock_id === value.stock_id
                                    ? 'cart-active'
                                    : '',
                            ]"
                            class="d-flex flex-column mt-2 mb-2"
                            v-for="(value, i) in order_data_surface"
                            :key="i"
                            @click="cartClck(value)"
                        >
                            <div class="row">
                                <div class="col-5">
                                    <b class="cart-items"
                                        >{{
                                            value.stock
                                                ? value.stock.product_name
                                                : "NA"
                                        }}
                                        <span
                                            v-if="
                                                value.accompaniment.product_name
                                            "
                                        >
                                            Served With
                                            {{
                                                value.accompaniment.product_name
                                            }}</span
                                        >
                                    </b>
                                </div>
                                <div class="col-2">
                                    <b class="cart-items"> X </b>
                                    <b class="cart-items"> {{ value.qty }}</b>
                                </div>
                                <div class="col-1">
                                    <b class="cart-items">
                                        {{ cashFormatter(value.row_total) }}</b
                                    >
                                </div>
                                <div
                                    class="col-4 d-flex justify-content-around"
                                >
                                    <span>
                                        <v-icon
                                            v-if="isUpdatePermitted"
                                            @click="incrementOrder(value)"
                                            style="color: green"
                                            medium
                                            >{{ icons.mdiPlusThick }}</v-icon
                                        >
                                    </span>
                                    <span>
                                        <v-icon
                                            v-if="isUpdatePermitted"
                                            @click="decrementOrder(value)"
                                            style="color: black"
                                            medium
                                            >{{ icons.mdiMinusThick }}</v-icon
                                        >
                                    </span>
                                    <b>
                                        <v-icon
                                            v-if="isDeletePermitted"
                                            @click="deleteRecord(value, i)"
                                            style="color: red"
                                            medium
                                            >{{
                                                icons.mdiTrashCanOutline
                                            }}</v-icon
                                        ></b
                                    >
                                </div>
                            </div>

                            <hr />
                        </div>

                        <div class="d-flex justify-content-end mt-2">
                            <span>
                                <b> TOTAL: {{ cashFormatter(total_order) }}</b>
                            </span>
                        </div>
                        <div class="d-flex justify-content-end mt-1">
                            <span>
                                <b> TAX: {{ cashFormatter(total_tax) }}</b>
                            </span>
                        </div>
                    </div>

                    <div class="col-4 mt-3 widget-wrapper">
                        <div class="row">
                            <div class="col-md-4 col-sm-6">
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

                            <div class="col-md-4 col-sm-6">
                                <button
                                    class="btn btn-outline-success btn-lg"
                                    style="width: 100%"
                                    @click="addGuestModal()"
                                >
                                    Guest
                                    <span class="badge badge-light">{{
                                        form_data.no_guest
                                    }}</span>
                                </button>
                            </div>
                            <div class="col-md-4 col-sm-6">
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

                            <div class="col-md-4 col-sm-6">
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
                            <div class="col-6">
                                <button
                                    class="btn btn-secondary btn-lg"
                                    style="width: 100%"
                                    @click="modal.add_cust = true"
                                >
                                    <v-icon style="color: #fff" medium>{{
                                        icons.mdiAccountPlus
                                    }}</v-icon>
                                    Add Customer
                                </button>
                            </div>
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
                                    Cash Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Modal>
        </div>
        <scroll-widget-component />

        <br />
        <Modal v-model="modal.add_accompaniment" fullscreen>
            <add-accompaniment
                :accomp_data="accomp_data"
                v-if="modal.add_accompaniment"
                v-on:send-accompaniment="getAccompaniment"
            />
        </Modal>
        <Modal v-model="modal.table_chooser" fullscreen>
            <table-chooser-component
                :accomp_data="accomp_data"
                v-if="modal.table_chooser"
                :department_id="form_data.department_id"
                v-on:set-table_id="setTableId"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import OrderPrint from "./orders/OrderPrint.vue";
import BillPrint from "./orders/BillPrint.vue";
import AddCustomerToOrder from "./AddCustomerToOrder.vue";
import BillPayment from "./BillPayment.vue";
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
    mdiArrowDownCircleOutline,
    mdiArrowUpCircleOutline,
} from "@mdi/js";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
import DineInCategory from "./dinerscomponents/DineInCategory.vue";
import CustomBoard from "./dinerscomponents/CustomBoard.vue";
import DinerCartItems from "./dinerscomponents/DinerCartItems.vue";
import AddAccompaniment from "./dinerscomponents/AddAccompaniment.vue";
import ItemParticularsComponent from "./dinerscomponents/ItemParticularsComponent.vue";
import TableChooserComponent from "./TableChooserComponent.vue";
import CartItemUpdated from "./CartItemUpdated.vue";
export default {
    data() {
        return {
            init_height: 0,
            keyboard_status: true,
            accomp_data: null,
            isUpdateOrder: false,
            order_pending: null,
            active: {
                show_category: true,
                show_products: false,
                view_cart: false,
            },
            visible: false,
            layout: "normal",
            input: null,

            options: {
                useKbEvents: false,
                preventClickEvent: false,
            },
            modal: {
                guest_modal: false,
                notes: false,
                add_cust: false,
                cashout: false,
                add_accompaniment: false,
                table_chooser: false,
            },
            product_category_data: [],
            stock_data: [],
            user: "",
            curr_stock_id: null,
            current: "",
            menu_current: "",
            cart_current: "",
            search: "",
            params: {
                category_id: null,
            },
            waiter_list: [],
            order_data: [],
            order_data_surface: [],
            total_order: 0,
            total_tax: 0,
            isLoading: false,
            select_category: "All Category",
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
                mdiArrowDownCircleOutline,
                mdiArrowUpCircleOutline,
            },
            department: "",

            form_data: {
                user_id: null,
                accompaniment_id: null,
                department_id: null,
                isBar: false,
                stock_id: null,
                table_id: null,
                unit_id: null,
                customer_id: "",
                qty: 1,
                price: 0,
                cost_price: 0,
                cost_total: 0,
                row_total: 0,
                row_vat: 0,
                no_guest: 1,
                notes: "",
                order_date: "",
                order_no: "",
                order_type: "Dine In",
                company_name: "Walk in",
                table_name: "",
                delivery_time: "",
                customer_address: "",
                company_phone: "",
            },
        };
    },
    created() {
        var tableInfo = JSON.parse(localStorage.getItem("tableInfo"));
        this.waiter_list = this.$store.state.waiter_list;
        this.form_data.user_id = this.$store.state.user.id;

        this.form_data.table_name = tableInfo.name;
        this.form_data.table_id = tableInfo.id;
        this.user = this.$store.state.user;

        this.form_data.department_id = tableInfo.department.id;
        // this.$store.state.dine_department_data.id;
        this.department = tableInfo.department.department; //this.$store.state.dine_department_data.department;
        this.form_data.order_date = this.getCurrentDate();
        this.form_data.isBar = this.department.toLowerCase().includes("bar");

        this.form_data.order_no = this.$store.state.order_no2;
        this.$store.commit("setOrderNo2", "");
        this.concurrentApiReq();
    },
    components: {
        OrderPrint,
        BillPrint,
        AddCustomerToOrder,
        BillPayment,
        Pagination,
        ScrollWidgetComponent,
        DineInCategory,
        CustomBoard,
        DinerCartItems,
        AddAccompaniment,
        ItemParticularsComponent,
        TableChooserComponent,
        CartItemUpdated,
    },

    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.fetchStock(1);
            }, 500),
        },

        order_data_surface: {
            deep: true,
            handler(val, old) {
                this.total_order = this.order_data_surface.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.row_total;
                },
                0);
                this.total_tax = this.order_data_surface.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.row_vat;
                },
                0);
            },
        },
    },
    methods: {
        onWaiterChange(event) {
            this.form_data.user_id = event.target.value;
        },
        setTableId(val) {
            console.log(JSON.stringify(val));
            this.modal.table_chooser = false;
            this.form_data.table_name = val.name;
            this.form_data.table_id = val.id;
        },
        getAccompaniment(data) {
            this.modal.add_accompaniment = false;

            if (data.data) {
                this.form_data.accompaniment_id = data.data.accompaniment_id;
            }
            this.createOrderOffline(data.accomp_data);
        },
        dismissBoard() {
            this.keyboard_status = this.keyboard_status;
        },
        transferTable() {
            this.$store.commit("setOrderNo", this.form_data.order_no);
            this.$router.push("transfer_table");
        },

        async dismissDialog() {
            this.modal.cashout = false;
            this.order_data_surface = [];
            this.$store.commit("setOrderNo2", "");
            this.$router.push("posmenu");
        },
        async cashout() {
            if (this.order_data_surface.length > 0) {
                this.modal.cashout = true;
            } else {
                this.errorNotif("No Sale available");
            }
        },
        async cancelOrder() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/pos_order/destroyOrder/" + this.form_data.order_no,
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
        async customerSearchResult(value) {
            this.form_data.customer_id = value.id;
            this.form_data.company_name = value.company_name;

            this.modal.add_cust = false;
            if (
                this.order_data_surface.length > 0 ||
                this.order_pending.length > 0
            ) {
                this.showLoader();
                const res = await this.callApi(
                    "POST",
                    "data/pos_order/updateOrderCustomer",
                    this.form_data
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.successNotif("Updated order customer");
                    this.order_data = res.data;
                } else {
                    this.servo();
                }
            }
        },
        splitBill() {
            this.$store.commit("setOrderNo", this.form_data.order_no);
            this.$router.push("split_bills");
        },
        async newOrder() {
            this.$router.push("dine_in");
        },
        async printBill() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/pos_order/updateBillPrintStatus",
                {
                    order_no: this.form_data.order_no,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.$router.push("dine_in");
                // if (res.data == "1") {
                //     this.$refs.printBillComponent.printBill();
                // } else {
                //     this.errorNotif("Complete Order before print bill");
                // }
            } else {
                this.form_error(res);
            }
        },
        async completeOrder() {
            if (this.order_data_surface.length == 0) {
                this.errorNotif("No sales available");
                return;
            }
            this.showLoader();
            const res = await this.callApi("post", "data/pos_order/create", {
                sales_orders: this.order_data_surface,
            });
            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully added");
                var hasNewOrder = this.order_data.some(
                    (item) => item.orderPrinted === 0
                );
                if (hasNewOrder) {
                    //disable browser printing
                    // this.$refs.childComponent.setValue(2.0);
                } else {
                    this.s("No new items added");
                }
                this.$router.push("dine_in");
            } else {
                this.form_error(res);
            }
        },
        async printOrder() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/pos_order/printOrder",
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
                this.$router.push("dine_in");
            } else {
                this.servo();
            }
            //  printJS("printMe", "html");
        },
        addNote() {
            var nwdata = this.order_data_surface
                .map((val) => val.stock_id)
                .indexOf(this.curr_stock_id);
            this.order_data_surface[nwdata].notes = this.form_data.notes;

            this.hide();
            this.form_data.notes = "";
            this.modal.notes = false;
        },

        cartClck(value) {
            this.curr_stock_id = value.stock_id;

            this.cart_current = value;
        },
        show(e) {
            this.input = e.target;

            this.layout = e.target.dataset.layout;
            if (!this.visible) this.visible = true;
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
        addGuestModal() {
            this.hide();
            this.modal.guest_modal = true;
        },
        addNoteModal() {
            this.hide();
            this.modal.notes = true;
        },
        async deleteRecord(val, i) {
            this.order_data_surface.splice(i, 1);
            this.s("deleted ");
        },
        incrementOrder(val) {
            var nwdata = this.order_data_surface
                .map((val) => val.stock_id)
                .indexOf(val.stock_id);

            this.order_data_surface[nwdata].qty += 1;
            var amount = val.price * this.order_data_surface[nwdata].qty;
            this.order_data_surface[nwdata].row_vat = this.calculateTax(
                val.tax_rate,
                amount
            );
            this.order_data_surface[nwdata].row_total = amount;
        },
        decrementOrder(val) {
            if (val.qty < 2) {
                return;
            }
            var nwdata = this.order_data_surface
                .map((val) => val.stock_id)
                .indexOf(val.stock_id);

            this.order_data_surface[nwdata].qty -= 1;
            var amount = val.price * this.order_data_surface[nwdata].qty;
            this.order_data_surface[nwdata].row_vat = this.calculateTax(
                val.tax_rate,
                amount
            );
            this.order_data_surface[nwdata].row_total = amount;
        },

        async checkAccompaniment(data) {
           if(data.qty<1){
            this.errorNotif('Out of Stock')
            return false
           } 
            if (data.accompaniment.length > 0) {
                this.accomp_data = data;
                //   console.log(JSON.stringify(this.accomp_data));
                this.modal.add_accompaniment = true;
            } else {
                this.createOrderOffline(data);
            }
        },
        async createOrderOffline(data) {
            var amount = data.selling_price * 1;
            var row_vat = this.calculateTax(data.tax_dept.tax_rate, amount);
            this.order_data_surface.push({
                stock_id: data.id,
                price: data.selling_price,
                unit_id: data.unit_id,
                cost_price: data.cost_price + data.production_cost,
                tax_rate: data.tax_dept.tax_rate,
                qty: this.form_data.qty,
                row_vat: row_vat,
                customer_id: this.form_data.customer_id,
                accompaniment_id: this.form_data.accompaniment_id,
                row_total: data.selling_price * this.form_data.qty,
                department_id: this.form_data.department_id,
                isBar: false,
                table_id: this.form_data.table_id,
                cost_total:
                    (data.cost_price + data.production_cost) *
                    this.form_data.qty,
                no_guest: this.form_data.no_guest,
                notes: this.form_data.notes,
                order_date: this.getCurrentDate(),
                order_no: this.form_data.order_no,
                order_type: this.form_data.order_type,
                company_name: this.form_data.company_name,
                table_name: this.form_data.table_name,
                stock: data,
                accompaniment: data.accompaniment,
                delivery_time: this.form_data.delivery_time,
                address: this.form_data.customer_address,
                company_phone: this.form_data.company_phone,
                user_id: this.form_data.user_id,
            });

            let result = this.order_data_surface.reduce(
                (
                    r,
                    {
                        stock_id,
                        price,
                        unit_id,
                        cost_price,
                        tax_rate,
                        qty,
                        row_vat,
                        row_total,
                        department_id,
                        customer_id,
                        isBar,
                        table_id,
                        cost_total,
                        no_guest,
                        notes,
                        order_date,
                        order_no,
                        order_type,
                        company_name,
                        table_name,
                        stock,

                        accompaniment_id,
                        accompaniment,
                        delivery_time,
                        address,
                        company_phone,
                        user_id,
                    }
                ) => {
                    let key = `${stock_id}`;

                    if (r.has(key)) {
                        {
                           r.get(key).qty +=qty;
                            var amount = price * r.get(key).qty;
                            r.get(key).row_vat = this.calculateTax(
                                tax_rate,
                                amount
                            );
                            (r.get(key).row_total = amount),
                                (r.get(key).cost_total =
                                    cost_price * r.get(key).qty);
                        }
                    } else {
                        r.set(key, {
                            stock_id,
                            price,
                            unit_id,
                            cost_price,
                            tax_rate,
                            qty: qty,
                            row_vat: row_vat,
                            row_total: row_total,
                            cost_total: cost_total,
                            department_id: department_id,
                            isBar: isBar,
                            table_id: table_id,
                            customer_id: customer_id,
                            no_guest: no_guest,
                            notes: notes,
                            order_date: order_date,
                            order_no: order_no,
                            order_type: order_type,
                            company_name: company_name,
                            table_name: table_name,
                            stock: stock,

                            accompaniment_id: accompaniment_id,
                            accompaniment: accompaniment,
                            delivery_time: delivery_time,
                            address: address,
                            company_phone: company_phone,
                            user_id: user_id,
                        });
                    }

                    return r;
                },
                new Map()
            );

            this.order_data_surface = [...result.values()];
            this.form_data.qty=1
        },

        async logout() {
            this.logoutPOS();
        },
        async fetchAllCategoriesStock() {
            this.select_category = "All Categories";
            this.params.category_id = null;
            this.current = "";
            this.active.show_category = true;
            this.active.show_products = false;
            // await this.fetchStock(1);
        },
        async categoryChange(value) {
            this.search = "";
            this.select_category = value.name;
            this.current = value.name;
            this.isLoading = false;
            this.params.category_id = value.id;
            this.active.show_category = false;
            this.active.show_products = true;
            await this.fetchStock(1);
        },
        async getProductCategories() {
            const res = await this.getApi("data/product_category/fetchAll", {
                params: {
                    search: this.search,
                    department_id: this.form_data.department_id,
                    show_pos: "show_pos",
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
                "data/pos_order/getPendingOrders",
                {
                    order_no: this.form_data.order_no,
                }
            );

            if (res.status == 200) {
                if (res.data.status == "orders") {
                    this.order_pending = res.data.results;
                    this.form_data.table_id = res.data.results[0].table_id;
                    this.form_data.table_name = res.data.results[0].table.name;
                    this.form_data.customer_id =
                        res.data.results[0].customer_id;
                    this.form_data.company_name =
                        res.data.results[0].customer_name;
                    this.form_data.user_id = res.data.results[0].user_id;
                    this.isUpdateOrder = true;
                }
                if (res.data.status == "order_no") {
                    this.order_data = [];
                    this.form_data.order_no = res.data.results;
                }
            } else {
                this.swr("Server error occurred");
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
                    show_pos: "show_pos",
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.stock_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            //this.showLoader();
            const res = await Promise.all([
                this.getPendingOrders(),
                this.getProductCategories(),

                //  this.fetchStock(1),
            ]).then(function (results) {
                return results;
            });
            // this.hideLoader();
        },
    },
    // computed: {
    //     ...mapGetters({
    //         userInfo: "getUser",
    //         branchInfo: "getBranch"
    //     })
    // }
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
.top-panel {
    background: #212121;
}
.category-list:hover,
.menu-item:hover {
    cursor: pointer;
}
.active {
    background: #4caf50 !important;
}

.cart-active {
    background: #bec5b7 !important;
}
/* .order-menu {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    flex-grow: 1;
    align-content: flex-start;
} */
.menu-item {
    background: #ffe0b2;
}

.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
.order-printed {
    background: #c8e6c9 !important;
}
</style>
