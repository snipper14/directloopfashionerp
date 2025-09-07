<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header"><b>Profit / Loss Summary</b></div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="from"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    type="datetime"
                                    width="300px"
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
                             <div class="col-md-2 form-group">
                                <label for="">Branch Filter</label>
                                <treeselect
                                    :load-options="fetchBranch"
                                    :options="branch_select_data"
                                    :auto-load-root-options="false"
                                    v-model="branch_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Branch "
                                />
                            </div>
                        </div>

                        <div class="total-display" v-if="isDownloadPermitted">
                            <span class="badge badge-secondary totals-badge"
                                >Total Qty:
                                {{
                                    cashFormatter(profit_loss_totals.sum_qty)
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Sales:
                                {{
                                    cashFormatter(
                                        profit_loss_totals.sum_row_total
                                    )
                                }}</span
                            >
                            <span class="badge badge-warning totals-badge"
                                >Total Cost:
                                {{
                                    cashFormatter(
                                        profit_loss_totals.sum_cost_total
                                    )
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Vat:
                                {{
                                    cashFormatter(
                                        profit_loss_totals.sum_row_vat
                                    )
                                }}</span
                            >
                            <span class="badge badge-info totals-badge"
                                >PROFIT/LOSS:
                                {{
                                    cashFormatter(
                                        profit_loss_totals.sum_row_total -
                                            profit_loss_totals.sum_cost_total
                                    )
                                }}</span
                            >
                            <span class="badge badge-success totals-badge"
                                >Margin%:
                                {{
                                    Math.round(
                                        ((profit_loss_totals.sum_row_total -
                                            profit_loss_totals.sum_cost_total) *
                                            100) /
                                            profit_loss_totals.sum_row_total
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
                                        <th>View</th>
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
                                        <th>
                                            Total Sale Amount
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
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_profit_loss
                                                "
                                                sort-key="sort_profit_loss"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            % Margin
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_margin
                                                "
                                                sort-key="sort_margin"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <v-btn
                                                color="primary"
                                                x-small
                                                @click="viewDetails(data)"
                                                >view</v-btn
                                            >
                                        </td>
                                        <td>{{ data.order_date }}</td>
                                        <td>{{ data.stock.name }}</td>
                                        <td>{{ data.unit.name }}</td>
                                        <td>{{ data.sum_qty }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_cost_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_row_total
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(data.sum_row_vat)
                                            }}
                                        </td>

                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(
                                                        data.profit_loss
                                                    )
                                                }}</b
                                            >
                                        </td>
                                        <td>
                                            <b>
                                                {{
                                                    Math.round(
                                                        ((data.sum_row_total -
                                                            data.sum_cost_total) *
                                                            100) /
                                                            data.sum_row_total
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
                        <div class="row" v-if="isDownloadPermitted">
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
            <div v-else><unauthorized /></div>
            <notifications group="foo" />
        </div>
        <Modal v-model="view_details_modal" width="1200px">
            <ProfitLossSummaryDetailsModal
                v-if="view_details_modal"
                :details_data="details_data"
            />
        </Modal>
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
import ProfitLossSummaryDetailsModal from "./ProfitLossSummaryDetailsModal.vue";

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
            branch_id: null,
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
            view_details_modal: false,
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
        ProfitLossSummaryDetailsModal,
    },
    watch: {
        branch_id: {
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
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
        viewDetails(data) {
            this.details_data = data;
            this.view_details_modal = true;
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
        async printDetailedData() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                this.all_booking_data = res.data;

                setTimeout(() => {
                    this.$refs.POSReportPrintComponent.printBill();
                }, 1000);
            } else {
                this.servo();
            }
        },

        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/profitLossSummary", {
                params: {
                    search: this.search,
                    from: this.formatDateTime(this.from),
                    to: this.formatDateTime(this.to),
                    currentRoute: this.$route.name,
                    ...this.sort_params,
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
                        order_date: data.order_date,
                        product: data.stock.name,
                        unit: data.unit.name,
                        qty_sold: data.sum_qty,
                        total_cost: data.sum_cost_total,
                        total_sold: data.sum_row_total,
                        total_vat: data.sum_row_vat,
                        profit_loss: data.sum_row_total - data.sum_cost_total,
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
                "data/pos_sale/profitLossSummary",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
                        page,
                        branch_id: this.branch_id,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                        ...this.sort_params,
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async profitLossTotals() {
            const res = await this.getApi(
                "data/pos_sale/profitLossTotals",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
  branch_id: this.branch_id,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.profit_loss_totals = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.profitLossTotals(),
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
