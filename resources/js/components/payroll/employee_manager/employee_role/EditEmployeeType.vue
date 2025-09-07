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
                <div class="card-header" >
                   
                    <h4 >Update Employee Type</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="name">  Name*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="name"
                                        v-model="form_data.name"
                                        placeholder="Name"
                                    />
                                   
                                </div>
                                <div class="form-group">
                                    <label for="details">Details *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="details"
                                        v-model="form_data.details"
                                        placeholder="Details"
                                    />
                                  
                                    
                                </div>
                               
                                  
                                
                            </div>
                           </div>

                        <button type="button" class="btn btn-primary" @click="edit()">
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
            
            form_data: {
                name:'',
                details: "",
                id:null
               
            }
        };
    },
    mounted(){
      
        this.form_data=this.data
    },
    methods: {
        async edit() {
         const res = await this.callApi(
                "put",
                "data/employee_type/update",
                this.form_data
            );
           
            if (res.status === 200) {
              this.s(" dept has been updated successfully!");
                Object.keys(this.form_data).forEach(key => (this.form_data[key] = ""));
               this.$emit('dashboard-active')
            
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
