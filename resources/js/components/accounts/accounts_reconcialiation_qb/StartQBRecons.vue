<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Reconciling the 
                        <span v-if="statement_data.length > 0">{{statement_data[0].account?statement_data[0].account.account:""}}</span>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h4>
                                    Ending Balances :
                                    {{
                                        cashFormatter(
                                            recons_form_data.closing_balance
                                        )
                                    }}
                                </h4>
                            </div>
                            <div class="col-md-4">
                                <h4>
                                    Cleared Balances :
                                    {{
                                        cashFormatter(
                                            recons_form_data.cleared_balance
                                        )
                                    }}
                                </h4>
                            </div>
                            <div class="col-md-4">
                                <h4>
                                    Pending Clearance :
                                    {{
                                        cashFormatter(
                                            recons_form_data.pending_clearance
                                        )
                                    }}
                                </h4>
                            </div>
                            <div class="col-md-5">
                                <h5>
                                    Reconciliation Date:
                                    {{ recons_form_data.entry_date }}
                                </h5>
                            </div>
                            <div class="col-md-3">
                                <h5>
                                    Ref:
                                    {{ recons_form_data.ref }}
                                </h5>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <input
                                                type="checkbox"
                                                v-model="selectAll"
                                            />
                                        </th>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Ref</th>
                                        <th>Description</th>
                                        <th>Details</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(value, i) in statement_data"
                                        :key="i"
                                    >
                                        <td>
                                            <input
                                                type="checkbox"
                                                v-model="selectedItems"
                                                :value="value.id"
                                            />
                                        </td>
                                        <td>{{ value.id }}</td>
                                        <td>{{ value.entry_date }}</td>
                                        <td>{{value.ref}}</td>
                                        <td>{{ value.other_account_name }}</td>
                                        <td>{{ value.details }}</td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    value.debit_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    value.credit_amount
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <br />
                            <Button
                                type="primary"
                                :loading="isSending"
                                :disabled="isSending"
                                v-if="isWritePermitted"
                                @click="completeRecons"
                                >Complete Reconciliation</Button
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["form_data"],
    data() {
        return {
            selectedItems: [],
            selectAll: false,
            isSending: false,
            statement_data: [],
            server_statement_array: [],
            isLoading: true,
            watchSelectedItems: true,
            recons_form_data: {
                opening_balance: this.form_data.opening_balance,
                closing_balance: this.form_data.closing_balance,
                difference: 0,
                account_id: this.form_data.account_id,
                cleared_balance: 0,
                pending_clearance: 0,
                entry_date: this.form_data.entry_date,
                ref: this.form_data.ref,
            },
        };
    },
    created() {
        this.recons_form_data.difference =
            parseFloat(this.recons_form_data.closing_balance) -
            parseFloat(this.recons_form_data.opening_balance);

        this.concurrentApiReq();
    },
    watch: {
        selectAll(newVal) {
            this.selectedItems = newVal
                ? this.statement_data.map((item) => item.id)
                : [];
        },
        selectedItems(newVal) {
            if (!this.watchSelectedItems) return;
            this.selectAll = newVal.length === this.statement_data.length;

            this.create();
        },
        server_statement_array: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.recons_form_data.cleared_balance =
                    this.server_statement_array.reduce(function (sum, val) {
                        return sum + (val.dr_amount - val.cr_amount);
                    }, 0);
                this.recons_form_data.pending_clearance =
                    parseFloat(this.recons_form_data.difference) -
                    parseFloat(this.recons_form_data.cleared_balance);
                console.log(
                    "reached" + this.recons_form_data.pending_clearance
                );
            }, 500),
        },
    },
    methods: {
        async completeRecons() {
            if(this.server_statement_array.length <1){
                this.errorNotif("No accounts added")
                return
            }
            if(this.recons_form_data.pending_clearance!=0){
                this.errorNotif("Pending balances should be zero")
                return
            }
            this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/qb_bank_reconciliation/completeRecons",
                {
                    ...this.recons_form_data,
                }
            );
            this.isSending = false;
            if (res.status == 200) {
                this.successNotif("Completed successfully");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
        async create() {
            const res = await this.callApi(
                "POST",
                "data/qb_bank_reconciliation/create",
                {
                    ...this.recons_form_data,
                    selectedItems: this.selectedItems,
                }
            );

            if (res.status == 200) {
                this.s("added");
                this.server_statement_array = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchUnreconciledStatements() {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchUnreconciledStatements",
                {
                    params: {
                        ...this.recons_form_data,
                    },
                }
            );

            if (res.status === 200) {
                this.statement_data = res.data;

                this.fetchPendingStatements();
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchPendingStatements() {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchPendingStatements",
                {
                    params: {
                        ...this.recons_form_data,
                    },
                }
            );

            if (res.status === 200) {
                this.server_statement_array = res.data;

                this.watchSelectedItems = false;
                this.selectedItems = this.server_statement_array.map(
                    (item) => item.general_ledger_id
                );
                this.$nextTick(() => {
                    this.watchSelectedItems = true; // Re-enable watcher after updating
                });
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([this.fetchUnreconciledStatements()]);

            this.isLoading ? this.hideLoader() : "";
        },
    },
};
</script>

<style scoped>
.modern-table {
    color: #333;
    background-color: #f8f9fa;
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead {
    background-color: #1434a4;
    color: white;
}

.modern-table th,
.modern-table td {
    padding: 15px;
    text-align: left;
    border: 1px solid #dee2e6;
}

.modern-table th:first-child,
.modern-table td:first-child {
    width: 40px;
    text-align: center;
}

.modern-table tbody tr:nth-child(even) {
    background-color: #e9ecef;
}

.modern-table input[type="checkbox"] {
    accent-color: #007bff;
    cursor: pointer;
    transform: scale(1.2);
}

/* Hover effect */
.modern-table tbody tr:hover {
    background-color: #d6d8db;
}
</style>
