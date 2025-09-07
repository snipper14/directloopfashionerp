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
                                            <th></th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Purchase Price</th>
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
                                                <button
                                                    v-if="isWritePermitted"
                                                    class="btn btn-primary btn-sm"
                                                    @click="submitRecords(data)"
                                                >
                                                    Add
                                                </button>
                                            </td>
                                            <td>
                                                {{ data.code }}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native.prevent="
                                                        checkInventory(data)
                                                    "
                                                >
                                                    {{ data.product_name }}
                                                </router-link>
                                            </td>

                                            <!-- Purchase Price -->
                                            <td>
                                                <input
                                                    class="form-control form-control-sm"
                                                    style="width: 120px"
                                                    type="number"
                                                    v-model="
                                                        data.purchase_price
                                                    "
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    @input="updateSubTotal(i)"
                                                />
                                            </td>

                                            <!-- Quantity -->
                                            <td>
                                                <input
                                                    class="form-control form-control-sm"
                                                    style="width: 120px"
                                                    type="number"
                                                    v-model="data.qty"
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    @input="updateSubTotal(i)"
                                                />
                                            </td>

                                            <!-- Subtotal -->
                                            <td>
                                                <input
                                                    class="form-control form-control-sm"
                                                    style="width: 120px"
                                                    type="number"
                                                    v-model="data.sub_total"
                                                       @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    @input="
                                                        updateTotalFromSubTotal(
                                                            i
                                                        )
                                                    "
                                                />
                                            </td>

                                            <!-- Total -->
                                            <td>
                                                <input
                                                    class="form-control form-control-sm"
                                                    style="width: 120px"
                                                    type="number"
                                                    disabled
                                                    v-model="data.sub_total"
                                                       @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    @input="
                                                        updateSubTotalFromTotal(
                                                            i
                                                        )
                                                    "
                                                />
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
        <Modal v-model="check_inventory_modal">
            <check-inventory-modal
                v-if="check_inventory_modal"
                :inventory_data="inventory_data"
        /></Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import CheckInventoryModal from "../pos_retail/CheckInventoryModal.vue";
export default {
    components: { CheckInventoryModal },
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
        updateSubTotal(index) {
        const item = this.search_results[index];
        // Update sub_total based on purchase_price and qty
        item.sub_total = (item.purchase_price || 0) * (item.qty || 0);
    },
    updateTotalFromSubTotal(index) {
        const item = this.search_results[index];
        // Recalculate purchase_price based on sub_total and qty
        if (item.qty > 0) {
            item.purchase_price = item.sub_total / item.qty;
        }
    },
    updateSubTotalFromTotal(index) {
        const item = this.search_results[index];
        // Set sub_total directly to the user-entered value
        item.sub_total = parseFloat(item.sub_total) || 0;

    },
        updatePurchasePrice(index) {
            const item = this.search_results[index];
            if (item.purchase_price !== 0) {
                item.purchase_price = item.sub_total / item.qty;
            } else {
                // Handle division by zero if needed
                // For example, set quantity to 0 or show an error message
                item.qty = 0;
            }
        },
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
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    purchase_price: obj.purchase_price,

                    tax_rate: obj.tax_dept.tax_rate,
                    stock_amount: obj.main_store_qty,
                    sub_total: obj.purchase_price,
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
