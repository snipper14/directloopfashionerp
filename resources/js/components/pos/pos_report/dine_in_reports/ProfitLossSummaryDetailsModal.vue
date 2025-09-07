<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">
                        <b>Sales for {{ details_data.stock.name }}</b>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="to"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <button
                                    class="btn btn-secondary btn-sm"
                                    @click="filterDate()"
                                >
                                    Filter Date
                                </button>
                            </div>
                            <div class="form-group">
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Receipt No</th>
                                        <th scope="col">Sale Date</th>
                                        <th>Product</th>
                                        <th>Unit</th>
                                        <th>
                                            Qty Sold<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_qty
                                                "
                                                sort-key="sort_qty"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Purchase Price</th>
                                        <th scope="col">
                                            Total Purchase Costs
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_purchases
                                                "
                                                sort-key="sort_purchases"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Selling Price</th>
                                        <th>
                                            Sale Amount
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total
                                                "
                                                sort-key="sort_total"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Total Vat</th>
                                        <th scope="col">
                                            <b>Profit/Loss</b>
                                          
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                    <td>{{ data.order_no }}</td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.stock.name }}</td>
                                        <td>{{ data.unit.name }}</td>
                                        <td>{{ data.qty }}</td>
                                        <td>{{ data.cost_price }}</td>
                                        <td>
                                            {{ cashFormatter(data.cost_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_total) }}
                                        </td>

                                        <td>
                                            {{ cashFormatter(data.row_vat) }}
                                        </td>

                                        <td>
                                            <b>
                                                {{
                                                    Math.round(
                                                        ((data.row_total -
                                                            data.cost_total) *
                                                            100) /
                                                            data.row_total
                                                    )
                                                }}%</b
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
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
import POSDetailReport from "./POSDetailReport.vue";
import POSReportNav from "../POSReportNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import POSReportPrintComponent from "../prints_components/POSReportPrintComponent.vue";
import DineTotalPrintComponent from "../prints_components/DineTotalPrintComponent.vue";
import DailySummaryDetails from "./DailySummaryDetails.vue";
import SortButtons from "../../../utilities/SortButtons.vue";

export default {
    props: ["details_data"],
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
                sort_margin: "",
                sort_profit_loss: "",
                sort_qty: "",
                sort_purchases: "",
                sort_total: "",
            },
            isLoading: true,
            search: "",
            to: "",
            from: "",
            sale_data: [],
            all_sale_data: [],
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        POSDetailReport,
        POSReportPrintComponent,
        DineTotalPrintComponent,
        DailySummaryDetails,
        SortButtons,
    },
    watch: {
        sort_params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
        search: {
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

        roundUpToNearest10(number) {
            return Math.ceil(number / 10) * 10;
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard_active");
        },

        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/profitLossDetails", {
                params: {
                    search: this.search,
                    from: this.from,
                    to: this.to,
                    currentRoute: this.$route.name,
                    ...this.sort_params,
                    stock_id: this.details_data.stock_id,
                },
            });
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        created: this.formatDateTime(data.created_at),
                        product: data.stock.name,
                        unit: data.unit.name,

                        qty_sold: data.qty,
                        cost_total: data.cost_total,
                        selling_price: data.price,
                        row_vat: data.row_vat,
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

        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/profitLossDetails",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
                        page,
                        from: this.from,
                        to: this.to,
                        ...this.sort_params,
                        stock_id: this.details_data.stock_id,
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

            const res = await Promise.all([this.fetchData(1)]).then(function (
                results
            ) {
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
