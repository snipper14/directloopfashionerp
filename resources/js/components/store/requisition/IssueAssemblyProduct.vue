<template>
    <div>
        <fieldset class="border">
            <legend class="text-center">
                Issue Assembly Product
            </legend>

            <div class="row">
                <div class="col-3">
                    <div class="form-group d-flex flex-column">
                        <label for="">User *</label>

                        <Select
                            v-model="form_data.user_id"
                            style="width:200px"
                            @on-change="changeUser"
                        >
                            <Option
                                v-for="item in user_data"
                                :value="item.id"
                                :key="item.id"
                                >{{ item.name }}</Option
                            >
                        </Select>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <div class="form-group">
                            <label>Issue Date *</label>
                            <date-picker
                                v-model="form_data.issue_date"
                                valueType="format"
                                type="datetime"
                            ></date-picker>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <label>Details:</label>
                    <input
                        type="text"
                        class="form-control "
                        v-model="form_data.details"
                    />
                </div>
            </div>

            <div class="row">
                <div class="col-2 border border-secondary">
                    <div class="form-group">
                        <label>Req ID. </label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="search_query.req_id"
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
                        <label>Ingredient Name </label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="search_query.ingredient_name"
                            autocomplete="new-user-street-address"
                            placeholder="Product Name"
                        />
                    </div>
                    <button
                        class="btn btn-secondary btn-sm"
                        @click="clearSearchFields()"
                    >
                        Clear Search
                    </button>
                </div>
                <div class="col-9 ">
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <td>Req No</td>
                                <th scope="col">Manufactured Name</th>
                                   <th scope="col">Final Qty</th>
                                <th scope="col">Raw Materials Code</th>
                                <th scope="col">Raw Materials</th>
                                <th scope="col">Raw Materials Stock</th>
                                <th scope="col">Qty Ordered</th>
                                <th scope="col">Qty Issued</th>
                                <th>Dispatch Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in req_data"
                                :key="i"

                                  :class="{
                                    'low-prod':
                                        data.raw_material_stock <
                                        data.dispatch_qty
                                }"
                            >
                                <td>{{ data.req_id }}</td>
                                <td>
                                    {{ data.manufacture_item }}
                                </td>
                                <td>
                                    {{
                                        cashFormatter(data.manufactured_qty)
                                    }}
                                </td>
                                <td>{{ data.raw_material_code }}</td>
                                <td>{{ data.raw_material_name }}</td>

                                <td>
                                    {{ cashFormatter(data.raw_material_stock) }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.ingredient_qty) }}
                                </td>

                                <td>
                                    {{
                                        cashFormatter(
                                            data.ingredient_qty_issued
                                        )
                                    }}
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        v-model="data.dispatch_qty"
                                    />
                                </td>
                                <td>
                                    <router-link
                                        v-if="isWritePermitted"
                                        to="#"
                                        @click.native="submitRecords(data)"
                                    >
                                        Issue
                                    </router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- </form> -->
        </fieldset>
    </div>
</template>
<script>
export default {
    props: ["user_data"],
    data() {
        return {
            search_query: {
                req_id: "",
                product_name: "",
                ingredient_name:""
                
            },
            req_data: null,
            total_items: 0,
            total_amount: 0,
            form_data: {
                id:null,
                user_id: null,
                details: "",
                dispatch_qty: 0,
                order_qty: 0,
                pending_qty: 0,
                stock_id: null,
                ingredient_id:null,
                req_id: null,
                issue_date: "",
                final_qty:0,
            },

            search_results: []
        };
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.searchRequests();
            }, 500)
        },

        req_data: {
            deep: true,
            handler() {
                this.total_amount = this.req_data.reduce(function(sum, val) {
                    return sum + val.unit_price * val.qty;
                }, 0);
                this.total_items = this.req_data.reduce(function(qty, val) {
                    return qty + val.qty;
                }, 0);
            }
        }
    },
    mounted() {
        this.form_data.issue_date = this.getDateTime();
    },
    methods: {
        clearSearchFields(){
             Object.keys(this.search_query).forEach(
                    key => (this.search_query[key] = "")
                );
        },
        async submitRecords(data) {
        
            this.form_data.dispatch_qty = data.dispatch_qty;
            this.form_data.order_qty = data.ingredient_qty;
            this.form_data.pending_qty =
                data.ingredient_qty_issued + data.ingredient_qty - data.dispatch_qty;
            this.form_data.stock_id = data.stock_id;
            this.form_data.req_id = data.req_id;
            this.form_data.issue_date = this.formatDateTime(
                this.form_data.issue_date
            );
            this.form_data.final_qty=data.manufactured_qty
            this.form_data.ingredient_id=data.ingredient_id
            this.form_data.id=data.id
            if (parseFloat(this.form_data.pending_qty) < 0) {
                this.e("You have dispatched more than ordered amount");
                return;
            }
            this.showLoader();
            var result = await this.callApi(
                "POST",
                "data/dispatch_assembly_req/create",
                this.form_data
            );
            this.hideLoader();

            if (result.status === 200) {
                this.s("Successfully dispatched");
                this.req_data = this.modifyResData(result.data);
            } else {
                this.form_error(result);
            }
        },
        async changeUser() {
            let index = this.user_data.findIndex(
                user => user.id == this.form_data.user_id
            );
            this.form_data.user_id = this.user_data[index].id;
            this.showLoader();
            const res = await this.getApi(
                "data/assembly_requisition/fetchUserRequests" ,
                {
                     params: {
                         user_id: this.form_data.user_id,
                        ...this.search_query
                    }
                }
            );

            this.hideLoader();
            if (res.status === 200) {
                this.req_data = this.modifyResData(res.data);
            } else {
                this.swr("Server error");
            }
        },

        async searchRequests() {
            if (
                this.search_query.req_id.length > 0 ||
                this.search_query.ingredient_name.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
              this.fetchRequest()
            } else {
                 this.fetchRequest()
            }
        },
        async fetchRequest(){
  const res = await this.getApi("data/assembly_requisition/fetchUserRequests", {
                    params: {
                         user_id: this.form_data.user_id,
                        ...this.search_query
                    }
                });

                this.req_data = this.modifyResData(res.data);
        },
        modifyResData(data) {
            let modif = data.map(obj => {
                return {
                    stock_id: obj.stock_id,
                    id: obj.id,
                    ingredient_id: obj.ingredient_id,
                    req_id: obj.req_id,
                    manufacture_item: obj.stock ? obj.stock.product_name : "",
                     manufactured_qty: obj.qty ,
                    raw_material_code: obj.ingredient
                        ? obj.ingredient.code
                        : "",
                    raw_material_name: obj.ingredient
                        ? obj.ingredient.product_name
                        : "",
                    raw_material_stock: obj.ingredient ? obj.ingredient.qty : 0,
                    ingredient_qty:  obj.ingredient_qty,
                       
                    ingredient_qty_issued: obj.ingredient_qty_issued,
                    dispatch_qty:
                        obj.ingredient_qty -
                        obj.ingredient_qty_issued
                };
            });
         
            return modif;
        }
    }
};
</script>
<style scoped>
.low-prod {
    background: #ff8a80 !important;
}
</style>