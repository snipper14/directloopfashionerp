<template>
    <div class="row justify-content-center">
        <div class="col-2">
            <receivable-nav />
        </div>

        <div class="col-md-10">
            <div class="card" v-if="isReadPermitted">
                <div class="card-header">
                    <h4>Add payments</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="form-group col-md-3 nopadding">
                                <label for="">Customer</label>
                                <treeselect
                                    v-model="customer_id"
                                    :multiple="false"
                                    :options="select.customer_options"
                                    :show-count="true"
                                    placeholder="Select Customer"
                                />
                            </div>
                            <div class="form-group col-md-3 nopadding">
                                <label for="email"> Amount Due*</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    disabled
                                    v-model="form_data.amount_pending"
                                    placeholder="Amount Due"
                                />
                            </div>

                            <div class="form-group col-md-3 nopadding">
                                <label for="email"> Amount Paid*</label>
                                <input
                                    type="number"
                                    autocomplete="new-user-street-address"
                                    class="form-control"
                                    v-model="form_data.amount_paid"
                                    placeholder="Amount Paid"
                                />
                            </div>
                            <div class="form-group col-md-3 nopadding">
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
                            <div class="form-group col-md-3 nopadding">
                                <label for="details">{{ref_no}}*</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.ref_no"
                                    :placeholder="ref_no"
                                />
                            </div>
                              <div class="form-group col-md-2 nopadding">
                                        <label for=""
                                            > Accounts</label
                                        >
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
                            <div
                                class="form-group col-md-3 d-flex flex-column nopadding"
                            >
                                <label> Payment Date*</label>
                                <div>
                                    <date-picker
                                        v-model="form_data.date_paid"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="form-group col-md-4 nopadding">
                                <label for="details">Optional Details </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.payment_details"
                                    placeholder="Optional Details"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <input type="text" placeholder="Search Invoice,CU,D Note" v-model="params.search"   style="width: 300px" class="form-control">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Invoice No</th>
                                              <th scope="col">CU. NO.</th>
                                              <th scope="col">D. NO.</th>
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
                                              <td>
                                                {{ data.notes }}
                                            </td>
                                              <td>
                                                {{ data.d_note }}
                                            </td>
                                            <td>{{ data.invoice_total }}</td>
                                            <td>{{ data.unpaid_amount }}</td>

                                            <td>
                                                <input
                                                    class="form-control"
                                                    type="number"
                                                    style="width: 150px"
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
                        <v-btn
                            v-if="isWritePermitted"
                            color="primary"
                            class="mt-4"
                            @click="submitPayment()"
                        >
                            Complete Payment
                        </v-btn>
                    </form>
                </div>
            </div>
            <div v-else>
                <unauthorized />
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
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            customer_id: null,
            ref_no:'ref no',
            select: {
                customer_options: [],
            },
              accounts_select_data: null,
            payment_method_select: [
                  { name: "Cheque" },
                { name: "M-pesa" },
                { name: "Cash" },
                 { name: "VISA" },
                { name: "Bank Transfer" },
            ],
            params:{search:''},
            isLoading: true,
            invoice_data: [],
            account_id:null,
            form_data: {
                customer_id: "",
                amount_paid: 0,
                amount_pending: 0,
                ref_no: "",
                date_paid: "",
                payment_details: "",
                pay_method: "",
                account_id:null,
                description:'',
            },
        };
    },
    components: {
        Treeselect,
        Unauthorized,
        ReceivableNav,
    },
    created() {
        this.getCustomersSelect();

        this.form_data.date_paid = this.getCurrentDate();
    },
    watch: {
        customer_id(val, old) {
              this.isLoading=false
            this.concurrentApiReq();
        },
       
         params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                  this.isLoading=false
              this.fetchUnpaidInvoices()
            }, 500),
        },
        account_id: {
            handler: _.debounce(function (val, old) {
                var name = this.accounts_select_data.find(
                    (x) => x.id === val
                ).label;
              this.form_data.account_id=val
               this.form_data.description=name
            }, 0),
            
        },
        'form_data.pay_method':function(newVal,oldVal){
             console.log(`myProperty changed from "${oldVal}" to "${newVal}"`);
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
                this.ref_no = "Ref No.";
            }
            if (newVal == "Bank Transfer") {
                this.ref_no = "Bank transfer code";
            }

        }
    },
    methods: {
        
       
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([
                this.customerChange(),
                this.fetchUnpaidInvoices(),
            ]);
            this.isLoading ? this.hideLoader() : "";
        },
        async fetchUnpaidInvoices() {
            const res = await this.getApi("data/invoices/fetchUnpaidInvoices", {
                params: {
                    customer_id: this.customer_id,
                    ...this.params
                },
            });

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
                    notes:obj.notes,
                    d_note:obj.d_note,
                    invoice_total: obj.invoice_total,
                    unpaid_amount: obj.unpaid_amount,
                    amount_paid: 0,
                    pending_amount: 0,
                };
            });
            return modif;
        },
        async customerChange() {
            const res = await this.callApi(
                "post",
                "data/cust_ledger/getAmountOwed",
                {
                    customer_id: this.customer_id,
                }
            );

            this.form_data.customer_id = this.customer_id;
            if (res.status == 200) {
                this.form_data.amount_pending = res.data;
            } else {
                this.swr("Error occured in the server");
            }
        },
        async getCustomersSelect() {
            const res = await this.getApi(
                "data/customers/getCustomerSelect",
                ""
            );

            this.select.customer_options = res.data;
        },
        async submitPayment() {
            this.showLoader();
            if(this.form_data.pay_method=="Cash" && this.form_data.ref_no==""){
                this.form_data.ref_no="Cash"
            }
            const res = await this.callApi("post", "data/payments/create", {
                invoice_data: this.invoice_data,
                ...this.form_data,
            });
            this.hideLoader();
            if (res.status === 200) {
                //	this.users.unshift(res.data)
                this.s(" record has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                 this.customer_id=null
                this.invoice_data=[]
                this.params.search=""
                this.form_data.date_paid=this.getCurrentDate()
               
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
.nopadding {
    padding: 0 !important;
    margin: 0 !important;
}
</style>
