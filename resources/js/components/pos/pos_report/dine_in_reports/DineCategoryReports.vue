<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Dine Category Summary</div>

                    <div class="card-body">
                        <div class="row"></div>
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
                            <div>  <button class="btn btn-primary btn-sm"
                            v-if="isDownloadPermitted"
                            @click="printRecords"
                        >
                            Print Records
                        </button>
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
                                                </th>
                                                <th
                                                    scope="col"
                                                    style="width: 2%"
                                                >
                                                    Qty
                                                </th>
                                                <th
                                                    scope="col"
                                                    style="width: 5%"
                                                >
                                                    Total
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
                        </div>

                      
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <dine-summary-print-component
            ref="DineSummaryPrintComponent"
            :print_title="print_title"
            v-if="category_data.length > 0"
            :category_data="category_data"
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
export default {
    data() {
        return {
            isLoading: true,
            search: "",
            print_title:"DINE SUMMARY REPORT",
            to: "",
            from: "",
            category_data: [],
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        DineSummaryPrintComponent,
        Treeselect,
    },

    methods: {
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

                        from: this.from,
                        to: this.to,
                    },
                }
            );

            if (res.status == 200) {
                this.category_data = res.data;
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
