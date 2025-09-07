<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><ReceivableNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <b>Invoice Profit / Loss Summary</b>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label> From *</label
                                ><date-picker
                                    v-model="params.from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group col-md-3">
                                <label> To *</label
                                ><date-picker
                                    v-model="params.to"
                                    valueType="format"
                                ></date-picker>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    v-model="params.search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group col-md-3 nopadding">
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

                        <div class="total-display">
                            <span class="badge badge-secondary totals-badge"
                                >Total Qty:
                                {{
                                    cashFormatter(sale_data.totals.overall_quantity)
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Sales:
                                {{
                                    cashFormatter(
                                        sale_data.totals.overall_amount
                                    )
                                }}</span
                            >
                            <span class="badge badge-warning totals-badge"
                                >Total No. of Invoices:
                                {{
                                    cashFormatter(
                                        sale_data.totals.overall_invoice_count
                                    )
                                }}</span
                            >
                           
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                       
                                        <th>Product<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_stock_name
                                                "
                                                sort-key="sort_stock_name"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                      
                                        <th>
                                            Qty Sold<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_quantity
                                                "
                                                sort-key="sort_total_quantity"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Total  Costs
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total_amount
                                                "
                                                sort-key="sort_total_amount"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                           Invoice Count
                                           
                                        </th>
                                      
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.stock_items.data"
                                        :key="i"
                                    >
                                        <td>{{ data.stock.name }}</td>
                                       
                                       
                                        <td>{{ data.total_quantity }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_amount
                                                )
                                            }}
                                        </td>
                                         <td>
                                            {{
                                                cashFormatter(
                                                    data.invoice_count
                                                )
                                            }}
                                        </td>
                                       

                                     
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data.stock_items"
                            v-on:get-page="scopeWithStockSummary"
                        ></Pagination>
                        Items Count {{ sale_data.stock_items.total }}
                        <div class="row">
                            <div class="col-8 d-flex">
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

            <notifications group="foo" />
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Pagination from "../../../../utilities/Pagination.vue";
import SortButtons from "../../../../utilities/SortButtons.vue";
import ReceivableNav from "../../ReceivableNav.vue";

export default {
    data() {
        return {
            modal: {
                details: false,
            },
            details_data: null,
            active: {
                dashboard_active: true,
                summary_details: false,
            },
            report_title: "Dine Totals Report",
            order_data_details: null,
            profit_loss_totals: {
                sum_row_total: 0,
                sum_cost_total: 0,
                sum_row_vat: 0,
                sum_qty: 0,
            },
            sort_params: {
                
               sort_stock_name:"",
                sort_total_quantity: "",
                sort_total_amount: "",
                sort_total: "",
            },
            params: {
                customer_id: null,
                search: "",
                to: "",
                from: "",
            },
            isLoading: true,

            sale_data: [],
            all_sale_data: [],
            customer_select_data: null,
        };
    },
    components: {
        ReceivableNav,
        SortButtons,
        Pagination,
        Treeselect,
    },
    watch: {
        sort_params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
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
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        roundUpToNearest10(number) {
            return Math.ceil(number / 10) * 10;
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard_active");
        },
        async printTotals() {
            setTimeout(() => {
                this.$refs.DineTotalPrintComponent.printBill();
            }, 1000);
        },
    

        viewDetails(data) {
            this.order_data_details = data;
            this.setActive(this.active, "summary_details");
        },
        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi(
                "data/invoices/scopeWithStockSummary",
                {
                    params: {
                        ...this.params,
                       
                        ...this.sort_params,
                    },
                }
            );
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data_array = [];
                res.data.data.stock_items.map((data) => {
                    data_array.push({
                        stock:data.stock.name,
                        total_quantity: data.total_quantity,
                        total_amount: data.total_amount,
                        invoice_count:data.invoice_count,
                     
                                      
                                        
                                       
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },

        async scopeWithStockSummary(page) {
            const res = await this.getApi(
                "data/invoices/scopeWithStockSummary",

                {
                    params: {
                        
                        page,
                        ...this.params,
                        ...this.sort_params,
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data.data;
            } else {
                this.swr("Server error occurred");
            }
        },
     
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.scopeWithStockSummary(1),
                
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>

<style scoped></style>
