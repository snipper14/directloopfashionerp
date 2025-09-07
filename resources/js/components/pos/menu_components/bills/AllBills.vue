<template>
    <div>
        <div v-if="active.dashboard" class="col-12">
            <div v-if="isReadPermitted">
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
                                    <th>Order Type</th>
                                    <th>No Guest</th>
                                    <th>Cashier</th>
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
                                    <th>{{ data.results[0].order_type }}</th>
                                    <td>{{ data.no_guest }}</td>
                                    <td>{{ data.user.name }}</td>
                                    <td>
                                        {{ cashFormatter(data.order_total) }}
                                    </td>
                                    <td>
                                        <v-btn
                                         v-if="
                                               
                                                data.isComplete == 1
                                            "
                                            @click="updateBillPrintStatus(data)"
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
                                           v-if="
                                               
                                                data.isComplete == 1 &  isApprovePermitted
                                            "
                                            @click="cashoutBill(data)"
                                            title="Approve"
                                            color="info"
                                        >
                                            <v-icon medium>mdi-cash</v-icon>
                                            Cash
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <BillPrint
                            v-if="order_data.length > 0"
                            :order_data="order_data"
                            :bill_total="bill_total"
                            ref="printBillComponent"
                        />
                    </div>
                </div>
            </div>
        </div>

        <Modal
            v-model="details_modal"
            title="Bill Details"
            :styles="{ top: '20px' }"
        >
            <bill-details
                v-if="details_modal"
                :bill_data="bill_data"
                v-on:void_info="voidBillsFromChild"
                v-on:dismiss-modal="dismissModal"
            />
        </Modal>
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
        <void-bills
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
import BillPrint from "../orders/BillPrint.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import _ from "lodash";
import VoidBills from "./VoidBills.vue";
import BillDetails from "./BillDetails.vue";
import BillPayment from "../BillPayment.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false,
            },
            cashout: false,
            bill_data: [],
            details_modal: false,
            void_data: null,
            bills_data: [],
            location: "",
            bill_total: 0,
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
        BillPrint,
        Unauthorized,
        VoidBills,
        BillDetails,
        BillPayment,
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
                this.total_tax = data.total_tax;
                this.total_order = data.order_total;
                this.order_no = data.order_no;
            } else {
                this.swr("No Sale available");
            }
        },
        async updateBillPrintStatus(data) {
            // const shouldPrint = await this.confirmDialogue(
            //     "You want to print this bill"
            // );
            // if (!shouldPrint) {
            //     return;
            // }
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/pos_order/updateBillPrintStatus",
                {
                    order_no: data.order_no,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                let index = this.bills_data.findIndex(function (item, i) {
                    return item.order_no === data.order_no;
                });

                this.bills_data[index].isBillPrinted = "1";
                //print bills on UI
               // this.print(data);
            } else {
                this.servo();
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
            this.bill_total = data.order_total;

            setTimeout(() => {
                this.$refs.printBillComponent.printBill();
                this.order_data = [];
            }, 1000);
        },
        async getBills() {
            const res = await this.getApi("data/pos_order/fetchBills", {
                params: {
                    // department_id: this.form_data.department_id,
                    search: this.search,
                    order_type: "Dine In",
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
