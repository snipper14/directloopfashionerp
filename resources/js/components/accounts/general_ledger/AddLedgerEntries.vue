<template>
    <div class="row justify-content-center">
        <div class="col-md-3">
            <ledger-nav />
        </div>
        <div class="col-md-8">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left> mdi-arrow-left </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header"></div>

                <div class="card-body">
                    <fieldset class="border">
                        <legend class="text-center">Ledger Entries</legend>
                        <form>
                            <div class="row">
                                    <div class="d-flex flex-column">
                                    <v-btn
                                        color="info"
                                        small
                                        @click="
                                            select_credit_account_modal = true
                                        "
                                    >
                                        <v-icon class="v-icon" medium>{{
                                            icons.mdiPlusThick
                                        }}</v-icon>
                                        Transfer From (CR)</v-btn
                                    >
                                    <div class="form-group nopadding">
                                        <label>Transfer From (Credit Acc.) *</label>
                                        <input
                                            disabled
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="
                                                form_data.credit_account_name
                                            "
                                        />
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <v-btn
                                        small
                                        color="primary"
                                        @click="
                                            select_debit_account_modal = true
                                        "
                                    >
                                        <v-icon class="v-icon" medium>{{
                                            icons.mdiPlusThick
                                        }}</v-icon>
                                       Transfer To (DR)</v-btn
                                    >
                                    <div class="form-group nopadding">
                                        <label>Transfer To (Debit Acc)*</label>
                                        <input
                                            disabled
                                            type="text"
                                            class="form-control"
                                            autocomplete="new-user-street-address"
                                            v-model="
                                                form_data.debit_account_name
                                            "
                                        />
                                    </div>
                                </div>
                            

                                <div class="form-group col-md-3 nopadding mt-6">
                                    <label> Amount *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.amount"
                                    />
                                </div>

                                <div class="form-group col-md-3 nopadding mt-6">
                                    <label>Ledger No *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.entry_code"
                                        
                                    />
                                </div>
                                <div class="form-group col-md-3 nopadding ">
                                    <label>Details</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.details"
                                        
                                    />
                                </div>

                                <div
                                    class="form-group col-md-3 d-flex flex-column nopadding"
                                >
                                    <label> Entry Date*</label>
                                    <div>
                                        <date-picker
                                            v-model="form_data.entry_date"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                </div>
                                <div class="row form-group col-md-4 mt-3">
                                    <v-btn
                                        x-small
                                        type="button"
                                        color="info"
                                        :loading="loading_btn"
                                        @click="submitLedger()"
                                    >
                                        Save Entries
                                    </v-btn>
                                </div>
                            </div>
                        </form>
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>

                                    <th scope="col">Acc</th>
                                    <th scope="col">Descr.</th>

                                    <th scope="col">Dr</th>
                                    <th scope="col">Cr</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in ledger_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.id }}
                                    </td>
                                    <td>
                                        {{ data.account.account }}
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

                                    <td>
                                        <v-btn
                                            color="danger"
                                            title="delete"
                                            v-if="isDeletePermitted"
                                            @click="deleteEntry(data.id, i)"
                                            small
                                            >Delete</v-btn
                                        >
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"><b>Total: </b></td>
                                    <b>
                                        {{
                                            cashFormatter(total_debit_amount)
                                        }}</b
                                    >
                                    <td>
                                        <b>{{
                                            cashFormatter(total_credit_amount)
                                        }}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-4">
                                <v-btn
                                    :loading="compelete_loading_btn"
                                    color="warning"
                                    @click="completeLedger()"
                                    >Complete Ledger</v-btn
                                >
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
        <Modal v-model="select_credit_account_modal" width="800">
            <select-account-modal
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
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import SelectAccountModal from "./SelectCreditAccountModal.vue";
import LedgerNav from "./LedgerNav.vue";
import SelectDebitAcc from "./SelectDebitAcc.vue";
export default {
    components: {
        Treeselect,
        SelectAccountModal,
        LedgerNav,
        SelectDebitAcc,
    },
    data() {
        return {
            select_credit_account_modal: false,
            select_debit_account_modal: false,
            isLoading: true,
            loading_btn: false,
            delete_loading_btn: false,
            compelete_loading_btn: false,

            ledger_data: [],
            total_debit_amount: 0,
            total_credit_amount: 0,
            form_data: {
                amount: 0,
                credit_account_type: "",
                debit_account_type: "",
                debit_account_id: null,
                credit_account_id: null,
                details: "",
                amount: 0,
                entry_code: null,
                entry_date: null,
                credit_account_name: "",
                debit_account_name: "",
            },

            search_params: {
                accounts_id: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    mounted() {
        this.form_data.entry_date = this.getCurrentDate();
        this.concurrentApiReq();
    },
    watch: {
        ledger_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_debit_amount = this.ledger_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.debit_amount;
                },
                0);
                this.total_credit_amount = this.ledger_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.credit_amount;
                },
                0);
            }, 500),
        },
    },
    methods: {
        dismissCreditModal(value) {
            this.select_credit_account_modal = false;
            this.form_data.credit_account_name = value.name;
            this.form_data.credit_account_id = value.id;
            this.form_data.credit_account_type = value.type;
        },
        dismissDebitModal(value) {
            this.select_debit_account_modal = false;
            this.form_data.debit_account_name = value.name;
            this.form_data.debit_account_id = value.id;
            this.form_data.debit_account_type = value.type;
        },
        async completeLedger() {
            this.compelete_loading_btn = true;
            const res = await this.callApi(
                "post",
                "data/general_ledger/completeLedger",
                {
                    entry_code: this.form_data.entry_code,
                }
            );
            this.compelete_loading_btn = false;
            if (res.status === 200) {
                this.s(" Entries completed !");

                this.$router.push({ path: `/general_ledger` });
            } else {
                this.form_error(res);
            }
        },
        async deleteEntry(id, i) {
            this.delete_loading_btn = true;
            const res = await this.callApi(
                "delete",
                "data/general_ledger/destroy/" + id
            );
            this.delete_loading_btn = false;
            if (res.status === 200) {
                this.s(" Record deleted !");

                this.ledger_data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchLedgerNo(),
                //   this.fetchPending(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchPending() {
            const res = await this.getApi(
                "data/general_ledger/fetchPending",
                {}
            );

            if (res.status == 200) {
                this.ledger_data = res.data;
                if (this.ledger_data.length > 0) {
                    this.form_data.entry_code = this.ledger_data[0].entry_code;
                    this.form_data.entry_date = this.ledger_data[0].entry_date;
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchLedgerNo() {
            const res = await this.getApi(
                "data/general_ledger/fetchEntryNo",
                {}
            );

            if (res.status == 200) {
                this.form_data.entry_code = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },

        async submitLedger() {
            if (
                this.form_data.credit_account_id +
                    this.form_data.credit_account_type ==
                this.form_data.debit_account_id +
                    this.form_data.debit_account_type
            ) {
                this.errorNotif("Credit /Debit accounts can't be the same");
                return;
            }
            var count = 0;
            if (this.form_data.credit_account_type == "account") {
                count++;
            }
            if (this.form_data.debit_account_type == "account") {
                count++;
            }
            if (count == 0) {
                this.errorNotif("Select atleast one account to be G/N");
                return;
            }
            this.loading_btn = true;
            const res = await this.callApi(
                "post",
                "data/general_ledger/create",
                this.form_data
            );
            this.loading_btn = false;
            if (res.status === 200) {
                this.s(" Records Details has been added successfully!");
                this.form_data.amount = 0;
                this.form_data.credit_amount = 0;
                this.form_data.debit_amount = 0;
                (this.form_data.account_id = null),
                    (this.form_data.credit_account_type = "");
                this.form_data.credit_account_name = "";
                this.form_data.debit_account_name = "";
                this.ledger_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
