<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><cashier-balance-nav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4><b> CashDrop Reports</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input
                                        type="text"
                                        v-model="params.search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="">From</label>
                                <date-picker
                                    v-model="params.from"
                                    type="datetime"
                                />
                            </div>
                            <div class="col-3">
                                <label for="">To</label>
                                <date-picker
                                    v-model="params.to"
                                    type="datetime"
                                />
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

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">User</th>
                                        <th scope="col">Date</th>
                                        <th>Cash</th>
                                        <th>Cashdrawer Account</th>
                                        <th>Deposit Account</th>
                                        <th scope="col">Ref</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in balance_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.user.name }}</td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.cash_amount)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                data.cashdrawer_account.account
                                            }}
                                        </td>
                                        <td>{{ data.account.account }}</td>
                                        <td>
                                            {{ data.ref_no }}
                                        </td>
                                        <td>
                                            <v-btn
                                                x-small
                                                color="primary"
                                                @click="reprintCahDrop(data)"
                                                >Reprint</v-btn
                                            >
                                            <v-btn color="danger" x-small v-if="isDeletePermitted" @click="destroy(data)">Delete</v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="balance_data !== null"
                            v-bind:results="balance_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ balance_data.total }}
                        <div class="row">
                            <div class="col-4 d-flex">
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
            </div>
        </div>
        <div class="row">
            <ReprintCashdropReceipt
                v-if="show_print"
                ref="CashdropReceipt"
                :printData="printData"
            />
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import CashierBalanceNav from "./CashierBalanceNav.vue";
import ReprintCashdropReceipt from "./ReprintCashdropReceipt.vue";

export default {
    data() {
        return {
            params: {
                from: null,
                to: null,
                search: "",
            },
            printData: null,
            show_print: false,
            isLoading: true,
            balance_data: [],
        };
    },
    components: {
        Pagination,
        Unauthorized,
        CashierBalanceNav,
        ReprintCashdropReceipt,
    },

    watch: {
        params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
    },
    methods: {
        async destroy(value) {
            const confirm = await this.deleteDialogue();
            if (!confirm) {
                return;
            }
            const res = await this.callApi(
                "delete",
                "data/cashdrops/destroy/" + value.ref_no,
                {}
            );
            if (res.status == 200) {
                 this.concurrentApiReq();
            }
        },
        async reprintCahDrop(data) {
            this.printData = data;
            this.show_print = true;

            setTimeout(() => {
                this.$refs.CashdropReceipt.printReceipt();
            }, 1000);
        },
        async fetchExcel() {
            this.closing_time = this.formatDateTime(this.params.to);
            this.opening_time = this.formatDateTime(this.params.from);

            const res = await this.getApi(
                "data/cashdrops/fetch",

                {
                    params: {
                        ...this.params,
                    },
                }
            );
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    name: data.user.name,

                    created_at: this.formatDateTime(data.created_at),

                    cash_amount: data.cash_amount,

                    cashdrawer_account: data.cashdrawer_account.account,
                    account_deposited: data.account.account,
                    ref_no: data.ref_no,
                });
            });
            return data_array;
        },

        async filterByDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },
        async fetch(page) {
            this.closing_time = this.formatDateTime(this.params.to);
            this.opening_time = this.formatDateTime(this.params.from);
            const res = await this.getApi(
                "data/cashdrops/fetch",

                {
                    params: {
                        page: page,
                        ...this.params,
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
                this.fetch(1),
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
