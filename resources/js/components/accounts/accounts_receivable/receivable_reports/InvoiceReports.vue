<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-3">
                <receivable-reports-nav />
            </div>

            <div class="col-md-9" v-if="isReadPermitted">
                <div class="col-md-4">
                    <v-btn
                        small
                        color="info"
                        @click="grouped_report_modal = true"
                        >Customer Grouped Invoice</v-btn
                    >
                </div>
                <div class="card">
                    <div class="card-header-wrapper">
                        <h4><b>Customer Invoices Reports</b></h4>
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
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Invoice No</th>
                                    <th scope="col">Order No</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">
                                        Items Qty<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_qty
                                            "
                                            sort-key="sort_qty"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                    <th scope="col">
                                        Total Amount<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total
                                            "
                                            sort-key="sort_total"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">View Delivery No</th>
                                    <th scope="col">View Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in orders_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.customer.company_name }}
                                    </td>
                                    <td>
                                        {{ data.invoice_no }}
                                    </td>
                                    <td>
                                        {{ data.order_no }}
                                    </td>
                                    <td>{{ data.invoice_date }}</td>
                                    <td>{{ data.sum_qty }}</td>
                                    <td>
                                        {{ cashFormatter(data.invoice_total) }}
                                    </td>

                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="viewDelivery(data)"
                                        >
                                            View Delivery Notes
                                        </router-link>
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="viewInvoice(data)"
                                        >
                                            View Invoice
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="orders_data !== null"
                            v-bind:results="orders_data"
                            v-on:get-page="fetchInvoices"
                        ></Pagination>
                        Items Count {{ orders_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllSales"
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
                                    :fetch="getAllSales"
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
            <div v-else>
                <unauthorized />
            </div>
        </div>
        <view-invoice
            :data="invoice_data"
            v-if="active.view_invoice"
            v-on:dashboard-active="setActiveRefresh"
        />
        <delivery-dashboard
            :data="invoice_data"
            v-if="active.delivery_dash"
            v-on:dashboard-active="setActiveRefresh"
        />
        <view-delivery-notes
            :data="invoice_data"
            v-if="active.view_delivery_notes"
            v-on:dashboard-active="setActiveRefresh"
        />
        <Modal v-model="grouped_report_modal" width="1200px">
            <customer-grouped-invoice-report-modal
                v-if="grouped_report_modal"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import ReceivableReportsNav from "./ReceivableReportsNav.vue";
import ViewInvoice from "../invoices/ViewInvoice.vue";
import ViewDeliveryNotes from "../delivery/ViewDeliveryNotes.vue";
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import CustomerGroupedInvoiceReportModal from "./CustomerGroupedInvoiceReportModal.vue";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                delivery_dash: false,
                view_invoice: false,
                view_delivery_notes: false,
            },
            grouped_report_modal: false,
            new_data: null,
            orders_data: [],
            invoice_data: null,
            pdf_data: [],
            sort_params: {
                sort_qty: "",
                sort_total: "",
            },
            params: { search: "" },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        ReceivableReportsNav,
        ViewInvoice,
        ViewDeliveryNotes,
        Unauthorized,
        CustomerGroupedInvoiceReportModal,
        SortButtons,
    },
    mounted() {
        this.showLoader();
        this.fetchInvoices(1);
    },
    watch: {
        params: {
            handler: _.debounce(function (val, old) {
                this.fetchInvoices(1);
            }, 500),

            deep: true,
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.fetchInvoices(1);
            }, 500),
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        viewInvoice(data) {
            this.invoice_data = data;

            this.setActiveComponent("view_invoice");
        },
        generateInvoice(data) {
            this.invoice_data = data;

            this.setActiveComponent("new_invoice");
        },
        generateDelivery(data) {
            this.invoice_data = data;

            this.setActiveComponent("delivery_dash");
        },
        viewDelivery(data) {
            this.invoice_data = data;

            this.setActiveComponent("view_delivery_notes");
        },

        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllSales();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllSales() {
            const res = await this.getApi("data/invoices/fetch", "");
            var data_array = [];
            res.data.map((array_item) => {
                data_array.push({
                    company_name: array_item.customer.company_name,
                    items: array_item.product.name,
                    unit: array_item.unit.unit,
                    qty: array_item.qty,
                    item_price: array_item.price,

                    row_total: array_item.row_total,
                    invoice_no: array_item.invoice_no,
                    order_no: array_item.order_no,
                    invoice_date: array_item.invoice_date,
                    user: array_item.user.name,
                });
            });
            return data_array;
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchInvoices(1);
        },

        async fetchInvoices(page) {
            const res = await this.getApi("data/invoices/fetch", {
                params: {
                    page,
                    ...this.sort_params,
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.orders_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
