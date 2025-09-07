<template>
    <div class="container">
        <div class="row justify-content-center">
            <v-btn @click="$emit('dashboard-active')" color="secondary">Back</v-btn>
            <div class="col-md-12">
                <div class="card">
                   

                    
                        <div
                            class="col-md-12 border border-secondary d-flex flex-row"
                        >
                            <div class="form-group">
                                <label>Scan Code... </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.code"
                                    autocomplete="new-user-street-address"
                                    autofocus
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
                        </div><br><br>
                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Batchno</th>

                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">B.Price</th>
                                            <th scope="col">Sell Price</th>
                                            <th scope="col">Qty</th>
                                            <th scope="col">Sub Total</th>

                                            <th>Shelf</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in search_results"
                                            :key="i"
                                        >
                                            <td>
                                                <v-btn
                                                   
                                                    color="primary"
                                                    small
                                                    @click="submitRecords(data)"
                                                >
                                                    Add Item
                                                </v-btn>
                                            </td>
                                            <td>
                                                <input
                                                    class="form-control"
                                                    style="
                                                        width: 80px;
                                                        font-size: 12px;
                                                        font-style: bold;
                                                    "
                                                    type="text"
                                                    v-model="data.batch_no"
                                                />
                                            </td>
                                            <td>
                                                {{ data.code }}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        checkInventory(data)
                                                    "
                                                    >{{
                                                        data.product_name
                                                    }}</router-link
                                                >
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
                                                    class="form-control table-input"
                                                    type="number"
                                                    @input="
                                                        updateSubTotalEvent(i)
                                                    "
                                                    :disabled="b_setting.selling_price_type === 'fixed'" 
                                                    v-model="data.selling_price"
                                                />
                                            </td>

                                            <td>
                                                <input
                                                    @input="
                                                        updateSubTotalEvent(i)
                                                    "
                                                    class="form-control table-input"
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    type="number"
                                                    v-model="data.qty"
                                                />
                                            </td>
                                            <td>
                                                <input
                                                    @input="updateQtyEvent(i)"
                                                    @keyup.enter="
                                                        submitRecords(data)
                                                    "
                                                    :disabled="b_setting.selling_price_type === 'fixed'" 
                                                    class="form-control table-input"
                                                    type="number"
                                                    v-model="data.sub_total"
                                                />
                                            </td>

                                            <td>{{ data.shelf }}</td>
                                        </tr>
                                    </tbody>
                                </table>
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
               b_setting: this.$store.state.branch,
            search_results: [],
            search_query: {
                code: "",
                product_name: "",
                // code: "",
            },
        };
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                if (this.search_query.code !== "") {
                    this.search_query.code = this.search_query.code;
                }
                this.searchProduct();
            }, 500),
        },
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods: {
          updateQtyEvent(index) {
            const item = this.search_results[index];
           
            item.selling_price = item.sub_total / item.qty;
          
        },
        updateSubTotalEvent(index) {
            const item = this.search_results[index];
          
            item.sub_total = item.qty * item.selling_price;
         
        },
        submitRecords(data){
            this.$emit("dashboard-active",data)
        },
        async searchProduct() {
            if (this.search_query.product_name.length > 0) {
                this.search_query.code = "";
            }

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
                if (this.search_query.code !== "") {
                    if (this.search_results[0]) {
                        this.$emit("stockSearchResult", this.search_results[0]);
                        this.search_query.code = "";
                    } else {
                        this.e("Code not found");
                    }
                }
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
                    shelf: obj.shelf.name,
                    selling_price: obj.selling_price,
                    tax_rate: obj.tax_dept.tax_rate,
                    tax_dept: obj.tax_dept,
                    stock_amount: obj.main_store_qty,
                    customer_name: this.customer_name,
                    customer_id: this.customer_id,
                    batch_no: "",
                    sub_total: obj.selling_price * 1,
                    item_id: obj.item_id,
                };
            });
            return modif;
        },
    },
};
</script>
