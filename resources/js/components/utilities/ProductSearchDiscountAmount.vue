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
                                            <th>Shelf</th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                            <th scope="col">BatchNo</th>
                                            <th scope="col">P. Price</th>
                                            <th scope="col">Selling Price</th>
                                            <th scope="col">Discount(Amt)</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Sub Total</th>
                                            <th></th>
                                            <th>Margin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in search_results"
                                            :key="i"
                                        >
                                            <td>{{ data.shelf }}</td>
                                            <td  style="max-width: 80px">
                                               <p style="font-size:10px;font-style:bold"> {{ data.code }}</p>
                                            </td>
                                            <td  style="max-width: 80px;">
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        checkInventory(data)
                                                    "
                                                    ><p style="font-size:10px;font-weight:600">{{
                                                        data.product_name
                                                    }}</p></router-link
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    style="width: 80px"
                                                    v-model="data.batch_no"
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
                                                  @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                class="form-control"
                                                    type="number"
                                                    style="width: 120px"
                                                    v-model="data.cost_price"
                                                />
                                            </td>

                                            <td>
                                                <input
                                                  @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                     style="width: 80px"
                                                    type="number"
                                                    v-model="data.discount"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                  @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    class="form-control"
                                                    type="number"
                                                      style="width: 80px"
                                                    v-model="data.qty"
                                                />
                                            </td>
                                            <td>
                                                {{
                                                    data.cost_price * data.qty -
                                                    data.discount
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
                                              <td>
                                                {{
                                                    calculateProfitPercentage(
                                                        data.cost_price,
                                                        data.purchase_price
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
    props: ["data"],
    data() {
        return {
            search_results: [],
            inventory_data: [],
            check_inventory_modal: false,
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
        async checkInventory(data) {
            this.showLoader();
            const res = await this.getApi("data/inventory/getItemQty", {
                params: {
                    id: data.id,
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
        submitRecords(data) {
              var min_profit_margin = data.min_profit_margin;
           
            var profit_percent = this.calculateProfitPercentage(
                data.cost_price,
                data.purchase_price
            );
           
            
                var profit_limit = this.$store.state.branch.min_profit_margin;
                 console.log("profit_limit>>>"+profit_limit+"profit_percent>>>"+profit_percent+">>>>min_profit_margin"+min_profit_margin);
                if (min_profit_margin > 0) {
                if (profit_percent < min_profit_margin) {
                    this.errorNotif(
                        "The set price cannot have margin lower than min product margin of %" +
                            min_profit_margin
                    );
                    return;
                }
            } else if (profit_limit > 0 && min_profit_margin == 0) {
                if (profit_percent < profit_limit) {
                    this.errorNotif(
                        "The set price cannot have margin lower than %" +
                            profit_limit
                    );
                    return;
                }
            }
           
            
            this.$emit("stockSearchResult", data);
        },

        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi(
                    "data/stock/searchItempPriceGroup",
                    {
                        params: {
                            ...this.search_query,
                            ...this.data,
                        },
                    }
                );
console.log(JSON.stringify(res.data));

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
                    cost_price: obj.selling_price,
                    discount: 0,
                    min_profit_margin:obj.min_profit_margin,
                    product_category_id: obj.product_category_id,
                    tax_rate: obj.tax_dept.tax_rate,
                    stock_amount: obj.qty,
                    purchase_price: obj.purchase_price,
                    batch_no: "",
                    shelf: obj.branchshelves?obj.branchshelves.shelf.name:"NA",
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
