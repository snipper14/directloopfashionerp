<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <p>Add payments for <b>{{data.customer.company_name}} </b> Invoice No. {{data.invoice_no}}</p>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="email">Invoice Total*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.invoice_amount"
                                        placeholder="Invoice"
                                    />
                                </div>
                                  <div class="form-group">
                                    <label for="email">Total Due*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.amount_pending"
                                        placeholder="Amount Due"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="email">Inv. Amount Due*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.invoice_pending_amount"
                                        placeholder="Inv Amount Due"
                                    />
                                </div>

                                <div class="form-group">
                                    <label for="email"> Amount Paid*</label>
                                    <input
                                        type="number"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                        v-model="form_data.amount_paid"
                                        placeholder="Amount Paid"
                                    />
                                </div>

                                <div class="form-group">
                                    <label> Payment Date*</label>
                                    <date-picker
                                        v-model="form_data.date_paid"
                                        valueType="format"
                                    ></date-picker>
                                </div>

                                <div class="form-group">
                                    <label for="details"
                                        >Ref No (unique)*</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.ref_no"
                                        placeholder="Ref No"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Payment Method (Account) </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.pay_method"
                                        placeholder="e.g Bank , Cash"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="details">Details </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.payment_details"
                                        placeholder="Details"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="isWritePermitted"
                            type="button"
                            class="btn btn-primary"
                            @click="submitPayment()"
                        >
                            Save
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import ReceivableNav from "../ReceivableNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";

export default {
    props: ["data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            customer_id: null,
            select: {
                customer_options: [],
            },
            form_data: {
                customer_id: "",
                amount_paid: 0,
                amount_pending: 0,
                invoice_pending_amount:0,
                ref_no: "",
                date_paid: "",
                payment_details: "",
                pay_method: "",
                invoice_id: null,
                invoice_amount:0
              
            },
        };
    },
    components: {
        Treeselect,
        Unauthorized,
        ReceivableNav,
    },
    created() {
        this.customerChange();
        console.log(JSON.stringify(this.data));
        this.form_data.date_paid = this.getCurrentDate();
        this.form_data.invoice_id = this.data.id;
        this.form_data.invoice_amount=this.data.invoice_total
    },
    watch: {
        customer_id(val, old) {
            this.customerChange(val);
        },
    },
    methods: {
        async customerChange() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/payments/getInvoiceAmountOwed",
                {
                    customer_id: this.data.customer_id,
                    invoice_id: this.data.id,
                }
            );
            this.hideLoader();
            this.form_data.customer_id = this.data.customer_id;
            if (res.status == 200) {
                this.hideLoader();
                this.form_data.invoice_pending_amount =
                    this.data.invoice_total - res.data.paid_amount;
                       this.form_data.amount_pending =
                     res.data.pending_amount;
            } else {
                this.swr("Error occured in the server");
            }
        },

        async submitPayment() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/payments/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                //	this.users.unshift(res.data)
                this.s(" record has been added successfully!");
                this.$emit('dashboard-active')
                //this.customer_id=null
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
