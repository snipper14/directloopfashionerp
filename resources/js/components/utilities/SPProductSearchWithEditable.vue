<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-3 border border-secondary">
                            <div class="form-group">
                                <label>Code. </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.code"
                                    autocomplete="new-user-street-address"
                                    placeholder="Code"
                                />
                            </div>
                            <div class="form-group">
                                <label>Product Name </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.product_name"
                                    autocomplete="new-user-street-address"
                                    placeholder="Product Name"
                                />
                            </div>
                            <div class="form-group">
                                <button
                                    class="btn btn-secondary btn-sm"
                                    @click="search_results = []"
                                >
                                    Clear Search
                                </button>
                            </div>
                        </div>
                        <div class="col-9 border border-secondary">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">P. Price</th>
                                            <th scope="col">Selling Price</th>
                                            
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
                                            <td style="max-width: 80px">
                                                {{ data.code }}
                                            </td>
                                            <td>
                                                <textarea
                                                    type="text"
                                                    class="form-control"
                                                    v-model="data.product_name"
                                                />
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.purchase_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <input
                                                    class="form-control"
                                                    type="number"
                                                    style="width: 80px"
                                                    v-model="data.selling_price"
                                                      @input="
                                                        updateTotalFromSubTotal(
                                                            i
                                                        )
                                                    "
                                                />
                                            </td>

                                          
                                            <td>
                                                <input
                                                    class="form-control"
                                                    type="number"
                                                    v-model="data.qty"
                                                      @input="
                                                        updateTotalFromSubTotal(
                                                            i
                                                        )
                                                    "
                                                />
                                            </td>
                                            <!-- <td>
                                                <input
                                                    class="form-control form-control-sm"
                                                    style="width: 120px"
                                                    type="number"
                                                    v-model="data.row_total"
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    @input="
                                                        updateTotalFromSubTotal(
                                                            i
                                                        )
                                                    "
                                                />
                                            </td> -->
                                            <td>
                                                <input
                                                    class="form-control form-control-sm"
                                                    style="width: 120px"
                                                    type="number"
                                                    v-model="data.row_total"
                                                    @input="
                                                        updateSubTotalFromTotal(
                                                            i
                                                        )
                                                    "
                                                />
                                            </td>
                                            <td>
                                                <button
                                                    v-if="isWritePermitted"
                                                    class="btn btn-primary btn-sm"
                                                    @click="submitRecords(data)"
                                                >
                                                    Add
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
export default {
    props: ["supplier_data", "tax_data", "pending_order"],
    data() {
        return {
            search_results: [],

            search_query: {
                code: "",
                product_name: "",
            },
        };
    },

    mounted() {},
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
        submitRecords(data) {
            this.$emit("stockSearchResult", data);
        },

        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },
        updateTotalFromSubTotal(index) {
            const item = this.search_results[index];
            // Recalculate purchase_price based on sub_total and qty
            if (item.qty > 0) {
                item.row_total= item.selling_price * item.qty;
            }
        },
        updateSubTotalFromTotal(index) {
            const item = this.search_results[index];
            // Set sub_total directly to the user-entered value
            item.row_total = parseFloat(item.row_total) || 0;
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    selling_price: obj.selling_price,
                    discount: 0,
                    product_category_id: obj.product_category_id,
                    tax_rate: obj.tax_dept.tax_rate,
                    stock_amount: obj.qty,
                    purchase_price: obj.purchase_price,
                    row_total: obj.selling_price,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
