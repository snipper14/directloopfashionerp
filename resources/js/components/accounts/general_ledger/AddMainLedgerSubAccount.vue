<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Add Ledger Account</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for=""> Select Category*</label>
                                <select
                                    class="form-control"
                                    v-model="form_data.category"
                                >
                                    <option value="asset">Assets(100)</option>
                                    <option value="liability">
                                        Liability(200)
                                    </option>
                                    <option value="equity">Equity(300)</option>
                                    <option value="income">Income(400)</option>
                                    <option value="expense">
                                        Expenses COGs(500)
                                    </option>
                                    <option value="accrued_expenses">
                                        Expenses (600)
                                    </option>
                                    <option value="other_expenses">
                                        Other Expenses(700)
                                    </option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for=""> Account Type*</label>
                                <Select v-model="form_data.account_type">
                                    <Option
                                        v-for="(data, i) in account_type_select"
                                        :key="i"
                                        :value="data.value"
                                        >{{ data.label }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Account Name(Unique)* </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.name"
                                />
                            </div>
                            <div class="form-group col-md-6">
                                <label>Account Description (Optional)</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    autocomplete="new-user-street-address"
                                    v-model="form_data.description"
                                />
                            </div>
                            <v-btn
                                :loading="btn_loading"
                                color="primary"
                                @click="saveSubAccount"
                                >Save</v-btn
                            >
                        </div>
                    </div>
                </div>

                <!-- Ledger Sub Accounts Table -->
                <div class="card mt-4">
                    <div class="card-header">Ledger Sub Accounts</div>
                    <div class="card-body">
                        <v-text-field
                            v-model="search"
                            label="Search"
                            class="mb-3"
                            @input="fetchLedgerSubAccounts"
                        ></v-text-field>
                        <v-simple-table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Account no</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Account Type</th>
                                    <th>Category</th>
                                    <th>Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(
                                        item, idx
                                    ) in ledgerSubAccounts.data"
                                    :key="item.id"
                                >
                                    <td>{{ idx + 1 }}</td>
                                    <td>{{ formatLedgerNo(item.account_no) }}</td>
                                    <td>{{ item.name }}</td>
                                    <td>{{ item.description }}</td>
                                    <td>{{ item.account_type }}</td>
                                    <td>{{ item.category }}</td>
                                    <td>
                                        {{ item.user ? item.user.name : "" }}
                                    </td>
                                    <td>
                                        <v-btn
                                            v-if="isDeletePermitted"
                                            title="DELETE"
                                            x-small
                                            color="red"
                                            @click="deleteSubAccount(item.id)"
                                            >Delete</v-btn
                                        >
                                        <v-btn
                                            x-small
                                            v-if="isUpdatePermitted"
                                            title="EDIT"
                                            color="blue"
                                            @click="editSubAccount(item)"
                                            >Edit</v-btn
                                        >
                                    </td>
                                </tr>
                                <tr v-if="ledgerSubAccounts.length === 0">
                                    <td colspan="6" class="text-center">
                                        No records found.
                                    </td>
                                </tr>
                            </tbody>
                            <pagination
                                v-if="ledgerSubAccounts !== null"
                                v-bind:results="ledgerSubAccounts"
                                v-on:get-page="fetchLedgerSubAccounts"
                            ></pagination>
                            Items Count {{ ledgerSubAccounts.total }}
                        </v-simple-table>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
export default {
    components: { Pagination },
    data() {
        return {
            btn_loading: false,
            form_data: {
                id: null,
                name: "",
                category: "",
                account_type: "",
                description: "",
            },
            ledgerSubAccounts: [],
            search: "",
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
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchLedgerSubAccounts(1);
    },
    methods: {
        async deleteSubAccount(id) {
            if (!confirm("Are you sure you want to delete this account?"))
                return;

            const res = await this.callApi(
                "DELETE",
                `data/main_ledger_account/destroy/${id}`
            );
            if (res.status === 200) {
                this.successNotif("Account deleted successfully.");
                this.fetchLedgerSubAccounts(1);
            } else {
                this.form_error(res.data);
            }
        },
        editSubAccount(item) {
            this.form_data = {
                id: item.id,
                name: item.name,
                category: item.category,
                account_type: item.account_type,
                description: item.description,
            };
        },
        async fetchLedgerSubAccounts(page) {
            const res = await this.getApi("data/main_ledger_account/fetch", {
                params: { search: this.search, page: page },
            });
            if (res.status === 200) {
                this.ledgerSubAccounts = res.data;
            } else {
                this.form_error(res.data);
            }
        },
        async saveSubAccount() {
            this.btn_loading = true;

            let url = this.form_data.id
                ? `data/main_ledger_account/update/${this.form_data.id}`
                : "data/main_ledger_account/create";

            const method = this.form_data.id ? "PUT" : "POST";

            const res = await this.callApi(method, url, this.form_data);
            this.btn_loading = false;

            if (res.status === 201 || res.status === 200) {
                const msg = this.form_data.id
                    ? "Account Updated"
                    : "Account Created";
                this.successNotif(msg + " Successfully");
                this.resetForm();
                this.fetchLedgerSubAccounts(1);
            } else {
                this.form_error(res);
            }
        },
        resetForm() {
            this.form_data = {
                id: null,
                name: "",
                category: "",
                account_type: "",
                description: "",
            };
        },
    },
};
</script>
