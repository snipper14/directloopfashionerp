<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><POSReportNav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Customer Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <label> To Datetime *</label>
                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="to"
                                ></date-picker>
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm mt-4"
                                    @click="filterDate()"
                                >
                                    Filter
                                </button>
                                <button
                                    class="btn btn-info btn-sm mt-4"
                                    @click="fetchAllData()"
                                >
                                    Fetch All
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Search..</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                            </div>

                            <div class="col-md-2 form-group">
                                <label for="">Branch Filter</label>
                                <treeselect
                                    :load-options="fetchBranch"
                                    :options="branch_select_data"
                                    :auto-load-root-options="false"
                                    v-model="search_params.branch_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Branch "
                                />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">
                                            Total Amount<v-btn
                                                v-if="
                                                    sort_params.sort_total ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortTotal('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortTotal('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>Total Cash</th>
                                        <th>Total Mpesa</th>
                                        <th>Total Card</th>
                                        <th>Total Credit</th>
                                        <th>Total Discount</th>
                                        <th>View Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.customer.company_name }}
                                        </td>

                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.sum_receipt_total
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.sum_cash_pay)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_mpesa_pay
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.sum_card_pay)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_credit_pay
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_discount_pay
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <v-btn
                                                title="update"
                                                color="info"
                                                @click="viewDetails(data)"
                                                x-small
                                                >View Details
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="searchCustomerSales"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                        <div class="row" v-if="isDownloadPermitted">
                            <div class="col-md-8 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>

        <Modal
            title="Customer Sales Details"
            v-model="receipt_details_modal"
            width="1200px"
            ><view-customer-sales-details-modal
                v-if="receipt_details_modal"
                :sale_details="sale_details"
        /></Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import POSReportNav from "./POSReportNav.vue";
import AllSaleReportPrintComponent from "./prints_components/AllSaleReportPrintComponent.vue";
import AllSaleTotalPrintComponents from "./prints_components/AllSaleTotalPrintComponents.vue";
import datetime from "vuejs-datetimepicker";

import SaleDetailsReportModal from "./SaleDetailsReportModal.vue";
import ViewCustomerSalesDetailsModal from "./ViewCustomerSalesDetailsModal.vue";
export default {
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        AllSaleReportPrintComponent,
        AllSaleTotalPrintComponents,
        datetime,

        SaleDetailsReportModal,
        ViewCustomerSalesDetailsModal,
    },
    data() {
        return {
            sale_details: null,
            receipt_details_modal: false,
            search_params: {
                user_id: null,
                cashier_id: null,
                order_type: null,
                card_terminal_account_id: null,
                branch_id: null,
            },
            sort_params: {
                sort_total: "",
                sort_card_pay: "",
            },
            settle_credit_model: false,
            settle_credit_data: null,

            branch_select_data: null,
            total_refundable: 0,
            search: "",
            sale_data: [],
            waiter_select_data: null,
            from: null,
            to: null,
            total_vat: 0,
            total_sales: 0,
            total_cash: 0,
            total_mpesa: 0,
            total_card: 0,
            total_credit: 0,
            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        sortTotal(stat) {
            Object.keys(this.sort_params).forEach((key) => {
                this.sort_params[key] = null;
            });
            this.sort_params.sort_total = stat;
        },
        sortCard(stat) {
            Object.keys(this.sort_params).forEach((key) => {
                this.sort_params[key] = null;
            });
            this.sort_params.sort_card_pay = stat;
        },
        viewDetails(data) {
            this.sale_details = data;
            this.receipt_details_modal = true;
        },
        dismissCredit() {
            this.settle_credit_model = false;
            this.concurrentApiReq();
        },

        async fetchWaiter() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.waiter_select_data = this.modifyWaiterSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyWaiterSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        async fetchBranch() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/branch/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.branch_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.branch,
                    };
                });
                // this.modifyBranchSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },

        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchAllData() {
            this.isLoading = true;
            this.from = null;
            this.to = null;
            this.concurrentApiReq();
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.searchCustomerSales(1)]).then(
                function (results) {
                    return results;
                }
            );

            this.isLoading ? this.hideLoader() : "";
        },
        async searchCustomerSales(page) {
            const res = await this.getApi(
                "data/pos_sale/searchCustomerSales",

                {
                    params: {
                        page,
                        ...this.search_params,
                        search: this.search,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        ...this.sort_params,
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchExcel() {
            const res = await this.getApi(
                "data/pos_sale/searchCustomerSales",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        ...this.sort_params,
                    },
                }
            );

            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        customer: data.customer.company_name,

                        receipt_total: data.sum_receipt_total,
                        total_cash: data.sum_cash_pay,

                        total_mpesa: data.sum_mpesa_pay,
                        credit_pay: data.sum_credit_pay,

                        discount: data.sum_discount_pay,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
