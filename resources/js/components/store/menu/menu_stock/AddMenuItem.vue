<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
              <!-- <ImportStock/> -->
            <div class="card">
                <div class="card-header">
                  
                </div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">
                            Stock Details
                        </legend>
                        <form>
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group d-flex flex-column">
                                    <label for="">  Category*</label>

                                    <Select
                                        v-model="form_data.product_category_id"
                                        
                                    >
                                        <Option
                                            v-for="item in menu_category_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.name }}</Option
                                        >
                                    </Select>
                                </div>
                                 <div class="form-group d-flex flex-column">
                                    <label for="">  Portions/Unit*</label>

                                    <Select
                                        v-model="form_data.unit_id"
                                       @on-change="changePortion"
                                    >
                                        <Option
                                            v-for="item in unit_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.name }}</Option
                                        >
                                    </Select>
                                </div>
                                    <div class="form-group">
                                        <label>Code(Unique) *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.code"
                                            placeholder="Product Code"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label>Item Name *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.name"
                                          
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label>Selling Price *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.selling_price"
                                           
                                        />
                                    </div>
                                </div>

                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Cost Price *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.cost_price"
                                            
                                        />
                                    </div>
                                     <div class="form-group">
                                        <label>Production Cost *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.production_cost"
                                            
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label>Wholesale Price *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.wholesale_price"
                                         
                                        />
                                    </div>
                                      <div class="form-group">
                                        <label>Available Stock *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.qty"
                                          
                                        />
                                    </div>
                                       <div class="form-group">
                                        <label>Reorder Qty *</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.reorder_qty"
                                          
                                        />
                                    </div>
                                </div>

                                <div class="col-4">
                                    
                                    

                                       <div class="form-group">
                                        <label>Description *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.description"
                                        />
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                v-if="isWritePermitted"
                                class="btn btn-primary "
                                @click="submitData()"
                            >
                                Save
                            </button>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import ImportStock from "./ImportStock";
export default {
   props:['unit_data','menu_category_data'],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            form_data: {
               
                product_category_id:null,
                menu_name:'',
               portion_name:'',
                unit_id:null,
                name: "",
                code: "",
                selling_price: 0,
                cost_price: 0,
                wholesale_price: 0,
                production_cost:0,
                qty:0,
              reorder_qty:0,
                description: ""
            }
        };
    },
    components:{

        ImportStock
    },
    watch: {
        form_data: {
            handler() {
              
               this.form_data.menu_name=this.form_data.name.trim()+"-" +this.form_data.portion_name.trim()
             
            },
            deep: true
        },
    },
    methods: {
        changePortion(){
             let index = this.unit_data.findIndex(
                unit => unit.id == this.form_data.unit_id
            );
             let portion = this.unit_data[index].name;
             this.form_data.portion_name=portion
          
        },
        async submitData() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/menu_product/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        }
    }
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
