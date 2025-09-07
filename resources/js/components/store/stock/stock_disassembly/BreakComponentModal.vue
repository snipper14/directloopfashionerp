<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"> <h3>Disassembly Modal</h3> </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <Select
                                        @change="onChange($event)"
                                        v-model="form_data.stock_id"
                                        filterable
                                        :remote-method="searchProducts"
                                        :loading="loading1"
                                    >
                                        <Option
                                            v-for="(
                                                option, index
                                            ) in search_stock_list"
                                            :value="option.value"
                                            :key="index"
                                            >{{ option.label }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                            
                            <div
                                class="col-md-3 form-group d-flex flex-direction-raw"
                            >
                                <label for="">Parent Qty</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model="form_data.parent_qty"
                                />
                            </div>

                             <!-- <div class=" col-2 form-group">
                                <label for="" 
                                    >End Product </label
                                >
                                <input v-model="form_data.end_product" type="checkbox" />
                            </div> -->
                            <div class="col-md-3">
                                <v-btn
                                    class="ma-2"
                                    :loading="btn_loading"
                                    outlined
                                    color="indigo"
                                    @click="fetchRawMaterials()"
                                >
                                    Fetch Materials</v-btn
                                >
                            </div>
                        </div>
                        <div class="row">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label for=""> Qty</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.parent_qty"
                                    />
                                </div>
                              
                                <div class="col-md-3 form-group">
                                    <label for="">Issue No</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="form_data.issue_no"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="">Prod No</label>
                                    <input
                                        type="text"
                                        disabled
                                        v-model="form_data.production_no"
                                        class="form-control"
                                    />
                                </div>
                                 <div class="col-md-3 form-group">
                                    <label for="">Date</label>
                                   <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="form_data.disassembly_date"
                                ></date-picker>
                                </div>
                                <div
                                    class="col-md-4 form-group d-flex flex-column"
                                >
                                    <label for="">Parent Location*</label>

                                    <Select v-model="form_data.parent_department_id">
                                        <Option
                                            v-for="item in dept_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                                  <div
                                    class="col-md-4 form-group d-flex flex-column"
                                >
                                    <label for="">Child Location*</label>

                                    <Select v-model="form_data.child_department_id">
                                        <Option
                                            v-for="item in dept_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                                  
                            </div>
                            <div class="table-responsive">
                                <center>
                                    <h4><b>By Products </b></h4>
                                </center>
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Item</th>
                                            <th scope="col">Unit</th>
                                            <th>Cost Price</th>
                                          
                                            <th>Qty Req.</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                value, i
                                            ) in raw_materials_data"
                                            :key="i"
                                        >
                                            <th scope="row">
                                                {{ value.product_name }}
                                            </th>
                                            <td>
                                                {{ value.unit }}
                                            </td>
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="value.cost_price"
                                                />
                                            </td>
                                          
                                            <td>
                                                <input
                                                    type="number"
                                                    v-model="value.qty"
                                                />
                                            </td>

                                            <td>
                                                {{
                                                    (
                                                        value.qty *
                                                        value.cost_price
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <button
                                                    v-if="isDeletePermitted"
                                                    class="btn btn-sm btn-danger"
                                                    @click="
                                                        deleteRawMaterialsDetails(
                                                            i
                                                        )
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                 
                            <div>
                                <center>
                                   
                                      <v-btn
                                        :loading="btn_loading"
                                        small
                                        color="primary"
                                        dark
                                        @click="completeIssue('complete')"
                                    >
                                        Complete Disassembly</v-btn
                                    >
                                </center>
                            </div>
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
            
            search_stock_list: [],
            loading1: false,
            raw_materials_data: [],
        
            btn_loading: false,
            dept_data: null,
            form_data: {
                stock_id: "",
                 parent_qty: 0,
                cost_price: 0,
                production_cost: 0,
                parent_department_id: null,
                issue_no: null,
                production_no: null,
              disassembly_date:null,
                child_department_id:null,
            },
        };
    },
    mounted() {
        this.form_data.disassembly_date=this.getCurrentDate()
        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                let index = this.search_stock_list.findIndex(
                    (cp) => cp.value == this.form_data.stock_id
                );

                this.form_data.cost_price =
                    this.search_stock_list[index].cost_price;
                this.form_data.production_cost =
                    this.search_stock_list[index].production_cost;
            }, 500),
        },
    },
    methods: {
     
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchDept(),
                this.fetchIssueNo(),
                this.fetchProductionNo(),
            ]);
            this.hideLoader();
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchProductionNo() {
            const res = await this.getApi(
                "data/stock_production/productionNo",
                ""
            );

            if (res.status == 200) {
                this.form_data.production_no = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchIssueNo() {
            const res = await this.getApi(
                "data/stock_production/fetchIssueNo",
                ""
            );

            if (res.status == 200) {
                this.form_data.issue_no = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async completeIssue() {
            this.btn_loading = true;
            const data = {
                child_elements_arr: this.raw_materials_data,
              
                ...this.form_data,
               
            };
            const res = await this.callApi(
                "POST",
                "data/stock_disassembly/productDisassembly",
                data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.successNotif("Successfully issued");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
        async deleteRawMaterialsDetails(i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.raw_materials_data.splice(i, 1);
            }
        },

       

        async fetchRawMaterials() {
            this.btn_loading = true;
            const res = await this.callApi(
                "post",
                "data/stock_production/fetchRawMaterials",
                {
                    stock_id: this.form_data.stock_id,
                    desired_qty: this.form_data.parent_qty,
                }
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.raw_materials_data = this.modifyIngredientKey(
                    res.data.raw_materials
                );
           
              
            } else {
                this.form_error(res);
            }
        },
        modifyIngredientKey(data) {
            let modif = data.map((obj) => {
                return {
                    child_stock_id: obj.ingredient.id,
                    product_name: obj.ingredient.product_name,
                    unit: obj.ingredient.unit.name,
                    unit_id: obj.ingredient.unit_id,
                   
                    cost_price: obj.ingredient.purchase_price,
                    qty: (
                        (obj.qty / obj.produced_qty) *
                        this.form_data.parent_qty
                    ),
                    produced_qty: obj.produced_qty,
                };
            });
            return modif;
        },
      

        searchProducts: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;
                const res = await this.getApi("data/stock/search_items", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_stock_list = this.modifyKey(res.data).filter(
                    (item) =>
                        item.label.toLowerCase().indexOf(query.toLowerCase()) >
                        -1
                );
            }
        }, 500),

        modifyKey(data) {
            let modif = data.map((obj) => {
                return {
                    value: obj.id,
                    label: obj.product_name,
                    cost_price: obj.purchase_price,
                    production_cost: obj.production_cost,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
td {
    font-size: 0.8rem !important;
}
</style>
