<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Code. </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.code"
                                    autocomplete="new-user-street-address"
                                    placeholder="Code"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Product Name </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="search_query.product_name"
                                    autocomplete="new-user-street-address"
                                    placeholder="Product Name"
                                />
                            </div>
                            <div class="col-md-2 form-group">
                                <button
                                    class="btn btn-secondary btn-sm"
                                    @click="search_results = []"
                                >
                                    Clear Search
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12 border border-secondary">
                            <div class="table-responsive custom-table">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Qty</th>
                                         
                                            <th scope="col">Note</th>
                                          
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
                                            <td>
                                                <textarea
                                                    class="form-control custom-text"
                                                    type="text"
                                                    v-model="data.product_name"
                                                />
                                            </td>

                                            <td>
                                                <input
                                                    class="form-control custom-text"
                                                    type="number"
                                                    v-model="data.qty"
                                                />
                                            </td>

                                           
                                            <td>
                                                <textarea
                                                    class="form-control"
                                                    type="text"
                                                    v-model="data.note"
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
                            <h4><b>Prescriptions</b></h4>
                            <div class="table-responsive custom-table">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Qty</th>
                                           
                                            <th scope="col">Note</th>
                                           
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in pending_lab_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.product_name }}
                                            </td>

                                            <td>
                                                {{ data.qty }}
                                            </td>

                                        
                                            <td>
                                                {{ data.note }}
                                            </td>
                                         
                                            <td>
                                                <v-btn
                                                    title="delete"
                                                    v-if="isDeletePermitted"
                                                    color="danger"
                                                    x-small
                                                    @click="deleteRecords(data,i)"
                                                >
                                                    Delete
                                                </v-btn>
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
    props: ["edit_data",'form_data'],
    data() {
        return {
            search_results: [],
            pending_lab_data: [],
            search_query: {
                code: "",
                product_name: "",
            },
        };
    },

    mounted() {
        this.fetchPendingProducts();
        
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
        async deleteRecords(data,i){
             const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {return;}
             this.showLoader();
            const res = await this.callApi('DELETE',
                "data/lab/destroy/"+data.id,
               {}
            );
            this.hideLoader();
            if (res.status == 200) {
                this.pending_lab_data.splice(i, 1);
            }
        },
        async fetchPendingProducts() {
            this.showLoader();
            const res = await this.getApi(
                "data/lab/fetchPendingProducts",
                {
                    params: {
                        customer_id: this.form_data.customer_id,
                     
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.pending_lab_data = res.data;
            }
        },
        async submitRecords(data) {
            this.showLoader();
            const mergedObject = Object.assign({}, data, {
                customer_id: this.edit_data.id,
            });
            const res = await this.callApi(
                "POST",
                "data/lab/addLabProducts",
                mergedObject
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Added");
                this.pending_lab_data = res.data;
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
                    repeat_pattern: "",
                    note: "",
                    prescription_date_time: "",
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
.custom-text {
    font-size: 10px !important;
    font-weight: bold !important;
}
</style>
