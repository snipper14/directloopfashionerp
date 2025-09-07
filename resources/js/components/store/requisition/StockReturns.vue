<template>
    <div>
        <div class="row">
            <div class="col-2">
                <RequisitioNav />
            </div>
            <div class="col-9">
                <fieldset class="border" v-if="isReadPermitted">
                    <legend class="text-center">
                        <b><h4>Product Returns</h4></b>
                    </legend>

                    <div class="row">
                        <div class="col-3 ">
                            <label>Return Code:</label>
                            <input
                                type="text"
                                class="form-control "
                                disabled
                                v-model="form_data.return_code"
                            />
                        </div>

                        <div class="col-3">
                            <div class="form-group d-flex flex-column">
                                <label for="">Returned By *</label>


                                <div class="form-group">
                                    <treeselect
                                        v-model="form_data.return_user_id"
                                        :multiple="false"
                                        :options="user_data"
                                        :show-count="true"
                                        placeholder="Select "
                                    />
                                </div>
                            
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="form-group">
                                <div class="form-group">
                                    <label>Return Date *</label>
                                    <date-picker
                                        v-model="form_data.date_returned"
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
                        <div class="col-3">
                            <div class="form-group d-flex flex-column">
                                <label for="">Department*</label>

                                <Select
                                    v-model="form_data.department_id"
                                    style="width:200px"
                                   
                                >
                                    <Option
                                        v-for="item in dept_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.department }}</Option
                                    >
                                </Select>
                            </div>
                        </div>
                    </div>

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
                            <button
                                class="btn btn-secondary btn-sm"
                                @click="search_results = []"
                            >
                                Clear Search
                            </button>
                        </div>
                        <div class="col-8 ">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>

                                        <th scope="col">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in search_results"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.code }}
                                        </td>
                                        <td>{{ data.product_name }}</td>

                                        <td>
                                            <input
                                                type="number"
                                                v-model="data.qty_returned"
                                            />
                                        </td>

                                        <td>
                                            <button
                                                v-if="isWritePermitted"
                                                class="btn btn-secondary btn-sm"
                                                @click="submitRecords(data)"
                                            >
                                                Add Items
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="stock_data">
                                <center><b> Return List</b></center>
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>
                                          

                                            <th scope="col">Qty</th>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in inserted_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    data.stock
                                                        ? data.stock.code
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    data.stock
                                                        ? data.stock
                                                              .product_name
                                                        : ""
                                                }}
                                            </td>
                                           

                                            <td>
                                                
                                                   {{cashFormatter(data.qty_returned)}}
                                               
                                            </td>
                                          
                                            <td>
                                                <router-link to="#" class="danger" v-if="isDeletePermitted" @click.native="delRecord(data,i)">Delete</router-link>
                                            </td>
                                        </tr>
                                        <tr>
                                          
                                            
                                            <td></td>
                                            <td><b>Total Items</b></td>
                                            <td>
                                                <b>{{
                                                    cashFormatter(total_items)
                                                }}</b>
                                            </td>
                                           
                                        </tr>

                                      
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- </form> -->
                </fieldset>
                <center v-else>
                    <b style="color:red;font-size:1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>
           <notifications group="foo" />
    </div>
</template>
<script>
import RequisitioNav from "./RequisitioNav.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    data() {
        return {
            search_query: {
                code: "",
                product_name: ""
            },

            dept_data: [],
            user_data: [],
            inserted_data:[],
            total_items: 0,
            form_data: {
                return_user_id: null,
                return_code: "",
                stock_id: null,
                department_id: null,
                qty_returned: 0,
                date_returned: null,
                details: ""
            },
            stock_data: [],
            search_results: []
        };
    },
    components: {
        RequisitioNav,
        Treeselect,
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.searchProduct();
            }, 500)
        },
        
     inserted_data: {
            deep: true,
            handler() {
                this.total_items = this.inserted_data.reduce(function(sum, val) {
                    return sum + val.qty_returned;
                }, 0);
               
            }
        }
        
    },
    mounted() {
        this.form_data.date_returned = this.getDateTime();
        this.concurrentApiReq();
    },
    methods: {
       async delRecord(data,i){
           this.showLoader()
             const res = await this.callApi('POST',
                "data/stock_returns/destroy",
                {
                    qty_returned:data.qty_returned,
                    id:data.id,
                    stock_id:data.stock_id

                }
            );
            this.hideLoader();
            if(res.status===200){
                this.s("Deleted")
            this.inserted_data.splice(i,1)
            }else{
                this.servo()
            }
       },
      
        async submitRecords(data) {
            this.showLoader();
           /// this.form_data = data;
            this.form_data.date_returned = this.formatDateTime(
                this.form_data.date_returned
            );
            this.form_data.stock_id=data.stock_id
            this.form_data.qty_returned=data.qty_returned
            const res = await this.callApi('POST',
                "data/stock_returns/create",
                this.form_data
            );
            this.hideLoader();
           
            if (res.status === 200) {
                this.s("Successfully added")
              this.inserted_data=res.data
            } else {
                this.form_error(res);
            }
        },
        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query
                    }
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchCode(),
                this.fetchUsers(),
                this.fetchDept()
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        async fetchCode() {
            const res = await this.getApi("data/stock_returns/fetchCode", "");
            if (res.status === 200) {
                this.form_data.return_code = res.data;
            } else {
                this.servo();
            }
        },
        async fetchUsers() {
            const res = await this.getApi("data/users/fetchAll", "");

            if (res.status == 200) {
                this.user_data=this.userSelect(res.data)
         
              
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },

        modifyResData(data) {
            let modif = data.map(obj => {
                return {
                    stock_id: obj.id,

                    code: obj.code,
                    product_name: obj.product_name,
                    qty_returned: 0
                };
            });
            return modif;
        }
    }
};
</script>
