<template>
    <div>
        <fieldset class="border">
            <legend class="text-center">
                Update Manufactured  Stock
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

                
            </div>

            <div class="row">
              
                <div class="col-10 border border-secondary">
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <td>Req No</td>
                                <th scope="col">Code</th>
                                <th scope="col">Manufactured Item Name</th>
                                <th scope="col">Cost Price</th>
                                <th scope="col">Availability</th>
                                <th scope="col">Dispatched Raw Materials</th>
                                <th scope="col">Pending Raw Materials</th>
                                <th>Qty After Production</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in req_data"
                                :key="i"
                               
                            >
                                <td>{{ data.req_id }}</td>
                                <td>
                                    {{ data.manufacture_item_code }}
                                </td>
                                <td>{{ data.manufacture_item }}</td>
                                <td>{{ cashFormatter(data.cost_price) }}</td>
                                <td>{{ cashFormatter(data.available_qty) }}</td>

                                <td>
                                    {{
                                        cashFormatter(
                                            data.total_ingredient_qty_issued
                                        )
                                    }}
                                </td>
                                <td>
                                    {{
                                        cashFormatter(
                                            data.total_ingredient_pending
                                        )
                                    }}
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="data.final_qty_produced"
                                    />
                                </td>
                                <td>
                                    <button
                                    class="btn btn-warning btn-sm"
                                        v-if="isUpdatePermitted"
                                        to="#"
                                        @click="submitRecords(data,i)"
                                    >
                                        Update-Stock
                                    </button>
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
                product_name: ""
            },
            req_data: null,
            total_items: 0,
            total_amount: 0,
            form_data: {
              
                final_qty_produced: 0,
                stock_id: null,
                req_id: null,
                
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
        clearSearchFields() {
            Object.keys(this.search_query).forEach(
                key => (this.search_query[key] = "")
            );
        },
        async submitRecords(data,i) {
           const shouldDelete = await this.confirmDialogue("You are about to updated Stock Qty");
            if (shouldDelete) {
           this.form_data.final_qty_produced = data.final_qty_produced;
           
            this.form_data.stock_id = data.stock_id;
            this.form_data.req_id = data.req_id;
            // if(parseFloat(this.form_data.final_qty_produced)<=0){
            //     this.e("Update Stock Qty Can' t  be ")
            //     return 
            // }
            this.showLoader();
            var result = await this.callApi(
                "POST",
                "data/dispatch_assembly_req/update_assembly_stock_qty",
                this.form_data
            );
            this.hideLoader();

            if (result.status === 200) {
                this.s("Successfully Updated");
                 this.req_data.splice(i, 1);
                this.req_data = this.modifyResData(result.data);
            } else {
                this.form_error(result);
            }
            }
        },
        async changeUser() {
            let index = this.user_data.findIndex(
                user => user.id == this.form_data.user_id
            );
            this.form_data.user_id = this.user_data[index].id;
            this.showLoader();
            const res = await this.getApi(
                "data/assembly_requisition/fetchGroupedUserRequests",
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
                this.search_query.product_name.length > 0
            ) {
                this.fetchRequest();
            } else {
                this.fetchRequest();
            }
        },
        async fetchRequest() {
            const res = await this.getApi(
                "data/assembly_requisition/fetchGroupedUserRequests",
                {
                    params: {
                        user_id: this.form_data.user_id,
                        ...this.search_query
                    }
                }
            );

            this.req_data = this.modifyResData(res.data);
        },
        modifyResData(data) {
            let modif = data.map(obj => {
                return {
                    stock_id: obj.stock_id,
                    id: obj.id,
                  
                    req_id: obj.req_id,
                    cost_price: obj.stock.cost_price,
                    manufacture_item: obj.stock.product_name,
                    available_qty: obj.stock.qty,
                    total_ingredient_qty_issued:
                        obj.total_ingredient_qty_issued,
                    total_ingredient_pending: obj.total_ingredient_pending,
                    final_qty_produced: obj.qty
                };
            });
            return modif;
        }
    }
};
</script>
