<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-3">
                <receivable-reports-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>View/ Print Customer Ledger</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="params.search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div
                            class="row d-flex flex-row justify-content-between"
                        >
                            <h3>
                                <b>
                                    TOTAL BALANCE:
                                    {{ cashFormatter(total_balance) }}</b
                                >
                            </h3>
                            <div class="col-md-3 d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.from"
                                ></date-picker>
                            </div>
                            <div class="col-md-3 d-flex flex-column">
                                <label> To Datetime *</label>
                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.to"
                                ></date-picker>
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered mt-3"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Company<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_customer
                                            "
                                            sort-key="sort_customer"
                                            @update-sort="updateSortParameter"
                                        /></th>

                                    <th scope="col">
                                        Balance<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total_balance
                                            "
                                            sort-key="sort_total_balance"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in customer_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.customer.id }}
                                    </td>
                                    <td>
                                        {{ data.customer.company_name }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.total_balance) }}
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="viewStatement(data)"
                                            >View Statemt</router-link
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="customer_data !== null"
                            v-bind:results="customer_data"
                            v-on:get-page="fetchCustomerBalances"
                        ></Pagination>
                        Items Count {{ customer_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllBalances"
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
                                    :fetch="getAllBalances"
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
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>

        <ledger-details
            v-if="active.view_stamt"
            :customer_info="customer_info"
            v-on:dashboard-active="setActiveRefresh"
        />
    </div>
</template>

<script>
import LedgerDetails from "./LedgerDetails";
import ReceivableReportsNav from "./ReceivableReportsNav.vue";
import Pagination from "../../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import SortButtons from "../../../utilities/SortButtons.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,

                view_stamt: false,
            },
            customer_data: [],
            customer_info: null,
            total_balance: 0,
            isLoading: true,
            search: "",
            params: {
                search: "",
                from: null,
                to: null,
            },
            sort_params: {
                sort_total_balance: "",
                sort_customer:""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        LedgerDetails,
        Pagination,
        Unauthorized,
        ReceivableReportsNav,
        SortButtons,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([
                this.fetchCustomerBalances(1),
                this.fetchTotals(),
            ]);
            this.isLoading ? this.hideLoader() : "";
        },
        async getAllBalances() {
            this.showLoader();
            const res = await this.getApi("data/cust_ledger/customerLedgerStatement", "");
            this.hideLoader();
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.customer.id,
                    company_name: data.customer.company_name,
                    balance: data.total_balance,
                });
            });
            return data_array;
        },
        viewStatement(data) {
            this.customer_info = data;

            this.setActive(this.active, "view_stamt");
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
        },
        async fetchTotals() {
            const res = await this.getApi(
                "data/cust_ledger/generateCustTotal",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.total_balance = res.data.total_balance;
        },
        async fetchCustomerBalances(page) {
            const res = await this.getApi(
                "data/cust_ledger/customerLedgerStatement",
                {
                    params: {
                        page,
                        ...this.sort_params,
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.customer_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
