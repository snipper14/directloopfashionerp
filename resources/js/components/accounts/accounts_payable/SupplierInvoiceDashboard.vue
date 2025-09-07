<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-3">
                <payable-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div class="col-md-3">
                        <v-btn
                            color="success"
                            @click="invoice_grouped_modal = true"
                            small
                            >Invoices Grouped by Supplier</v-btn
                        >
                    </div>
                    <div class="card-header-wrapper">
                        <h4>Supplier Invoices</h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="params.search"
                                placeholder="Search Customer,Order,Date"
                                aria-label="Search"
                            />
                        </form>
                    </div>
                    <div class="card-body">
                        <div
                            class="d-flex justify-content around mt-3 primary-color"
                        >
                            <div class="d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column mr-2">
                                <label> To Datetime *</label>
                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.to"
                                ></date-picker>
                            </div>

                            <div class="d-flex flex-column">
                                <span class="badge badge-primary totals-badge">
                                    Total Invoice :
                                    {{
                                        cashFormatter(
                                            supplier_total.sum_invoice
                                        )
                                    }}</span
                                >
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Supplier Name<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_supplier
                                            "
                                            sort-key="sort_supplier"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                    <th scope="col">Invoice No</th>
                                    <th scope="col">
                                        Date<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_invoice_date
                                            "
                                            sort-key="sort_invoice_date"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">Total Amount<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_invoice_total
                                            "
                                            sort-key="sort_invoice_total"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                    <th scope="col">Total Paid<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_paid_amount
                                            "
                                            sort-key="sort_paid_amount"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                    <th scope="col">Total Unpaid<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_unpaid_amount
                                            "
                                            sort-key="sort_unpaid_amount"
                                            @update-sort="updateSortParameter"
                                        /></th>

                                    <th scope="col">Additional Info</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(
                                        data, i
                                    ) in supplier_invoice_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.supplier.company_name }}
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="
                                                viewInvoiceDetail(data)
                                            "
                                            >{{ data.invoice_no }}
                                        </router-link>
                                    </td>
                                    <td>{{ data.invoice_date }}</td>
                                    <td>
                                        {{ cashFormatter(data.invoice_total) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.paid_amount) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.unpaid_amount) }}
                                    </td>
                                    <td>
                                        {{ data.description }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="supplier_invoice_data !== null"
                            v-bind:results="supplier_invoice_data"
                            v-on:get-page="fetchInvoices"
                        ></Pagination>
                        Items Count {{ supplier_invoice_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllInvoice"
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
                                    :fetch="getAllInvoice"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.csv"
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
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>

        <supplier-invoice-details
            :data="invoice_details"
            v-if="active.view_invoice"
            v-on:dashboard-active="setActiveRefresh"
        />

        <Modal v-model="invoice_grouped_modal" width="1200">
            <view-grouped-supplier-invoice
                v-if="invoice_grouped_modal"
                v-on:dashboard-active="dismissGroupedInvoice"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import PayableNav from "./PayableNav.vue";
import SupplierInvoiceDetails from "./SupplierInvoiceDetails.vue";
import Pagination from "../../utilities/Pagination.vue";
import NewSupplierInvoice from "./NewSupplierInvoice.vue";

import Unauthorized from "../../utilities/Unauthorized.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
import AddPaymentSupplierInvoice from "./AddPaymentSupplierInvoice.vue";
import ViewGroupedSupplierInvoice from "./ViewGroupedSupplierInvoice.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                new_invoice: false,
                view_invoice: false,
            },
            supplier_total: {},
            isLoading: true,
            invoice_grouped_modal: false,
            invoice_details: null,
            supplier_invoice_data: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],

            params: { search: "", to: null, from: null },
            sort_params: {
                sort_supplier: "",
                sort_invoice_date: "",
                sort_invoice_total:"",
                sort_paid_amount:"",
                sort_unpaid_amount:""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
        };
    },
    components: {
        Pagination,
        NewSupplierInvoice,
        SupplierInvoiceDetails,
        PayableNav,
        Unauthorized,
        AddPaymentSupplierInvoice,
        ViewGroupedSupplierInvoice,
        SortButtons,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
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
            await Promise.all([this.fetchInvoices(1), this.fetchTotal()]);
            this.isLoading ? this.hideLoader() : "";
        },
        dismissGroupedInvoice() {
            this.invoice_grouped_modal = false;
        },

        generateInvoice(data) {
            this.invoice_data = data;

            this.setActiveComponent("new_invoice");
        },
        async viewInvoiceDetail(data) {
            this.invoice_details = data;

            this.setActiveComponent("view_invoice");
        },
        editCustomer(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_customer");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllInvoice();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllInvoice() {
            const res = await this.getApi("data/supplier_invoice/fetch", {
                params: {
                    ...this.sort_params,
                    ...this.params,
                },
            });
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                    company_name: array_item.supplier.company_name,
                    invoice_no: array_item.invoice_no,
                    invoice_total: array_item.invoice_total,
                    paid_amount: array_item.paid_amount,
                    unpaid_amount: array_item.unpaid_amount,
                    invoice_date: array_item.invoice_date,
                    description: array_item.description,
                });
            });
            return data_array;
        },

        async searchRecords() {
            let page = 1;
            const res = await this.getApi("data/supplier_invoice/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.supplier_invoice_data = res.data.results;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchInvoices(1);
        },

        async fetchInvoices(page) {
            const res = await this.getApi("data/supplier_invoice/fetch", {
                params: {
                    page,

                    ...this.params,
                    ...this.sort_params,
                },
            });

            if (res.status === 200) {
                this.supplier_invoice_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchTotal() {
            const res = await this.getApi("data/supplier_invoice/fetchTotal", {
                params: {
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.supplier_total = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
