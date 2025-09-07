<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-3">
                <payable-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <h4>Supplier Credit Note</h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="search"
                                placeholder="Search Customer,Order,Date"
                                aria-label="Search"
                            />
                        </form>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Supplier Name</th>
                                    <th scope="col">Invoice No</th>
                                    <th scope="col">Date</th>

                                    <th scope="col">Total Amount</th>
                                    <th scope="col">Total Paid</th>
                                    <th scope="col">Total Unpaid</th>
                                    
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
                                        {{ data.invoice_no }}
                                      
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
                                     <td>
                                       <v-btn color="info" small v-if="isUpdatePermitted" title="update" @click="creditNoteModal(data)">Raise CreditNote</v-btn>
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

                     
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
       
        <Modal v-model="show_creditnote_modal" >
            <add-supplier-credit-note-modal
                :data="invoice_details"
                v-if="show_creditnote_modal"
                v-on:dashboard-active="dismissPaymentModal"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import PayableNav from "../PayableNav.vue";

import Pagination from "../../../utilities/Pagination.vue";

import Unauthorized from "../../../utilities/Unauthorized.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
import AddSupplierCreditNoteModal from './AddSupplierCreditNoteModal.vue';

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
            show_creditnote_modal: false,
            invoice_details: null,
            supplier_invoice_data: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            search: "",
            params: {
                name: "",
                description: "",
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
       
        PayableNav,
        Unauthorized,
        AddSupplierCreditNoteModal,
        
    },
    mounted() {
        this.fetchInvoices(1);
    },
    watch: {
        params: {
            handler() {
                this.searchRecords();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchRecords();
            }
        },
    },
    methods: {
        creditNoteModal(data){
            this.invoice_details=data
             this.show_creditnote_modal = true;
        },
        dismissPaymentModal() {
            this.show_creditnote_modal = false;
        },
        showMakePaymentModal(data) {
            this.invoice_details = data;
            this.show_creditnote_modal = true;
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
            const res = await this.getApi("data/supplier_invoice/fetchAll", "");
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                    company_name: array_item.supplier.company_name,
                    invoice_no: array_item.invoice_no,
                    invoice_total: array_item.invoice_total,
                     paid_amount: array_item.paid_amount,
                   unpaid_amount: array_item.unpaid_amount,
                    invoice_date: array_item.invoice_date,
                    description: array_item.description
                  
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
            this.showLoader();
            const res = await this.getApi("data/supplier_invoice/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.supplier_invoice_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
