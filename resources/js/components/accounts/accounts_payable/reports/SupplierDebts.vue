<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <payable-report-nav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>Supplier Statements</h5>
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
                        <div class="row table-responsive">
                            <div class="col-md-8">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <tr>
                                        <td>
                                            <b> Total Debit :</b>
                                        </td>
                                        <td>
                                            <b> Total Credit :</b>
                                        </td>
                                        <td>
                                            <b> TOTAL BALANCE:</b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    all_supplier_data.total_debit
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    all_supplier_data.total_credit
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    all_supplier_data.actual_balance
                                                )
                                            }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Company
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_supplier
                                            "
                                            sort-key="sort_supplier"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">Debit</th>

                                    <th scope="col">Credit</th>
                                    <th scope="col">
                                        Outstanding Amount
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_actual_balance
                                            "
                                            sort-key="sort_actual_balance"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">View Statements</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in supplier_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{
                                            data.supplier
                                                ? data.supplier.company_name
                                                : ""
                                        }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.total_debit) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total_credit) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.total_credit -
                                                    data.total_debit
                                            )
                                        }}
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-primary custom-button"
                                            @click="viewStaements(data)"
                                        >
                                            Statements
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="supplier_data !== null"
                            v-bind:results="supplier_data"
                            v-on:get-page="fetchSupplierBalances"
                        ></Pagination>
                        <!-- Items Count {{ supplier_data.total }} -->
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

        <supplier-ledger-details
            v-if="active.view_stamt"
            :supplier_info="supplier_info"
            v-on:dashboard-active="setActiveRefresh"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import PayableReportNav from "./PayableReportNav.vue";
import SupplierLedgerDetails from "./SupplierLedgerDetails";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_supplier: false,
                view_stamt: false,
            },
            params: {
                search: "",
            },
            sort_params: {
                sort_supplier: "",
                sort_actual_balance: "",
            },
            isLoading: true,
            total_outstanding_balance: 0,
            total_invoice_balance: 0,
            supplier_invoice_data: {},
            supplier_data: [],
            all_supplier_data: [],
            payable_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        SupplierLedgerDetails,
        Unauthorized,
        PayableReportNav,
        SortButtons,
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
        sort_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
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

            const res = await Promise.all([
                this.fetchSupplierBalances(1),
                this.fetchAllSupplierBalances(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        isBalanceZero(data) {
            var res = data.total_credit - data.total_debit;
            return res == 0 ? false : true;
        },
        async getAllBalances() {
            this.showLoader();
            const res = await this.getApi("data/supplier_invoice/fetchInvoice", {
                params: {
                    ...this.sort_params,
                    ...this.params,
                },
            });
            this.hideLoader();
            var data_array = [];

            res.data.map((array_item) => {
                data_array.push({
                    company_name: array_item.supplier.company_name,
                    total_debit: array_item.total_debit,
                    total_credit: array_item.total_credit,
                    balance: array_item.total_credit - array_item.total_debit,
                });
            });
            return data_array;
        },

        viewStaements(data) {
            this.supplier_info = data;
            this.setActiveComponent("view_stamt");
        },
        async searchSuppliers() {
            let page = 1;
            const res = await this.getApi(
                "data/supplier_invoice/fetchInvoice",
                {
                    params: {
                        page,
                        search: this.search.length >= 2 ? this.search : "",
                        ...this.params,
                    },
                }
            );
            if (res.status === 200) {
                this.supplier_data = res.data;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        async fetchAllSupplierBalances() {
            const res = await this.getApi(
                "data/supplier_invoice/fetchTotalBalances",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            if (res.status == 200) {
                this.all_supplier_data = res.data;
            } else {
                this.swr("server error");
            }
        },
        async fetchSupplierBalances(page) {
            const res = await this.getApi(
                "data/supplier_invoice/fetchInvoice",
                {
                    params: {
                        page,
                        ...this.sort_params,
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.supplier_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
