<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Open Cashier Registers</h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 form-group nopadding">
                                <label for="">Opening Cash Balance</label>
                                <input
                                    v-model="form_data.opening_cash_balance"
                                      :disabled="isDisabled"
                                    type="number"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-8 form-group nopadding">
                                <label for="">Opening Mpesa Balance</label>
                                <input
                                    v-model="form_data.opening_mpesa_balance"
                                 
                                    type="number"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-8 nopadding">
                                <Button
                                    type="primary"
                                    :loading="isSending"
                                    :disabled="isSending"
                                    v-if="isWritePermitted"
                                    @click="addRegister"
                                    >Open Register</Button
                                >
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
            isSending: false,
             isDisabled: false,
            form_data: {
                opening_cash_balance: 0,
                opening_mpesa_balance: 0,
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchClosingShift();
    },
    methods: {
        async fetchClosingShift() {
            this.showLoader();
            const res = await this.getApi("data/register/fetchClosingShift");
            this.hideLoader();
            if (res.status == 200) {
                this.s("loaded");
                //disable fields
                this.form_data.opening_cash_balance = res.data.cashier_left_cash;
                
                this.isDisabled = true;
            } else if (res.status == 201) {
                //enable fields
                 this.isDisabled = false
            } else {
                this.form_error(res);
            }
        },
        async addRegister() {
            this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/register/create",
                this.form_data
            );
            this.isSending = false;
            if (res.status == 200) {
                this.successNotif("Added");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
