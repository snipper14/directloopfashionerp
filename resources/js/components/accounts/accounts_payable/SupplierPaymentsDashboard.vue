<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-3">
                <payable-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>Supplier Payments</h5>
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
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Company<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_supplier
                                            "
                                            sort-key="sort_supplier"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">Debit<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total_debit
                                            "
                                            sort-key="sort_total_debit"
                                            @update-sort="updateSortParameter"
                                        /></th>

                                    <th scope="col">Credit<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total_credit
                                            "
                                            sort-key="sort_total_credit"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                    <th scope="col">Outstanding Amount<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_actual_balance
                                            "
                                            sort-key="sort_actual_balance"
                                            @update-sort="updateSortParameter"
                                        /></th>

                                    <th scope="col">View Details</th>
                                    <th>Add Payment</th>
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
                                            @click="viewDetails(data)"
                                        >
                                            Payments
                                        </button>
                                    </td>
                                    <td>
                                        <button
                                            v-if="isWritePermitted"
                                            class="btn btn-secondary custom-button"
                                            @click="addPayment(data)"
                                        >
                                            Add Payments
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
                        Items Count {{ supplier_data.total }}
                        <div
                            class="row"
                            title="download"
                            v-if="isDownloadPermitted"
                        >
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" small>
                                        <v-icon medium>{{
                                            icons.mdiMicrosoftExcel
                                        }}</v-icon>
                                        Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                    ><v-btn color="secondary" small>
                                        <v-icon class="v-icon" medium>{{
                                            icons.mdiFileExcel
                                        }}</v-icon>
                                        Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
                <center v-else>
                    <unauthorized />
                </center>
            </div>
        </div>

        <add-payment
            v-if="active.add_payment"
            :data="payable_data"
            v-on:dashboard-active="setActiveRefresh"
        />
        <payment-statements
            v-if="active.view_details"
            :data="payable_data"
            v-on:dashboard-active="setActiveRefresh"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";

import PayableNav from "./PayableNav.vue";
import AddPayment from "./AddPayment.vue";
import PaymentStatements from "./PaymentStatements.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_supplier: false,
                create_invoice: false,
                add_payment: false,
                view_details: false,
            },
            params: { search: "" },
            sort_params: {
                sort_supplier: "",
                sort_actual_balance:"",
                sort_total_credit:"",
                sort_total_debit:""
            },
            supplier_invoice_data: {},
            supplier_data: [],
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
        PayableNav,
        AddPayment,
        PaymentStatements,
        Unauthorized,
        SortButtons,
    },
    mounted() {
        this.fetchSupplierBalances(1);
    },
    watch: {
        params: {
            handler: _.debounce(function (val, old) {
                this.fetchSupplierBalances(1);
            }, 500),
            deep: true,
        },
        sort_params: {
            handler: _.debounce(function (val, old) {
                this.fetchSupplierBalances(1);
            }, 500),
            deep: true,
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_invoice/fetchInvoice",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    supplier: data.supplier.company_name,
                    total_debit: data.total_debit,
                    total_credit: data.total_credit,
                    balance: data.total_credit - data.total_debit,
                });
            });
            return data_array;
        },
        outstandingBalance(data) {
            const inv_total =
                data.invoice_sum.length > 0
                    ? data.invoice_sum[0].sum_invoice
                    : 0;
            const paid_amount =
                data.payment_totals.length > 0
                    ? data.payment_totals[0].total_amount_paid
                    : 0;
            return parseFloat(inv_total) - parseFloat(paid_amount);
        },
        addPayment(data) {
            this.payable_data = data;
            this.setActiveComponent("add_payment");
        },

        viewDetails(data) {
            this.payable_data = data;
            this.setActiveComponent("view_details");
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchSupplierBalances(1);
        },

        async fetchSupplierBalances(page) {
            this.showLoader();
            const res = await this.getApi(
                "data/supplier_invoice/fetchInvoice",
                {
                    params: {
                        page,

                        ...this.params,
                        ...this.sort_params,
                    },
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.supplier_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
