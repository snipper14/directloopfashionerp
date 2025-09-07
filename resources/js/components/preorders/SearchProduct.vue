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
                                <label for="">Press Ctrl</label>
                                <button
                                    v-shortkey.once="['ctrl']"
                                    @shortkey="clearSearch()"
                                    class="btn btn-secondary btn-sm"
                                    @click="clearSearch"
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

                                            <th scope="col">Selling Price</th>
                                            <th>Description</th>
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
                                            <td style="max-width: 80px">
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        checkInventory(data)
                                                    "
                                                    > <p
                                                    style="
                                                        font-size: 10px;
                                                        font-weight: 600;
                                                    "
                                                >
                                                    {{ data.product_name }}
                                                </p></router-link>

                                            </td>
                                            <td>
                                                <input
                                                  @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    :disabled="
                                                        b_setting.selling_price_type ===
                                                        'fixed'
                                                    "
                                                    class="form-control"
                                                    type="number"
                                                    v-model="data.selling_price"
                                                />
                                            </td>

                                            <td>
                                                <textarea
                                                    class="form-control"
                                                    style="
                                                        font-size: 10px;
                                                        font-weight: 600;
                                                    "
                                                    v-model="data.description"
                                                    id=""
                                                    cols="30"
                                                    rows="10"
                                                ></textarea>
                                            </td>
                                            <td>
                                                <input
                                                @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    class="form-control"
                                                    type="number"
                                                    v-model="data.qty"
                                                />
                                            </td>
                                            <td>
                                                {{
                                                    data.selling_price *
                                                    data.qty
                                                }}
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
import CheckInventoryModal from '../pos_retail/CheckInventoryModal.vue';
export default {
  components: { CheckInventoryModal },
    props: ["supplier_data", "tax_data", "pending_order"],
    data() {
        return {
            search_results: [],
            b_setting: this.$store.state.branch,
            search_query: {
                code: "",
                product_name: "",
            },
            inventory_data:[],
            check_inventory_modal:false
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
        async checkInventory(data) {
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: data.stock_id,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.inventory_data = res.data;
                this.check_inventory_modal = true;
            } else {
                this.form_error(res);
            }
        },
        clearSearch() {
            this.search_query.product_name=''
             this.search_query.code=''
            this.search_results = [];
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
                    stock_id: obj.id,
                    qty: 1,
                    code: obj.code,
                    product_name: obj.product_name,
                    selling_price: obj.selling_price,

                    description: "",
                    tax_rate: obj.tax_dept.tax_rate,

                    purchase_price: obj.purchase_price,
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
