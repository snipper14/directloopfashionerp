<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard_active">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">POS Daily Sales</div>

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
                                    v-model="params.search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">
                                            Amount
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_sum_receipt_total
                                                "
                                                sort-key="sort_sum_receipt_total"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>
                                            Vat
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_vat
                                                "
                                                sort-key="sort_vat"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Cash Paid

                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_cash_paid
                                                "
                                                sort-key="sort_cash_paid"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Mpesa Paid
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_mpesa_paid
                                                "
                                                sort-key="sort_mpesa_paid"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Card Paid<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_card_paid
                                                "
                                                sort-key="sort_card_paid"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>

                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.paid_date }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_receipt_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_total_vat
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(data.sum_cash_pay)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_mpesa_pay
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.sum_card_pay)
                                            }}
                                        </td>

                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                            >
                                                View Items Sales
                                            </router-link>
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
                            <div
                                class="col-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
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
                                    v-if="isDownloadPermitted"
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
                                <div>
                                    <button
                                        @click="printDetailedData()"
                                        v-if="isDownloadPermitted"
                                        class="btn btn-primary btn-sm"
                                    >
                                        Print Detailed Records
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>

            <notifications group="foo" />
        </div>
        <daily-summary-details-itemized-report
            v-if="active.summary_details"
            :order_data_details="order_data_details"
            v-on:dashboard-active="setActiveRefresh"
        />
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
import DailySummaryDetailsItemizedReport from "./DailySummaryDetailsItemizedReport.vue";

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
            params: {
                search: "",
            },
            sort_params: {
                sort_vat: "",
                sort_cash_paid: "",
                sort_sum_receipt_total: "",

                sort_mpesa_paid: "",
                sort_card_paid: "",
            },
            report_title: "Dine Totals Report",
            order_data_details: null,

            isLoading: true,

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
        DailySummaryDetailsItemizedReport,
    },
    watch: {
        params: {
            deep: true,
            handler() {
                this.isLoading = false;
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
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
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

        viewDetails(data) {
            this.order_data_details = data;
            this.setActive(this.active, "summary_details");
        },
        async fetchAllRecApi() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/dailySalesReport", {
                params: {
                    ...this.params,
                    ...this.sort_params,
                    from: this.from,
                    to: this.to,
                    currentRoute: this.$route.name,
                },
            });
            this.hideLoader();
            return res;
        },
        async fetchExcel() {
            const res = await this.fetchAllRecApi();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        paid_date: array_item.paid_date,
                        sum_receipt_total: array_item.sum_receipt_total,
                        sum_total_vat: array_item.sum_total_vat,
                        sum_cash_pay: array_item.sum_cash_pay,
                        sum_mpesa_pay: array_item.sum_mpesa_pay,
                        sum_card_pay: array_item.sum_card_pay,
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
                "data/pos_sale/dailySalesReport",

                {
                    params: {
                        currentRoute: this.$route.name,
                        ...this.params,
                        ...this.sort_params,
                        page,
                        from: this.from,
                        to: this.to,
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
