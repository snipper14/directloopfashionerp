<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
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
                    <div class="card-header">Update User Password</div>
                    <div class="card-body">
                        <div class="row ml-2">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">New Password</label>
                                    <input
                                        type="password"
                                        v-model="form_data.password"
                                        autocomplete="new-user-street-address"
                                      
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Confirm Password</label>
                                    <input
                                        type="password"
                                        v-model="form_data.password_confirmation"
                                        autocomplete="new-user-street-address"
                                       
                                        class="form-control"
                                    />
                                </div>
                             
                           <button class="btn btn-primary btn-small" @click="updatePassword()">Update</button>
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
export default {
    props: ['data'],
    data() {
        return {
        
            form_data: {
              password:'',
              password_confirmation:''
            }
        };
    },
    mounted() {
      
        this.form_data.id=this.data
    
    },
    methods: {
        async updatePassword() {
            
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/admin/updatePassword",
                this.form_data
            );

            this.hideLoader()

            if (res.status === 200) {
                this.s(" Password  has been updated successfully!");

                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active", res.data);
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
label {
    color: #000;
    font-weight: 600;
}
</style>
