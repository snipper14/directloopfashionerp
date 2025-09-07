<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-9">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header-wrapper">
                        <div class="row">
                            <div class="col-md-3">
                                <h4>General Ledger</h4>
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
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column mr-2">
                                <label> To Datetime *</label>
                                <date-picker
                                    valueType="format"
                                    width="300px"
                                    v-model="params.to"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column" v-if="false">
                                <v-btn
                                    small
                                    color="primary"
                                    @click="select_debit_account_modal = true"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiPlusThick
                                    }}</v-icon>
                                    Add Debit Account</v-btn
                                >
                                <div class="form-group nopadding">
                                    <label>Debit Acc*</label>
                                    <input
                                        disabled
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.debit_account_name"
                                    />
                                </div>
                            </div>
                            <div class="d-flex flex-column" v-if="false">
                                <v-btn
                                    color="info"
                                    small
                                    @click="select_credit_account_modal = true"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiPlusThick
                                    }}</v-icon>
                                    Add Credit Account</v-btn
                                >
                                <div class="form-group nopadding">
                                    <label>Credit Acc. *</label>
                                    <input
                                        type="text"
                                        disabled
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.credit_account_name"
                                    />
                                </div>
                            </div>
                            <v-btn @click="clearSearch()"> Clear Search</v-btn>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>

                                    <th scope="col">Acc</th>
                                    <th scope="col">Account Type.</th>
                                    <th scope="col">Descr.</th>

                                    <th scope="col">Dr</th>
                                    <th scope="col">Cr</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(
                                        data, i
                                    ) in general_ledger_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.id }}
                                    </td>
                                    <td>
                                        {{ data.account.account }}
                                        <sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_account
                                                "
                                                sort-key="sort_account"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                    </td>
                                     <td>
                                        {{ data.account.account_type }}
                                    </td>
                                    <td>
                                        {{ data.other_account_name }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.debit_amount) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.credit_amount) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                    <td><b>{{cashFormatter(ledger_total.sum_debit_amount)}}</b></td>
                                    <td><b>{{cashFormatter(ledger_total.sum_credit_amount)}}</b></td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="general_ledger_data !== null"
                            v-bind:results="general_ledger_data"
                            v-on:get-page="fetchLedgers"
                        ></pagination>
                        Items Count {{ general_ledger_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="exportExcel"
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
                                    :fetch="exportExcel"
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
        <Modal v-model="select_credit_account_modal" width="800">
            <select-credit-account-modal
                @dismissModal="dismissCreditModal"
                v-if="select_credit_account_modal"
            />
        </Modal>

        <Modal v-model="select_debit_account_modal" width="800">
            <select-debit-acc
                @dismissModal="dismissDebitModal"
                v-if="select_debit_account_modal"
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
import AddLedgerEntries from "./AddLedgerEntries.vue";
import LedgerDetailsModal from "./LedgerDetailsModal.vue";
import SelectCreditAccountModal from "./SelectCreditAccountModal.vue";
import SelectDebitAcc from "./SelectDebitAcc.vue";
export default {
    data() {
        return {
            select_debit_account_modal: false,
            select_credit_account_modal: false,
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                new_invoice: false,
                view_invoice: false,
            },
            isLoading: true,
            ledger_total: {},
            add_ledger_modal: false,
            detail_model: false,
            invoice_details: null,
            general_ledger_data: [],
            total_profit_loss: 0,
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            search: "",
            code: null,
            params: {
                search: "",
                from: null,
                to: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
            form_data: {
                credit_account_type: "",
                debit_account_type: "",
                debit_account_id: null,
                credit_account_id: null,

                credit_account_name: "",
                debit_account_name: "",
            },
            sort_params:{
                sort_account:""
            }
        };
    },
    components: {
        LedgerNav,
        Pagination,
        AddLedgerEntries,
        LedgerDetailsModal,
        SelectCreditAccountModal,
        SelectDebitAcc,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchLedgers(1);
            }, 500),
        },
          sort_params: {
            deep: true,
            handler: _.debounce(function () {
               
                this.fetchLedgers(1);
            }, 500),
        },
    },
    methods: {
         updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        clearSearch() {
            Object.keys(this.form_data).forEach(
                (key) => (this.form_data[key] = null)
            );
            Object.keys(this.params).forEach(
                (key) => (this.params[key] = null)
            );
            this.concurrentApiReq();
        },
        dismissCreditModal(value) {
            this.select_credit_account_modal = false;
            this.form_data.credit_account_name = value.name;
            this.form_data.credit_account_id = value.id;
            this.form_data.credit_account_type = value.type;
            this.concurrentApiReq()
        },
        dismissDebitModal(value) {
            this.select_debit_account_modal = false;
            this.form_data.debit_account_name = value.name;
            this.form_data.debit_account_id = value.id;
            this.form_data.debit_account_type = value.type;
            this.concurrentApiReq();
        },
        viewDetails(code) {
            this.code = code;

            this.detail_model = true;
        },
        dismissLedgerModal() {
            this.add_ledger_modal = false;
            this.detail_model = false;
            this.concurrentApiReq();
        },

        async viewInvoiceDetail(data) {
            this.invoice_details = data;

            this.setActiveComponent("view_invoice");
        },
        editCustomer(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_customer");
        },

        async exportExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/general_ledger/fetchLedgerGrouped",
                {
                    params: {
                        ...this.params,
                        ...this.form_data,
                    },
                }
            );
            this.hideLoader();
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    entry_code: data.entry_code,
                    account: data.account.account,
                    details: data.other_account_name,
                    debit_amount: data.debit_amount,
                    credit_amount: data.credit_amount,

                    entry_date: data.entry_date,
                    user: data.user.name,
                });
            });
            return data_array;
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchLedgers(1);
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchLedgers(1),
                this.fetchLedgerTotal(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchLedgers(page) {
        
            const res = await this.getApi(
                "data/general_ledger/fetchLedgerGrouped",
                {
                    params: {
                        page,

                        ...this.params,
                        ...this.form_data,
                        ...this.sort_params
                    },
                }
            );
           

            if (res.status === 200) {
                this.general_ledger_data = res.data;
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
<style scoped></style>
