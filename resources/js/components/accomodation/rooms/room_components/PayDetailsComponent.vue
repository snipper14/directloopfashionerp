<template>
    <div>
        <div class="border p-2">
            <b>Previous Payments</b>
            <div class="form-group">
                <label for="">Cash Pay</label>
                <input
                    type="number"
                    disabled
                    v-model="form_data.cash_paid"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="">Mobile Money</label>
                <input
                    type="number"
                    disabled
                    v-model="form_data.mobile_money_paid"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="">Credit Card</label>
                <input
                    type="number"
                    disabled
                    v-model="form_data.card_paid"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="">Bank Transfer</label>
                <input
                    type="number"
                    disabled
                    v-model="form_data.bank_transfer_paid"
                    class="form-control"
                />
            </div>
            <div class="form-group">
                <label for="">Cheque</label>
                <input
                    type="number"
                    disabled
                    v-model="form_data.cheque_paid"
                    class="form-control"
                />
            </div>
              <div class="form-group">
                <label for="">Online Pay</label>
                <input
                    type="number"
                    disabled
                    v-model="form_data.online_paid"
                    class="form-control"
                />
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["form_data"],
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted.");
    },
};
</script>
