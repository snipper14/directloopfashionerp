<template>
    <div class="container">
        <v-app>
             <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Update Shelves/Racks</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                 <label >Shelve</label>
                                <input
                                  
                                    v-model="formData.name"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                                 <div class="col-md-12">
                                <input
                                  
                                    v-model="formData.description"
                                    type="text"
                                    class="form-control"
                                   
                                />
                                 </div>
                            </div>
                        
                        <v-btn
                            type="submit"
                            @click="handleSubmit"
                            :loading="is_loading"
                            color="primary"
                        >
                            Update
                        </v-btn>
                    </div>
                </div>
            </div>
        </div>
        </v-app>
       
    </div>
</template>

<script>
export default {
    props:['details_data'],
    data() {
        return {
            is_loading:false,
            formData: {
              id:null,
                name: "",
                description: "",
            },
        };
    },
    mounted() {
      this.formData= this.details_data
    },
    methods:{
         async handleSubmit() {
            this.is_loading = true;
            const res = await this.callApi(
                "PUT",
                "data/shelves/update",
                this.formData
            );
            this.is_loading = false;
            if (res.status === 200) {
                this.s("Added successfully");
                this.$emit("dashboard-active");
               
            } else {
                this.form_error(res);
            }
        },
         
    }
};
</script>
