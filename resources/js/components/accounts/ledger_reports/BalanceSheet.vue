<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header-wrapper">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex flex-column">
                                    <label>From Datetime *</label>
                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.from"
                                    ></date-picker>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex flex-column mr-2">
                                    <label>To Datetime *</label>
                                    <date-picker
                                        valueType="format"
                                        width="300px"
                                        v-model="params.to"
                                    ></date-picker>
                                </div>
                            </div>
                            <div>
                                <v-btn
                                    color="primary"
                                    small
                                    @click="concurrentApiReq()"
                                    >Filter</v-btn
                                >
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="col-md-12" ref="contentToPrint">
                            <!-- Primary Balance Sheet Table -->
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>
                                                <center>
                                                    <h4>
                                                        Balance Sheet as of
                                                        {{ params.to }}
                                                    </h4>
                                                </center>
                                            </th>
                                        </tr>
                                    </thead>
                                    <thead>
                                        <tr>
                                            <th scope="col">
                                                <h3><b>Assets</b></h3>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="col">
                                                <b>Fixed Assets</b>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <tr
                                            v-for="data in fixed_assets_data"
                                            :key="data.id"
                                            v-if="data.amount != 0"
                                        >
                                            <td>{{ data.account }}</td>
                                            <td></td>
                                            <td>{{ cashFormatter(data.amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td scope="col">
                                                <b>Total Fixed Assets</b>
                                            </td>
                                            <td scope="col"></td>
                                            <td scope="col">
                                                <b>{{ cashFormatter(totals.total_fixed_asset || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Current Assets -->
                                        <tr>
                                            <th scope="col">
                                                <b>Current Assets</b>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <tr
                                            v-for="data in current_asset_data"
                                            :key="data.id"
                                            v-if="data.amount != 0"
                                        >
                                            <td>{{ data.account }}</td>
                                            <td></td>
                                            <td>{{ cashFormatter(data.amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Current Assets</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(totals.total_current_asset || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Other Current Assets -->
                                        <tr>
                                            <th scope="col">
                                                <b>Other Current Assets</b>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <tr
                                            v-for="data in other_current_asset_data"
                                            :key="data.id"
                                            v-if="data.amount != 0"
                                        >
                                            <td>{{ data.account }}</td>
                                            <td></td>
                                            <td>{{ cashFormatter(data.amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Other Current Assets</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(totals.total_other_current_asset || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Total Assets -->
                                        <tr>
                                            <td><b>Total Assets</b></td>
                                            <td></td>
                                            <td>
                                                <h3>
                                                    <b>{{ cashFormatter(totals.total_assets || 0) }}</b>
                                                </h3>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <thead>
                                        <tr>
                                            <th scope="col">
                                                <h3><b>Liabilities & Equity</b></h3>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <!-- Current Liabilities -->
                                        <tr>
                                            <th scope="col">
                                                <b>Current Liabilities</b>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <tr
                                            v-for="data in current_liabilities_data"
                                            :key="data.id"
                                            v-if="data.amount != 0"
                                        >
                                            <td>{{ data.account }}</td>
                                            <td></td>
                                            <td>{{ cashFormatter(data.amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Current Liability</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(totals.total_current_liability || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Long Term Liabilities -->
                                        <tr>
                                            <th scope="col">
                                                <b>Long Term Liabilities</b>
                                            </th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <tr
                                            v-for="data in longterm_liability_data"
                                            :key="data.id"
                                            v-if="data.amount != 0"
                                        >
                                            <td>{{ data.account }}</td>
                                            <td></td>
                                            <td>{{ cashFormatter(data.amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Long Term Liability</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(totals.total_longterm_liability || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Total Liabilities -->
                                        <tr>
                                            <td><b>Total Liabilities</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(parseFloat(totals.total_longterm_liability) + parseFloat(totals.total_current_liability)) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Equity -->
                                        <tr>
                                            <th scope="col"><b>Equity</b></th>
                                            <th scope="col"></th>
                                            <th scope="col"></th>
                                        </tr>
                                        <tr
                                            v-for="data in equity_data"
                                            :key="data.id"
                                            v-if="data.amount != 0"
                                        >
                                            <td>{{ data.account }}</td>
                                            <td></td>
                                            <td>{{ cashFormatter(data.amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Equity</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(totals.total_equity || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Earnings -->
                                        <tr>
                                            <td><b>Earnings</b></td>
                                            <td></td>
                                            <td>
                                                <b>{{ cashFormatter(parseFloat(totals.profit_loss_amount)) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Total Liabilities & Equity -->
                                        <tr>
                                            <td><b>Total Liabilities & Equity</b></td>
                                            <td></td>
                                            <td>
                                                <h3>
                                                    <b>{{ cashFormatter(totals.total_liabilities_and_equity || 0) }}</b>
                                                </h3>
                                            </td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- Detailed Comparison Table -->
                            <div class="table-responsive mt-4" v-if="comparison.length > 0">
                                <table class="table table-sm table-striped table-bordered custom-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <center>
                                                    <h4>Last 3 Years Comparison</h4>
                                                </center>
                                            </th>
                                            <th v-for="year in sortedYears" :key="year" class="text-center">
                                                {{ year }} ({{ getYearDateRange(year) }})
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Fixed Assets -->
                                        <tr>
                                            <th><b>Fixed Assets</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <tr v-for="account in getUniqueAccounts('fixed_assets')" :key="account.id">
                                            <td>{{ account.account }}</td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                {{ cashFormatter(getAccountAmount(year, 'fixed_assets', account.id, account.account) || 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Fixed Assets</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_fixed_asset') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Current Assets -->
                                        <tr>
                                            <th><b>Current Assets</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <tr v-for="account in getUniqueAccounts('current_assets')" :key="account.id">
                                            <td>{{ account.account }}</td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                {{ cashFormatter(getAccountAmount(year, 'current_assets', account.id, account.account) || 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Current Assets</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_current_asset') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Other Current Assets -->
                                        <tr>
                                            <th><b>Other Current Assets</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <tr v-for="account in getUniqueAccounts('other_current_assets')" :key="account.id">
                                            <td>{{ account.account }}</td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                {{ cashFormatter(getAccountAmount(year, 'other_current_assets', account.id, account.account) || 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Other Current Assets</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_other_current_asset') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Total Assets -->
                                        <tr>
                                            <td><b>Total Assets</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_assets') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Liabilities & Equity -->
                                        <tr>
                                            <th><b>Liabilities & Equity</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <!-- Current Liabilities -->
                                        <tr>
                                            <th><b>Current Liabilities</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <tr v-for="account in getUniqueAccounts('current_liabilities')" :key="account.id">
                                            <td>{{ account.account }}</td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                {{ cashFormatter(getAccountAmount(year, 'current_liabilities', account.id, account.account) || 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Current Liabilities</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_current_liability') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Long Term Liabilities -->
                                        <tr>
                                            <th><b>Long Term Liabilities</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <tr v-for="account in getUniqueAccounts('long_term_liabilities')" :key="account.id">
                                            <td>{{ account.account }}</td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                {{ cashFormatter(getAccountAmount(year, 'long_term_liabilities', account.id, account.account) || 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Long Term Liabilities</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_longterm_liability') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Total Liabilities -->
                                        <tr>
                                            <td><b>Total Liabilities</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter((getTotalForYear(year, 'total_current_liability') || 0) + (getTotalForYear(year, 'total_longterm_liability') || 0)) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Equity -->
                                        <tr>
                                            <th><b>Equity</b></th>
                                            <th v-for="year in sortedYears" :key="year"></th>
                                        </tr>
                                        <tr v-for="account in getUniqueAccounts('equity')" :key="account.id">
                                            <td>{{ account.account }}</td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                {{ cashFormatter(getAccountAmount(year, 'equity', account.id, account.account) || 0) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>Total Equity</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_equity') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Earnings -->
                                        <tr>
                                            <td><b>Earnings</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'profit_loss_amount') || 0) }}</b>
                                            </td>
                                        </tr>
                                        <!-- Total Liabilities & Equity -->
                                        <tr>
                                            <td><b>Total Liabilities & Equity</b></td>
                                            <td v-for="year in sortedYears" :key="year" class="text-right">
                                                <b>{{ cashFormatter(getTotalForYear(year, 'total_liabilities_and_equity') || 0) }}</b>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">
                            <v-btn
                                color="primary"
                                medium
                                @click="printBalanceSheet"
                                >Print Balance Sheet</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import LedgerNav from "../general_ledger/LedgerNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";

export default {
    data() {
        return {
            current_asset_data: [],
            other_current_asset_data: [],
            fixed_assets_data: [],
            current_liabilities_data: [],
            longterm_liability_data: [],
            equity_data: [],
            comparison: [],
            totals: {
                total_fixed_asset: 0,
                total_current_asset: 0,
                total_other_current_asset: 0,
                total_current_liability: 0,
                total_longterm_liability: 0,
                total_equity: 0,
                profit_loss_amount: 0,
                total_assets: 0,
                total_liabilities_and_equity: 0,
            },
            select_debit_account_modal: false,
            select_credit_account_modal: false,
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                new_invoice: false,
                view_invoice: false,
            },
            total_asset: 0,
            total_liabilities: 0,
            isLoading: true,
            total_debit_amount: 0,
            total_credit_amount: 0,
            invoice_details: null,
            trial_balance_arr: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            search: "",
            code: null,
            params: {
                search: "",
                from: '1970-01-01 00:00:00',
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
        this.params.to = this.getDateTime();
        this.concurrentApiReq();
    },
    computed: {
        sortedYears() {
            return this.comparison.map(c => c.year).sort((a, b) => b - a); // Descending order (2025, 2024, 2023)
        },
        sections() {
            return [
                { key: 'fixed_assets', label: 'Fixed Assets' },
                { key: 'current_assets', label: 'Current Assets' },
                { key: 'other_current_assets', label: 'Other Current Assets' },
                { key: 'current_liabilities', label: 'Current Liabilities' },
                { key: 'long_term_liabilities', label: 'Long Term Liabilities' },
                { key: 'equity', label: 'Equity' },
            ];
        },
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.params.to = this.formatDateTime(this.params.to);
                this.params.from = this.formatDateTime(this.params.from);
            }, 500),
        },
    },
    methods: {
        getUniqueAccounts(sectionKey) {
            const accounts = new Map();
            this.comparison.forEach(comp => {
                (comp.data.sections[sectionKey] || []).forEach(account => {
                    //accounts.set(account.id + '-' + account.account, { id: account.id, account: account.account });
                    accounts.set( account.account, { id: account.id, account: account.account });
                });
            });
            return Array.from(accounts.values());
        },
        getAccountAmount(year, sectionKey, accountId, accountName) {
            const comp = this.comparison.find(c => c.year === year);
            if (!comp) return 0;
            const account = (comp.data.sections[sectionKey] || []).find(a => a.id === accountId && a.account === accountName);
            return account ? account.amount : 0;
        },
        getTotalForYear(year, totalKey) {
            const comp = this.comparison.find(c => c.year === year);
            return comp ? comp.data.totals[totalKey] : 0;
        },
        getYearDateRange(year) {
            const comp = this.comparison.find(c => c.year === year);
            return comp ? `${comp.data.from} to ${comp.data.to}` : '';
        },
        async concurrentApiReq() {
            this.isLoading && this.showLoader();

            this.params.to = this.formatDateTime(this.params.to);
            this.params.from = this.formatDateTime(this.params.from);

            const res = await this.getApi('data/balance_sheet/summary', {
                params: { ...this.params }
            });

            if (res.status === 200) {
                const s = res.data.sections || {};
                const t = res.data.totals || {};
                this.fixed_assets_data = s.fixed_assets || [];
                this.current_asset_data = s.current_assets || [];
                this.other_current_asset_data = s.other_current_assets || [];
                this.current_liabilities_data = s.current_liabilities || [];
                this.longterm_liability_data = s.long_term_liabilities || [];
                this.equity_data = s.equity || [];
                this.totals = res.data.totals || this.totals;
                this.comparison = res.data.comparison || [];
                this.total_fixed_asset = Number(t.total_fixed_asset || 0);
                this.total_current_asset = Number(t.total_current_asset || 0);
                this.total_other_current_asset = Number(t.total_other_current_asset || 0);
            } else {
                this.swr("Server error try again later");
            }

            this.isLoading && this.hideLoader();
        },
        printBalanceSheet() {
            const contentToPrint = this.$refs.contentToPrint;
            const printWindow = window.open("", "", "");
            printWindow.document.open();
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print Balance Sheet</title>
                        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
                        <style>
                            .top-table table tr, .top-table table tr td {
                                border: 1px solid;
                            }
                            .table-title {
                                padding-left: 10px;
                                padding-right: 10px;
                            }
                            .table-responsive {
                                margin-bottom: 20px;
                            }
                            h3, h4 {
                                margin: 10px 0;
                            }
                            .custom-table th, .custom-table td {
                                padding: 8px;
                            }
                            .text-right {
                                text-align: right;
                            }
                            .text-center {
                                text-align: center;
                            }
                            @media print {
                                .table-responsive {
                                    page-break-inside: avoid;
                                }
                                .custom-table {
                                    width: 100%;
                                    font-size: 12px;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        ${contentToPrint.innerHTML}
                    </body>
                </html>`);
            printWindow.document.close();
            setTimeout(() => {
                printWindow.print();
            }, 1000);
        },
    },
};
</script>

<style scoped>
.mt-4 {
    margin-top: 1.5rem;
}
.custom-table th,
.custom-table td {
    vertical-align: middle;
}
.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
</style>