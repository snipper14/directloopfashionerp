<template>
    <div>
        <div v-if="isReadPermitted">
            <div class="jumbotron">
                <div class="row d-flex flex-column">
                    <div>
                        <h4 class="mt-4"><b>CUSTOMER ORDERS</b></h4>
                        <br />
                    </div>
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-primary" @click="goBack()">
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </button>
                        <h3 class="ml-3">Merge Order >></h3>

                        <input
                            type="text"
                            style="width: 200px"
                            placeholder="Search Order No"
                            class="form-control"
                            v-model="search"
                        />
                        <input
                            type="text"
                            style="width: 200px"
                            placeholder="Search "
                            class="form-control"
                            v-model="department"
                        />
                    </div>
                </div>
                <hr class="my-4" />
                <div class="row">
                    <div
                        class="col-4"
                        v-for="(data, i) in bills_data"
                        :key="i"
                        v-if="
                            data.order_no != $store.state.order_no_merge &&
                            data.isComplete == 1
                        "
                    >
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-4">
                                        <button
                                            @click="mergeBill(data.order_no)"
                                            class="btn btn-secondary"
                                        >
                                            Merge Bill
                                        </button>
                                    </div>
                                    <div class="col-7">
                                        <p>
                                            Order No: <b>{{ data.order_no }}</b>
                                        </p>
                                        <br />
                                        <p>
                                            Department:
                                            <b>{{
                                                data.department
                                                    ? data.department.department
                                                    : ""
                                            }}</b>
                                        </p>
                                        <br />
                                        <p>
                                            No Guest: <b>{{ data.no_guest }}</b>
                                        </p>
                                        <p>
                                            Waiter:
                                            <b>{{
                                                data.user ? data.user.name : ""
                                            }}</b>
                                        </p>
                                        <br />
                                    </div>
                                </div>
                                <order-details-component :data="data" />
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import _ from "lodash";
import ScrollWidgetComponent from "../../../utilities/ScrollWidgetComponent.vue";
import ItemParticularsComponent from "../dinerscomponents/ItemParticularsComponent.vue";
import OrderDetailsComponent from "../dinerscomponents/OrderDetailsComponent.vue";
export default {
    data() {
        return {
            bills_data: [],
            location: "",
            table: "",
            search: "",
            order_data: [],
            bill_current: "",
            department: "",
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
            },
        };
    },
    components: {
        Unauthorized,
        ScrollWidgetComponent,
        ItemParticularsComponent,
        OrderDetailsComponent,
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
        department: {
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    methods: {
        async mergeBill(order_no) {
            const isConfirmed = await this.confirmDialogue(
                "Confirm merge bill"
            );
            if (isConfirmed) {
                const res = await this.callApi(
                    "POST",
                    "data/pos_order/mergeBills",
                    {
                        original_order_no: this.$store.state.order_no_merge,
                        merged_order_no: order_no,
                    }
                );
                if (res.status == 200) {
                    this.s("successfully merged");
                    this.$router.push("bills");
                } else {
                    this.servo();
                }
            }
        },

        async getBills() {
            const res = await this.getApi("data/pos_order/fetchBills", {
                params: {
                    search: this.search,
                    department: this.department,
                    bill_printed_status:false,
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
b {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
</style>
