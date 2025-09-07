<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">REFUND</div>

                    <div class="card-body">
                        <div class="col-md-8 form-group">
                            <label for="">Refund Amount</label>
                            <input
                                type="number"
                                disabled
                                class="form-control"
                                v-model="formData.refund_amount"
                            />
                        </div>
                           <div class="form-group col-md-8 nopadding">
                                <label>Payment Method*</label>
                                <Select v-model="formData.pay_method">
                                    <Option
                                        v-bind:value="data.name"
                                        v-for="(
                                            data, i
                                        ) of payment_method_select"
                                        :key="i"
                                    >
                                        {{ data.name }}
                                    </Option>
                                </Select>
                            </div>
                        <div class="form-group col-md-8">
                            <label for="">Refund Accounts</label>
                            <treeselect
                                :load-options="fetchAccounts"
                                :options="accounts_select_data"
                                :auto-load-root-options="false"
                                v-model="formData.account_id"
                                :multiple="false"
                                :show-count="true"
                                placeholder="Select  Refund Account "
                            />
                        </div>
                        <div class="col-md-8 form-group">
                            <label for="">Receipt No*</label>
                            <input
                                type="text"
                                disabled
                                class="form-control"
                                v-model="formData.receipt_no"
                            />
                        </div>
                        <div class="col-md-8 form-group">
                            <label for="">Ref Details*</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="formData.ref_details"
                            />
                        </div>
                        <div class="col-md-8 form-group">
                            <label for="">Comments</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="formData.comments"
                            />
                        </div>
                        <div class="col-md-6">
                            <Button
                                type="primary"
                                :loading="isSending"
                                :disabled="isSending"
                                v-if="isWritePermitted"
                                @click="commitRefund"
                                >Commit</Button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    components: { Treeselect },
    props: ["form_data", "receipt_details"],
    data() {
        return {
            isSending: false,
               payment_method_select: [
                { name: "Cheque" },
                { name: "M-pesa" },
                { name: "Cash" },
                { name: "VISA" },
                { name: "Bank Transfer" },
            ],
            formData: {
                pay_method:"",
                refund_amount: this.form_data.total_cr,
                account_id: null,
                ref_details: "",
                comments: "",
                receipt_no: this.receipt_details.receipt_no,
                credit_no: this.form_data.credit_no,
                credit_date:this.form_data.credit_date,
                   paid_date:this.form_data.credit_date
            },
        };
    },
    mounted() {
        console.log(
            "Component mounted." + JSON.stringify(this.receipt_details)
        );
    },
    methods: {
        async commitRefund() {
            this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/pos_sale/commitRefund",
                this.formData
            );
            this.isSending = false;
            if (res.status === 200) {
                this.successNotif("Successfully credited")
                this.$emit('dashboard-active')
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
