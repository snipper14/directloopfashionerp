<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <div class="row">
                            <div class="col-md-3">
                                <h4>General Ledger Accounts</h4>
                            </div>
                            <div class="col-md-4">
                                <form class="form-inline">
                                    <input
                                        class="form-control mr-sm-2"
                                        type="search"
                                        v-model="params.search"
                                        placeholder="Search Account"
                                        aria-label="Search"
                                    />
                                </form>
                            </div>
                            <div class="col-md-4">
                                <v-btn
                                    @click="add_account_modal = true"
                                    color="primary"
                                    >Add Account</v-btn
                                >
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">
                                        Account Name<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_account_name
                                            "
                                            sort-key="sort_account_name"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th>Balance<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total_balance
                                            "
                                            sort-key="sort_total_balance"
                                            @update-sort="updateSortParameter"
                                        /></th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in account_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.account.account }}
                                    </td>

                                    <th scope="col">
                                        {{ cashFormatter(data.total_balance) }}
                                    </th>
                                    <td>
                                        <v-btn
                                            @click="viewStatement(data)"
                                            color="primary"
                                            x-small
                                            >View Statement</v-btn
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="account_data !== null"
                            v-bind:results="account_data"
                            v-on:get-page="fetchAccounts"
                        ></pagination>
                        Items Count {{ account_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="getAllInvoice"
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
                                    :fetch="getAllInvoice"
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

        <Modal v-model="edit_account_modal" width="1000px">
            <details-ledger-statement
                v-if="edit_account_modal"
                v-bind:edit_data="edit_data"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import Unauthorized from "../../utilities/Unauthorized.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
import LedgerNav from "./LedgerNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import AddLedgerAccount from "./AddLedgerAccount.vue";
import EditLedgerAccount from "./EditLedgerAccount.vue";
import DetailsLedgerStatement from "./DetailsLedgerStatement.vue";
import SortButtons from '../../utilities/SortButtons.vue';
export default {
    data() {
        return {
            add_account_modal: false,
            edit_account_modal: false,
            account_data: [],
            edit_data: null,

            isLoading: true,
            sort_params: {
                sort_account_name: "",
                sort_total_balance: "",
            },
            params: {
                search: "",
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
        };
    },
    components: {
        LedgerNav,
        Pagination,
        AddLedgerAccount,
        Unauthorized,
        EditLedgerAccount,
        DetailsLedgerStatement,
        SortButtons,
    },
    mounted() {
        this.fetchAccounts(1);
    },
    watch: {
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.fetchAccounts(1);
            }, 500),
        },
        params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.fetchAccounts(1);
            }, 500),
        },
    },
    methods: {
        dismissAddModal() {
            this.edit_account_modal = false;
        },

        viewStatement(data) {
            this.edit_data = data;

            this.edit_account_modal = true;
        },

        async getAllInvoice() {
            const res = await this.getApi("data/supplier_invoice/fetchAll", "");
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                    company_name: array_item.supplier.company_name,
                    invoice_no: array_item.invoice_no,
                    invoice_total: array_item.invoice_total,
                    paid_amount: array_item.paid_amount,
                    unpaid_amount: array_item.unpaid_amount,
                    invoice_date: array_item.invoice_date,
                    description: array_item.description,
                });
            });
            return data_array;
        },

        async fetchAccounts(page) {
            this.isLoading == true ? this.showLoader() : null;
            const res = await this.getApi(
                "data/general_ledger/generalLedgerStatement",
                {
                    params: {
                        page,
                        ...this.sort_params,
                        ...this.params,
                    },
                }
            );

            this.isLoading == true ? this.hideLoader() : null;
            if (res.status === 200) {
                this.account_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
