<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ edit_data.account }}</div>

                        <div class="card-body row">
                            <div class="form-group col-md-12">
                                <label for="">Description*</label>
                                <input
                                    type="text"
                                    placeholder="Description"
                                    class="form-control"
                                    v-model="form_data.desc"
                                />
                            </div>
                            <div class="form-group col-md-12">
                                <label for="">Dr Amount*</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    placeholder="Description"
                                    v-model="form_data.debit_amount"
                                />
                            </div>
                            <div class="form-group d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="form_data.entry_date"
                                ></date-picker>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="">Cr Amount*</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    placeholder="Description"
                                    v-model="form_data.credit_amount"
                                />
                            </div>
                            <v-btn
                                :loading="btn_loading"
                                color="info"
                                small
                                @click="saveBalances()"
                                >Save</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
export default {
    props: ["edit_data"],
    data() {
        return {
            btn_loading: false,
            form_data: {
                desc: "Opening Balance",
                credit_amount: 0,
                debit_amount: 0,
                account_id: null,
                account: "",
                entry_date: null,
            },
        };
    },
    mounted() {
        this.form_data.entry_date = this.getCurrentDate();
        this.form_data.account_id = this.edit_data.id;
        this.form_data.account = this.edit_data.account;
        console.log("Component mounted." + JSON.stringify(this.edit_data));
    },
    methods: {
        async saveBalances() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/general_ledger/saveBalances",
                this.form_data
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.s("added...");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
