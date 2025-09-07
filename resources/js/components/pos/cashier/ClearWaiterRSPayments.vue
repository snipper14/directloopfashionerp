<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><cashier-orders-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4><b> Clear Waiter Room Service Payments</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Order No</th>
                                        <th>Order Time</th>

                                        <th>Guest</th>
                                        <th>Waiter</th>
                                        <th>Cash Paid</th>
                                        <th>Mobile Money</th>
                                        <th>Card Pay</th>
                                        <th>Receipt Balance</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="large-tr"
                                        v-for="(data, i) in sales_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.order_no }}
                                        </td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>

                                        <td>{{ data.guest.name }}</td>
                                        <td>{{ data.waiter.name }}</td>
                                        <td>
                                            {{ cashFormatter(data.cash_pay) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.mpesa_pay) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.card_pay) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.receipt_balance
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.receipt_total
                                                )
                                            }}
                                        </td>

                                        <td>
                                            <button
                                                @click="clearPrintBills(data)"
                                                class="btn btn-primary btn-sm"
                                            >
                                                Clear & Print Receipt
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
            <Modal v-model="modal.details" title="Sale Details">
                <ClearWaiterRSBillsDetails
                    v-if="sales_data_details && modal.details"
                    :sales_data_details="sales_data_details"
                    v-on:cleared-bill="dismissDiag"
                />
            </Modal>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import CashierOrdersNav from "./CashierOrdersNav.vue";

import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from "../../utilities/Unauthorized.vue";
import ClearWaiterRSBillsDetails from "./ClearWaiterRSBillsDetails.vue";
export default {
    data() {
        return {
            search: "",

            sales_data_details: null,
            modal: { details: false },
            sales_data: [],
            total_sales: 0,
        };
    },
    components: {
        Pagination,
        CashierOrdersNav,

        Unauthorized,
        ClearWaiterRSBillsDetails,
    },

    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
    },
    methods: {
        dismissDiag() {
            this.modal.details = false;
            this.concurrentApiReq();
        },
        clearPrintBills(data) {
            this.sales_data_details = data;
            this.modal.details = true;
        },
        async fetchData() {
            const res = await this.getApi(
                "data/room_sale/fetchPendingWaiterSales",

                {
                    params: {
                        search: this.search,
                    },
                }
            );

            if (res.status == 200) {
                this.sales_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData()]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
<style scoped>
.is_deducted {
    background: red !important;
}
</style>
