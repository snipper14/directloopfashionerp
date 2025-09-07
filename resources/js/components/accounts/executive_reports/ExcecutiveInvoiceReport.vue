<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <executive-nav />
            </div>

            <div class="col-md-10">
                <div class="row"></div>

                <div class="row">
                    <div class="card">
                        <div class="card-header-wrapper">
                           <div class="col-md-4">  <h4>Invoices Reports</h4>
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
                            <div class="col-md-4">
                                <div class="flex-row">
                                    <div class="d-flex flex-row mr-2">
                                        <span
                                            class="badge badge-info totals-badge"
                                        >
                                            Items Qty:
                                            {{
                                                cashFormatter(
                                                    invoice_total.sum_qty
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="d-flex flex-row">
                                        <span
                                            class="badge badge-primary totals-badge"
                                        >
                                            Total Invoice :
                                            {{
                                                cashFormatter(
                                                    invoice_total.invoice_total
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content around mt-3">
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

                                <div class="form-group col-md-2 nopadding">
                                    <label for=""> Select Location</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchLocation"
                                        :options="department_data"
                                        :auto-load-root-options="false"
                                        v-model="params.department_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Location "
                                    />
                                </div>
                                <div class="form-group col-md-2 nopadding">
                                    <label for=""> Select Customer</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchCustomer"
                                        :options="customer_select_data"
                                        :auto-load-root-options="false"
                                        v-model="params.customer_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Customer "
                                    />
                                </div>
                                <div class="form-group col-md-2 nopadding">
                                    <label for=""> Select User</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchUser"
                                        :options="user_data"
                                        :auto-load-root-options="false"
                                        v-model="params.user_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select User "
                                    />
                                </div>
                            </div>
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Dept</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">Invoice No</th>
                                        <th scope="col">Order No</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Items Qty</th>
                                        <th scope="col">Total Amount</th>
                                        <th scope="col">Total Paid</th>
                                        <th scope="col">Total Unpaid</th>
                                        <th scope="col">User</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in orders_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.department.department }}
                                        </td>
                                        <td>
                                            {{
                                                data.customer
                                                    ? data.customer.company_name
                                                    : ""
                                            }}
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
                                            {{
                                                cashFormatter(
                                                    data.invoice_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.amount_paid)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.unpaid_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                data.user ? data.user.name : ""
                                            }}
                                        </td>

                                        <td>
                                            <v-btn
                                                title="write"
                                                x-small
                                                color="primary"
                                                @click="viewInvoice(data)"
                                            >
                                                View Invoice
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <pagination
                                v-if="orders_data !== null"
                                v-bind:results="orders_data"
                                v-on:get-page="fetchInvoices"
                            ></pagination>
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
                </div>
            </div>
        </div>
        <view-invoice
            :data="invoice_data"
            v-if="active.view_invoice"
            v-on:dashboard-active="setActiveRefresh"
        />

        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiEyeCircleOutline,
} from "@mdi/js";

import ExecutiveNav from "./ExecutiveNav.vue";
import ViewInvoice from "../accounts_receivable/invoices/ViewInvoice.vue";
import Pagination from "../../utilities/Pagination.vue";
export default {
    data() {
        return {
            invoice_payment_modal: false,
            active: {
                dashboard: true,
                delivery_dash: false,
                view_invoice: false,
                view_delivery_notes: false,
            },
            new_data: null,
            orders_data: [],
            invoice_data: null,
            pdf_data: [],
            invoice_total: {},
            isLoading: true,
            user_data: null,
            customer_select_data: null,
            department_data: null,
            params: {
                search: "",
                department_id: null,
                user_id: null,
                customer_id: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiEyeCircleOutline,
            },
        };
    },
    components: {
        Treeselect,
        ExecutiveNav,
        ViewInvoice,
        Pagination,
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
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchInvoices(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },

        async fetchLocation() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/dept/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.department_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.department,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchCustomer() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/customers/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.customer_select_data = res.data.results.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.company_name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchUser() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.user_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchTotals() {
            const res = await this.getApi("data/invoices/fetchTotals", {
                params: {
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.invoice_total = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        makePayment(data) {
            this.invoice_data = data;
            this.invoice_payment_modal = true;
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
            this.showLoader();
            const res = await this.getApi("data/invoices/fetch", {
                params: {
                    ...this.params,
                },
            });
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    department: data.department.department,
                    comapny_name: data.customer.company_name,
                    invoice_no: data.invoice_no,
                    order_no: data.order_no,
                    invoice_date: data.invoice_date,

                    sum_qty: data.sum_qty,
                    invoice_total: data.invoice_total,
                    amount_paid: data.amount_paid,
                    unpaid_amount: data.unpaid_amount,
                    user: data.user.name,
                });
            });
            return data_array;
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        dismissModal() {
            this.invoice_payment_modal = false;
        },
        async fetchInvoices(page) {
            const res = await this.getApi("data/invoices/fetch", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.orders_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
