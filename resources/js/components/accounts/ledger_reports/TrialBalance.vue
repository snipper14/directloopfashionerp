<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <label> From Datetime *</label>

                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.from"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column mr-2">
                                    <label> To Datetime *</label>
                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.to"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <form class="form-inline">
                                    <input
                                        class="form-control mr-sm-2"
                                        type="search"
                                        v-model="params.search"
                                        placeholder="Search Account"
                                        aria-label="Search"
                                    />
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-7">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>
                                                <center>
                                                    <h4>Trial Balance</h4>
                                                </center>
                                            </th>
                                        </tr>
                                    </thead>
                                    <thead>
                                        <tr>
                                            <th scope="col">Account</th>

                                            <th scope="col">Dr</th>
                                            <th scope="col">Cr</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in trial_balance_arr"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.account }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.dr_amount
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.cr_amount
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>

                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            total_debit_amount
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            total_credit_amount
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
import LedgerNav from "../general_ledger/LedgerNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";

export default {
    data() {
        return {
            select_debit_account_modal: false,
            select_credit_account_modal: false,
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                new_invoice: false,
                view_invoice: false,
            },

            isLoading: true,
            ledger_total: {},
            total_debit_amount: 0,
            total_credit_amount: 0,
            invoice_details: null,
            trial_balance_arr: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            expenses_data: [],
            search: "",
            code: null,
            params: {
                search: "",
                from: null,
                to: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
            form_data: {
                credit_account_type: "",
                debit_account_type: "",
                debit_account_id: null,
                credit_account_id: null,

                credit_account_name: "",
                debit_account_name: "",
            },
        };
    },
    components: {
        LedgerNav,
        Unauthorized,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq();
            }, 500),
        },
        trial_balance_arr: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_debit_amount = this.trial_balance_arr.reduce(
                    function (sum, val) {
                        return sum + val.dr_amount;
                    },
                    0
                );
                this.total_credit_amount = this.trial_balance_arr.reduce(
                    function (sum, val) {
                        return sum + val.cr_amount;
                    },
                    0
                );
            }, 500),
        },
    },
    methods: {
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/general_ledger/fetchLedgerGrouped",
                {
                    params: {
                        ...this.params,
                        ...this.form_data,
                    },
                }
            );
            this.hideLoader();
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    entry_code: data.entry_code,
                    account: data.account.account,
                    details: data.other_account_name,
                    debit_amount: data.debit_amount,
                    credit_amount: data.credit_amount,

                    entry_date: data.entry_date,
                    user: data.user.name,
                });
            });
            return data_array;
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetch(1),
                this.fetchExpenses(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchExpenses() {
            const res = await this.getApi("data/trial_balance/fetchExpenses", {
                params: {
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.expenses_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetch(page) {
            const res = await this.getApi("data/trial_balance/fetch", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.trial_balance_arr = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchLedgerTotal() {
            const res = await this.getApi(
                "data/general_ledger/fetchLedgerTotal",
                {
                    params: {
                        ...this.params,
                        ...this.form_data,
                    },
                }
            );

            if (res.status === 200) {
                this.ledger_total = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
