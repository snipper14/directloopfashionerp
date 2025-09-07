<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div
                        class="card-header d-flex flex-row justify-content-between"
                    >
                        <b><h3>Sales Summary</h3></b>

                        <div
                            class="form-group d-flex flex-row"
                            style="width: 250px"
                        >
                            <label for=""> User</label>
                            <treeselect
                                :load-options="fetchWaiter"
                                :options="waiter_select_data"
                                :auto-load-root-options="false"
                                v-model="search_params.waiter_id"
                                :multiple="false"
                                :show-count="true"
                                placeholder="Select "
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row"></div>
                        <div
                            class="d-flex flex-row justify-content-around mt-2"
                        >
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="from"
                                    type="datetime"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="to"
                                    type="datetime"
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
                            <div>
                                <button
                                 v-if="isDownloadPermitted"
                                    class="btn btn-primary btn-sm"
                                    @click="printRecords"
                                >
                                    Print Records
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <tr
                                        v-for="(
                                            value, i
                                        ) in grouped_dept_summary"
                                        :key="i"
                                    ></tr>

                                    <tr>
                                        <td><b>TOTAL CASHOUTS</b></td>
                                        <td>
                                            <b>{{
                                                cashFormatter(total_sales)
                                            }}</b>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 10%">
                                            <thead>
                                                <th
                                                    scope="col"
                                                    style="width: 10%"
                                                >
                                                    stock
                                                     <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_sum_main_total
                                                        "
                                                        sort-key="sort_sum_main_total"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                   
                                                </th>
                                                <th
                                                    scope="col"
                                                    style="width: 2%"
                                                >
                                                    Qty
                                                    <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_sum_qty
                                                        "
                                                        sort-key="sort_sum_qty"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                                <th
                                                    scope="col"
                                                    style="width: 5%"
                                                >
                                                    Total
                                                    <sort-buttons
                                                        :initial-sort="
                                                            sort_params.sort_sum_row_total
                                                        "
                                                        sort-key="sort_sum_row_total"
                                                        @update-sort="
                                                            updateSortParameter
                                                        "
                                                    />
                                                </th>
                                            </thead>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in category_data"
                                        :key="i"
                                    >
                                        <td>
                                            <span style="width: 10%">
                                                <b>
                                                    {{ data.category_name }}</b
                                                ></span
                                            >
                                            <span style="width: 40%">
                                                TOTAL SALES:
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            data.category_total
                                                        )
                                                    }}</b
                                                ></span
                                            >
                                            <br />
                                            <table class="table table-sm">
                                                <tr
                                                    v-for="(
                                                        products, x
                                                    ) in data.data"
                                                    :key="x"
                                                >
                                                    <td style="width: 10%">
                                                        {{
                                                            products.stock
                                                                .product_name
                                                        }}
                                                    </td>
                                                    <td style="width: 2%">
                                                        {{ products.sum_qty }}
                                                    </td>
                                                    <td style="width: 5%">
                                                        {{
                                                            cashFormatter(
                                                                products.sum_row_total
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-8 d-flex"   v-if="isDownloadPermitted">
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
            <div v-else><unauthorized /></div>
        </div>

        <dine-summary-print-component
            ref="DineSummaryPrintComponent"
            :print_title="print_title"
            v-if="category_data.length > 0"
            :category_data="category_data"
            :total_sales="total_sales"
            :total_refundable="total_refundable"
            :grouped_dept_summary="grouped_dept_summary"
            :to="to"
            :from="from"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import POSReportNav from "../POSReportNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import DineSummaryPrintComponent from "../prints_components/DineSummaryPrintComponent.vue";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            print_title: "SALE SUMMARY",
            isLoading: true,
            grouped_dept_summary: [],
            total_refundable: 0,
            total_sales: 0,
            search: "",
            to: "",
            from: "",
            category_data: [],
            waiter_select_data: null,
            search_params: {
                waiter_id: null,
                cashier_id: null,
            },
            sort_params: {
                sort_sum_row_total: "",
                sort_sum_qty: "",
                sort_sum_main_total: "",
            },
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        DineSummaryPrintComponent,
        Treeselect,
        SortButtons,
    },
    watch: {
        search_params: {
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
        async fetchExcel() {
            const res = await this.getApi(
                "data/pos_sale/fetchGroupedCategory",

                {
                    params: {
                        search: this.search,
                        ...this.search_params,
                        ...this.sort_params,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                var product_array = [];
                res.data.map((array_item) => {
                    array_item.data.map((array_item2) => {
                        product_array.push({
                            category_name: array_item2.stock.product_name,
                            total_qty_sold: array_item2.sum_qty,
                            sum_amount_sold: array_item2.sum_row_total,
                        });
                    });
                });
                return product_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchExcelGrouped() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
        },
        async fetchWaiter() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/users/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.waiter_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },

        printRecords() {
            setTimeout(() => {
                this.$refs.DineSummaryPrintComponent.printBill();
            }, 1000);
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },

        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchGroupedCategory",

                {
                    params: {
                        search: this.search,
                        ...this.search_params,
                        ...this.sort_params,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.category_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async groupedDeptTotals() {
            const res = await this.getApi(
                "data/pos_sale/groupedDeptTotals",

                {
                    params: {
                        search: this.search,

                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.grouped_dept_summary = res.data;
                this.total_sales = this.grouped_dept_summary.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.sum_row_total;
                },
                0);
            } else {
                this.swr("Server error occurred");
            }
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.groupedDeptTotals(),
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
