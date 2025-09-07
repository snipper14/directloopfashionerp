<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Production Processing</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <Select
                                        @change="onChange($event)"
                                        v-model="form_data.stock_id"
                                        filterable
                                        :remote-method="searchProducts"
                                        :loading="loading1"
                                    >
                                        <Option
                                            v-for="(
                                                option, index
                                            ) in search_stock_list"
                                            :value="option.value"
                                            :key="index"
                                            >{{ option.label }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>

                            <div
                                class="col-md-3 form-group d-flex flex-direction-raw"
                            >
                                <label for="">Desired Qty</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.desired_qty"
                                />
                            </div>

                            <!-- <div class=" col-2 form-group">
                                <label for="" 
                                    >End Product </label
                                >
                                <input v-model="form_data.end_product" type="checkbox" />
                            </div> -->
                            <div class="col-md-3">
                                <v-btn
                                    class="ma-2"
                                    :loading="btn_loading"
                                    outlined
                                    color="indigo"
                                    @click="fetchRawMaterials()"
                                >
                                    Fetch Materials</v-btn
                                >
                            </div>
                        </div>
                        <div class="row">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label for=""> Qty</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.produced_qty"
                                    />
                                </div>

                                <div class="col-md-3 form-group">
                                    <label for="">Issue No</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="form_data.issue_no"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="">Prod No</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="form_data.production_no"
                                        class="form-control"
                                    />
                                </div>
                                <div
                                    class="col-md-4 form-group d-flex flex-column"
                                >
                                    <label for="">Raw Material Location*</label>

                                    <Select
                                        v-model="
                                            form_data.raw_material_department_id
                                        "
                                    >
                                        <Option
                                            v-for="item in dept_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                                <div
                                    class="col-md-4 form-group d-flex flex-column"
                                >
                                    <label for="">Destination Location*</label>

                                    <Select v-model="form_data.department_id">
                                        <Option
                                            v-for="item in dept_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <center>
                                    <h4><b>Raw Materials</b></h4>
                                </center>
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
                                            v-for="(
                                                value, i
                                            ) in raw_materials_data"
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
                                                    value.qty * value.cost_price
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    v-if="isDeletePermitted"
                                                    class="btn btn-sm btn-danger"
                                                    @click="
                                                        deleteRawMaterialsDetails(
                                                            i
                                                        )
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-responsive">
                                <center>
                                    <h4><b>Extra Cost</b></h4>
                                </center>
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Details</th>

                                            <th>Cost</th>
                                            <th>.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                value, i
                                            ) in production_cost_data"
                                            :key="i"
                                        >
                                            <th scope="row">
                                                {{ value.details }}
                                            </th>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="value.total_amount"
                                                />
                                            </td>

                                            <td>
                                                <button
                                                    v-if="isDeletePermitted"
                                                    class="btn btn-sm btn-danger"
                                                    @click="
                                                        deleteProductionCost(i)
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div>
                                <center>
                                    <v-btn
                                        :loading="btn_loading"
                                        small
                                        color="primary"
                                        dark
                                        @click="completeIssue('not_complete')"
                                    >
                                        Issue Production Materials</v-btn
                                    >
                                    <v-btn
                                        :loading="btn_loading"
                                        small
                                        color="primary"
                                        dark
                                        @click="completeIssue('complete')"
                                    >
                                        Complete Production Materials</v-btn
                                    >
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            search_stock_list: [],
            loading1: false,
            raw_materials_data: [],
            production_cost_data: [],
            btn_loading: false,
            dept_data: null,
            form_data: {
                stock_id: "",
                produced_qty: 0,
                desired_qty: 0,
                cost_price: 0,
                production_cost: 0,
                department_id: null,
                issue_no: null,
                production_no: null,
                end_product: true,
                raw_material_department_id: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                let index = this.search_stock_list.findIndex(
                    (cp) => cp.value == this.form_data.stock_id
                );

                this.form_data.cost_price =
                    this.search_stock_list[index].cost_price;
                this.form_data.production_cost =
                    this.search_stock_list[index].production_cost;
            }, 500),
        },
    },
    methods: {
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchDept(),
                this.fetchIssueNo(),
                this.fetchProductionNo(),
            ]);
            this.hideLoader();
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchProductionNo() {
            const res = await this.getApi(
                "data/stock_production/productionNo",
                ""
            );

            if (res.status == 200) {
                this.form_data.production_no = res.data;
            } else {
                this.swr("Server error occurred");
            }
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
        async completeIssue(completeProduction) {
            this.btn_loading = true;
            const data = {
                raw_materials: this.raw_materials_data,
                production_cost_data: this.production_cost_data,
                ...this.form_data,
                completeProduction: completeProduction,
            };
            const res = await this.callApi(
                "POST",
                "data/stock_production/completeProductIssue",
                data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.successNotif("Successfully issued");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
        async deleteRawMaterialsDetails(i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.raw_materials_data.splice(i, 1);
            }
        },

        async deleteProductionCost(i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.production_cost_data.splice(i, 1);
            }
        },

        async fetchRawMaterials() {
            this.btn_loading = true;
            const res = await this.callApi(
                "post",
                "data/stock_production/fetchRawMaterials",
                {
                    stock_id: this.form_data.stock_id,
                    desired_qty: this.form_data.desired_qty,
                }
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.raw_materials_data = this.modifyIngredientKey(
                    res.data.raw_materials
                );
                this.form_data.produced_qty =
                    this.raw_materials_data[0].produced_qty;

                if (res.data.production_data.length > 0) {
                    this.production_cost_data = this.modifyProductionKey(
                        res.data.production_data
                    );
                } else {
                    this.production_cost_data = [];
                }
            } else {
                this.form_error(res);
            }
        },
        modifyIngredientKey(data) {
            let modif = data.map((obj) => {
                return {
                    stock_id: obj.ingredient.id,
                    product_name: obj.ingredient.product_name,
                    unit: obj.ingredient.unit.name,
                    unit_id: obj.ingredient.unit_id,
                    avail_qty: obj.ingredient.store_qty,
                    cost_price: obj.ingredient.purchase_price,
                    qty:
                        (obj.qty / obj.produced_qty) *
                        this.form_data.desired_qty,
                    produced_qty: obj.produced_qty,
                };
            });
            return modif;
        },
        modifyProductionKey(data) {
            let modif = data.map((obj) => {
                return {
                    stock_id: obj.id,
                    details: obj.details,
                    total_amount:
                        (obj.total_amount / obj.qty_produced) *
                        this.form_data.desired_qty,
                };
            });
            return modif;
        },

        searchProducts: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;
                const res = await this.getApi("data/stock/search_items", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_stock_list = this.modifyKey(res.data).filter(
                    (item) =>
                        item.label.toLowerCase().indexOf(query.toLowerCase()) >
                        -1
                );
            }
        }, 500),

        modifyKey(data) {
            let modif = data.map((obj) => {
                return {
                    value: obj.id,
                    label: obj.product_name,
                    cost_price: obj.purchase_price,
                    production_cost: obj.production_cost,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
td {
    font-size: 0.8rem !important;
}
</style>
