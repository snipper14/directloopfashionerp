<template>
    <div>
        <div v-if="isReadPermitted">
            <div class="jumbotron">
                <div class="row d-flex flex-column">
                    <div>
                        <h4 class="mt-4"><b>CUSTOMER BILLS</b></h4>
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

                        <input
                            type="text"
                            style="width: 200px"
                            placeholder="Search Order No"
                            class="form-control"
                            v-model="search"
                        />
                    </div>
                </div>
                <hr class="my-4" />
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Order No</th>
                                <th>Order Time</th>
                                <th>No Guest</th>
                                <th>Cashier</th>
                                <th>Order Type</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="large-tr"
                                :class="[
                                    data.order_no,
                                    bill_current === data.order_no
                                        ? 'bill-active'
                                        : '',
                                ]"
                                v-for="(data, i) in bills_data"
                                :key="i"
                            >
                                <td>
                                    {{ data.order_no }}
                                </td>
                                <td>
                                    {{ formatDateTime(data.created_at) }}
                                </td>
                                <td>{{ data.no_guest }}</td>
                                <td>{{ data.user.name }}</td>
                                <td>{{data.results[0].order_type}}</td>
                                <td>
                                    {{ cashFormatter(data.order_total) }}
                                </td>
                                <td>
                                    <button
                                        @click="cashoutBill(data)"
                                        v-if="isApprovePermitted"
                                        title="Approve"
                                        type="button"
                                        class="btn btn-warning btn-lg ml-2"
                                    >
                                        Cash-out Bill
                                    </button>
                                    <button
                                        class="btn btn-primary btn-lg"
                                        @click="viewDetails(data)"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
<!-- 
            <Modal v-model="cashout" fullscreen title="Payment">
                <waiter-cash-bill
                    v-if="cashout"
                    :order_no="order_no"
                    :order_data="order_data"
                    :total_tax="total_tax"
                    :total_order="total_order"
                    v-on:dismiss-diag="dismissDialog"
                />
            </Modal> -->
             <Modal v-model="cashout" fullscreen title="Payment">
            <bill-payment
                v-if="cashout"
                :order_no="order_no"
                :order_data="order_data"
                :total_tax="total_tax"
                :total_order="total_order"
                v-on:dismiss-diag="dismissDialog"
            />
        </Modal>
            <Modal v-model="details_modal" title="Bill Details">
                <waiter-bill-details
                    v-if="details_modal"
                    :bill_data="bill_data"
                />
            </Modal>
        </div>
        <div v-else>
            <unauthorized />
        </div>
        <scroll-widget-component />
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
import BillPayment from "../menu_components/BillPayment.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
import _ from "lodash";

import WaiterBillDetails from "./WaiterBillDetails.vue";
export default {
    data() {
        return {
            bills_data: [],
            bill_data: null,
            location: "",
            table: "",
            search: "",
            cashout: false,
            order_data: [],
            total_tax: 0,
            total_order: 0,
            order_no: "",
            bill_current: "",
            details_modal: false,
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
        Unauthorized,
        BillPayment,
        ScrollWidgetComponent,
        WaiterBillDetails,
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
        viewDetails(data) {
            this.details_modal = true;
            this.bill_data = data;
        },
        async cashoutBill(data) {
            if (this.bills_data.length > 0) {
                this.cashout = true;
                this.order_data = data.results;
                this.total_tax = data.total_tax;
                this.total_order = data.order_total;
                this.order_no = data.order_no;
            } else {
                this.swr("No Sale available");
            }
        },
        async dismissDialog() {
            this.cashout = false;

            this.concurrentApiReq();
        },
        async getBills() {
            const res = await this.getApi("data/pos_order/fetchAllBills", {
                params: {
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
</style>
