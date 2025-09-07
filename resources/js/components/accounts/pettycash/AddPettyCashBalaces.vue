<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header"><h3>Add Petty Balances</h3></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex flex-column">
                                    <label> Date*</label>
                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="form_data.date_recorded"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                <label for="">Opening Balance</label>
                                <input
                                    v-model="form_data.opening_balance"
                                    type="number"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="">Closing Balance</label>
                                <input
                                    v-model="form_data.closing_balance"
                                    type="number"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-4">
                                <v-btn
                                    color="primary"
                                    @click="addPettyBalances()"
                                    dark
                                    :loading="loading"
                                    >Save</v-btn
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
            form_data: {
                opening_balance: 0,
                closing_balance: 0,
                date_recorded: null,
            },
            loading: false,
        };
    },

    mounted() {
        this.form_data.date_recorded = this.getCurrentDate();
    },
    methods: {
        async addPettyBalances() {
            this.loading = true;
            const res = await this.callApi(
                "POST",
                "data/petty_cash/create",
                this.form_data
            );
            this.loading = false;
            if (res.status === 200) {
                this.s("Added Successfully");
                this.form_data.opening_balance=0
                this.form_data.closing_balance=0
                this.$emit('closing_diag')
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
