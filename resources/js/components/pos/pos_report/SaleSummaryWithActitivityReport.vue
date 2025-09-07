<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><POSReportNav /></div>

            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Sale Summary With Activity</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <label> To Datetime *</label>
                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="to"
                                ></date-picker>
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm mt-4"
                                    @click="filterDate()"
                                >
                                    Filter
                                </button>
                                <button
                                    class="btn btn-info btn-sm mt-4"
                                    @click="fetchAllData()"
                                >
                                    Fetch All
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Search..</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Stock</th>
                                           <th>
                                            Qty Sold
                                            <v-btn
                                                v-if="
                                                    search_params.sort_total_qty ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortTotalQty('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortTotalQty('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>

                                        <th>Curr. Stock</th>
                                        <th>
                                            Invoice Qty<v-btn
                                                v-if="
                                                    search_params.sort_invoice_qty ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortInvoiceQty('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortInvoiceQty('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            Invoice Amount<v-btn
                                                v-if="
                                                    search_params.sort_invoice_total ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="
                                                    sortInvoiceTotal('DESC')
                                                "
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortInvoiceTotal('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            Retail Qty
                                            <v-btn
                                                v-if="
                                                    search_params.sort_retail_qty ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortRetailQty('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortRetailQty('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            Retail Amount
                                            <v-btn
                                                v-if="
                                                    search_params.sort_sum_retail_qty_and_price ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortRetailTotal('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortRetailTotal('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            Total Amt
                                            <v-btn
                                                v-if="
                                                    search_params.sort_total ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sortTotal('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sortTotal('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                     
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.product_name }}</td>
                                          <td>
                                            {{ cashFormatter(data.total_qty) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.current_qty)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.invoice_qty_sale
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.invoice_total_sale
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.pos_qty_sale)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.pos_total_sale
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.total_amount
                                                    )
                                                }}</b
                                            >
                                        </td>
                                      
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="combineStockSummary"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                        <div class="row" v-if="isDownloadPermitted">
                            <div class="col-md-8 d-flex">
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
            <div v-else><unauthorized /></div>
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
    },
    data() {
        return {
            sale_details: null,
            receipt_details_modal: false,
            search_params: {
                user_id: null,
                cashier_id: null,
                order_type: null,

                sort_sum_retail_qty_and_price: "",
                sort_total_qty: "",
                sort_invoice_total: "",
                sort_invoice_qty: "",
                sort_retail_qty: "",
                sort_total: "",
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
            sale_data: [],
            waiter_select_data: null,
            from: null,
            to: null,
            total_vat: 0,
            total_sales: 0,
            total_cash: 0,
            total_mpesa: 0,
            total_card: 0,
            total_credit: 0,
            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        order_type_obj: {
            deep: true,
            handler: _.debounce(function (val) {
                this.isLoading = false;
                console.log(JSON.stringify(val));
                // this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        sortTotal(stat) {
            Object.keys(this.search_params).forEach((key) => {
                this.search_params[key] = null;
            });
            this.search_params.sort_total = stat;
        },
        sortInvoiceQty(stat) {
            Object.keys(this.search_params).forEach((key) => {
                this.search_params[key] = null;
            });
            this.search_params.sort_invoice_qty = stat;
        },
        sortRetailQty(stat) {
            Object.keys(this.search_params).forEach((key) => {
                this.search_params[key] = null;
            });
            this.search_params.sort_retail_qty = stat;
        },
        sortInvoiceTotal(stat) {
            Object.keys(this.search_params).forEach((key) => {
                this.search_params[key] = null;
            });
            this.search_params.sort_invoice_total = stat;
        },
        sortRetailTotal(stat) {
            Object.keys(this.search_params).forEach((key) => {
                this.search_params[key] = null;
            });
            this.search_params.sort_sum_retail_qty_and_price = stat;
        },
        sortTotalQty(stat) {
            Object.keys(this.search_params).forEach((key) => {
                this.search_params[key] = null;
            });
            this.search_params.sort_total_qty = stat;
        },

        viewDetails(data) {
            this.sale_details = data;
            this.receipt_details_modal = true;
        },
        dismissCredit() {
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
        async printDetailedData() {
            setTimeout(() => {
                this.$refs.AllSaleReportPrintComponent.printBill();
            }, 1000);
        },
        async combineStockSummary(page) {
            const res = await this.getApi(
                "data/pos_sale/combineStockSummary",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.combineStockSummary(1)]).then(
                function (results) {
                    return results;
                }
            );

            this.isLoading ? this.hideLoader() : "";
        },

        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_sale/combineStockSummaryExcel",

                {
                    params: {
                        ...this.search_params,
                        search: this.search,

                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        stock: data.product_name,
                        "pos qty": data.pos_qty_sale,
                        "pos amount": data.pos_total_sale,
                        "invoice qty": data.invoice_qty_sale,
                        "invoice amount": data.invoice_total_sale,
                        "total qty combined": data.total_qty,
                        "total amount combined": data.total_amount,

                        current_stock: data.current_qty,
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
