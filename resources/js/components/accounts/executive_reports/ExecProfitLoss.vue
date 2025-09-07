<template>
    <div class="container">
        <div v-if="isReadPermitted">
            <div class="row justify-content-center">
                <div class="col-md-3">
                    <executive-nav />
                </div>
                <div class="col-md-9">
                    <div class="card">
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
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th>
                                                    <center>
                                                        <h4>
                                                            PROFIT $ LOSS REPORT
                                                        </h4>
                                                    </center>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered custom-table"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        <h3><b>Income</b></h3>
                                                    </th>

                                                    <th scope="col"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Sales</td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                total_sales
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Cost Of Goods Sold</td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                total_cost_of_goods_sold
                                                            )
                                                        }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <b>Gross Profit</b>
                                                    </td>

                                                    <td>
                                                        <b>
                                                            {{
                                                                cashFormatter(
                                                                    total_sales -
                                                                        total_cost_of_goods_sold
                                                                )
                                                            }}</b
                                                        >
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered custom-table"
                                        >
                                            <tbody>
                                                <tr>
                                                    <th scope="col">
                                                        <h3>
                                                            <b>Expenses</b>
                                                        </h3>
                                                    </th>

                                                    <th scope="col"></th>
                                                </tr>
                                                <tr
                                                    v-for="(
                                                        data, i
                                                    ) in expenses_data"
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
                                                </tr>

                                                <tr>
                                                    <td>
                                                        <b>Total Expenses</b>
                                                    </td>

                                                    <td>
                                                        <b>{{
                                                            cashFormatter(
                                                                total_liabilities
                                                            )
                                                        }}</b>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <th>PROFIT/LOSS</th>
                                            <th>
                                                {{
                                                    cashFormatter(
                                                        total_sales -
                                                            total_cost_of_goods_sold -
                                                            total_liabilities
                                                    )
                                                }}
                                            </th>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>

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

import ExecutiveNav from "./ExecutiveNav.vue";
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
            total_sales: 0,
            total_cost_of_goods_sold: 0,
            expenses_data: {},
            assets_data: {},
            total_asset: 0,
            total_liabilities: 0,
            isLoading: true,
            ledger_total: {},
            total_debit_amount: 0,
            total_credit_amount: 0,
            invoice_details: null,
            trial_balance_arr: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            tax_value: {},
            total_payroll: 0,
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
        ExecutiveNav,
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
        assets_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_asset = this.assets_data.reduce(function (sum, val) {
                    return sum + val.dr_amount;
                }, 0);
                this.total_liabilities +=
                    this.tax_value.cr_amount + this.total_payroll;
            }, 500),
        },
        expenses_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_liabilities = this.expenses_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.dr_amount;
                },
                0);
            }, 500),
        },
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchLiabilities(),
                this.totalcostOfGoodSold(),
                this.totalSales(),
                // this.taxAmount(),
                // this.totalPayroll()
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchLiabilities(page) {
            const res = await this.getApi("data/profit_loss/fetchLiabilities", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.expenses_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async taxAmount(page) {
            const res = await this.getApi("data/profit_loss/taxAmount", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.tax_value = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async totalPayroll(page) {
            const res = await this.getApi("data/profit_loss/totalPayroll", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.total_payroll = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },

        async totalSales(page) {
            const res = await this.getApi("data/profit_loss/totalSales", {
                params: {
                    page,

                    ...this.params,
                },
            });

            this.total_sales = res.data;
        },
        async totalcostOfGoodSold(page) {
            const res = await this.getApi(
                "data/profit_loss/totalcostOfGoodSold",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );

            this.total_cost_of_goods_sold = res.data;
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
