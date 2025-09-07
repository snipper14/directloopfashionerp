<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2"><cashier-z-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4><b> Opening/Closing Balance Reports</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="">From</label>
                                <date-picker v-model="from" type="datetime" />
                            </div>
                            <div class="col-3">
                                <label for="">To</label>
                                <date-picker v-model="to" type="datetime" />
                            </div>
                            <div class="col-2">
                                <button
                                    class="btn btn-primary btn-sm"
                                    @click="filterByDate()"
                                >
                                    filter
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive custom-table" style="overflow-x: auto; white-space: nowrap;">
                            <table
                                class="table table-sm table-striped table-bordered custom-table modern-table"
                            >
                                <thead>
                                    <tr>
                                        <th></th>

                                        <th scope="col">cashier</th>
                                        <th scope="col">Opening Date</th>
                                        <th>Closing Date</th>

                                        <th scope="col">
                                            opening mpesa amount
                                        </th>
                                        <th>opening cash amount</th>
                                        <th>cash payments</th>
                                        <th>mpesa payments</th>
                                        <th>card payments</th>
                                        <th>loyalty payments</th>
                                        <th>credit payments</th>
                                        <th>return cash sales</th>
                                        <th>return mpesa sales</th>

                                        <th>cash drops</th>
                                        <th>mpesa drops</th>
                                        <th>expected cash</th>
                                        <th>expected mpesa</th>
                                        <th>expected card</th>
                                        <th>closing cash</th>
                                        <th>closing mpesa</th>
                                        <th>closing card</th>
                                        <th>.cash shortover</th>
                                        <th>mpesa shortover</th>
                                        <th>card shortover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in balance_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <v-btn
                                                title="DOWNLOAD"
                                                v-if="isDownloadPermitted"
                                                x-small
                                                color="success"
                                                @click="
                                                    reprintClosingBalance(data)
                                                "
                                                >Reprint</v-btn
                                            >
                                            <v-btn
                                                x-small
                                                color="info"
                                                @click="viewDetails(data)"
                                            >
                                                Details
                                            </v-btn>
                                            <v-btn
                                                x-small
                                                color="primary"
                                                @click="viewItemized(data)"
                                            >
                                                Itemized
                                            </v-btn>
                                        </td>

                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.opened_time }}</td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.opening_mpesa_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.opening_cash_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.cash_payments
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.mpesa_payments
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.card_payments
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.loyalty_payments
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.credit_payments
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.return_cash_sales
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.return_mpesa_sales
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.cash_drops) }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(data.mpesa_drops)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.expected_cash
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.expected_mpesa
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.expected_card
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.closing_cash)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.closing_mpesa
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.closing_card)
                                            }}
                                        </td>
                                        <td
                                            :class="{
                                                'negative-value':
                                                    data.cash_shortover < 0,
                                            }"
                                        >
                                            {{
                                                cashFormatter(
                                                    data.cash_shortover
                                                )
                                            }}
                                        </td>
                                        <td
                                            :class="{
                                                'negative-value':
                                                    data.mpesa_shortover < 0,
                                            }"
                                        >
                                            {{
                                                cashFormatter(
                                                    data.mpesa_shortover
                                                )
                                            }}
                                        </td>
                                        <td
                                            :class="{
                                                'negative-value':
                                                    data.card_shortover < 0,
                                            }"
                                        >
                                            {{
                                                cashFormatter(
                                                    data.card_shortover
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <pagination
                            v-if="balance_data !== null"
                            v-bind:results="balance_data"
                            v-on:get-page="fetchData"
                        ></pagination>
                        Items Count {{ balance_data.total }}
                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <CashierBalanceDetails
            v-if="active.cashier_balance && details_data"
            v-on:dashboard-active="setActiveRefresh"
            :details_data="details_data"
        />
        <div class="row">
            <print-balance-report-consolidated
                v-if="show_print"
                ref="PrintClosingBalanceReceipt"
                :reprint_data="reprint_data"
            />
        </div>
        <Modal v-model="itemized_modal" width="1200px">
            <modal-cashier-itemized-report
                v-if="itemized_modal"
                :details_data="details_data"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import CashierBalanceDetails from "./CashierBalanceDetails.vue";

import CashierZNav from "./CashierZNav.vue";
import ModalCashierItemizedReport from "./ModalCashierItemizedReport.vue";
import PrintBalanceReportConsolidated from "./PrintBalanceReportConsolidated.vue";

export default {
    data() {
        return {
            show_print: false,
            search: "",
            active: {
                dashboard: true,
                previous_balance: false,
                cashier_balance: false,
            },
            from: null,
            to: null,
            details_data: null,
            opening_time: null,
            closing_time: null,
            total_opening_balance: 0,
            total_closing_balance: 0,

            isLoading: true,
            balance_data: [],
            reprint_data: {},
            itemized_modal: false,
        };
    },
    components: {
        CashierBalanceDetails,

        CashierZNav,

        Pagination,
        Unauthorized,
        PrintBalanceReportConsolidated,
        ModalCashierItemizedReport,
    },

    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
    },

    methods: {
        dismissReport() {
            this.setActiveComponent("dashboard");
        },
        showPreviousReport() {
            this.setActiveComponent("previous_balance");
        },
        async reprintClosingBalance(data) {
            this.reprint_data = data;
            this.show_print = true;
            setTimeout(() => {
                this.$refs.PrintClosingBalanceReceipt.printReceipt();
                this.show_print = false;
            }, 1000);
        },
        getDeficit(data) {
            return (
                parseFloat(data.cashier_counted_cash) -
                (parseFloat(data.system_total_cash) +
                    parseFloat(data.cashier_opening_cash))
            );
        },
        getMpesaDeficit(data) {
            return (
                parseFloat(data.closing_mpesa_balance) -
                parseFloat(data.opening_mpesa_balance) -
                parseFloat(data.expected_mpesa_amount)
            );
        },
        viewDetails(data) {
            this.details_data = data;
            this.setActiveComponent("cashier_balance");
        },
        viewItemized(data) {
            this.details_data = data;
            this.itemized_modal = true;
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");

            this.details_data = null;
        },
        async fetchExcel() {
            this.closing_time = this.formatDateTime(this.to);
            this.opening_time = this.formatDateTime(this.from);
            const res = await this.getApi(
                "data/register/fetch",

                {
                    params: {
                        search: this.search,
                        closing_time: this.closing_time,
                        opening_time: this.opening_time,
                        currentRoute: this.$route.name,
                    },
                }
            );

            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    user: data.user.name,
                    opened_time: data.opened_time,
                    created_at: data.created_at,

                    opening_mpesa_amount: data.opening_mpesa_amount,

                    opening_cash_amount: data.opening_cash_amount,
                    cash_payments: data.cash_payments,
                    mpesa_payments: data.mpesa_payments,
                    card_payments: data.card_payments,
                    loyalty_payments: data.loyalty_payments,
                    credit_payments: data.credit_payments,
                    return_cash_sales: data.return_cash_sales,
                    return_mpesa_sales: data.return_mpesa_sales,
                    cash_drops: data.cash_drops,
                    mpesa_drops: data.mpesa_drops,
                    expected_cash: data.expected_cash,
                    expected_mpesa: data.expected_mpesa,
                    expected_card: data.expected_card,
                    closing_cash: data.closing_cash,
                    closing_mpesa: data.closing_mpesa,
                    closing_card: data.closing_card,
                    cash_shortover: data.cash_shortover,
                    mpesa_shortover: data.mpesa_shortover,
                 card_shortover:   data.card_shortover
                    
                });
            });
            return data_array;
        },

        async filterByDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchData(page) {
            this.closing_time = this.formatDateTime(this.to);
            this.opening_time = this.formatDateTime(this.from);
            const res = await this.getApi(
                "data/register/fetch",

                {
                    params: {
                        page: page,
                        search: this.search,
                        closing_time: this.closing_time,
                        opening_time: this.opening_time,
                        currentRoute: this.$route.name,
                    },
                }
            );

            if (res.status == 200) {
                this.balance_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchTotals() {
            this.closing_time = this.formatDateTime(this.to);
            this.opening_time = this.formatDateTime(this.from);
            const res = await this.getApi(
                "data/cashier_balance/fetchSum",

                {
                    params: {
                        search: this.search,
                        closing_time: this.closing_time,
                        opening_time: this.opening_time,
                        currentRoute: this.$route.name,
                    },
                }
            );

            if (res.status == 200) {
                this.total_opening_balance = res.data.total_opening_amount;
                this.total_closing_balance = res.data.total_closing_amount;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.fetchTotals(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
<style scoped>
.is_deducted {
    background: red !important;
}
.short-alert {
    background: #ff8f00 !important;
}
.m-short-alert {
    background: #cb5c12 !important;
}
.m-surplus-alert {
    background: #06e6c1 !important;
    color: #fff;
}
.negative-value {
    background-color: #ffcccc; /* Light red */
}
</style>
