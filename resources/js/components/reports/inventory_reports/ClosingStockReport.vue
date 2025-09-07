<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><branch-inventory-nav /></div>

            <div class="col-md-8" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">Closing Stock</div>

                    <div class="card-body">
                        <!-- Date Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Filter From Date:</label>
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="from"
                                    
                                />
                            </div>
                         
                            <div class="col-md-3 d-flex align-items-end">
                                <v-btn x-small color="info" @click="filterDate">Filter Date</v-btn>
                                <v-btn x-small color="secondary" class="ms-2" @click="fetchAllData">Reset</v-btn>
                            </div>
                            <div class="col-md-3">
                                <input type="text" placeholder="Search" v-model="search" class="form-control">
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered custom-table">
                                <thead>
                                    <tr>
                                        <th>Stock</th>
                                        <th>Cummulative Qty</th>
                                        <th>Purchase Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(data, i) in closing_data.data" :key="i">
                                        <td>{{ data.stock_name }}</td>
                                        <td>{{ data.cummulative_qty }}</td>
                                        <td>{{cashFormatter(data.purchase_price)}}</td>
                                        <td>{{ cashFormatter(data.total_cost) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination :results="closing_data" @get-page="fetchClosingStock" />
                        </div>

                        <!-- Export Buttons -->
                        <div class="row mt-3" v-if="isDownloadPermitted">
                            <div class="col-8 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export me-2"
                                    :fetch="fetchExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="closing-stock.xls"
                                >
                                    <v-btn color="success" x-small>Export Excel</v-btn>
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export"
                                    :fetch="fetchExcel"
                                    type="csv"
                                    worksheet="My Worksheet"
                                    name="closing-stock.csv"
                                >
                                    <v-btn color="primary" x-small>Export CSV</v-btn>
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
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import BranchInventoryNav from "./BranchInventoryNav.vue";

export default {
    components: { Pagination, BranchInventoryNav, Unauthorized },
    data() {
      //  const today = new Date();
     //   const firstDayOfCurrentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
      //  const maxPastDate = new Date(firstDayOfCurrentMonth - 1); // Last day of previous month

        return {
            from:  this.getCurrentDate(),
            to: null,
            closing_data: [],
            search:"",
            is_loading:true
           // maxPastDate: maxPastDate.toISOString().split("T")[0],
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
       watch: {
        search: {
          
            handler: _.debounce(function (val, old) {
            this.is_loading=false
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/closing_stock/fetch", {
                params: {
                    per_page: 1000000,
                    from: this.from,
                    search:this.search
                  
                },
            });
            this.hideLoader();

            return res.data.data.data.map((data) => ({
                stock_name: data.stock_name,
                cummulative_qty: data.cummulative_qty,
                purchase_price:data.purchase_price,
                total_cost: data.total_cost,
            }));
        },

        async filterDate() {
            this.concurrentApiReq();
        },

        async fetchAllData() {
            this.from = null;
            this.to = null;
            await this.concurrentApiReq();
        },

        async concurrentApiReq() {
            await this.fetchClosingStock(1);
        },

        async fetchClosingStock(page) {
           this.is_loading?this.showLoader():""
            const res = await this.getApi("data/closing_stock/fetch", {
                params: {
                    page,
                    from: this.from,
                   search:this.search
                },
            });
          
 this.is_loading?this.hideLoader():""
            if (res.status === 200) {
                this.closing_data = res.data.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
