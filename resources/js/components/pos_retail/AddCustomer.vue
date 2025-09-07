<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('addCustomerComplete')"
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
                    
                </div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">
                            Customer Details
                        </legend>
                        <form>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name(Unique) *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.company_name"
                                           
                                        />
                                    </div>
                                   
                                     

                                    <div class="form-group">
                                        <label>Company Contacts *</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="form_data.company_phone"
                                            placeholder="Company Contacts"
                                        />
                                    </div>
                                    
                                   
                                </div>
                            

                          

                                
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary "
                                @click="submitCustomer()"
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
export default {
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            select_data: {
                employee_data: "",
                dept_data: "",
                employee_type_data: ""
            },
          
            form_data: {
                acc_code:'',
                company_name: "",
                company_phone: "",
                address: "",
                postal_address: "",
                city: "",
                country: "",
                company_email: "",
                bank_name: "",
                bank_branch: "",
                account_name: "",
                account_no: "",
                contact_person: "",
                contact_email: "",
                contact_phone: "",
                branch_id:''
            }
        };
    },
    mounted() {
       
    },
    methods: {
        
        async submitCustomer() {
            this.showLoader()
            const res = await this.callApi(
                "post",
                "data/customers/create",
                this.form_data
            );
           this.hideLoader()
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                this.$emit("addCustomerComplete");
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
