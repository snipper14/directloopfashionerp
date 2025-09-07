<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2"><cashier-balance-nav /></div>
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
                        <span class="badge short-alert" style="color: #fff"
                            >closing and total sales not matching</span
                        >
                        <div class="total-display">
                            <span class="badge badge-secondary totals-badge"
                                >Total Closing Balance:
                                {{ cashFormatter(total_closing_balance) }}</span
                            >
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">cashier</th>
                                        <th scope="col">Opening Date</th>
                                        <th>Closing Date</th>

                                        <th scope="col">
                                            System Total Sales Amount
                                        </th>
                                        <th>Cash Opening Amount</th>
                                        <th>Cash Closing Amount</th>

                                        <th>Cash Collected</th>
                                        <th>System Cash Sales Amount</th>

                                        <th>System Mobile Money Sales</th>
                                        <th>System Card Payments Sales</th>

                                        <th>Details</th>
                                        <th>Cash Left Amount</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in balance_data.data"
                                        :key="i"
                                        :class="[
                                            {
                                                'short-alert':
                                                    data.cash_collected !==
                                                    data.cash_sold_amount,
                                            },
                                        ]"
                                    >
                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.opening_time }}</td>
                                        <td>{{ data.closing_time }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.card_sold_amount +
                                                        data.cash_sold_amount +
                                                        data.mobile_money_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.opening_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.closing_cash_amount
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.cash_collected
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.cash_sold_amount
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.mobile_money_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.card_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>{{ data.details }}</td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.cash_left_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <v-btn
                                             x-small
                                               color="info"
                                                @click="viewDetails(data)"
                                            >
                                                Details
                                            </v-btn>
                                            <v-btn
                                               
                                                @click="
                                                    reprintBalances(data)
                                                "
                                               color="primary"
                                               x-small
                                            >
                                                Reprint
                                            </v-btn>
                                        </td>

                                        <td>
                                            <v-btn
                                                v-if="isAdmin"
                                                @click="
                                                    deleteRecord(data.id, i)
                                                "
                                               color="danger"
                                            >
                                                Del
                                            </v-btn>
                                              
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="balance_data !== null"
                            v-bind:results="balance_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
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
            <reprint-closing-balance
                v-if="show_print"
                ref="PrintClosingBalanceReceipt"
                :details_data="details_data"
            />
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import CashierBalanceDetails from "./CashierBalanceDetails.vue";
import CashierBalanceNav from "./CashierBalanceNav.vue";
import ReprintClosingBalance from './ReprintClosingBalance.vue';

export default {
    data() {
        return {
            show_print:false,
            search: "",
            active: {
                dashboard: true,

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
        };
    },
    components: {
        Pagination,
        Unauthorized,
        CashierBalanceNav,
        CashierBalanceDetails,
        ReprintClosingBalance,
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
        async reprintBalances(data){

            console.log(JSON.stringify(data));
                     this.details_data = data;
            
              this.show_print = true;
                //await printJS("printReceipt", "html");
                setTimeout(() => {
                    this.$refs.PrintClosingBalanceReceipt.printReceipt();
                  this.details_data = null;
             this.show_print = false;
                }, 1000);
        },
        viewDetails(data) {
            this.details_data = data;
            this.setActiveComponent("cashier_balance");
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
                "data/cashier_balance/fetch",

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
                const data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        id: data.id,
                        user: data.user.name,
                        opening_time: data.opening_time,
                        closing_time:data.closing_time,
                        system_total_sales:
                            data.card_sold_amount +
                            data.cash_sold_amount +
                            data.mobile_money_sold_amount,
                        opening_amount: data.opening_amount,

                        closing_cash_amount: data.closing_cash_amount,
                        cash_collected: data.cash_collected,
                        cash_sold_amount: data.cash_sold_amount,
                        mobile_money_sold_amount: data.mobile_money_sold_amount,
                        card_sold_amount: data.card_sold_amount,
                        details: data.details,
                        cash_left_amount: data.cash_left_amount,
                        expense_payments:data.expense_payments,
                        cash_sales_amount:data.cash_sales_amount,
                        invoice_payments:data.invoice_payments
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "delete",
                    "data/cashier_balance/destroy/" + id
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.successNotif("Deleted");
                    this.balance_data.data.splice(i, 1);
                    this.concurrentApiReq();
                } else {
                    this.servo();
                }
            }
        },
        async filterByDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetchData(page) {
            this.closing_time = this.formatDateTime(this.to);
            this.opening_time = this.formatDateTime(this.from);
            const res = await this.getApi(
                "data/cashier_balance/fetch",

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
</style>
