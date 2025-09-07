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

                <div
                    class="border border-primary d-flex flex-row justify-content-center"
                >
                    <div class="form-group">
                        <label>Issue no </label>
                        <input
                            type="text"
                            class="form-control"
                            autocomplete="new-user-street-address"
                            v-model="form_data.issue_no"
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
                    <div class="form-group d-flex flex-column">
                        <label for="">Raw Material Location*</label>

                        <Select v-model="form_data.raw_material_department_id">
                            <Option
                                v-for="item in dept_data"
                                :value="item.id"
                                :key="item.id"
                                >{{ item.department }}</Option
                            >
                        </Select>
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
                                    <th scope="col">Item</th>
                                    <th scope="col">Unit</th>
                                    <th>Cost Price</th>

                                    <th>Qty Req.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(value, i) in search_results"
                                    :key="i"
                                >
                                    <th scope="row">
                                        {{ value.product_name }}
                                    </th>
                                    <td>
                                        {{ value.unit }}
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            v-model="value.cost_price"
                                        />
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            v-model="value.qty"
                                        />
                                    </td>

                                    <td>
                                        {{
                                            (
                                                value.qty * value.cost_price
                                            ).toFixed(2)
                                        }}
                                    </td>
                                    <td>
                                        <v-btn
                                            v-if="isWritePermitted"
                                            small
                                            color="primary"
                                            dark
                                            @click="updateRawProducts(value)"
                                        >
                                            <v-icon color="white" medium>{{
                                                icons.mdiPlusThick
                                            }}</v-icon>
                                            Add
                                        </v-btn>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

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
            dept_data: null,
            search_results: [],
            total_cost: 0,
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
                raw_material_department_id: null,
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
                    this.total_cost = this.ingredient_data.reduce(function (
                        sum,
                        val
                    ) {
                        var tt = val.qty * val.total_materials_cost;
                        return sum + tt;
                    },
                    0);
                }
            },
            deep: true,
        },
    },
    created() {
        this.form_data.product_name = this.row_data.stock.product_name;
        this.form_data.stock_id = this.row_data.stock_id;
        this.form_data.production_no = this.row_data.production_no;
        this.form_data.desired_qty = this.row_data.desired_qty;
        this.form_data.department_id = this.row_data.department_id;
        this.form_data.cost_price = this.row_data.cost_price;
        this.form_data.raw_material_department_id =
            this.row_data.raw_material_department_id;
        console.log(JSON.stringify(this.form_data));
        this.concurrentApiReq();
    },
    methods: {
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchIngredient(),
                this.fetchIssueNo(),
                this.fetchDept(),
            ]);
            this.hideLoader();
        },
        async fetchIssueNo() {
            const res = await this.getApi(
                "data/stock_production/fetchIssueNo",
                ""
            );

            if (res.status == 200) {
                this.form_data.issue_no = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async updateRawProducts(data) {
            this.form_data.sub_total = data.cost_price * data.qty;
            this.form_data.material_id = data.id;
            this.form_data.material_cost = data.cost_price;
            this.form_data.material_qty = data.qty;
            this.form_data.unit_id = data.unit_id;
            this.form_data.unit = data.unit;
            this.showLoader();
            const res = await this.callApi(
                "post",
                 "data/stock_production/updateRawMterials",
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
            const res = await this.getApi(
                "data/stock_production/fetchOngoingProductionMaterials",
                {
                    params: {
                        ...this.form_data,
                    },
                }
            );

            if (res.status === 200) {
                this.ingredient_data = res.data;
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
