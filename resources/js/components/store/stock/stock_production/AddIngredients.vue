<template>
    <div class="row">
        
        <div class="col-md-12">
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
                        <b>BOM for: </b>{{ form_data.product_name }}
                    </h3>
                </center>

                <div
                    class="border border-primary d-flex flex-row justify-content-center"
                >
                    <div class="form-group">
                        <label>Qty Produced </label>
                        <input
                            type="text"
                            class="form-control"
                            autocomplete="new-user-street-address"
                            v-model="form_data.produced_qty"
                        />
                    </div>
                    <div class="form-group">
                        <label>Product Code </label>
                        <input
                            type="text"
                            class="form-control"
                            autocomplete="new-user-street-address"
                            v-model="search_query.code"
                        />
                    </div>
                    <div class="form-group">
                        <label>Product Name </label>
                        <input
                            type="text"
                            class="form-control"
                            autocomplete="new-user-street-address"
                            v-model="search_query.product_name"
                        />
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
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Unit</th>
                                    <th scope="col">Cost Price</th>

                                    <th scope="col">Qty</th>
                                    <th scope="col">Sub Total</th>
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
                                    <td>{{data.unit}}</td>
                                    <td>
                                         {{data.cost_price}}
                                      </td>

                                    <td>
                                        <input type="text" v-model="data.qty" />
                                    </td>
                                    <td>
                                        {{ data.cost_price * data.qty }}
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

                <center><h4>Ingredients</h4></center>
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
                                    <th scope="col">Item Name</th>
                                     <th scope="col">Unit</th>
                                    <th scope="col">Cost Price</th>

                                    <th scope="col">Qty</th>
                                    <th scope="col">Sub Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in ingredient_data"
                                    :key="i"
                                >
                                    <td>
                                        {{
                                            data.ingredient
                                                ? data.ingredient.code
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            data.ingredient
                                                ? data.ingredient.product_name
                                                : ""
                                        }}
                                    </td>
                                       <td>
                                        {{
                                            data.ingredient
                                                ? data.ingredient.unit.name
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            data.ingredient
                                                ? cashFormatter(
                                                      data.ingredient.purchase_price
                                                  )
                                                : ""
                                        }}
                                    </td>

                                    <td>{{ data.qty }}</td>
                                    <td>{{ sub_total(data) }}</td>
                                    <td>
                                        <button
                                            class="btn btn-danger btn-sm"
                                            @click="deleteRecords(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <b>{{ cashFormatter(total_cost) }}</b>
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-warning"
                                            @click="updateProductionCost()"
                                        >
                                           Complete 
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
    components: {},
    data() {
        return {
            search_query: {
                code: "",
                product_name: "",
            },
            ingredient_data: [],
            search_results: [],
            total_cost: 0,
            form_data: {
                produced_qty: 1,
                stock_id: null,
                qty: 1,
                sub_total: 0,
                ingredient_id: null,
                product_name: "",
              
            },
        };
    },
    components: {
        StockOperationNav,
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
        ingredient_data: {
            handler() {
                if (this.ingredient_data.length > 0) {
                    this.total_cost = this.ingredient_data.reduce(function (
                        sum,
                        val
                    ) {
                        var sub_t = val.ingredient
                            ? val.ingredient.purchase_price
                            : 0;
                        var tt = val.qty * sub_t;
                        return sum + tt;
                    },
                    0);
                }
            },
            deep: true,
        },
    },
    created() {
        this.form_data.product_name =this.product_data.product_name
        this.form_data.stock_id = this.product_data.id
        this.fetchIngredient();
    },
    methods: {
        async updateProductionCost() {
            const res = await this.callApi(
                "post",
                  "data/stock/update_stock_cost",
                {
                    id: this.form_data.stock_id,
                    total_cost: this.total_cost,
                }
            );

            if (res.status === 200) {
                this.s("Updated Successfully ");
            this.$emit('edit-active')
            } else {
                this.form_error(res);
            }
        },
        async deleteRecords(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/ingredient/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.ingredient_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        sub_total(data) {
            var qty = data.qty;
            var cost_price = data.ingredient ? data.ingredient.purchase_price : 0;
            return this.cashFormatter(qty * cost_price);
        },
        async submitRecords(data) {
            console.log(JSON.stringify(data));
            this.form_data.sub_total = data.cost_price * data.qty;
            this.form_data.ingredient_id = data.id;
            this.form_data.qty = data.qty;
          
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/ingredient/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");
                this.ingredient_data = res.data;
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
                     "data/stock/searchItem",
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
        async fetchIngredient() {
            this.showLoader();
            const res = await this.getApi(
                 "data/ingredient/fetch/" +
                    this.form_data.stock_id,
                ""
            );
            this.hideLoader();

            if (res.status === 200) {
                this.ingredient_data = res.data;
                this.form_data.produced_qty = this.ingredient_data[0].produced_qty;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    cost_price: obj.purchase_price,
                    unit:obj.unit.name
                };
            });
            return modif;
        },
    },
};
</script>

<style scoped></style>
