<template>
    <div class="row">
       
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
                        <b>Production for: </b>{{ form_data.product_name }}
                    </h3>
                </center>
                <div class="card">
                    <div class="card-header"></div>
                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-center">
                            <div class="form-group mt-2">
                                <label>Qty Produced</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.qty_produced"
                                />
                            </div>
                            <div class="form-group mt-2">
                                <label>Cost Amount </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.total_amount"
                                />
                            </div>
                            <div class="form-group mt-2">
                                <label>Cost Details </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.details"
                                />
                            </div>
                            <div class="form-group" style="text: center">
                                <Button
                                    class="center-vertical"
                                    type="info"
                                    size="large"
                                    :loading="modal_loading"
                                    @click="submitDetails()"
                                    >+ Add</Button
                                >
                            </div>
                        </div>

                        <center><h4>Production Cost</h4></center>
                        <div
                            class="border border-dark d-flex flex-row justify-content-center"
                        >
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Details</th>
                                            <th scope="col">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in production_cost_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.details }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.total_amount
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <button
                                                    class="btn btn-danger btn-sm"
                                                    @click="
                                                        deleteRecords(
                                                            data.id,
                                                            i
                                                        )
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>

                                            <td>
                                                <b>{{
                                                    cashFormatter(total_cost)
                                                }}</b>
                                            </td>
                                            <td>
                                                <button
                                                    class="btn btn-sm btn-warning"
                                                    @click="
                                                        updateProductionCost()
                                                    "
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
            modal_loading: false,
            production_cost_data: [],
            search_results: [],
            total_cost: 0,
            form_data: {
                stock_id: null,
                qty_produced: 1,
                total_amount: 0,
                product_id: null,
                product_name: "",
            },
        };
    },
    components: {
        StockOperationNav,
    },
    watch: {
        production_cost_data: {
            handler() {
                if (this.production_cost_data.length > 0) {
                    this.total_cost = this.production_cost_data.reduce(
                        function (sum, val) {
                            var tt = val.total_amount;
                            return sum + tt;
                        },
                        0
                    );
                }
            },
            deep: true,
        },
    },
    created() {
        this.form_data.product_id = this.product_data.id
   
           this.form_data.product_name =this.product_data.product_name
        this.form_data.stock_id = this.product_data.id
        this.fetchProductionCosts();
    },
    methods: {
        async updateProductionCost() {
            const res = await this.callApi(
                "post",
                this.base_url + "data/production_cost/updateProductionCost",
                {
                    id: this.form_data.product_id,
                    total_cost: this.total_cost,
                }
            );

            if (res.status === 200) {
                this.s("Updated Successfully ");
                //  this.$router.push('stock_management')
            } else {
                this.form_error(res);
            }
        },
        async deleteRecords(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    this.base_url + "data/production_cost/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.production_cost_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        sub_total(data) {
            var qty = data.qty;
            var cost_price = data.stock ? data.stock.cost_price : 0;
            return this.cashFormatter(qty * cost_price);
        },
        async submitDetails(data) {
            this.modal_loading = true;

            const res = await this.callApi(
                "post",
                this.base_url + "data/production_cost/create",
                this.form_data
            );
            this.modal_loading = false;
            if (res.status == "200") {
                this.s("Record added successfully");
                this.$Message.success("Successfully Added");
                this.production_cost_data = res.data;
            } else {
                this.form_error(res);
            }
        },

        async fetchProductionCosts() {
            this.showLoader();
            const res = await this.getApi(
                this.base_url +
                    "data/production_cost/fetch/" +
                    this.form_data.stock_id,
                ""
            );
            this.hideLoader();

            if (res.status === 200) {
                this.production_cost_data = res.data;
                this.form_data.qty_produced =
                    this.production_cost_data[0].qty_produced;
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
                };
            });
            return modif;
        },
    },
};
</script>

<style scoped></style>
