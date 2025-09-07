<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
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
            <div class="card">
                <div class="card-header">
                    <h4>Add Production Details</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label> Production Date *</label>
                                      <date-picker  v-model="form_data.produced_on" valueType="format"></date-picker>
                                </div>
                                <div class="form-group">
                                    <label>Employee *</label>
                                    <input type="text" disabled v-model="form_data.employee_name" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Product Details *</label>
                                   <input type="text"
                                    v-model="form_data.product_name"  disabled class="form-control">                                </div>
                                <div class="form-group">
                                    <label>QTY *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.qty"
                                        placeholder="QTY"
                                    />
                                </div>


                                 <div class="form-group">
                                    <label>Total Pay *</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.total_pay"
                                        placeholder="Total Pay"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Size *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.size"
                                        placeholder="size"
                                    />
                                </div>

                                  <div class="form-group">
                                    <label>Color *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="form_data.colour"
                                        placeholder="Color"
                                    />
                                </div>

                                <div class="form-group">
                                    <label>Description *</label>
                                    <textarea
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.description"
                                        placeholder="Description"
                                    >
                                    </textarea>
                                </div>
                            </div>
                        </div>

                        <button
                        v-if="isUpdatePermitted"
                            type="button"
                            class="btn btn-primary"
                            @click="submitProduction()"
                        >
                            Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>

export default {
props:['data'],
data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            select_data: {
                employee_data: "",
                product_data: "",
                
            },
            form_data: {
               product_name:'',
                id:'',
               produced_on:'',
               product_id:'',
               employee_id:'',
               qty:1,
               total_pay:0,
               size:'',
               colour:'',
               description:'',
               employee_name:'',
               pay_rate:0,
            }
        };
    },
    mounted() {
       
        this.form_data=this.data
        this.form_data.employee_name=this.data.employee.first_name+ ' '+this.data.employee.last_name
         this.form_data.product_name=this.data.product.name
          this.form_data.pay_rate=this.data.product.pay_rate
    },
    watch: {
       
    //      form_data: {
    //     handler(val){
         
    //               this.form_data.total_pay= val.qty*val.pay_rate
       
    //  },
    //   deep: true
       //  }
    
    },
    methods: {
    
       
        
        async submitProduction() {
            const res = await this.callApi(
                "put",
                "data/production/update",
                this.form_data
            );

            if (res.status === 200) {
                this.s("  Details has been updated successfully!");
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
</style>
