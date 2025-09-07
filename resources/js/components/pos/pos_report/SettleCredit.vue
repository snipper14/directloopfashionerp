<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <b>
                                Settle Credit for
                                {{ settle_credit_data.customer_name }}</b
                            >
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 form-group">
                                    <label for=""
                                        >Amount To Pay
                                        {{
                                            settle_credit_data.credit_pay
                                        }}  CASH</label
                                    >
                                    <input
                                        type="number"
                                        v-model="form_data.cash_pay"
                                        class="form-control"
                                    />
                                </div>
                                 <div class="col-md-8 form-group">
                                    <label for=""
                                        > MPESA</label
                                    >
                                    <input
                                        type="number"
                                        v-model="form_data.mpesa_pay"
                                        class="form-control"
                                    />
                                </div>
                                 <div class="col-md-8 form-group">
                                    <label for=""
                                        > CARD</label
                                    >
                                    <input
                                        type="number"
                                        v-model="form_data.card_pay"
                                        class="form-control"
                                    />
                                </div>
                                <div class="col-md-8 form-group">
                                    <v-btn
                                    @click="settleCredit"
                                        color="primary"
                                        :loading="btn_loading"
                                        small
                                        >Settle</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
export default {
    props: ["settle_credit_data"],
    data() {
        return {
            btn_loading: false,
            form_data: {
                mpesa_pay: 0,
                cash_pay:0,
                card_pay:0,
                receipt_no: null,
                id: null,
            },
        };
    },
    mounted() {
        this.form_data.id = this.settle_credit_data.id;
       
    },
    methods: {
        async settleCredit() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/pos_sale/settleCredit",
                this.form_data
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.$emit('dismiss-credit')
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
