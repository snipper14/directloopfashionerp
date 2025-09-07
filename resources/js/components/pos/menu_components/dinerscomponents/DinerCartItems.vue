<template>
    <div>
        <div
            class="d-flex flex-column"
            style="height: 90%; overflow-x: hidden; overflow-y: auto"
        >
            <div style="height: 200px; overflow-x: hidden; overflow-y: auto">
                <div
                    :class="[
                        value.id,
                        cart_current === value.id ? 'cart-active' : '',
                    ]"
                    class="d-flex flex-column mt-2 mb-2"
                    v-for="(value, i) in order_data"
                    :key="i"
                    @click="cartClck(value)"
                >
                    <div class="row">
                        <div class="col-5">
                            <b class="cart-items">{{
                                value.stock ? value.stock.product_name : "NA"
                            }}</b>
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
                        <div class="col-4 d-flex justify-content-around">
                            <span>
                                <v-icon
                                    v-if="isUpdatePermitted"
                                    @click="crementOrder('add', value)"
                                    style="color: green"
                                    medium
                                    >{{ icons.mdiPlusThick }}</v-icon
                                >
                            </span>
                            <span>
                                <v-icon
                                    v-if="isUpdatePermitted"
                                    @click="crementOrder('minus', value)"
                                    style="color: black"
                                    medium
                                    >{{ icons.mdiMinusThick }}</v-icon
                                >
                            </span>
                            <b>
                                <v-icon
                                    v-if="isDeletePermitted"
                                    @click="deleteRecord(value.id, i)"
                                    style="color: red"
                                    medium
                                    >{{ icons.mdiTrashCanOutline }}</v-icon
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
                            <span class="badge badge-secondary cart-items">{{
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
                <div class="row d-flex" style="width: 200px; margin-left: 2rem">
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
                            v-if="keyboard_status"
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
                            <v-icon medium>{{ icons.mdiNotebookEdit }}</v-icon>
                            Note
                        </button>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <button
                            @click="transferTable()"
                            class="btn btn-outline-secondary btn-lg"
                            style="width: 100%"
                        >
                            Transfer
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
                            <v-icon medium>{{ icons.mdiPrinter }}</v-icon>
                            Bill
                        </button>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <button
                            v-if="isWritePermitted"
                            @click="splitBill()"
                            class="btn btn-outline-primary btn-lg"
                            style="width: 100%"
                        >
                            <v-icon medium>{{
                                icons.mdiArrowSplitVertical
                            }}</v-icon>
                            Split
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
                            <v-icon medium>{{ icons.mdiCashPlus }}</v-icon>
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
                    <div class="col-6">
                        <button
                            @click="mergeBill()"
                            v-if="isUpdatePermitted"
                            type="button"
                            class="btn btn-outline-warning btn-lg"
                        >
                            Merge Bill
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
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

export default {
    props: ["order_data"],
    data() {
        return {
            active: {
                show_category: true,
                show_products: false,
                view_cart: false,
              
            },
              total_order:0,
              total_tax:0,
              
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
        };
    },
};
</script>
