<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Etims POS Sales Report</b></h3>
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
                        </div>

                        <div class="total-display"  v-if="isDownloadPermitted">
                            <span class="badge badge-secondary totals-badge"
                                >Total Sales:
                                {{ cashFormatter(total_sales) }}</span
                            >
                            <span class="badge badge-warning totals-badge"
                                >Total VAT: {{ cashFormatter(total_vat) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Cash:
                                {{ cashFormatter(total_cash) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total MPESA:
                                {{ cashFormatter(total_mpesa) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Card:
                                {{ cashFormatter(total_card) }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Credit:
                                {{ cashFormatter(total_credit) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total Loyalty Points:
                                {{ cashFormatter(loyalty_points) }}</span
                            >

                            <button
                                class="btn btn-primary btn-sm mr-2"
                                v-if="isDownloadPermitted"
                                @click="printTotals"
                            >
                                Print Totals
                            </button>
                        </div>
                        <div class="table-responsive custom-table">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">Amount</th>
                                        <th>Vat</th>
                                        <th scope="col">Cash Paid</th>
                                        <th scope="col">Mpesa Paid</th>
                                        <th scope="col">Card Paid</th>
                                       
                                        <th scope="col">Credit Paid</th>
                                        <th>EtimsVerifLink</th>
                                        <th>Receipt No</th>

                                        <th scope="col">Cashier</th>
                                        <th scope="col">Seller</th>
                                        <th scope="col">Customer</th>
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
                                            {{ cashFormatter(data.total_vat) }}
                                        </td>
                                        <td>
                                            <span
                                                :title="
                                                    'balance: ' +
                                                    cashFormatter(
                                                        data.receipt_balance
                                                    )
                                                "
                                            >
                                                {{
                                                    cashFormatter(
                                                        data.cash_pay +
                                                            data.receipt_balance
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.mpesa_pay) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.card_pay) }}
                                        </td>
                                      
                                        <td>
                                            {{ cashFormatter(data.credit_pay) }}
                                        </td>
                                        <td>
                                            <a
                                                :href="
                                                    data.invoiceVerificationUrl
                                                "
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                >{{ data.scuReceiptNo }} /{{
                                                    data.signature
                                                }}</a
                                            >
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                            >
                                                {{
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
                                            <span v-if="data.customer.pin">{{
                                                data.customer.pin
                                            }}</span>
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
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                        <div class="row"  v-if="isDownloadPermitted">
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
                                <div>
                                    <button
                                        @click="printDetailedData()"
                                        class="btn btn-primary btn-sm"
                                    >
                                        Print Detailed Records
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <AllSaleReportPrintComponent
            v-if="sale_data.data"
            :sale_data="sale_data"
            :from="from"
            :to="to"
            :total_sales="total_sales"
            :total_vat="total_vat"
            :total_cash="total_cash"
            :total_mpesa="total_mpesa"
            :total_card="total_card"
            :total_credit="total_credit"
            ref="AllSaleReportPrintComponent"
        />
        <AllSaleTotalPrintComponents
            ref="AllSaleTotalPrintComponents"
            :total_sales="total_sales"
            :total_vat="total_vat"
            :total_cash="total_cash"
            :total_mpesa="total_mpesa"
            :total_card="total_card"
            :total_credit="total_credit"
            :loyalty_points="loyalty_points"
            :total_refundable="total_refundable"
            :from="from"
            :to="to"
        />
        <Modal v-model="settle_credit_model">
            <SettleCredit
                v-if="settle_credit_model"
                :settle_credit_data="settle_credit_data"
                v-on:dismiss-credit="dismissCredit"
            />
        </Modal>
        <Modal
            title="Order details"
            v-model="receipt_details_modal"
            width="700px"
            ><sale-details-report-modal
                v-if="receipt_details_modal"
                :sale_details="sale_details"
                v-on:dismiss-details="dismissCredit"
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
            search_params: {
                user_id: null,
                cashier_id: null,
                order_type: null,
                sales_filter: "vscu_etims_sales",
            },
            sale_details: null,
            receipt_details_modal: false,
            settle_credit_model: false,
            settle_credit_data: null,
            order_type_obj: [
                { value: "", label: "All" },
                { value: "Dine In", label: "Dine In" },
                { value: "takeaway", label: "takeaway" },
                { value: "online", label: "online" },
            ],
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
            loyalty_points: 0,
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
        viewDetails(data) {
            this.sale_details = data;
            this.receipt_details_modal = true;
        },
        dismissCredit() {
            this.receipt_details_modal = false;
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
        async printDetailedData() {
            setTimeout(() => {
                this.$refs.AllSaleReportPrintComponent.printBill();
            }, 1000);
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchAllSales",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
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
                this.fetchData(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchTotals() {
            const res = await this.getApi(
                "data/pos_sale/fetchAllSaleTotal",

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
                    this.total_vat = data.sum_total_vat;
                    this.total_sales = data.sum_receipt_total;
                    this.total_cash = data.sum_cash_pay;
                    this.total_mpesa = data.sum_mpesa_pay;
                    this.total_card = data.sum_card_pay;
                    this.total_credit = data.sum_credit_pay;
                    this.loyalty_points = data.sum_loyalty_points;
                }
            } else {
                this.swr("Server error occurred");
            }
        },

        async printTotals() {
            setTimeout(() => {
                this.$refs.AllSaleTotalPrintComponents.printBill();
            }, 1000);
        },

        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/fetchAllSales", {
                params: {
                    ...this.search_params,
                    search: this.search,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                },
            });
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        receipt_total: array_item.receipt_total,
                        total_vat:array_item.total_vat,
                        cash_pay: array_item.cash_pay,
                        mpesa_pay: array_item.mpesa_pay,
                        card_pay: array_item.card_pay,
                        credit_pay: array_item.credit_pay,
                        loyalty_points: array_item.loyalty_points,
                        receipt_total: array_item.receipt_total,
                        invoiceVerificationUrl:
                            array_item.invoiceVerificationUrl,
                        scuReceiptNo: array_item.scuReceiptNo,
                        signature: array_item.signature,

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
