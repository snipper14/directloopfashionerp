<template>
    <div>
        <div v-if="active.dashboard">
            <div v-if="isReadPermitted">
                <div class="jumbotron">
                    <div class="row d-flex flex-column">
                        <div>
                            <button
                                class="btn btn-warning"
                                @click="logoutPOS()"
                            >
                                Log out
                            </button>
                            <h4 class="mt-4"><b>CUSTOMER ORDERS</b></h4>
                            <br />
                        </div>
                        <div class="d-flex justify-content-between">
                            <button
                                class="btn btn-primary"
                                @click="$router.push('bills')"
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
                     <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Order No</th>
                                    <th>Order Time</th>
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
                                    @click="billClck(data)"
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
                                    <td>{{ data.no_guest }}</td>
                                    <td>{{ data.user.name }}</td>
                                    <td>
                                        {{ cashFormatter(data.order_total) }}
                                    </td>
                                    <td>
                                        <button
                                            @click="updateBillPrintStatus(data)"
                                            v-if="
                                                isDownloadPermitted &&
                                                data.isComplete == 1 &&
                                                 data.isBillPrinted == '0'
                                            "
                                            type="button"
                                            class="btn btn-secondary btn-lg"
                                        >
                                            <v-icon medium>{{
                                                icons.mdiPrinter
                                            }}</v-icon
                                            >Print Bill
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
            <div v-else>
                <unauthorized />
            </div>
        </div>
        <VoidBills
            :void_data="void_data"
            v-if="active.void_bills"
            v-on:dashboard-active="setActiveRefresh"
        />
         <Modal v-model="details_modal" title="Bill Details">
            <bill-details
                v-if="details_modal"
                :bill_data="bill_data"
                v-on:void_info="voidBillsFromChild"
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
import BillDetails from './BillDetails.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false,
            },
            void_data: null,
            bills_data: [],
            bill_data:null,
            location: "",
            bill_total: 0,
            department: "",
            search: "",
            details_modal:false,
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
    },
    mounted() {
        this.form_data.department_id =
            this.$store.state.dine_department_data.id;
        this.department = this.$store.state.dine_department_data.department;
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
        async updateBillPrintStatus(data) {
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
                this.print(data);
            } else {
                this.servo();
            }
        },
         viewDetails(data) {
            this.details_modal = true;
            this.bill_data = data;
        },
       
        voidBillsFromChild(data) {
            this.details_modal=false
            this.void_data = data;
            this.setActiveComponent("void_bills");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq(1);
        },
        splitBill(order_no) {
            this.$store.commit("setOrderNo", order_no);
            this.$router.push("split_bills");
        },
        async recollBill(data) {
            this.showLoader();
            const res = await this.getApi("data/pos_order/recallBill", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var value = data.results;

                var data_obj = {
                    department: value[0].department.department,
                    id: value[0].department_id,
                };
                this.$store.commit("setOrderNo2", data.order_no);
                this.$store.commit("setDineDepartment", data_obj);
                this.$store.commit("setOrderType", value[0].order_type);
                this.$router.push("dine_in");
            } else {
                this.servo();
            }
        },
        async deleteRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/pos_order/destroy",
                   {id: data.id}
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.concurrentApiReq();
                } else {
                    this.form_error(res);
                }
            }
        },
        async cancelOrder(order_no, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/pos_order/destroyOrder/" + order_no,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.bills_data.splice(i, 1);
                } else {
                    this.servo();
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
            }, 1000);
        },
        async getBills() {
            const res = await this.getApi("data/pos_order/fetchBills", {
                params: {
                    department_id: this.form_data.department_id,
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
.printed-bills {
    background: #ffb74d !important;
}
</style>
