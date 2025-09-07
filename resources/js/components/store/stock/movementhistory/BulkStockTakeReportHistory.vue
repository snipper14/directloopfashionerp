<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Bulk Stock-Take History</div>

                    <div class="card-body">
                        <!-- <div class="row">
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
                        </div> -->
                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Created</th>
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
                                            v-for="(data, i) in data.data"
                                            :key="i"
                                        >
                                          <td>{{formatMonthDateTime(data.created_at)}}</td>
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td
                                                :class="{
                                                    current_stock:
                                                        data.stock_id ==
                                                        stock_id,
                                                }"
                                            >
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
                            <pagination
                                v-if="data !== null"
                                v-bind:results="data"
                                v-on:get-page="fetchDetails"
                            ></pagination>
                            Items Count {{ data.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
export default {
    components: { Pagination },
    props: ["info_data", "details_data"],
    data() {
        return {
            data: [],
            stock_id: null,
            variation_options: [
                { name: "Positive Variance", value: "positive_variance" },
                { name: "Negative Variance", value: "negative_variance" },
                { name: "Zero Variance", value: "zero_variance" },
            ],
            params: {
                search: "",
                variation_type: true,
                stocktake_code: null,
            },
        };
    },
    mounted() {
        console.log("Component mounted." + JSON.stringify(this.details_data));
        console.log("Component mounted." + JSON.stringify(this.info_data));
        this.stock_id = this.details_data.id;
        this.params.stocktake_code = this.info_data.ref;
        this.fetchDetails(1);
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchDetails(1);
            }, 500),
        },
    },
    methods: {
        async fetchDetails(page) {
            const res = await this.getApi("data/stock_stake/fetchDetails", {
                params: {
                    page,
                    stock_id: this.stock_id,
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.current_stock {
    color: rgb(255, 145, 0);
}
</style>
