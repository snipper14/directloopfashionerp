<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header-wrapper">
                        <h4>Accounts Categories</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 nopadding">
                                <v-btn
                                v-if="isWritePermitted"
                                title="Write"
                                    small
                                    @click="chart_acc_modal = true"
                                    color="primary"
                                    >Add Chart Of Accounts</v-btn
                                >
                            </div>
                        </div>
                        <div class="row">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Account Name</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Additional Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in account_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.name }}
                                        </td>
                                        <td>
                                            {{ data.type }}
                                        </td>
                                        <td>
                                            {{ data.description }}
                                        </td>
                                        <td>
                                            <v-btn
                                                v-if="isDeletePermitted"
                                                title="Delete"
                                                @click="
                                                    deleteAccount(data.id, i)
                                                "
                                                color="danger"
                                                x-small
                                                >Delete</v-btn
                                            >
                                            <v-btn
                                                title="update"
                                                v-if="isUpdatePermitted"
                                                @click="editAccount(data)"
                                                color="primary"
                                                x-small
                                                >Edit</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal v-model="edit_chart_account_modal">
            <edit-chart-accounts
                v-if="edit_chart_account_modal"
                v-bind:data="edit_data"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>

        <Modal v-model="chart_acc_modal">
            <add-chart-accounts-modal
                v-if="chart_acc_modal"
                v-on:dashboard-active="dismissAddModal"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
import AddChartAccountsModal from "./AddChartAccountsModal.vue";
import LedgerNav from "../LedgerNav.vue";
import EditChartAccounts from "./EditChartAccounts.vue";
export default {
    data() {
        return {
            add_account_modal: false,
            edit_chart_account_modal: false,
            chart_acc_modal: false,
            account_data: [],
            edit_data: null,

            isLoading: true,

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
        AddChartAccountsModal,
        LedgerNav,
        EditChartAccounts,
    },
    mounted() {
        this.fetchAccounts(1);
    },
    watch: {
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
                    "data/chart_accounts/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.account_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        dismissAddModal() {
            this.add_account_modal = false;
            this.edit_chart_account_modal = false;
            this.chart_acc_modal = false;
            this.fetchAccounts(1);
        },

        editAccount(data) {
            this.edit_data = data;

            this.edit_chart_account_modal = true;
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
            const res = await this.getApi("data/chart_accounts/fetch", {
                params: {
                    page,

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
