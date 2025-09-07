<template>
    <div class="row justify-content-center">
        <v-app>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">
                        <h4>
                            Add payments for {{ line_data.expense_type.name }}
                        </h4>
                    </div>

                    <div class="card-body">
                        <form>
                            <div class="row">
                                <!-- <div class="form-group col-md-6 nopadding">
                                    <label for=""
                                        >Search Supplier Account</label
                                    >
                                    <treeselect
                                        :load-options="fetchSupplier"
                                        :options="supplier_accounts_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.supplier_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select  Supplier "
                                    />
                                </div> -->
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

                                <div class="form-group col-md-2 nopadding">
                                    <label for=""> Accounts</label>
                                    <treeselect
                                        width="300px"
                                        :load-options="fetchAccounts"
                                        :options="accounts_select_data"
                                        :auto-load-root-options="false"
                                        v-model="account_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select  Debit Account "
                                    />
                                </div>
                                <div class="form-group col-md-3 nopadding">
                                    <label>{{ ref_no }}*</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.ref_no"
                                        :placeholder="ref_no"
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
                                    <label for="details"
                                        >Optional Details
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.payment_details"
                                        placeholder="Optional Details"
                                    />
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
                </div></div
        ></v-app>

        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import Unauthorized from "../../utilities/Unauthorized.vue";

export default {
    props: ["line_data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            expense_id: null,
            ref_no: "ref no",
            supplier_accounts_select_data: null,
            accounts_select_data: null,
            payment_method_select: [
                { name: "Cheque" },
                { name: "Mobile Money" },
                { name: "Cash" },
                { name: "Bank Transfer" },
            ],
            isLoading: true,
            invoice_data: [],
            account_id: null,
            form_data: {
                supplier_id: null,
                account_id: null,
                expense_amount: 0,
                amount_paid: 0,
                ref_no: "",
                date_paid: null,
                payment_details: "",
                expense_id: null,
                amount_pending: 0,
                pay_method: "",
            },
        };
    },
    components: {
        Treeselect,
        Unauthorized,
    },
    created() {
        this.getAmountOwed();
        this.form_data.expense_amount = this.line_data.amount;
        this.form_data.expense_id = this.line_data.id;
        this.form_data.supplier_id = this.line_data.supplier_id;
        this.form_data.date_paid = this.getCurrentDate();
    },
    watch: {
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
            console.log(`myProperty changed from "${oldVal}" to "${newVal}"`);
            if (newVal == "Cheque") {
                this.ref_no = "Cheque No.";
            }
            if (newVal == "Mobile Money") {
                this.ref_no = "Mobile Money Confirmation Code";
            }
            if (newVal == "Cash") {
                this.ref_no = "Cash";
            }
            if (newVal == "Bank Transfer") {
                this.ref_no = "Bank transfer code";
            }
        },
    },
    methods: {
        async getAmountOwed() {
            const res = await this.callApi(
                "post",
                "data/expense_payment/getAmountOwed",
                {
                    expense_id: this.line_data.id,
                }
            );

            this.form_data.customer_id = this.customer_id;
            if (res.status == 200) {
                this.form_data.amount_pending = res.data;
            } else {
                this.swr("Error occured in the server");
            }
        },
        async fetchSupplier() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/suppliers/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.supplier_accounts_select_data = res.data.results.map(
                    (obj) => {
                        return {
                            id: obj.id,
                            label: obj.company_name,
                        };
                    }
                );
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchAccounts() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/branch_accounts/fetchBranchAccounts", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.accounts_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.account,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([
                this.customerChange(),
                this.fetchUnpaidInvoices(),
            ]);
            this.isLoading ? this.hideLoader() : "";
        },

        async submitPayment() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/expense_payment/create",
                {
                    ...this.form_data,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                //	this.users.unshift(res.data)
                this.s(" record has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
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
