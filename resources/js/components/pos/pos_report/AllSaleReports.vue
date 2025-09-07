<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><POSReportNav /></div>
            <div class="col-md-10" v-if="isDisplayPermitted">
                <div class="card">
                    <div class="card-header">
                        <h3><b>All Sales Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter From Date:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="from"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter To Date:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="to"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="filterDate()"
                                    >Filter Date</v-btn
                                >
                                <v-btn
                                    x-small
                                    color="secondary"
                                    @click="fetchAllData()"
                                    >Reset Date Filter</v-btn
                                >
                            </div>
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter From Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="from_time"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter To Date & time:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="datetime-local"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="to_time"
                                        placeholder="Select a datetime"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="filterDate()"
                                    >Filter Date $ Time</v-btn
                                >
                                <v-btn
                                    x-small
                                    color="secondary"
                                    @click="fetchAllData()"
                                    >Reset Datetime Filter</v-btn
                                >
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
                            <div class="col-md-2">
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
                            <div class="col-md-2">
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
                            <div class="col-md-2 form-group">
                                <label for="">Report Type</label>
                                <select
                                    v-model="search_params.report_type"
                                    class="form-control"
                                >
                                    <option value="">Select Report Type</option>
                                    <option
                                        v-for="(type, index) in reportTypes"
                                        :key="index"
                                        :value="type.value"
                                    >
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div
                            class="table-responsive"
                            v-if="isDownloadPermitted"
                        >
                            <table
                                class="table table-bordered table-sm stylish-table custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Description</th>
                                        <th scope="col">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="table-label">
                                            Total Sales:
                                        </td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_sales) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">Total VAT:</td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_vat) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">Total Cash:</td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_cash) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">
                                            Total MPESA:
                                        </td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_mpesa) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">Total Card:</td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_card) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">
                                            Total Credit:
                                        </td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_credit) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">
                                            Total Discount:
                                        </td>
                                        <td class="table-value">
                                            {{ cashFormatter(total_discount) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="table-label">
                                            Total Loyalty:
                                        </td>
                                        <td class="table-value">
                                            {{
                                                cashFormatter(
                                                    sum_loyalty_points
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="table-responsive" style="">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            Sale Date<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_created_at
                                                "
                                                sort-key="sort_created_at"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Branch</th>
                                        <th>
                                            Receipt No<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_receipt_no
                                                "
                                                sort-key="sort_receipt_no"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Amount
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total
                                                "
                                                sort-key="sort_total"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            Vat
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_vat
                                                "
                                                sort-key="sort_vat"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Cashier</th>
                                        <th scope="col">Seller</th>
                                        <th scope="col">Customer</th>
                                          <th scope="col">
                                            Discount Amt<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_discount_pay
                                                "
                                                sort-key="sort_discount_pay"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Cash Paid<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_cash_pay
                                                "
                                                sort-key="sort_cash_pay"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Mpesa Paid<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_mpesa_pay
                                                "
                                                sort-key="sort_mpesa_pay"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Card Paid<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_card_pay
                                                "
                                                sort-key="sort_card_pay"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Credit Paid<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_credit_pay
                                                "
                                                sort-key="sort_credit_pay"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                      
                                        <th>Loyalty Pay</th>

                                        <th></th>
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
                                                formatMonthDateTime(
                                                    data.created_at
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ data.branch.branch }}
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
                                            ><v-btn
                                                x-small
                                                color="info"
                                                @click="viewDetails(data)"
                                                >Open</v-btn
                                            >
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
                                            {{
                                                cashFormatter(data.discount_pay)
                                            }}
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
                                                        parseFloat(
                                                            data.cash_pay
                                                        ) +
                                                            parseFloat(
                                                                data.receipt_balance
                                                            )
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
                                            {{
                                                cashFormatter(
                                                    data.loyalty_points
                                                )
                                            }}
                                        </td>

                                        <td>{{ data.payment_ref }}</td>
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
                        <div class="row">
                            <div
                                class="col-md-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
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
                                    v-if="isDownloadPermitted"
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
                                        v-if="isDownloadPermitted"
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
            <div v-else>
                <unauthorized />
            </div>
        </div>

        <!-- <AllSaleReportPrintComponent
            v-if="print_data"
            :sale_data="print_data"
            :from="from"
            :to="to"
            :total_sales="total_sales"
            :total_vat="total_vat"
            :total_cash="total_cash"
            :total_mpesa="total_mpesa"
            :total_card="total_card"
            :total_credit="total_credit"
            ref="AllSaleReportPrintComponent"
        /> -->
        <AllSaleReportPrintComponent
            ref="AllSaleReportPrintComponent"
            v-if="print_data"
            :sale_data="print_data"
            :from="from"
            :to="to"
            :total_sales="total_sales"
            :total_vat="total_vat"
            :total_cash="total_cash"
            :total_mpesa="total_mpesa"
            :total_card="total_card"
            :total_credit="total_credit"
            :total_discount="total_discount"
        />
        <AllSaleTotalPrintComponents
            ref="AllSaleTotalPrintComponents"
            :total_sales="total_sales"
            :total_vat="total_vat"
            :total_cash="total_cash"
            :total_mpesa="total_mpesa"
            :total_card="total_card"
            :total_credit="total_credit"
            :total_refundable="total_refundable"
            :total_discount="total_discount"
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
import SortButtons from "../../utilities/SortButtons.vue";
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
        SortButtons,
    },
    data() {
        return {
            print_data: null,
            sale_details: null,
            receipt_details_modal: false,
            search_params: {
                report_type: "",
                user_id: null,
                cashier_id: null,
                order_type: null,
                branch_id: null,
            },
            sort_params: {
                sort_total: "",
                sort_vat: "",
                sort_cash_pay: "",
                sort_mpesa_pay: "",
                sort_card_pay: "",
                sort_receipt_no: "",
                sort_credit_pay: "",
                sort_created_at: "",
                sort_discount_pay: "",
            },
            reportTypes: [
                { value: "daily", label: "Daily" },
                { value: "bi-weekly", label: "Bi-Weekly" },
                { value: "monthly", label: "Monthly" },
                { value: "quarterly", label: "Quarterly" },
                { value: "yearly", label: "Yearly" },
            ],
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
            branch_select_data: null,
            from: null,
            to: null,
            from_time: null,
            to_time: null,
            total_vat: 0,
            total_sales: 0,
            total_cash: 0,
            total_mpesa: 0,
            total_card: 0,
            total_credit: 0,
            total_discount: 0,
            sum_loyalty_points: 0,
            isLoading: true,
        };
    },
    mounted() {
        //   const now = new Date();
        // this.from = this.setTime(new Date(), 0, 0);    // Midnight today
        // this.to = this.setTime(new Date(), 23, 59);
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
        from(newVal) {
            if (newVal) {
                this.from = this.setTime(newVal, 0, 0); // Set to 00:00
            }
        },
        to(newVal) {
            if (newVal) {
                this.to = this.setTime(newVal, 23, 59); // Set to 23:59
            }
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        viewDetails(data) {
            this.sale_details = data;
            this.receipt_details_modal = true;
        },

        dismissCredit() {
            this.hideLoader();
            this.settle_credit_model = false;
            this.receipt_details_modal = false;
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
                this.branch_select_data = this.modifyBranchSelect(res.data);
            } else {
                this.swr("Server error occurred");
            }
        },
        modifyBranchSelect(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.branch,
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
            this.from_time = null;
            this.to_time = null;
            this.concurrentApiReq();
        },
        async printDetailedData() {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_sale/fetchAllSales",

                {
                    params: {
                        ...this.search_params,
                        ...this.sort_params,
                        search: this.search,

                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        from_time: this.from_time,
                        to_time: this.to_time,
                    },
                }
            );
            this.hideLoader();

            if (res.status == 200) {
                this.print_data = res.data;
                setTimeout(() => {
                    this.$refs.AllSaleReportPrintComponent.printBill();
                }, 1000);
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchAllSales",

                {
                    params: {
                        ...this.search_params,
                        ...this.sort_params,
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        from_time: this.formatDateTime(this.from_time),
                        to_time: this.formatDateTime(this.to_time),
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
                        from_time: this.formatDateTime(this.from_time),
                        to_time: this.formatDateTime(this.to_time),
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
                    this.total_discount = data.sum_discount_pay;
                    this.sum_loyalty_points = data.sum_loyalty_points;
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
                    ...this.sort_params,
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
                        created: this.formatDateTime(array_item.created_at),
                        receipt_total: array_item.receipt_total,
                        cash_pay: array_item.cash_pay,
                        mpesa_pay: array_item.mpesa_pay,
                        card_pay: array_item.card_pay,
                        credit_pay: array_item.credit_pay,
                        discount_pay: array_item.discount_pay,
                        loyalty_points: array_item.loyalty_points,
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
                        payment_ref: array_item.payment_ref,
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
