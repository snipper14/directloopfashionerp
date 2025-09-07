<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Detailed Stocktake report</div>
                       
                        <div class="card-body">
                             <div class="row">
                            <input
                                type="text"
                                placeholder="SEARCH"
                                v-model="params.search"
                                class="form-control"
                            />
                            <div class="form-group">
                                <label for="">Variation Type</label>
                                <Select v-model="params.variation_type">
                                    <Option
                                        v-for="(value, i) in variation_options"
                                        :key="i"
                                        :value="value.value"
                                    >
                                        {{ value.name }}
                                    </Option>
                                </Select>
                            </div>
                        </div>
                        <div class="row">
                              <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th scope="col">Item</th>
                                            <th>Category</th>
                                            <th scope="col">Purchase Price</th>
                                            <th>Selling Price</th>

                                            <th scope="col">Inventory Qty</th>
                                            <th>Qty Counted</th>

                                            <th scope="col">Variant</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in details_report_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td>
                                                {{ data.stock.product_name }}
                                            </td>
                                            <td>
                                                {{
                                                    data.stock.product_category
                                                        .name
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.purchase_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.selling_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.current_qty
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.counted_qty
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            data.counted_qty -
                                                                data.current_qty
                                                        )
                                                    }}</b
                                                >
                                            </td>

                                            <td>
                                                {{ data.user.name }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                          

                            <pagination
                                v-if="details_report_data !== null"
                                v-bind:results="details_report_data"
                                v-on:get-page="fetchDetails"
                            ></pagination>
                            Items Count {{ details_report_data.total }}
                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="fetchExcel"
                                        type="xls"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <v-btn color="primary" x-small>
                                            Export Excel</v-btn
                                        >
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="fetchExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="erp_stock.csv"
                                    >
                                        <v-btn x-small color="success"
                                            >Export CSV</v-btn
                                        >
                                    </export-excel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
    </div>
</template>

<script>
import Pagination from "../utilities/Pagination.vue";
export default {
    components: { Pagination },
    props: ["details_data"],
    data() {
        return {
            details_report_data: [],
            show_loader: true,
            variation_options: [
                { name: "Positive Variance", value: "positive_variance" },
                { name: "Negative Variance", value: "negative_variance" },
                { name: "Zero Variance", value: "zero_variance" },
            ],
            params: {
                search: "",
                variation_type: true,
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.params.stocktake_code = this.details_data.stocktake_code;
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock_stake/fetchDetails", {
                params: {
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                const data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        CODE: data.stock.code,
                        ITEM: data.stock.product_name,
                        CATEGORY: data.stock.product_category.name,
                        "PURCHASE PRICE": data.purchase_price,
                        "SELLING PRICE ": data.selling_price,
                        "CURRENT QTY ": data.current_qty,

                        "COUNTED QTY": data.counted_qty,
                        VARIATION: data.counted_qty - data.current_qty,
                    });
                });
                return data_array;
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.show_loader ? this.showLoader() : "";
            await Promise.all([this.fetchDetails(1)]);
            this.show_loader ? this.hideLoader() : "";
        },
        async fetchDetails(page) {
            const res = await this.getApi("data/stock_stake/fetchDetails", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.details_report_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
