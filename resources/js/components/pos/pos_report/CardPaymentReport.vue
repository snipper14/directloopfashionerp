<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><POSReportNav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Card Payment Report</b></h3>
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
                                    <label for="">Search As Seller</label>
                                    <treeselect
                                        :load-options="fetchWaiter"
                                        :options="waiter_select_data"
                                        :auto-load-root-options="false"
                                        v-model="search_params.user_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select User"
                                    />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Search As Cashier</label>
                                    <treeselect
                                        :load-options="fetchWaiter"
                                        :options="waiter_select_data"
                                        :auto-load-root-options="false"
                                        v-model="search_params.cashier_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Cashier"
                                    />
                                </div>
                            </div>
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
                            <div class="form-group col-md-3">
                                <label for="">Cashier Terminal </label>
                                <treeselect
                                    :load-options="fetchCashierAccounts"
                                    :options="terminal_select_data"
                                    :auto-load-root-options="false"
                                    v-model="
                                        search_params.card_terminal_account_id
                                    "
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Terminal "
                                />
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

                        <div class="total-display" v-if="isDownloadPermitted">
                            <span class="badge badge-secondary totals-badge"
                                >Total Sales:
                                {{ cashFormatter(total_sales) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total Card:
                                {{ cashFormatter(total_card) }}</span
                            >
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">
                                            Amount<v-btn
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

                                        <th scope="col">
                                            Card Paid<v-btn
                                                v-if="
                                                    sort_params.sort_card_pay ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortCard('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortCard('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th scope="col">Terminal</th>

                                        <th>Receipt No</th>

                                        <th scope="col">Cashier</th>
                                        <th scope="col">Seller</th>
                                        <th scope="col">Customer</th>
                                        <th>Branch</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>

                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.receipt_total
                                                    )
                                                }}</b
                                            >
                                        </td>

                                        <td>
                                            {{ cashFormatter(data.card_pay) }}
                                        </td>
                                        <td>
                                            {{
                                                data.card_terminal_account
                                                    .account
                                                    ? data.card_terminal_account
                                                          .account.account
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                                >{{
                                                    data.receipt_no
                                                }}</router-link
                                            >
                                        </td>

                                        <td>
                                            {{
                                                data.cashier
                                                    ? data.cashier.name
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            {{ data.user.name }}
                                        </td>
                                        <td>
                                            {{ data.customer.company_name }}
                                        </td>
                                        <td>
                                            {{ data.branch.branch }}
                                        </td>
                                        <td>
                                            <v-btn
                                                title="update"
                                                color="info"
                                                v-if="
                                                    data.credit_pay != 0 &&
                                                    isUpdatePermitted
                                                "
                                                @click="settleCredit(data)"
                                                x-small
                                                >Settle
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchCardPaymentReport"
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
            <div v-else><unauthorized/></div>
        </div>

        <Modal
            title="Order details"
            v-model="receipt_details_modal"
            width="700px"
            ><sale-details-report-modal
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
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import SettleCredit from "./SettleCredit.vue";
import SaleDetailsReportModal from "./SaleDetailsReportModal.vue";
export default {
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        AllSaleReportPrintComponent,
        AllSaleTotalPrintComponents,
        datetime,
        Treeselect,
        SettleCredit,
        SaleDetailsReportModal,
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
            order_type_obj: [
                { value: "", label: "All" },
                { value: "Dine In", label: "Dine In" },
                { value: "takeaway", label: "takeaway" },
                { value: "online", label: "online" },
            ],
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
        order_type_obj: {
            deep: true,
            handler: _.debounce(function (val) {
                this.isLoading = false;
                console.log(JSON.stringify(val));
                // this.concurrentApiReq();
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
        settleCredit(data) {
            this.settle_credit_data = data;
            this.settle_credit_model = true;
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

        async fetchCardPaymentReport(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchCardPaymentReport",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        ...this.sort_params,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchCardPaymentReport(1),
                this.fetchCardPaymentTotalReport(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchCardPaymentTotalReport() {
            const res = await this.getApi(
                "data/pos_sale/fetchCardPaymentTotalReport",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                const data = res.data;
                if (data) {
                    this.total_sales = data.sum_receipt_total;

                    this.total_card = data.sum_card_pay;
                }
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchExcel() {
            const res = await this.getApi(
                "data/pos_sale/fetchCardPaymentReport",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,

                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        created_at: this.formatDateTime(array_item.created_at),

                        card_pay: array_item.card_pay,
                        card_terminal_account: array_item.card_terminal_account
                            .account
                            ? array_item.card_terminal_account.account.account
                            : "",
                        receipt_total: array_item.receipt_total,
                        customer: array_item.customer
                            ? array_item.customer.company_name
                            : "",
                        cashier: array_item.cashier
                            ? array_item.cashier.name
                            : "",
                        sales_person: array_item.user
                            ? array_item.user.name
                            : "",
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
