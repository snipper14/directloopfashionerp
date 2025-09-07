<template>
    <div>
        <div v-if="active.dashboard">
            <div v-if="isReadPermitted">
                <div class="jumbotron">
                    <div class="row d-flex flex-column">
                        <div>
                            <h4 class="mt-4"><b>Takeaway Orders</b></h4>
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
                                style="width:200px"
                                placeholder="Search Order No"
                                class="form-control"
                                v-model="search"
                            />
                        </div>
                    </div>
                    <hr class="my-4" />
                    <div class="row">
                        <div
                            class="col-4"
                            :class="[
                                data.order_no,
                                bill_current === data.order_no
                                    ? 'bill-active'
                                    : ''
                            ]"
                            v-for="(data, i) in bills_data"
                            :key="i"
                            @click="billClck(data)"
                        >
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-7">
                                            <br />
                                            <p>
                                                Order No:
                                                <b>{{ data.order_no }}</b>
                                            </p>
                                            <br />
                                        </div>
                                        <div class="col-5">
                                            <button
                                                @click="print(data)"
                                                v-if="isDownloadPermitted"
                                                type="button"
                                                class="btn btn-secondary btn-lg"
                                            >
                                                <v-icon medium>{{
                                                    icons.mdiPrinter
                                                }}</v-icon
                                                >Print Bill
                                            </button>
                                        </div>
                                    </div>
                                    <div class="bill-wrapper">
                                        <div
                                            class="d-flex flex-column mt-2 mb-2"
                                            v-for="(value, i) in data.results"
                                            :key="i"
                                        >
                                            <div class="row">
                                                <div class="col-5">
                                                    <b>{{
                                                        value.stock
                                                            ? value.stock
                                                                  .product_name
                                                            : "NA"
                                                    }}</b>
                                                </div>
                                                <div class="col-2">
                                                    <b> X </b>
                                                    <b> {{ value.qty }}</b>
                                                </div>
                                                <div class="col-3">
                                                    <b>
                                                        {{
                                                            cashFormatter(
                                                                value.row_total
                                                            )
                                                        }}</b
                                                    >
                                                </div>
                                                <div class="col-2">
                                                    <v-icon
                                                        v-if="isDeletePermitted"
                                                        @click="
                                                            deleteRecord(
                                                                value,
                                                                i
                                                            )
                                                        "
                                                        style="color:red"
                                                        medium
                                                        >{{
                                                            icons.mdiTrashCanOutline
                                                        }}</v-icon
                                                    >
                                                </div>
                                            </div>
                                            <div
                                                class="d-flex justify-content-start "
                                            >
                                                <p class="mr-2">Unit Price /</p>

                                                <p>
                                                    {{
                                                        cashFormatter(
                                                            value.price
                                                        )
                                                    }}
                                                </p>
                                                <p class="ml-2">
                                                    Guest
                                                    <span
                                                        class="badge badge-secondary"
                                                        >{{
                                                            value.no_guest
                                                        }}</span
                                                    >
                                                </p>
                                                <p class="ml-2">
                                                    {{ value.notes }}
                                                </p>
                                            </div>
                                            <hr />
                                        </div>
                                        <div
                                            class="d-flex justify-content-end mt-1 mr-4"
                                        >
                                            <span>
                                                <b>
                                                    TAX:
                                                    {{
                                                        cashFormatter(
                                                            data.total_tax
                                                        )
                                                    }}</b
                                                >
                                            </span>
                                        </div>
                                        <div
                                            class="d-flex justify-content-end mt-2 mr-4"
                                        >
                                            <span>
                                                <b>
                                                    TOTAL:
                                                    {{
                                                        cashFormatter(
                                                            data.order_total
                                                        )
                                                    }}</b
                                                >
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row  button-nav">
                                        <button
                                            @click="recollBill(data)"
                                            v-if="isUpdatePermitted"
                                            type="button"
                                            class="btn btn-warning btn-lg ml-2"
                                        >
                                            Recall Bill
                                        </button>
                                        <button
                                            @click="voidBill(data)"
                                            class="btn btn-outline-primary btn-lg"
                                            v-if="isUpdatePermitted"
                                        >
                                            <v-icon medium>{{
                                                icons.mdiArrowSplitVertical
                                            }}</v-icon>
                                            Void Bill
                                        </button>

                                        <button
                                            @click="
                                                cancelOrder(data.order_no, i)
                                            "
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
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <TakeAwayBillPrint
                            v-if="order_data.length > 0"
                            :order_data="order_data"
                            :bill_total="total_order"
                            :tax_total="total_tax"
                            :sub_total="sub_total"
                            :service_charge_total="service_charge_total"
                            ref="printBillComponent"
                        />
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>
        <VoidTakeAway
            :void_data="void_data"
            v-if="active.void_bills"
            v-on:dashboard-active="setActiveRefresh"
        />
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
    mdiArrowDownCircleOutline
} from "@mdi/js";
import TakeAwayBillPrint from "./TakeAwayBillPrint.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import _ from "lodash";
import VoidTakeAway from "./VoidTakeAway.vue";
import ScrollWidgetComponent from "../../utilities/ScrollWidgetComponent.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                void_bills: false
            },
            bills_data: [],

            search: "",
            order_data: [],
            bill_current: "",
            form_data: {
                room_id: ""
            },
            total_order: 0,
            isLoading: true,
            sub_total: 0,
            total_tax: 0,
            service_charge_total: 0,
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
                mdiArrowDownCircleOutline
            }
        };
    },
    components: {
        TakeAwayBillPrint,
        Unauthorized,
        VoidTakeAway,
        ScrollWidgetComponent
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.isLoading = false;
                this.concurrentApiReq(1);
            }, 500)
        }
    },
    methods: {
        voidBill(data) {
            this.void_data = data;

            this.setActiveComponent("void_bills");
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq(1);
        },
        async recollBill(data) {
            this.showLoader();
            const res = await this.getApi("data/take_away/recallBill", {
                params: {
                    order_no: data.order_no
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                var value = data.results;

                var data_obj = {
                    company_id: value[0].company_id
                };
                this.$store.commit("setOrderNoTakeAway", data.order_no);

                this.$store.commit("setOrderType", value[0].order_type);
                this.$router.push("takeaway_select_dept");
            } else {
                this.servo();
            }
        },
        async deleteRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/take_away/destroy/" + data.id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.concurrentApiReq();
                } else {
                    this.servo();
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
            this.total_order = data.order_total;
            this.sub_total = data.sum_sub_total;
            this.service_charge_total = data.sum_service_charge_amount;
            this.total_tax = data.total_tax;
            var ref = this.$refs;
            var ctx = this;
            ctx.showLoader();
            setTimeout(function() {
                ctx.hideLoader();
                ref.printBillComponent.printBill();
            }, 1000);
        },
        async getBills() {
            const res = await this.getApi("data/take_away/fetchBills", {
                params: {
                    search: this.search
                }
            });

            if (res.status == 200) {
                this.bills_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            const res = await Promise.all([this.getBills()]).then(function(
                results
            ) {
                return results;
            });
            this.isLoading ? this.hideLoader() : "";
        }
    }
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
