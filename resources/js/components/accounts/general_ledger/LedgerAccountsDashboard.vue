<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <h4>General Ledger Accounts</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 nopadding">
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

                            <div class="col-md-2 nopadding">
                                <v-btn
                                    small
                                    @click="add_sub_account_modal = true"
                                    color="primary"
                                    >Add Account</v-btn
                                >
                            </div>
                            <div class="col-md-2 nopadding">
                                <v-btn
                                    small
                                    @click="add_account_modal = true"
                                    color="primary"
                                    >Add Sub Account</v-btn
                                >
                            </div>
                        </div>
                        <div class="row">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>
                                            Account no<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_account_no
                                                "
                                                sort-key="sort_account_no"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Account Name<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_account_name
                                                "
                                                sort-key="sort_account_name"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Main Sub Account<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_main_sub_account
                                                "
                                                sort-key="sort_main_sub_account"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Category
                                            <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_category
                                                "
                                                sort-key="sort_category"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th>Type</th>
                                        <th scope="col">Additional Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in account_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.account_no_formatted }}</td>
                                        <td>
                                            {{ data.account }}
                                        </td>
                                        <td>
                                            {{
                                                formatLedgerNo(data.ledger_sub_account
                                                    .account_no)
                                            }}-{{
                                                data.ledger_sub_account.name
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                formatCategory(data.category)
                                            }}
                                            ({{ data.category }})
                                        </td>
                                        <td>
                                            {{ data.account_type }}
                                        </td>
                                        <td>
                                            {{ data.description }}
                                        </td>
                                        <td>
                                            <v-btn
                                                @click="
                                                    deleteAccount(data.id, i)
                                                "
                                                color="danger"
                                                x-small
                                                >Delete</v-btn
                                            >
                                            <v-btn
                                                @click="editAccount(data)"
                                                color="primary"
                                                x-small
                                                >Edit</v-btn
                                            >
                                            <v-btn
                                                @click="addBalances(data)"
                                                color="primary"
                                                x-small
                                                >Add Balances</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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
                                    :fetch="fetchExcell"
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
                                    :fetch="fetchExcell"
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

        <Modal v-model="add_sub_account_modal" width="1000px">
            <add-ledger-sub-account
                v-if="add_sub_account_modal"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>

        <Modal v-model="add_account_modal">
            <add-ledger-account
                v-if="add_account_modal"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>

        <Modal v-model="edit_account_modal">
            <edit-ledger-account
                v-if="edit_account_modal"
                v-bind:edit_data="edit_data"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>

        <Modal v-model="chart_acc_modal">
            <add-chart-accounts-modal
                v-if="chart_acc_modal"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>
        <Modal v-model="add_balances_modal">
            <add-ledger-balance
                v-if="add_balances_modal"
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
import AddChartAccountsModal from "./sub_accounts/AddChartAccountsModal.vue";
import AddLedgerBalance from "./AddLedgerBalance.vue";
import AddLedgerSubAccount from "./AddMainLedgerSubAccount.vue";
import SortButtons from "../../utilities/SortButtons.vue";
export default {
    data() {
        return {
            add_account_modal: false,
            edit_account_modal: false,
            chart_acc_modal: false,
            add_balances_modal: false,
            account_data: [],
            edit_data: null,
            add_sub_account_modal: false,
            isLoading: true,
            sort_params: {
                sort_main_sub_account: "",
                sort_category: "",
                sort_account_name: "",
                sort_account_no: "",
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
        AddChartAccountsModal,
        AddLedgerBalance,
        AddLedgerSubAccount,
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
                this.isLoading = true;
                this.fetchAccounts(1);
            }, 500),
        },
    },
    methods: {
         
        async deleteAccount(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/ledger_account/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.account_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        dismissAddModal() {
            this.add_account_modal = false;
            this.edit_account_modal = false;
            this.chart_acc_modal = false;
            this.add_balances_modal = false;
            this.add_sub_account_modal = false;
            this.fetchAccounts(1);
        },

        editAccount(data) {
            this.edit_data = data;

            this.edit_account_modal = true;
        },
        addBalances(data) {
            this.edit_data = data;
            this.add_balances_modal = true;
        },

        async fetchExcell() {
            const res = await this.getApi("data/ledger_account/fetch", "");
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    account: data.account,
                    category: data.category,
                    main_sub_account: data.ledger_sub_account.name,
                    account_type: data.account_type,
                    description: data.description,
                });
            });
            return data_array;
        },

        async fetchAccounts(page) {
            this.isLoading == true ? this.showLoader() : null;
            const res = await this.getApi("data/ledger_account/fetch", {
                params: {
                    page,
                    ...this.sort_params,
                    ...this.params,
                },
            });

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
