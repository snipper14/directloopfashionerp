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
                            <div class="col-md-4 nopadding">
                                <div class="form-group date-picker-wrapper">
                                    <label for="">Payment Date</label>
                                    <date-picker
                                        v-model="form_data.date_paid"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-4 nopadding">
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
                            <div class="col-md-4 nopadding">
                                <div class="form-group">
                                    <label>Invoice Amount *.</label>
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

                            <div class="col-md-4 nopadding">
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
                            <div class="form-group col-md-4 nopadding">
                                <label>Payment Method*</label>
                                <Select v-model="form_data.pay_method">
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
                            <div class="form-group col-md-2 nopadding">
                                <label for=""> Accounts</label>
                                <treeselect
                                    width="300"
                                    :load-options="fetchAccounts"
                                    :options="accounts_select_data"
                                    :auto-load-root-options="false"
                                    v-model="account_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select  Debit Account "
                                />
                            </div>

                            <div class="col-md-4 nopadding">
                                <div class="form-group">
                                    <label>{{ ref_no }}*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.ref_no"
                                        :placeholder="ref_no"
                                    />
                                </div>
                            </div>

                            <div class="col-md-4 nopadding">
                                <div class="form-group">
                                    <label> Outstanding Balance*.</label>
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
                        </div>

                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Invoice No</th>
                                            <th scope="col">Invoice Amount</th>

                                            <th scope="col">Unpaid Amount</th>

                                            <th scope="col">Amount Paid</th>
                                            <th scope="col">Amount Pending</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in invoice_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.invoice_no }}
                                            </td>
                                            <td>{{ data.invoice_total }}</td>
                                            <td>{{ data.unpaid_amount }}</td>

                                            <td>
                                                <input
                                                    class="form-control"
                                                    type="number"
                                                    style="width: 80px"
                                                    v-model="data.amount_paid"
                                                />
                                            </td>

                                            <td>
                                                {{
                                                    data.unpaid_amount -
                                                    data.amount_paid
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <v-btn color="primary" @click="savePayments()">
                                    Save Payment Info
                                </v-btn>
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
            accounts_select_data: null,
            payment_method_select: [
                { name: "Cheque" },
                { name: "M-pesa" },
                { name: "Cash" },
                 { name: "VISA" },
                { name: "Bank Transfer" },
            ],
            ref_no: "ref no",
            account_id: null,
            form_data: {
                supplier_id: "",
                amount_paid: 0,
                invoice_total: 0,
                outstanding_balance: 0,
                ref_no: "",
                date_paid: "",
                payment_details: "",
                pay_method: "",
                account_id: null,
                description: "",
            },
            isLoading: true,
            invoice_data: [],
        };
    },
    components: {
        Treeselect,
    },

    mounted() {
        this.form_data.supplier_id = this.data.supplier_id;

        this.form_data.invoice_total =
            this.data.total_credit - this.data.total_debit;

        this.form_data.date_paid = this.getCurrentDate();

        this.concurrentApiReq();
    },
    watch: {
        form_data: {
            handler(val) {
                this.form_data.outstanding_balance =
                    parseFloat(val.invoice_total) - parseFloat(val.amount_paid);
            },
            deep: true,
        },
        account_id: {
            handler: _.debounce(function (val, old) {
                var name = this.accounts_select_data.find(
                    (x) => x.id === val
                ).label;
                this.form_data.account_id = val;
                this.form_data.description = name;
            }, 0),
        },
        "form_data.pay_method": function (newVal, oldVal) {
           
            if (newVal == "Cheque") {
                this.ref_no = "Cheque No.";
            }
            if (newVal == "M-pesa") {
                this.ref_no = "M-pesa Confirmation Code";
                
            }
            if (newVal == "Cash") {
                this.ref_no = "Cash";
               
            }
            if (newVal == "VISA") {
                this.ref_no = "Confirmation code";
            }
            if (newVal == "Bank Transfer") {
                this.ref_no = "Bank transfer code";
            }
        },
    },
    methods: {
       
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetchUnpaidSupplierInvoices()]);
            this.isLoading ? this.hideLoader() : "";
        },
        async fetchUnpaidSupplierInvoices() {
            const res = await this.getApi(
                "data/supplier_invoice/fetchUnpaidSupplierInvoices",
                {
                    params: {
                        supplier_id: this.form_data.supplier_id,
                    },
                }
            );

            if (res.status == 200) {
                this.invoice_data = this.modifyResData(res.data);
            } else {
                this.swr("Error occured in the server");
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    invoice_no: obj.invoice_no,
                    invoice_total: obj.invoice_total,
                    unpaid_amount: obj.unpaid_amount,
                    amount_paid: 0,
                    pending_amount: 0,
                };
            });
            return modif;
        },
        async savePayments() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/supplier_payment/create",
                {
                    ...this.form_data,
                    invoice_data: this.invoice_data,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Successfully saved");
                this.$emit("dashboard-active");
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
.nopadding {
    padding: 0 !important;
    margin: 0 !important;
}
</style>
