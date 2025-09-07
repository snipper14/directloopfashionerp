<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <executive-nav />
            </div>
            <div class="col-md-9" >
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
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>
                                            <center>
                                                <h4>USERS $ BRANCH SALE SUMMARY REPORT</h4>
                                            </center>
                                        </th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">
                                                    <h3><b>User Summary</b></h3>
                                                </th>

                                                <th scope="col">
                                                    Cost Of Product
                                                </th>
                                                <th scope="col">
                                                    Total Qty Sold
                                                </th>
                                                <th scope="col">Total Sales</th>
                                                <th scope="col">Margin</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="(
                                                    data, i
                                                ) in user_totals_data.data"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ data.user.name }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_purchase_price
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_qty
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.invoice_total
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.invoice_total -
                                                                data.sum_purchase_price
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                     <pagination
                            v-if="user_totals_data !== null"
                            v-bind:results="user_totals_data"
                            v-on:get-page="fetchGroupedUserTotalsReport"
                        ></pagination>
                        Items Count {{ user_totals_data.total }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <tbody>
                                            <tr>
                                                <th scope="col">
                                                    <h3>
                                                        <b>Branch  Summary</b>
                                                    </h3>
                                                </th>

                                                <th scope="col">Total Cost</th>
                                                <th scope="col">Qty Sold</th>
                                                <th scope="col">Total Sales</th>
                                                <th>Margin</th>
                                            </tr>
                                            <tr
                                                v-for="(
                                                    data, i
                                                ) in locations_data"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ data.department.department }}
                                                </td>

                                                 <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_purchase_price
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.sum_qty
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.invoice_total
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.invoice_total -
                                                                data.sum_purchase_price
                                                        )
                                                    }}
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                  
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from '../../utilities/Pagination.vue';
import ExecutiveNav from "./ExecutiveNav.vue";
export default {
    components: { ExecutiveNav, Pagination },
    data() {
        return {
            params: {
                search: "",
                from: null,
                to: null,
            },
            locations_data: [],
            user_totals_data: [],
        };
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
     
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchGroupedLocationTotalsReport(),
                this.fetchGroupedUserTotalsReport(1),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchGroupedUserTotalsReport(page) {
            const res = await this.getApi(
                "data/invoices/fetchGroupedUserTotalsReport",
                {
                    params: {
                        page,

                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.user_totals_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },

        async fetchGroupedLocationTotalsReport() {
            const res = await this.getApi(
                "data/invoices/fetchGroupedLocationTotalsReport",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.locations_data = res.data;
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
