<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
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
                <div class="card-header"></div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">Account Details</legend>
                        <form>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label for=""
                                        >Main Sub Account Filter</label
                                    >
                                    <treeselect
                                        :load-options="fetchLedgerSubAccounts"
                                        :auto-load-root-options="false"
                                        :options="main_subaccounts_select_data"
                                        v-model="
                                            form_data.ledger_sub_account_id
                                        "
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Main Sub Account"
                                        @input="onSubAccountSelected"
                                    />
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Account Name(Unique) *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.account"
                                    />
                                </div>
                                <div class="form-group col-md-12">
                                    <label for=""> Select Category</label>
                                    <select
                                        disabled
                                        class="form-control"
                                        v-model="form_data.category"
                                    >
                                        <option value="asset">Assets</option>
                                        <option value="liability">
                                            Liability
                                        </option>
                                        <option value="equity">Equity</option>
                                        
                                        <option value="income">Income</option>
                                          <option value="expense">
                                            Expenses COGs
                                        </option>
                                        <option value="accrued_expenses">
                                            Expenses
                                        </option>
                                        <option value="other_expenses">
                                            Other Expenses
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for=""> Account Type*</label>
                                    <Select
                                        v-model="form_data.account_type"
                                        disabled
                                    >
                                        <Option
                                            v-for="(
                                                data, i
                                            ) in account_type_select"
                                            :key="i"
                                            :value="data.value"
                                            >{{ data.label }}</Option
                                        >
                                    </Select>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>Account Description </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.description"
                                    />
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary"
                                @click="submitAccount()"
                            >
                                Save Account
                            </button>
                        </form>
                    </fieldset>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    data() {
        return {
            accounts_select_data: null,

            account_type_select: [
                { label: "Current Asset", value: "Current Asset" },
                { label: "Other Current Asset", value: "Other Current Asset" },
                { label: "Inventory-Asset", value: "Inventory" },
                { label: "Fixed Asset", value: "Fixed Asset" },
                { label: "Current Liability", value: "Current Liability" },
                { label: "Long Term Liability", value: "Long Term Liability" },
                { label: "Equity", value: "Equity" },
                { label: "Input VAT-Asset ( Purchases)", value: "input VAT" },
                { label: "Output VAT-Liability ( Sales)", value: "output VAT" },
                {
                    label: "Late Delivery Charges",
                    value: "Late Delivery Charges",
                },
                { label: "Withholding Tax-WHT", value: "WHT" },
                { label: "Expenses", value: "expenses" },
                { label: "Income", value: "income" },
            ],
            form_data: {
                account: "",
                description: "",
                ledger_sub_account_id: null,
                account_type: null,
                category: null,
            },
        };
    },
    components: {
        Treeselect,
    },
    mounted() {
        this.fetchAccounts();
    },
    methods: {
        onSubAccountSelected(selectedId) {
            const selected = this.main_subaccounts_select_data.find(
                (opt) => opt.id === selectedId
            );

            if (selected && selected.raw) {
                this.form_data.account_type = selected.raw.account_type;
                this.form_data.category = selected.raw.category;
            }
        },

        async fetchAccounts() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/chart_accounts/fetch", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.accounts_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },

        async submitAccount() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/ledger_account/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
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
.border {
    padding: 1rem !important;
}
</style>
