<template>
    <div>
        <div class="row justify-content-center" >
           

            <div class="col-md-10" >
                <div class="card">
                    <div class="card-header-wrapper">
                        <h3>Transactions</h3>
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
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Ref No</th>
                                      <th scope="col">Details</th>

                                    <th scope="col">Transaction Date</th>
                                    <th scope="col">Debit (Dr)</th>
                                    <th scope="col">Credit (CR)</th>
                                   
                                    <th scope="col">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in transaction_data.data"
                                    :key="i"
                                >
                                 
                                    <td>
                                        {{ data.ref }}
                                    </td>
                                   <td>
                                        {{ data.description }}
                                    </td>
                                    <td>{{ data.entry_date }}</td>
                                    <td>{{ data.debit }}</td>
                                    <td>{{ data.credit }}</td>
                                    <td>
                                        {{ cashFormatter(data.amount) }}
                                    </td>

                                  
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="transaction_data !== null"
                            v-bind:results="transaction_data"
                            v-on:get-page="fetchTransaction"
                        ></Pagination>
                        Items Count {{ transaction_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
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
          
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";

import Pagination from "../../utilities/Pagination.vue";

export default {
    data() {
        return {
          
            transaction_data: [],
           
            search: "",
            params: {
                name: "",
                description: ""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    created() {},
    components: {
        Pagination,
       
    },
    mounted() {
        this.fetchTransaction(1);
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        }
    },
    methods: {
       

        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.fetchExcel();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
     

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/sales/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.transaction_data = res.data.results;
            }
        },
      
      async fetchExcel(){
            this.showLoader();
            const res = await this.getApi("data/transaction/fetch", {
                params: {
                  
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.transaction_data = res.data;
            } else {
                this.servo()
            }
      },
        async fetchTransaction(page) {
            this.showLoader();
            const res = await this.getApi("data/transaction/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.transaction_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
