<template>
    <div class="container-fluid">
        <div class="row mt-4">
            <div class="col-4">
                <div>
                    <h4><b style="color:#0288d1">TAKEAWAY ORDERS</b></h4>
                </div>
                <div>Customer: {{ form_data.company_name }}</div>
                <div>Order No: {{ form_data.order_no }}</div>
                <br />

                <div
                    class="d-flex flex-column"
                    style="height:90%; overflow-x: hidden;
                overflow-y: auto;"
                >
                    <div
                        style="height:300px; overflow-x: hidden;
                overflow-y: auto;"
                    >
                        <div
                            :class="[
                                value.id,
                                cart_current === value.id ? 'cart-active' : ''
                            ]"
                            class="d-flex flex-column mt-2 mb-2"
                            v-for="(value, i) in order_data"
                            :key="i"
                            @click="cartClck(value)"
                        >
                            <div class="row">
                                <div class="col-5">
                                    <b>{{
                                        value.stock
                                            ? value.stock.product_name
                                            : "NA"
                                    }}</b>
                                </div>
                                <div class="col-2">
                                    <b> X </b> <b> {{ value.qty }}</b>
                                </div>
                                <div class="col-1">
                                    <b> {{ cashFormatter(value.row_total) }}</b>
                                </div>
                                <div
                                    class="col-4 d-flex justify-content-around"
                                >
                                    <span>
                                        <v-icon
                                            v-if="isUpdatePermitted"
                                            @click="crementOrder('add', value)"
                                            style="color:green"
                                            medium
                                            >{{ icons.mdiPlusThick }}</v-icon
                                        >
                                    </span>
                                    <span>
                                        <v-icon
                                            v-if="isUpdatePermitted"
                                            @click="
                                                crementOrder('minus', value)
                                            "
                                            style="color:black"
                                            medium
                                            >{{ icons.mdiMinusThick }}</v-icon
                                        >
                                    </span>
                                    <b>
                                        <v-icon
                                            v-if="isDeletePermitted"
                                            @click="deleteRecord(value.id, i)"
                                            style="color:red"
                                            medium
                                            >{{
                                                icons.mdiTrashCanOutline
                                            }}</v-icon
                                        ></b
                                    >
                                </div>
                            </div>
                            <div class="d-flex justify-content-start ">
                                <p class="mr-2">Unit Price /</p>

                                <p>{{ cashFormatter(value.price) }}</p>

                                <p class="ml-2">
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
                    <div class="mt-3 widget-wrapper">
                        <div class="row">
                            <div class="col-4">
                                <button
                                    @click="addNoteModal()"
                                    class="btn btn-outline-secondary btn-lg"
                                    style="width:100%"
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
                                    style="width:100%"
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
                                    style="width:100%"
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
                                    style="width:100%"
                                    @click="modal.add_cust = true"
                                >
                                    <v-icon style="color:#fff" medium>{{
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
                                    style="width:100%"
                                >
                                    <v-icon medium>{{
                                        icons.mdiCashPlus
                                    }}</v-icon>
                                    Cash Out
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <button
                                    @click="cancelOrder()"
                                    v-if="isDeletePermitted"
                                    type="button"
                                    class="btn btn-danger btn-lg"
                                >
                                    Cancel Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <take-away-order-print
                    v-if="order_data.length > 0"
                    :order_data="order_data"
                    ref="childComponent"
                />
                <TakeAwayBillPrint
                    v-if="order_data.length > 0"
                    :order_data="order_data"
                    :bill_total="total_order"
                    ref="printBillComponent"
                />
            </div>
            <div class="col-8">
                <div class="row top-panel d-flex justify-content-between ">
                    <button
                        class="btn btn-primary"
                        @click="$router.push('posmenu')"
                    >
                        <b>
                            <v-icon medium>{{ icons.mdiBackburger }}</v-icon>
                            BACK</b
                        >
                    </button>
                    <button class="btn btn-primary" @click="newOrder()">
                        New Order
                    </button>
                    <v-icon
                        @click="$router.push('posmenu')"
                        style="color:#fff"
                        medium
                        >{{ icons.mdiHome }}</v-icon
                    >

                    <div class="ml-2 d-flex flex-column align-items-center">
                        <span class="ml-2 d-flex justify-content-between">
                            <p style="color:#fff;" v-if="user">
                                {{ user.name }} >>
                            </p>
                            <p style="color:#fff;">
                                {{ form_data.order_type }} >>
                            </p>
                        </span>
                    </div>
                    <button class="btn btn-warning" @click="logout()">
                        Log out
                    </button>
                </div>
                <div class="row mt-3 d-flex " style="background:#cfd8dc">
                    <button
                        class="btn btn-primary"
                        @click="fetchAllCategoriesStock()"
                    >
                        All Category
                    </button>
                    <span class="badge badge-success ml-3" align="center"
                        ><h6>
                            <b>{{ select_category }}</b>
                        </h6></span
                    >
                    <div class="input-group ml-3 " style="width:50%">
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
                <div
                    class="row mt-4"
                    style="height:90%; overflow-x: hidden;
                overflow-y: auto;"
                >
                    <div class="col-2">
                        <div class="card" style="width:100%;">
                            <div class="card-header">
                                Categories
                            </div>
                            <ul class="list-group list-group-flush">
                                <li
                                    :class="[
                                        value.name,
                                        current === value.name ? 'active' : ''
                                    ]"
                                    @click="categoryChange(value)"
                                    v-for="(value, i) in product_category_data"
                                    :key="i"
                                    class="list-group-item category-list"
                                >
                                    <b>{{ value.name }}</b>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-10">
                        <div class="row" id="order-menu">
                            <div
                                :class="[
                                    value.id,
                                    menu_current === value.id
                                        ? 'menu-active'
                                        : ''
                                ]"
                                class="col-3 card menu-item"
                                v-for="(value, i) in stock_data.data"
                                :key="i"
                                @click="createOrder(value)"
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
                                    <span
                                        v-if="value.qty > 0"
                                        class="badge badge-success mt-1"
                                        ><b
                                            >Avail.
                                            {{ cashFormatter(value.qty) }}</b
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
                <Pagination
                    v-if="stock_data !== null"
                    v-bind:results="stock_data"
                    v-on:get-page="fetchStock"
                ></Pagination>
                Items Count {{ stock_data.total }}
            </div>

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
                <take-away-bill-payment
                    v-if="modal.cashout"
                    :order_no="form_data.order_no"
                    :order_data="order_data"
                    :total_tax="total_tax"
                    :total_order="total_order"
                    v-on:dismiss-diag="dismissDialog"
                />
            </Modal>
        </div>
        <notifications group="foo" />
        <scroll-widget-component />
    </div>
</template>

<script>
import _ from "lodash";
import AddCustomerToOrder from "../menu_components/AddCustomerToOrder.vue";
import TakeAwayBillPayment from "./TakeAwayBillPayment.vue";
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
    mdiArrowDownCircleOutline
} from "@mdi/js";
import TakeAwayOrderPrint from "./TakeAwayOrderPrint.vue";
import TakeAwayBillPrint from "./TakeAwayBillPrint.vue";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
export default {
    data() {
        return {
            visible: false,
            layout: "normal",
            input: null,

            options: {
                useKbEvents: false,
                preventClickEvent: false
            },
            modal: {
                notes: false,
                add_cust: false,
                cashout: false
            },
            product_category_data: [],
            stock_data: [],
            user: "",

            current: "",
            menu_current: "",
            cart_current: "",
            search: "",
            params: {
                category_id: null
            },
            order_data: [],
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
                mdiArrowUpCircleOutline,
                mdiArrowDownCircleOutline
            },

            form_data: {
                stock_id: null,
                unit_id: null,
                customer_id: "",
                department_id: "",
                qty: 0,
                price: 0,
                cost_price: 0,
                cost_total: 0,
                row_total: 0,
                row_vat: 0,
                no_guest: 1,
                notes: "",
                order_date: "",
                order_no: "",
                order_type: "Take Away",
                company_name: "Walk in"
            }
        };
    },
    created() {
        this.user = this.$store.state.user;

        this.form_data.order_date = this.getCurrentDate();

        this.form_data.order_no = this.$store.state.order_no_takeaway;
        this.form_data.department_id = this.$store.state.takeaway_department_data.id;
        this.$store.commit("setOrderNoTakeAway", "");
        this.$store.commit("setOrderNoTakeAway", "");
        this.concurrentApiReq();
    },
    components: {
        AddCustomerToOrder,
        TakeAwayBillPayment,
        Pagination,
        TakeAwayOrderPrint,
        TakeAwayBillPrint,
        ScrollWidgetComponent
    },

    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.isLoading = false;
                this.fetchStock(1);
            }, 500)
        },

        order_data: {
            deep: true,
            handler(val, old) {
                this.total_order = this.order_data.reduce(function(sum, val) {
                    return sum + val.row_total;
                }, 0);
                this.total_tax = this.order_data.reduce(function(sum, val) {
                    return sum + val.row_vat;
                }, 0);
            }
        }
    },
    methods: {
        async dismissDialog() {
            this.modal.cashout = false;
            this.order_data = [];
            this.$store.commit("setOrderNo2", "");
            this.$router.push("posmenu");
        },
        async cashout() {
            if (this.order_data.length > 0) {
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
                    "data/take_away/destroyOrder/" + this.form_data.order_no,
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
            if (this.order_data.length > 0) {
                this.showLoader();
                const res = await this.callApi(
                    "POST",
                    "data/take_away/updateOrderCustomer",
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

        async newOrder() {
            this.form_data.order_no = "";
            this.$router.push("takeaway_select_dept");
            // this.concurrentApiReq();
        },
        async printBill() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/take_away/validateBill",
                {
                    order_no: this.form_data.order_no
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                if (res.data == "1") {
                    this.$refs.printBillComponent.printBill();
                } else {
                    this.errorNotif("Complete Order before print bill");
                }
            } else {
                this.servo();
            }
        },
        async printOrder() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/take_away/completeOrder",
                {
                    order_no: this.form_data.order_no
                }
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully added");
                this.$refs.childComponent.setValue(2.0);
            } else {
                this.servo();
            }
            //  printJS("printMe", "html");
        },
        async addNote() {
            this.showLoader();
            const res = await this.callApi("post", "data/take_away/addNote", {
                id: this.cart_current,
                notes: this.form_data.notes,
                order_no: this.form_data.order_no
            });
            this.hideLoader();

            if (res.status === 200) {
                this.s("Note Added ");
                this.order_data = res.data;
                this.hide();
                this.form_data.notes = "";
                this.modal.notes = false;
            } else {
                this.form_error(res);
            }
        },
        cartClck(value) {
            this.cart_current = value.id;
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

        addNoteModal() {
            this.hide();
            this.modal.notes = true;
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/take_away/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.order_data.splice(i, 1);
                } else {
                    this.servo();
                }
            }
        },
        async crementOrder(type, value) {
            if (value.qty < 2 && type == "minus") {
                return;
            }
            var row_total = value.price;
            var row_vat = this.calculateTax(
                value.stock.tax_dept.tax_rate,
                row_total
            );
            this.form_data.cost_total = value.cost_price;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/take_away/crementOrder",
                {
                    qty: 1,
                    row_total: row_total,
                    row_vat: row_vat,
                    order_no: this.form_data.order_no,
                    stock_id: value.stock_id,
                    update_type: type,
                    cost_total: this.form_data.cost_total
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async createOrder(data) {
            this.showLoader();
            this.menu_current = data.id;
            this.form_data.qty = 1;
            this.form_data.stock_id = data.id;
            this.form_data.price = data.selling_price;
            this.form_data.unit_id = data.unit_id;
            this.form_data.cost_price = data.cost_price + data.production_cost;
            var tax_rate = data.tax_dept.tax_rate;
            var amount = data.selling_price * this.form_data.qty;
            var tax_amount = this.calculateTax(tax_rate, amount);
            this.form_data.cost_total =
                this.form_data.cost_price * this.form_data.qty;

            this.form_data.row_vat = tax_amount;
            this.form_data.row_total = amount;

            const res = await this.callApi(
                "POST",
                "data/take_away/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async logout() {
            this.$router.push("waiter_logout");
            window.location.reload();
        },
        async fetchAllCategoriesStock() {
            this.select_category = "All Categories";
            this.params.category_id = null;
            this.current = "";
            await this.fetchStock(1);
        },
        async categoryChange(value) {
            this.select_category = value.name;
            this.current = value.name;
            this.isLoading = true;
            this.params.category_id = value.id;
            await this.fetchStock(1);
        },
        async getProductCategories() {
            const res = await this.getApi("data/product_category/fetchAll", {
                params: {
                    search: this.search,
                    department_id: this.form_data.department_id
                }
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
                "data/take_away/getPendingOrders",
                {
                    order_no: this.form_data.order_no
                }
            );

            if (res.status == 200) {
                if (res.data.status == "orders") {
                    this.order_data = res.data.results;
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
                    search: this.search,
                    department_id: this.form_data.department_id
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.stock_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getPendingOrders(),
                this.getProductCategories(),
                this.fetchStock(1)
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        }
    }
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
.order-menu {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    flex-grow: 1;
    align-content: flex-start;
}
.menu-item {
    max-width: 25%;
    background: #ffe0b2;
}
</style>
