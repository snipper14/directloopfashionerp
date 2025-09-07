<template>
    <div class="row">
        <div class="col-md-12">
            <div class="container">
                <center>
                    <h4 class="mt-2 mb-2">
                        <b>production no: </b>{{ form_data.production_no }}
                    </h4>
                </center>
                <center>
                    <h3 class="mt-2 mb-2">
                        <b>Ingredients for: </b>{{ form_data.product_name }}
                    </h3>
                </center>

               

                <center><h4>Production Materials</h4></center>
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
                                            data.material
                                                ? data.material.code
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            data.material
                                                ? data.material.product_name
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            data.material
                                                ? data.material.unit.name
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            data.material
                                                ? cashFormatter(
                                                      data.material_cost
                                                  )
                                                : ""
                                        }}
                                    </td>

                                    <td>{{ data.material_qty }}</td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.total_materials_cost
                                            )
                                        }}
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b> Material Qty</b></td>
                                    <td>
                                        <b>{{
                                            cashFormatter(total_raw_qty)
                                        }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><b>Total Raw Material</b></td>
                                    <td>
                                        <b>{{
                                            cashFormatter(
                                                total_raw_material_cost
                                            )
                                        }}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="table-responsive">
                    <center>
                        <h4><b>Extra Cost</b></h4>
                    </center>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Details</th>

                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(value, i) in production_cost_data"
                                :key="i"
                            >
                                <th scope="row">
                                    {{ value.details }}
                                </th>
                                <td>
                                    {{ cashFormatter(value.total_cost) }}
                                </td>
                            </tr>
                            <tr>
                                <td><b>TOTAL</b></td>
                                <td><b>{{cashFormatter(total_production_cost)}}</b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <center><h4><b>TOTAL PRODUCTION COST:     {{cashFormatter(total_production_cost+total_raw_material_cost)}}</b></h4></center>
                </div>

            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import { mdiPlusThick } from "@mdi/js";
import StockOperationNav from "../StockOperationNav.vue";
export default {
    props: ["row_data"],
    components: {},
    data() {
        return {
            search_query: {
                code: "",
                product_name: "",
            },
            ingredient_data: [],
            production_cost_data: [],
            search_results: [],
            total_raw_material_cost: 0,
            total_raw_qty: 0,
            total_production_cost:0,
            form_data: {
                produced_qty: 1,
                stock_id: null,
                qty: 1,
                sub_total: 0,
                ingredient_id: null,
                product_name: "",
                issue_no: "",
                material_id: null,
                material_cost: 0,
                desired_qty: 0,
                department_id: null,
                production_no: null,
                cost_price: 0,
                unit_id: null,
                unit: "",
            },
            icons: {
                mdiPlusThick,
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
                    this.total_raw_material_cost = this.ingredient_data.reduce(
                        function (sum, val) {
                            var tt = val.total_materials_cost;
                            return sum + tt;
                        },
                        0
                    );
                    this.total_raw_qty = this.ingredient_data.reduce(function (
                        sum,
                        val
                    ) {
                        var tt = val.material_qty;
                        return sum + tt;
                    },
                    0);
                }
            },
            deep: true,
        },
        production_cost_data:{
               handler() {
                if (this.production_cost_data.length > 0) {
                    this.total_production_cost = this.production_cost_data.reduce(
                        function (sum, val) {
                            var tt = val.total_cost;
                            return sum + tt;
                        },
                        0
                    );
                   
                   
                }
            },
            deep: true,
        }
    },
    created() {
        this.form_data.product_name = this.row_data.stock.product_name;
        this.form_data.stock_id = this.row_data.stock_id;
        this.form_data.production_no = this.row_data.production_no;
        this.form_data.desired_qty = this.row_data.desired_qty;
        this.form_data.department_id = this.row_data.department_id;
        this.form_data.cost_price = this.row_data.cost_price;

        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchIngredient()]);
            this.hideLoader();
        },

        async fetchIngredient() {
            const res = await this.getApi(
                "data/stock_production/productionDetails",
                {
                    params: {
                        ...this.form_data,
                    },
                }
            );

            if (res.status === 200) {
                this.ingredient_data = res.data.materials;
                this.production_cost_data = res.data.production_details;
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
                    cost_price: obj.cost_price,
                    unit: obj.unit.name,
                    unit_id: obj.unit_id,
                    store_qty: obj.store_qty,
                };
            });
            return modif;
        },
    },
};
</script>

<style scoped></style>
