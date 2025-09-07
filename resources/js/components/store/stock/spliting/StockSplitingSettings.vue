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
                    <v-icon dark left> mdi-arrow-left </v-icon>
                    Back
                </v-btn>
                <center>
                    <h3 class="mt-2 mb-2">
                        <b>Parent Product : </b>{{ form_data.product_name }}
                    </h3>
                </center>

                <div class="card">
                    <div class="card-header"></div>
                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-center">
                            <div class="form-group mt-3">
                                <label>Product Code </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="search_query.code"
                                />
                            </div>
                            <div class="form-group mt-3">
                                <label>Product Name </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="search_query.product_name"
                                />
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm center-vertical"
                                    @click="search_results = []"
                                >
                                    Clear Search
                                </button>
                            </div>
                        </div>

                        <center><h4>Search Results</h4></center>
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
                                            <th scope="col">Child Product</th>
                                            <th scope="col">Child  Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in search_results"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.code }}
                                            </td>
                                            <td>{{ data.product_name }}</td>
                                            <td>
                                                <input
                                                    type="text"
                                                    v-model="data.qty"
                                                    class="form-control"
                                                />
                                            </td>
                                            <td>
                                                <button
                                                    class="btn btn-secondary btn-sm"
                                                    @click="submitRecords(data)"
                                                >
                                                    Add Items
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <center><h4>Children Item/s</h4></center>
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
                                                Child Product
                                            </th>
                                            <th scope="col">Child Qty</th>
                                            <th scope="col">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in splitted_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    data.final_product
                                                        ? data.final_product
                                                              .code
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.final_product
                                                        ? data.final_product
                                                              .product_name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.final_qty
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <button
                                                    class="btn btn-danger btn-sm"
                                                    @click="
                                                        deleteRecords(data, i)
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
import _ from "lodash";
import StockOperationNav from "../StockOperationNav.vue";
export default {
    props: ["product_data"],
    components: {
        StockOperationNav,
    },
    data() {
        return {
            search_query: {
                code: "",
                product_name: "",
            },
            splitted_data: [],
            search_results: [],
            total_cost: 0,
            form_data: {
                original_product_id: null,
                final_qty: 0,
                final_product_id: null,
            },
        };
    },

    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    created() {
        this.form_data.product_name = localStorage.getItem("storeProductName");
        this.form_data.original_product_id =
            localStorage.getItem("storeProductId");
        this.fetchSplitItems();
    },
    methods: {
        async deleteRecords(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    this.base_url + "data/split_settings/destroy",
                    data
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.splitted_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async submitRecords(data) {
            this.form_data.final_product_id = data.id;
            this.form_data.final_qty = data.qty;
            this.showLoader();
            const res = await this.callApi(
                "post",
                this.base_url + "data/split_settings/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.successNotif("Record added successfully");
                this.splitted_data = res.data;
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
                            ...this.search_query,
                        },
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
                this.splitted_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty: 0,
                    code: obj.code,
                    product_name: obj.product_name,
                    cost_price: obj.cost_price,
                };
            });
            return modif;
        },
    },
};
</script>

<style scoped></style>
