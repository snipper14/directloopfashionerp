<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left> mdi-arrow-left </v-icon>
                    Back
                </v-btn>
                <div class="card">
                    <div class="card-header">
                        <b>Supplier: </b>{{ data.company_name }}
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group date-picker-wrapper">
                                    <label for="">Payment Date</label>
                                    <date-picker
                                        v-model="form_data.date_paid"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Payment Details</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.payment_details"
                                        placeholder="Payment Details"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ref No.</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.ref_no"
                                        placeholder="Ref No"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label> Amount Paid*.</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.amount_paid"
                                        placeholder="Paid Amount"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Invoice Amount *.</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.invoice_amount"
                                        placeholder="Invoice Amount"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Invoice Amount *.</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.invoice_total"
                                        placeholder="Invoice Amount"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label
                                        >Invoice Amount*.</label
                                    >
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        autocomplete="new-user-street-address"
                                        v-model="
                                            form_data.invoice_amount
                                        "
                                        placeholder="Outstanding Balance"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Total Outstanding Balance*.</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        disabled
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.outstanding_balance"
                                        placeholder="Outstanding Balance"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label> Pay Method/Account*.</label>
                                    <input
                                        type="txt"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.pay_method"
                                        placeholder="e.g Bank, Cash"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <button
                                    class="btn btn-primary mt-3"
                                    @click="savePayments()"
                                >
                                    Save Info
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import _ from "lodash";
import { debounce } from "debounce";
const simulateAsyncOperation = (fn) => {
    setTimeout(fn, 2000);
};

export default {
    props: ["data"],
    data() {
        return {
            form_data: {
                pending_data: {},
                supplier_id: "",
                amount_paid: 0,
                invoice_total: 0,
                outstanding_balance: 0,
                ref_no: "",
                date_paid: "",
                payment_details: "",
                pay_method: "",
             
                invoice_amount: 0,
                supplier_invoice_id: null,
            },
        };
    },
    components: {
        Treeselect,
    },

    mounted() {
        console.log(JSON.stringify(this.data));
        this.form_data.supplier_invoice_id = this.data.id;
        this.form_data.supplier_id = this.data.supplier_id;
        this.form_data.invoice_amount = this.data.invoice_total;
        this.form_data.date_paid = this.getCurrentDate();
        this.fetchSupplierCredit();
    },
    watch: {
        form_data: {
            handler(val) {
                this.form_data.outstanding_balance =
                    parseFloat(val.invoice_total) - parseFloat(val.amount_paid);
            },
            deep: true,
        },
    },
    methods: {
        async savePayments() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/supplier_payment/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Successfully saved");
                this.$emit("dashboard-active");
            } else {
               this.form_error(res)
            }
        },
        async fetchSupplierCredit() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_invoice/fetchSupplierCredit",
                {
                    params: {
                        supplier_id: this.form_data.supplier_id,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.pending_data = res.data;
                this.form_data.invoice_total =
                    this.pending_data.total_credit -
                    this.pending_data.total_debit;
             
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
.dropdown-search-ul {
    padding: 1rem;
    cursor: pointer;
    border-radius: 5px;
    background-color: #ddd;
    list-style: none;
    position: absolute;
    z-index: 1000;
}
.dropdown-search-li {
    padding: 1rem;
    cursor: pointer;
    border-bottom: 1px solid #fff;
}
</style>
