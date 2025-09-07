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
                            <h5>Supplier Credit Note</h5>
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
                                        TOTAL CREDIT:
                                        {{
                                            cashFormatter(
                                                credit_total.sum_credit_amount
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
                                      <th scope="col">Supplier</th>
                                    <th scope="col">Credit No</th>
                                  
                                    
                                    <th scope="col">Amount </th>
                                    <th scope="col">
                                        Invoice No
                                    </th>
                                    <th scope="col">
                                        Invoice Amount
                                    </th>
                                  
                                    <th>Date </th>

                                   
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in creditnote_data.data"
                                    :key="i"
                                >
                   
                                <td>{{data.supplier.company_name}}</td>
                                <td>{{data.credit_no}}</td>
                                <td><b>{{cashFormatter(data.credit_amount)}}</b></td>
                                    <td>
                                        {{
                                            data.supplier_invoice
                                                .invoice_no
                                        }}
                                    </td>
                                 <td>{{cashFormatter(data.supplier_invoice.invoice_total)}}</td>
                                   
                                
                                    <td>{{ data.credit_date }}</td>
                                  
                                    <td>{{ data.user.name }}</td>
                                    <td><v-btn x-small color="danger" v-if="isDeletePermitted" title="delete" @click="deleteCredit(data.id,i)"> Delete</v-btn></td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="creditnote_data !== null"
                            v-bind:results="creditnote_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ creditnote_data.total }}
                    </div>
                    <div class="row" v-if="isDownloadPermitted">
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
            isLoading: true,
            credit_total: {},

            supplier_invoice_data: {},
            creditnote_data: [],
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
         async deleteCredit(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/supplier_creditnote/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                  this.creditnote_data.data.splice(i,1)
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetch(1),
                this.fetchTotal(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
      
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_creditnote/fetch",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    supplier:data.supplier.company_name,
                    credit_no: data.credit_no,
                    credit_amount: data.credit_amount,
                    invoice_no:  data.supplier_invoice
                                                .invoice_no,
                    invoice_total: data.supplier_invoice.invoice_total,
                    credit_date: data.credit_date,
                 
                    user: data.user.name,


                    
                });
            });
            return data_array;
        },

       
        

        async fetch(page) {
            const res = await this.getApi(
                "data/supplier_creditnote/fetch",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.creditnote_data = res.data;
               
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchTotal() {
            const res = await this.getApi(
                "data/supplier_creditnote/fetchTotal",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.credit_total = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
