<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-12">
                <payment-nav />
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <h4>Customer Balance Records</h4>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Company</th>
                                    <th scope="col">Original Invoice Amount</th>
                                    <th scope="col">Total Paid</th>
                                    <th scope="col">Pending Balance</th>
                                    <th scope="col">Statements</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in customer_balance_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.customer }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.invoice_total) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.total_amount_paid
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.pending_balance)
                                        }}
                                    </td>

                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="viewStatement(data)"
                                            >View Statements</router-link
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="allPayment"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="allPayment"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFileExcel
                                    }}</v-icon>
                                    Export CSV
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
                <center v-else>
                    <b style="color: red; font-size: 1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>
        <notifications group="foo" />
        <customer-statement
            v-if="active.customer_statement"
            :data="customer_data"
            v-on:dashboard-active="setActiveRefresh"
        />
    </div>
</template>

<script>
import NewPayment from "./NewPayment.vue";
import Pagination from "../../utilities/Pagination.vue";
import PaymentNav from "./PaymentNav.vue";
import CustomerStatement from "./CustomerStatement.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";

export default {
    data() {
        return {
            active: {
                dashboard: true,
                customer_statement: false,
            },
            customer_data: null,
            customer_balance_data: [],

            search: "",

            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        NewPayment,
        PaymentNav,
        Pagination,
        CustomerStatement,
    },
    mounted() {
        this.fetchCustomerBalances(1);
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.customer_balance_data = this.searchRecords(val);
            }, 500),
        },
    },
    methods: {
        viewStatement(data) {
            this.customer_data = data;
            this.setActiveComponent("customer_statement");
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchCustomerBalances();
        },

        async allPayment() {
            const res = await this.getApi(
                "data/payments/fetchCustomerBalances",
                ""
            );

            return res.data.results;
        },

        async searchRecords(val) {
            var customer_balance_data = this.customer_balance_data;
            var res = [];
            for (var i = 0; i <script customer_balance_data.length; i++) {
                if (customer_balance_data[i].customer === val) {
                    res.push(customer_balance_data[i]);

                    return res;
                } else {
                    return customer_balance_data;
                }
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },

        async fetchCustomerBalances(page) {
            this.showLoader();
            const res = await this.getApi(
                "data/payments/fetchCustomerBalances",
                ""
            );
            this.hideLoader();

            if (res.status === 200) {
                this.customer_balance_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
