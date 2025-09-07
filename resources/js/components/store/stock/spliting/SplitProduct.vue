<template>
    <div class="row">
        <div class="col-md-2">
            <stock-operation-nav
                v-if="form_data.original_product_id"
                :id="form_data.original_product_id"
            />
        </div>
        <div class="col-md-10">
            <div class="container">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$router.push('/stock_management')"
                    color="primary"
                    dark
                >
                    <v-icon dark left>
                        mdi-arrow-left
                    </v-icon>
                    Back
                </v-btn>
                <center>
                    <h3 class="mt-2 mb-2">
                        <b>Split Item : </b>{{ form_data.product_name }}
                        <br />
                        <b>Quantity Avail: {{ form_data.qty_avail }}</b>
                    </h3>
                </center>

                <center><h4>Split Item/s</h4></center>
                <div
                    class="border border-dark d-flex flex-row justify-content-center"
                >
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">
                                        Final Product After Split
                                    </th>
                                    <th>Qty to split</th>
                                    <th scope="col">Qty after split</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in splitted_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.code }}
                                    </td>
                                    <td>
                                        {{ data.product_name }}
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            v-model="data.qty_split"
                                            class="form-control"
                                        />
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.final_qty * data.qty_split
                                            )
                                        }}
                                    </td>

                                    <td>
                                        <button
                                            class="btn btn-primary btn-sm"
                                            @click="splitRecord(data)"
                                        >
                                            Split
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import StockOperationNav from "../StockOperationNav.vue";
export default {
    props: ["product_data"],
    components: {
        StockOperationNav
    },
    data() {
        return {
            search_query: {
                code: "",
                product_name: ""
            },
            splitted_data: [],
            search_results: [],
            total_cost: 0,
            form_data: {
                original_product_id: null,
                final_qty: 0,
                qty_split: 0,
                final_product_id: null,
                qty_avail: 0,
                split_product_setting_id: null
            }
        };
    },

    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.searchProduct();
            }, 500)
        },
        splitted_data: {
            handler() {
                if (this.splitted_data.length > 0) {
                    this.total_cost = this.splitted_data.reduce(function(
                        sum,
                        val
                    ) {
                        var sub_t = val.ingredient
                            ? val.ingredient.cost_price
                            : 0;
                        var tt = val.qty * sub_t;
                        return sum + tt;
                    },
                    0);
                }
            },
            deep: true
        }
    },
    created() {
        this.form_data.product_name = localStorage.getItem("storeProductName");
        this.form_data.original_product_id = localStorage.getItem(
            "storeProductId"
        );
        this.fetchSplitItems();
    },
    methods: {
        async splitRecord(data) {
            this.form_data.final_product_id = data.final_product_id;
            this.form_data.final_qty = data.final_qty * data.qty_split;
            this.form_data.qty_split = data.qty_split;
            this.form_data.split_product_setting_id = data.id;
            this.showLoader();
            const res = await this.callApi(
                "post",
                this.base_url + "data/split_product/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.successNotif("Record added successfully");
                this.fetchSplitItems();
            } else {
                this.form_error(res);
            }
        },
        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi(
                    this.base_url + "data/stock/searchItem",
                    {
                        params: {
                            ...this.search_query
                        }
                    }
                );

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },
        async fetchSplitItems() {
            this.showLoader();
            const res = await this.getApi(
                this.base_url +
                    "data/split_settings/fetch/" +
                    this.form_data.original_product_id,
                ""
            );
            this.hideLoader();

            if (res.status === 200) {
                this.splitted_data = this.modifyResData(res.data);

                this.form_data.qty_avail = this.splitted_data[0].avail_qty;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        modifyResData(data) {
            let modif = data.map(obj => {
                return {
                    code: obj.final_product.code,
                    product_name: obj.final_product.product_name,
                    id: obj.id,
                    original_product_id: obj.original_product_id,
                    final_product_id: obj.final_product_id,
                    final_qty: obj.final_qty,
                    qty_split: 1,
                    avail_qty: obj.original_product.qty
                };
            });
            return modif;
        }
    }
};
</script>

<style scoped></style>
