<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Add Customer to Etims</div>

                        <div class="card-body">
                            <div class="form-group col-md-12">
                                <label for="">Customer </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="formData.company_name"
                                />
                            </div>
                            <div class="form-group col-md-12">
                                <label for="">PIN </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="formData.pin"
                                />
                            </div>
                            <div class="form-group col-md-12">
                                <v-btn
                                    :loading="btn_loading"
                                    color="primary"
                                    @click="handleSumbit()"
                                    >SUBMIT</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
    </div>
</template>

<script>
export default {
    props: ["edit_data"],
    data() {
        return {
            btn_loading: false,
            formData: {
                pin: "",
                id: null,
                company_name: "",
            },
        };
    },
    mounted() {
        this.formData.id = this.edit_data.id;
        this.formData.pin = this.edit_data.pin;
        this.formData.company_name = this.edit_data.company_name;
    },
    methods: {
        async handleSumbit() {
            this.btn_loading = true;
           
          
            const res = await this.callApi(
                "POST",
                "data/digitax_customer/addCustomerEtims",
                this.formData
            );
            this.btn_loading = false;

            if (res.status == 200) {
                this.s("Updated ");
                this.$emit('dismiss-diag',res.data)
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
