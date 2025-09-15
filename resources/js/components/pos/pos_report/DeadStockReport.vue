<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Dead Stock for Last {{ search_params.no_days }} Days</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                           
                           <div v-if="false" class="col-md-2 form-group">
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
                            <div class="col-md-2 form-group" v-if="true" >
                                <label for="">Limit Qty</label>
                                <input type="number" class="form-control" v-model="search_params.qty_limit" placeholder="Enter Limit Qty">
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="">No of Days</label>
                                <input type="number" class="form-control" v-model="search_params.no_days" placeholder="Enter No of Days">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Item Code</th>
                                        <th scope="col">Item Name <input type="text" class="form-control" v-model="search_params.search" ></th>
                                       <th>Batch No</th>
                                       <th>Purchase Date</th>
                                       <th>Total Sold</th>
                                        <th>Current Qty  <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_qty
                                                "
                                                sort-key="sort_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in dead_stock_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.code }}
                                        </td>

                                        <td>
                                            <b> {{ data.name }}</b>
                                        </td>
                                        <td>{{data.batch_numbers}}</td>
                                         <td>{{data.expiry_dates}}</td>
                                         <td>{{data.total_sold_qty}}</td>
                                        <td>{{data.current_qty}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="dead_stock_data !== null"
                            v-bind:results="dead_stock_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ dead_stock_data.total }}
                        <div class="row">
                            <div
                                class="col-md-8 d-flex"
                               
                            >
                                <export-excel
                                  
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
                 branch_select_data: null,
            print_data: null,
            sale_details: null,
            receipt_details_modal: false,
            search_params: {
               search:"",
               branch_id:null,
            qty_limit:0,
            no_days:30,
            },
            reportTypes: [
                { value: "daily", label: "Daily" },
                { value: "bi-weekly", label: "Bi-Weekly" },
                { value: "monthly", label: "Monthly" },
                { value: "quarterly", label: "Quarterly" },
                { value: "yearly", label: "Yearly" },
            ],
            sort_params: {
                sort_qty: "",
                
            },

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
            dead_stock_data: [],
            waiter_select_data: null,
         
            institution_select_data: null,
            from: null,
            to: null,
            total_vat: 0,
            total_sales: 0,
            total_cash: 0,
            total_mpesa: 0,
            total_card: 0,
            total_credit: 0,
            total_discount: 0,
            total_customers: 0,
            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {  
        sort_params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
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

        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchAllData() {
            this.isLoading = true;
            this.from = null;
            this.to = null;
            this.concurrentApiReq();
        },

        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/getDeadStock",

                {
                    params: {
                        ...this.search_params,
                       ...this.sort_params,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.dead_stock_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData(1)]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },

        
        async fetchExcel() {
            const res = await this.getApi(
                "data/pos_sale/getDeadStock",

                {
                    params: {
                        ...this.search_params,
                        ...this.sort_params,
                        search: this.search,
                       
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );
            if (res.status == 200) {
               

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        code: array_item.code,
                        name: array_item.name,
                        current_qty:array_item.current_qty
                       
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
