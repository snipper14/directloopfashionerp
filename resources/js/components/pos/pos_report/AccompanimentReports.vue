<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3>Accompaniments Reports</h3>
                    </div>

                    <div class="card-body">
                        <div class="row"></div>
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group d-flex flex-column">
                                <label> From *{{ params.from }}</label
                                ><date-picker
                                    v-model="params.from"
                                    type="datetime"
                                ></date-picker>
                            </div>
                            <div class="form-group d-flex flex-column">
                                <label> To *{{ params.to }}</label
                                ><date-picker
                                    v-model="params.to"
                                    type="datetime"
                                ></date-picker>
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm mt-4"
                                    @click="filterDate()"
                                >
                                    Filter
                                </button>
                            </div>
                            <div class="form-group">
                                <label for="" class="center-vertical"
                                    >Group data</label
                                >
                                <input v-model="is_grouped" type="checkbox" />
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
                        <div v-if="is_grouped">
                            <h3>Grouped Report</h3>

                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Stock</th>
                                            <th scope="col">Unit</th>
                                            <th>Price</th>
                                            <th>QTY</th>
                                            <th scope="col">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in sale_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    formatDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.accompaniment
                                                        ? data.accompaniment
                                                              .product_name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.accompaniment.unit
                                                        ? data.accompaniment
                                                              .unit.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.accompaniment
                                                            .selling_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(data.sum_qty)
                                                }}
                                            </td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            data.accompaniment
                                                                .selling_price *
                                                                data.sum_qty
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
                                v-on:get-page="fetchData"
                            ></Pagination>
                            Items Count {{ sale_data.total }}
                            <div class="row">
                                <div class="col-4 d-flex">
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
                        <div v-else>
                            <div class="table-responsive">
                                <h3>Ungrouped Records</h3>
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Date</th>
                                            <th scope="col">Stock</th>
                                            <th scope="col">Unit</th>
                                            <th>Price</th>
                                            <th>QTY</th>
                                            <th scope="col">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in ungrouped_sale_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    formatDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.accompaniment
                                                        ? data.accompaniment
                                                              .product_name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.accompaniment.unit
                                                        ? data.accompaniment
                                                              .unit.name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.accompaniment
                                                            .selling_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{ cashFormatter(data.qty) }}
                                            </td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            data.accompaniment
                                                                .selling_price *
                                                                data.qty
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination
                                v-if="ungrouped_sale_data !== null"
                                v-bind:results="ungrouped_sale_data"
                                v-on:get-page="fetchDataUnGrouped"
                            ></Pagination>
                            Items Count {{ ungrouped_sale_data.total }}
                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="exportUngroupedExcel"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <button class="btn btn-primary btn-sm">
                                            Export Excel
                                        </button>
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="exportUngroupedExcel"
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
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import POSDetailReport from "./dine_in_reports/POSDetailReport.vue";
import POSReportNav from "./POSReportNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";

export default {
    data() {
        return {
            modal: {
                details: false,
            },
            package_data: null,

            room_data: null,
            params: {
                from: "",
                to: "",
            },
            ungrouped_sale_data: [],
            is_grouped: true,
            order_data_details: null,
            sum_row_total: 0,
            sum_row_vat: 0,
            total_cost: 0,
            sum_service_charge_amount: 0,
            isLoading: true,
            search: "",

            sale_data: [],
            all_sale_data: [],
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        POSDetailReport,
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        is_grouped: {
            handler: _.debounce(function () {
                this.isLoading = true;
                if (this.is_grouped) {
                    this.concurrentApiReq();
                } else {
                    this.concurrentApiReqUnGrouped();
                }
            }, 100),
        },
        params: {
            deep: true,
            handler() {
                // this.params.from=this.formatDateTime(this.params.from)
                //  this.params.to=this.formatDateTime(this.params.to)
                // this.concurrentApiReq();
            },
        },
    },
    methods: {
        async filterDate() {
            this.params.from = this.formatDateTime(this.params.from);
            this.params.to = this.formatDateTime(this.params.to);
            if (this.is_grouped) {
                this.concurrentApiReq();
            } else {
                this.concurrentApiReqUnGrouped();
            }
        },
        async exportUngroupedExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_sale/fetchUngroupedAccompanimentSales",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,

                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        date: this.formatDateTime(array_item.created_at),
                        accompaniment_name:
                            array_item.accompaniment.product_name,
                        unit: array_item.accompaniment.unit.name,
                        selling_price: array_item.accompaniment.selling_price,
                        qty: array_item.qty,
                        amount:
                            array_item.accompaniment.selling_price *
                            array_item.qty,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_sale/fetchAccompanimentSales",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,

                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        date: this.formatDateTime(array_item.created_at),
                        accompaniment_name:
                            array_item.accompaniment.product_name,
                        unit: array_item.accompaniment.unit.name,
                        selling_price: array_item.accompaniment.selling_price,
                        qty: array_item.sum_qty,
                        amount:
                            array_item.accompaniment.selling_price *
                            array_item.sum_qty,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchTotals() {
            // const res = await this.getApi(
            //     "data/pos_sale/fetchAccompanimentTotals",
            //     {
            //         params: {
            //             currentRoute: this.$route.name,
            //             ...this.params,
            //         },
            //     }
            // );
            // if (res.status == 200) {
            //     const data = res.data;
            //     if (data) {
            //         this.sum_row_vat = data.sum_row_vat;
            //         this.sum_row_total = data.sum_row_total;
            //         this.total_cost = data.total_cost;
            //         this.sum_service_charge_amount =
            //             data.sum_service_charge_amount;
            //     }
            // } else {
            //     this.swr("Server error occurred");
            // }
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchAccompanimentSales",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
                        page,
                        is_grouped: this.is_grouped,
                        ...this.params,
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDataUnGrouped(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchUngroupedAccompanimentSales",

                {
                    params: {
                        currentRoute: this.$route.name,
                        search: this.search,
                        page,

                        ...this.params,
                    },
                }
            );

            if (res.status == 200) {
                this.ungrouped_sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async concurrentApiReqUnGrouped() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchDataUnGrouped(1),
                //  this.fetchTotalsGrouped(),
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
