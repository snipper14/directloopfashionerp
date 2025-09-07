<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <ReceivableNav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <h4>Invoices Management</h4>
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
                        <div class="row d-flex justify-content around mt-3">
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
                        </div>

                        <div class="row">
                            <div class="col-md-3 d-flex flex-column mr-2">
                                <span class="badge badge-info totals-badge">
                                    Items Qty:
                                    {{
                                        cashFormatter(invoice_total.sum_qty)
                                    }}</span
                                >
                            </div>
                            <div class="col-md-3 d-flex flex-column">
                                <span class="badge badge-primary totals-badge">
                                    Total VAT :
                                    {{
                                        cashFormatter(invoice_total.total_vat)
                                    }}</span
                                >
                            </div>
                            <div class="col-md-4 d-flex flex-column">
                                <span class="badge badge-primary totals-badge">
                                    Sub-Total Invoice :
                                    {{
                                        cashFormatter(
                                            invoice_total.invoice_total -
                                                invoice_total.total_vat
                                        )
                                    }}</span
                                >
                            </div>
                            <div class="col-md-3 d-flex flex-column">
                                <span class="badge badge-primary totals-badge">
                                    Total Invoice :
                                    {{
                                        cashFormatter(
                                            invoice_total.invoice_total
                                        )
                                    }}</span
                                >
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Dept</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">Invoice No</th>
                                       
                                        <th scope="col">Date</th>
                                        <th scope="col">
                                            Items Qty

                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_qty
                                                "
                                                sort-key="sort_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Disc.</th>
                                        <th scope="col">
                                            Total Amount  <sort-buttons
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
                                            VAT <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_vat
                                                "
                                                sort-key="sort_vat"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Total Paid</th>
                                        <th scope="col">Total Unpaid</th>
                                        <th scope="col">User</th>
                                        <th>Actions</th>
                                        <th>CU Invoice No</th>
                                        <th>D. Note</th>
                                        <th>Details</th>
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
                                       
                                        <td>{{ data.invoice_date }}</td>
                                        <td>{{ data.sum_qty }}</td>
                                        <td>
                                            {{
                                                cashFormatter(data.sum_discount)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.invoice_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total_tax) }}
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
                                            <button>
                                                <v-menu
                                                    open-on-click
                                                    top
                                                    offset-y
                                                >
                                                    <template
                                                        v-slot:activator="{
                                                            on,
                                                            attrs,
                                                        }"
                                                    >
                                                        <v-btn
                                                            v-bind="attrs"
                                                            v-on="on"
                                                            x-small
                                                            color="secondary"
                                                        >
                                                            <v-icon small>{{
                                                                icons.mdiEyeCircleOutline
                                                            }}</v-icon
                                                            >Actions</v-btn
                                                        >
                                                    </template>
                                                    <v-btn
                                                        v-if="isWritePermitted"
                                                        title="write"
                                                        x-small
                                                        color="info"
                                                        @click="
                                                            getInvoicePrint(
                                                                data
                                                            )
                                                        "
                                                    >
                                                        Print Invoice
                                                    </v-btn>
                                                    <v-btn
                                                        v-if="isWritePermitted"
                                                        title="write"
                                                        x-small
                                                        color="primary"
                                                        @click="
                                                            viewInvoice(data)
                                                        "
                                                    >
                                                        View Invoice
                                                    </v-btn>
                                                    <br />
                                                    <v-btn
                                                        title="write"
                                                        v-if="isWritePermitted"
                                                        x-small
                                                        color="success"
                                                        @click="
                                                            viewDelivery(data)
                                                        "
                                                    >
                                                        View Delivery Notes
                                                    </v-btn>
                                                    <br />

                                                    <v-btn
                                                        title="write"
                                                        v-if="isWritePermitted"
                                                        x-small
                                                        color="info"
                                                        @click="
                                                            generateDelivery(
                                                                data
                                                            )
                                                        "
                                                    >
                                                        Generate Delivery Note
                                                    </v-btn>
                                                    <br />

                                                    <v-btn
                                                        title="write"
                                                        v-if="isWritePermitted"
                                                        x-small
                                                        color="warning"
                                                        @click="addNotes(data)"
                                                    >
                                                        Add Note
                                                    </v-btn>
                                                </v-menu>
                                            </button>
                                        </td>
                                        <td>
                                            {{ data.cu_invoice_no }}
                                        </td>
                                        <td>
                                            {{ data.d_note }}
                                        </td>
                                        <td>{{data.notes}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                <center v-else>
                    <b style="color: red; font-size: 1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
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
        <PrintInvoice
            v-if="print_invoice_modal"
            ref="PrintInvoice"
            :invoice_details="invoice_details"
            :print_invoice_amount="print_invoice_amount"
            :print_tax_amount="print_tax_amount"
            :print_discount_amount="print_discount_amount"
            :logo="logo"
            :css_path="css_path"
        />
        <Modal v-model="invoice_payment_modal">
            <make-invoice-payments
                :data="invoice_data"
                v-on:dashboard-active="dismissModal"
                v-if="invoice_payment_modal"
            />
        </Modal>
        <Modal v-model="add_notes_modal">
            <div v-if="add_notes_modal">
                <div class="form-group">
                    <label for=""
                        >Invoice Notes {{ line_details.invoice_no }}</label
                    >
                    <input
                        type="text"
                        placeholder="CU INVOICE NO"
                        v-model="notes"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Delivery Notes </label>
                    <input
                        type="text"
                        placeholder="Delivery Notes"
                        v-model="d_note"
                        class="form-control"
                    />
                </div>
                <button class="btn btn-primary btn-small" @click="saveNotes()">
                    Save
                </button>
            </div>
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ReceivableNav from "../ReceivableNav.vue";
import ViewInvoice from "./ViewInvoice.vue";
import DeliveryDashboard from "../delivery/DeliveryDashboard.vue";
import ViewDeliveryNotes from "../delivery/ViewDeliveryNotes.vue";
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiEyeCircleOutline,
} from "@mdi/js";
import MakeInvoicePayments from "./MakeInvoicePayments.vue";
import PrintInvoice from "./invoice_print_elem/PrintInvoice.vue";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            add_notes_modal: false,
            invoice_payment_modal: false,
            active: {
                dashboard: true,
                delivery_dash: false,
                view_invoice: false,
                view_delivery_notes: false,
            },
            print_invoice_modal: false,
            invoice_details: null,
            notes: null,
            d_note: "",
            line_details: null,
            customer_select_data: null,
            new_data: null,
            orders_data: [],
            invoice_data: null,
            pdf_data: [],
            invoice_total: {},
            print_invoice_amount: 0,
            print_tax_amount: 0,
            print_discount_amount: 0,
            isLoading: true,
            search: "",
            params: {
                name: "",
                description: "",
                customer_id: null,
            },
            sort_params: {
                sort_total: "",
                sort_qty: "",
                sort_vat: "",
            },
            css_path: "",
            logo: null,
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
        Pagination,
        ReceivableNav,
        DeliveryDashboard,
        ViewInvoice,
        ViewDeliveryNotes,
        MakeInvoicePayments,
        Treeselect,
        PrintInvoice,
        SortButtons,
    },
    mounted() {
        this.logo = this.$store.state.branch.img_url;

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
                this.fetchInvoices(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async getInvoicePrint(data) {
            const res = await this.getApi("data/invoices/getInvoicePrint", {
                params: {
                    invoice_no: data.invoice_no,
                    customer_id: data.customer_id,
                },
            });
            if (res.status == 200) {
                this.invoice_details = res.data;
                this.print_invoice_amount = data.invoice_total;
                this.print_tax_amount = data.total_tax;
                this.print_discount_amount = data.sum_discount;
                this.print_invoice_modal = true;

                setTimeout(() => {
                    this.$refs.PrintInvoice.printBill();
                    this.print_invoice_modal = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },
        addNotes(data) {
            this.line_details = data;
            this.add_notes_modal = true;
        },
        async saveNotes() {
            const res = await this.callApi("post", "data/invoices/saveNotes", {
                notes: this.notes,
                invoice_no: this.line_details.invoice_no,
                d_note: this.d_note,
            });

            if (res.status === 200) {
                this.s("successfully saved");
                this.line_details = null;
                this.add_notes_modal = false;
            } else {
                this.form_error(res);
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
                    ...this.sort_params
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
                    sum_discount: data.sum_discount,
                    invoice_total: data.invoice_total,

                    vat: data.total_tax,
                    amount_paid: data.amount_paid,
                    unpaid_amount: data.unpaid_amount,
                    user: data.user.name,
                    note: data.notes,
                    d_note: this.d_note,
                    cu_invoice_no:data.cu_invoice_no
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
                    ...this.sort_params,
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
