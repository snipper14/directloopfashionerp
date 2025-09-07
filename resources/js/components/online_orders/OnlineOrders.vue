<template>
    <div class="row">
        <div v-if="active.dashboard" class="col-md-11 ml-6 mt-4">
            <div v-if="isReadPermitted">
                <div class="row d-flex flex-column ml-6">
                    <div>
                        <button class="btn btn-warning" @click="logoutPOS()">
                            Log out
                        </button>
                        <h4 class="mt-4">
                            <b style="color: #870000; font-weight: 600"
                                >ONLINE ORDERS</b
                            >
                        </h4>
                        <br />
                    </div>
                    <div class="d-flex justify-content-between">
                        <button
                            class="btn btn-primary"
                            @click="$router.push('posmenu')"
                        >
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </button>
                        <h3 class="ml-3">{{ department }}</h3>

                        <input
                            type="text"
                            style="width: 200px"
                            placeholder="Search Order No"
                            class="form-control"
                            v-model="search"
                        />
                        <div class="ml-4">
                            <span class="badge badge-pill printed-bills"
                                >Bill Printed</span
                            >
                        </div>
                    </div>
                </div>
                <hr class="my-4" />
                <div class="jumbotron">
                    <div class="row d-flex flex-row">
                        <div class="d-flex justify-content-between">
                            <input
                                type="text"
                                style="width: 200px"
                                placeholder="Search Order No"
                                class="form-control"
                                v-model="search"
                            />
                        </div>
                        <div class="ml-4">
                            <span class="badge badge-pill printed-bills"
                                >Bill Printed</span
                            >
                        </div>
                    </div>

                    <hr class="my-4" />

                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Order No</th>
                                    <th>Order Time</th>

                                    <th>Customer</th>
                                    <th>Address</th>
                                    <th>Total</th>
                                    <th>Total Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    :class="[
                                        data.order_no,
                                        bill_current === data.order_no
                                            ? 'bill-active'
                                            : '',
                                    ]"
                                    v-for="(data, i) in bills_data"
                                    :key="i"
                                >
                                    <td
                                        :class="[
                                            {
                                                'printed-bills':
                                                    data.isBillPrinted == '1',
                                            },
                                        ]"
                                    >
                                        {{ data.order_no }}
                                    </td>
                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>

                                    <td>
                                        {{ data.customer_name + " : "
                                        }}{{ data.customer_phone + " : " }}
                                    </td>
                                    <td>{{ data.customer_address }}</td>
                                    <td>
                                        {{ data.sum_qty }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.order_total) }}
                                    </td>
                                    <td class="d-flex flex-row">
                                        <v-btn
                                            v-if="data.orderPrinted == '0'"
                                            @click="
                                                printOrder(
                                                    data.order_no,
                                                    data.results,
                                                    i
                                                )
                                            "
                                            type="button"
                                            color="warning"
                                        >
                                            <v-icon medium>{{
                                                icons.mdiPrinter
                                            }}</v-icon>
                                            Order
                                        </v-btn>
                                        <v-btn
                                            @click="
                                                updateBillPrintStatus(data, i)
                                            "
                                            type="button"
                                            color="secondary"
                                        >
                                            <v-icon medium>{{
                                                icons.mdiPrinter
                                            }}</v-icon>
                                            Bill
                                        </v-btn>
                                        <v-btn
                                            color="primary"
                                            @click="viewDetails(data)"
                                        >
                                            View
                                        </v-btn>
                                        <v-btn
                                            v-if="isApprovePermitted"
                                            @click="cashoutBill(data)"
                                            title="Approve"
                                            type="button"
                                            color="info"
                                        >
                                            <v-icon medium>mdi-cash</v-icon>Cash
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <PrintOnlineBill
                            v-if="order_data.length > 0"
                            :order_data="order_data"
                            ref="printBillComponent"
                        />
                    </div>
                </div>
                <div class="col-4">
                    <OrderPrint
                        v-if="order_data.length > 0 && print_order"
                        :order_data="order_data"
                        ref="childComponent"
                    />
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>

        <Modal
            v-model="details_modal"
            title="Bill Details"
            :styles="{ top: '20px' }"
        >
            <online-bill-details
                v-if="details_modal"
                :bill_data="bill_data"
                v-on:void_info="voidBillsFromChild"
                v-on:dismiss-modal="dismissModal"
            />
        </Modal>
        <Modal v-model="cashout" fullscreen title="Payment">
            <cashout-orders
                v-if="cashout"
                :order_no="order_no"
                :order_data="order_data"
                :total_tax="total_tax"
                :total_order="total_order"
                v-on:dismiss-diag="dismissDialog"
            />
        </Modal>
        <button @click="scrollPageUp" class="btn-scroll-up">
            <v-icon class="scroll-icon" medium>{{
                icons.mdiArrowUpCircleOutline
            }}</v-icon>
        </button>
        <button @click="scrollPageDown" class="btn-scroll-down">
            <v-icon class="scroll-icon" medium>{{
                icons.mdiArrowDownCircleOutline
            }}</v-icon>
        </button>
        <void-online-bills
            :void_data="void_data"
            v-if="active.void_bills"
            v-on:dashboard-active="setActiveRefresh"
        />
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
    mdiArrowUpCircleOutline,
    mdiArrowDownCircleOutline,
} from "@mdi/js";
import _ from "lodash";
import VoidBills from "../pos/menu_components/bills/VoidBills.vue";
import BillDetails from "../pos/menu_components/bills/BillDetails.vue";
import BillPrint from "../pos/menu_components/orders/BillPrint.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
import BillPayment from "../pos/menu_components/BillPayment.vue";
import OnlineBillDetails from "./OnlineBillDetails.vue";
import OrderPrint from "../pos/menu_components/orders/OrderPrint.vue";
import PrintOnlineBill from "./PrintOnlineBill.vue";
import CashoutOrders from './CashoutOrders.vue';
import VoidOnlineBills from './VoidOnlineBills.vue';

export default {
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false,
            },
            print_order: false,
            cashout: false,
            bill_data: [],
            details_modal: false,
            void_data: null,
            bills_data: [],
            location: "",

            department: "",
            search: "",
            order_data: [],
            bill_current: "",
            form_data: {
                department_id: "",
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
                mdiArrowUpCircleOutline,
                mdiArrowDownCircleOutline,
            },
        };
    },
    components: {
        VoidBills,
        BillDetails,
        BillPrint,
        Unauthorized,
        BillPayment,
        OnlineBillDetails,
        OrderPrint,
        PrintOnlineBill,
        CashoutOrders,
        VoidOnlineBills,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    methods: {
        async dismissDialog() {
            this.cashout = false;

            this.concurrentApiReq();
        },
        async cashoutBill(data) {
            if (this.bills_data.length > 0) {
                this.cashout = true;
                this.order_data = data.results;
                this.total_tax = 0;
                this.total_order = data.order_total;
                this.order_no = data.order_no;
            } else {
                this.swr("No Sale available");
            }
        },
        async updateBillPrintStatus(data, i) {
            this.print(data);
        },
        async printOrder(order_no, order, i) {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/online_orders/updateOrderPrintStatus",
                { order_no: order_no }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = order;

                this.bills_data[i].orderPrinted = "1";
                this.print_order = true;
                setTimeout(() => {
                    this.$refs.childComponent.setValue(2.0);
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        dismissModal() {
            this.details_modal = false;
            this.concurrentApiReq(1);
        },
        viewDetails(data) {
            this.details_modal = true;
            this.bill_data = data;
        },
        voidBillsFromChild(data) {
            this.details_modal = false;
            this.void_data = data;
            this.setActiveComponent("void_bills");
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq(1);
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        async deleteRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/pos_order/destroy",
                    { id: data.id }
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.concurrentApiReq();
                } else {
                    this.form_error(res);
                }
            }
        },
        billClck(data) {
            this.order_data = data.results;
            this.bill_current = data.order_no;
        },
        print(data) {
            this.order_data = data.results;

            setTimeout(() => {
                this.$refs.printBillComponent.printOnlineBill();
                this.order_data = [];
            }, 1000);
        },
        async getBills() {
            const res = await this.getApi("data/online_orders/fetchOrders", {
                params: {
                    // department_id: this.form_data.department_id,
                    search: this.search,
                },
            });

            if (res.status == 200) {
                this.bills_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getBills()]).then(function (
                results
            ) {
                return results;
            });
            this.hideLoader();
        },
    },
};
</script>
<style scoped>
.bill-active {
    background: #bec5b7 !important;
}
.bill-wrapper {
    height: 300px;
    overflow-x: hidden;
    overflow-y: auto;
}
.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
td {
    font-size: 1rem !important;
}
.printed-bills {
    background: #ffb74d !important;
}
</style>
