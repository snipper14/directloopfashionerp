<template>
    <div class="container">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="">Opening Amount</label>
                    <input
                        type="number"
                        class="form-control"
                        v-model="form_data.opening_amount"
                    />
                </div>
                <div class="form-group">
                    <label>From Date *</label
                    ><date-picker
                        v-model="form_data.from"
                        type="datetime"
                    ></date-picker>
                </div>
                <div class="form-group">
                    <button
                        @click="saveOpeningBalance()"
                        class="btn btn-primary btn-sm"
                    >
                        Save Details
                    </button>
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
                opening_amount: 0,
                opening_time: null,
                from: null
            }
        };
    },
    mounted() {},
    methods: {
        async saveOpeningBalance() {
            this.form_data.opening_time = this.formatDateTime(
                this.form_data.from
            );
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/reception_balance/createOpeningBalance",
                this.form_data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Added");
            } else {
                this.form_error(res);
            }
        }
    }
};
</script>
<style scoped></style>
