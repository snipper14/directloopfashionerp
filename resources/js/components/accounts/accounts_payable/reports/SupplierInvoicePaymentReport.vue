<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-3">
                <payable-report-nav />
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>Supplier Payments</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="params.search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label> From Date *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <label> To Date *</label>
                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.to"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <h4>
                                    <b>
                                        TOTAL PAYMENT:
                                        {{
                                            cashFormatter(
                                                payment_total.total_amount_paid
                                            )
                                        }}</b
                                    >
                                </h4>
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Company
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_supplier
                                            "
                                            sort-key="sort_supplier"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">Amount Paid  <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_amount_paid
                                            "
                                            sort-key="sort_amount_paid"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                    <th scope="col">
                                        Total Outstanding Balance
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_outstanding_balance
                                            "
                                            sort-key="sort_outstanding_balance"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                    <th scope="col">Ref No</th>
                                    <th>Date Paid</th>

                                    <th>Pay Method</th>
                                    <th>Details</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in payment_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{
                                            data.supplier
                                                ? data.supplier.company_name
                                                : ""
                                        }}
                                    </td>

                                    <td>
                                        <b>
                                            {{
                                                cashFormatter(data.amount_paid)
                                            }}</b
                                        >
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.outstanding_balance
                                            )
                                        }}
                                    </td>
                                    <td>{{ data.ref_no }}</td>
                                    <td>{{ data.date_paid }}</td>
                                    <td>{{ data.pay_method }}</td>
                                    <td>{{ data.payment_details }}</td>
                                    <td>{{ data.user.name }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="payment_data !== null"
                            v-bind:results="payment_data"
                            v-on:get-page="fetchInvoiceSupplierPaymentReport"
                        ></Pagination>
                        Items Count {{ payment_data.total }}
                    </div>
                    <div class="row">
                        <div class="col-4 d-flex">
                            <export-excel
                                class="btn btn-default btn-export ml-2"
                                :fetch="exportExcel"
                                worksheet="My Worksheet"
                                name="filename.xls"
                            >
                                <v-icon medium>{{
                                    icons.mdiMicrosoftExcel
                                }}</v-icon>
                                Export Excel
                            </export-excel>

                            <export-excel
                                class="btn btn-default btn-export ml-2"
                                :fetch="exportExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="filename.xls"
                            >
                                <v-icon class="v-icon" medium>{{
                                    icons.mdiFileExcel
                                }}</v-icon>
                                Export CSV
                            </export-excel>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import PayableReportNav from "./PayableReportNav.vue";
import Pagination from "../../../utilities/Pagination.vue";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_supplier: false,
                create_invoice: false,
                add_payment: false,
                view_details: false,
            },
            params: {
                search: "",
                from: null,
                to: null,
            },
            sort_params: {
                sort_supplier: "",
                sort_amount_paid:"",
                sort_outstanding_balance:""
            },
            isLoading: true,
            payment_total: {},

            supplier_invoice_data: {},
            payment_data: [],
            payable_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        PayableReportNav,
        Pagination,
        SortButtons,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
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
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchInvoiceSupplierPaymentReport(1),
                this.fetchInvoiceSupplierPaymentTotal(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        outstandingBalance(data) {
            const inv_total =
                data.invoice_sum.length > 0
                    ? data.invoice_sum[0].sum_invoice
                    : 0;
            const paid_amount =
                data.payment_totals.length > 0
                    ? data.payment_totals[0].total_amount_paid
                    : 0;
            return parseFloat(inv_total) - parseFloat(paid_amount);
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_payment/fetchInvoiceSupplierPaymentReport",
                {
                    params: {
                        ...this.sort_params,
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    supplier: data.supplier.company_name,
                    invoice_no: data.supplier_invoice.invoice_no,
                    po_no: data.supplier_invoice.po_no,
                    invoice_total: data.supplier_invoice.invoice_total,
                    amount_paid: data.amount_paid,
                    outstanding_balance: data.outstanding_balance,
                    ref_no: data.ref_no,
                    date_paid: data.date_paid,
                    pay_method: data.pay_method,
                    payment_details: data.payment_details,
                    user: data.user.name,
                });
            });
            return data_array;
        },

        viewDetails(data) {
            this.payable_data = data;
            this.setActiveComponent("view_details");
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchSupplierBalances();
        },

        async fetchInvoiceSupplierPaymentReport(page) {
            const res = await this.getApi(
                "data/supplier_payment/fetchInvoiceSupplierPaymentReport",
                {
                    params: {
                        page,
                        ...this.sort_params,
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.payment_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchInvoiceSupplierPaymentTotal() {
            const res = await this.getApi(
                "data/supplier_payment/fetchInvoiceSupplierPaymentTotal",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.payment_total = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
