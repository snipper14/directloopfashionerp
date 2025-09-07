<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2"><reception-balance-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4>
                            <b>Reservation Opening/Closing Balance Reports</b>
                        </h4>
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
                            >Deficit</span
                        >
                        <div class="total-display">
                            <span class="badge badge-primary totals-badge"
                                >Total Sales:
                                {{
                                    cashFormatter(total_data.total_sold_amount)
                                }}</span
                            >
                            <span class="badge badge-primary totals-badge"
                                >Expected Total Sales:
                                {{
                                    cashFormatter(total_data.total_total_amount)
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Opening Balance:
                                {{
                                    cashFormatter(
                                        total_data.total_opening_amount
                                    )
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Closing Balance:
                                {{
                                    cashFormatter(
                                        total_data.total_closing_amount
                                    )
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Cash:
                                {{
                                    cashFormatter(total_data.total_cash_amount)
                                }}
                            </span>
                            <span class="badge badge-secondary totals-badge"
                                >Total Bank Transfer:
                                {{
                                    cashFormatter(
                                        total_data.total_bank_transfer_amount
                                    )
                                }}
                            </span>
                            <span class="badge badge-secondary totals-badge"
                                >Total Mobile Money:
                                {{
                                    cashFormatter(total_data.total_mobile_money)
                                }}</span
                            >
                              <span class="badge badge-secondary totals-badge"
                                >Online Payments:
                                {{
                                    cashFormatter(total_data.total_online_paid)
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Credit Card:
                                {{
                                    cashFormatter(total_data.total_credit_card)
                                }}</span
                            >
                            <span class="badge badge-secondary totals-badge"
                                >Total Cheque:
                                {{
                                    cashFormatter(total_data.total_cheque)
                                }}</span
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
                                        <th><b> Sold Amount</b></th>
                                        <th scope="col">Closing Amount</th>

                                        <td>Cash Received / Cash Sales</td>
                                        <td>
                                            Mobile Money Collected / M.M Sale
                                        </td>
                                        <td>Cheque Collected / Cheque Sale</td>
                                        <td>Bank Transfer / B.T Sale</td>
                                        <td>Card / Card Sale</td>
                                         <td>Online / Online Sale</td>
                                        <td>Deficit</td>
                                        <th>Details</th>
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
                                                    data.deficit_amount < 0,
                                            },
                                        ]"
                                    >
                                        <td>{{ data.user.name }}</td>
                                        <td>{{ data.opening_time }}</td>
                                        <td>{{ data.closing_time }}</td>

                                        <td>
                                           <b> {{
                                                cashFormatter(data.sold_amount)
                                            }}</b>
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.closing_amount
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(data.cash_amount)
                                            }}
                                            /
                                            {{
                                                cashFormatter(
                                                    data.cash_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.mobile_money)
                                            }}
                                            /
                                            {{
                                                cashFormatter(
                                                    data.mobile_money_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.cheque) }} /
                                            {{
                                                cashFormatter(
                                                    data.cheque_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.bank_transfer_amount
                                                )
                                            }}
                                            /
                                            {{
                                                cashFormatter(
                                                    data.bank_transfer_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.credit_card)
                                            }}
                                            /

                                            {{
                                                cashFormatter(
                                                    data.card_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.online_paid)
                                            }}
                                            /

                                            {{
                                                cashFormatter(
                                                    data.online_sold_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.deficit_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <button
                                                @click="viewDetails(data)"
                                                class="btn btn-info btn-sm"
                                            >
                                                Details
                                            </button>
                                        </td>
                                        <td>
                                            <button
                                                v-if="isAdmin"
                                                @click="
                                                    deleteRecord(data.id, i)
                                                "
                                                class="btn btn-danger btn-sm"
                                            >
                                                Delete
                                            </button>
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
        <ReceptionBalanceDetails
            v-if="active.reception_balance && details_data"
            v-on:dashboard-active="setActiveRefresh"
            :details_data="details_data"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import ReceptionBalanceDetails from "./ReceptionBalanceDetails.vue";
import ReceptionBalanceNav from "./ReceptionBalanceNav.vue";

export default {
    data() {
        return {
            search: "",
            active: {
                dashboard: true,
                reception_balance: false,
            },
            total_data: {},
            from: null,
            to: null,
            opening_time: null,
            closing_time: null,
            total_opening_balance: 0,
            total_closing_balance: 0,
            details_data: null,
            isLoading: true,
            balance_data: [],
        };
    },
    components: {
        Pagination,
        Unauthorized,
        ReceptionBalanceNav,
        ReceptionBalanceDetails,
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
        viewDetails(data) {
            this.details_data = data;
            this.setActiveComponent("reception_balance");
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
                "data/reception_balance/fetch",

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
                var data = res.data;
                for (var i = 0; i < data.length; i++) {
                    data[i].name = data[i].user ? data[i].user.name : "";
                }

                return data;
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
                    "data/reception_balance/destroy/" + id
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
                "data/reception_balance/fetch",

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
                "data/reception_balance/fetchSum",

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
                this.total_data = res.data;
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
