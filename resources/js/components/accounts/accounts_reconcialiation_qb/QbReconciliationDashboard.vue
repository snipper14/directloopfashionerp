<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <ReconNav />
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><b>BANK RECONCILIATION</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-5">
                                <label for="">Select Bank Account</label>
                                <treeselect
                                    width="1200px"
                                    :load-options="fetchBranchAccounts"
                                    :options="accounts_select_data"
                                    :auto-load-root-options="false"
                                    v-model="form_data.account_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select Account "
                                />
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Ref No</label>
                                <input
                                    type="text"
                                    v-model="form_data.ref"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="">Beginning Balance</label>
                                <input
                                    type="number"
                                   
                                    v-model="form_data.opening_balance"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="">Closing Balance</label>
                                <input
                                    type="number"
                                    v-model="form_data.closing_balance"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Ending Date</label>

                                <date-picker
                                    v-model="form_data.entry_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="col-md-4">
                                <v-btn
                                    v-if="show_btn"
                                    x-small
                                    @click="uploadStatement()"
                                    color="primary"
                                    >Start Reconciling</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal width="1000px" v-model="start_recons_modal">
            <StartQBRecons
                v-if="start_recons_modal"
                v-on:dismiss-modal="dismissModal"
                :form_data="form_data"
            />
        </Modal>

        <QBUploadBankStatement
            v-if="active.upload_statement"
            v-on:dashboard-active="setActiveRefresh"
            :form_data="form_data"
        />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import StartQBRecons from "./StartQBRecons.vue";
import ReconNav from "./ReconNav.vue";

import QBUploadBankStatement from "./QBUploadBankStatement.vue";
export default {
    components: { Treeselect, StartQBRecons, ReconNav, QBUploadBankStatement },
    data() {
        return {
            active: {
                dashboard: true,
                upload_statement: false,
            },
            start_recons_modal: false,
            show_btn: false,
            isLoading: true,
            form_data: {
                account_id: null,
                entry_date: this.getCurrentDate(),
                opening_balance: 0,
                closing_balance: 0,
                ref: null,
            },
        };
    },
    mounted() {},
    watch: {
        "form_data.account_id": function (newAccountId, oldAccountId) {
            // Your logic here, for example:
            console.log(
                "Account ID changed from",
                oldAccountId,
                "to",
                newAccountId
            );
            this.form_data.account_id = newAccountId;
            this.fetchPreviousReconsClosing();
            // Additional actions based on account_id change
        },
    },
    methods: {
        uploadStatement() {
         
            this.setActiveComponent("upload_statement");
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        dismissModal() {
            this.start_recons_modal = false;
            this.form_data.account_id = null;
            this.form_data.opening_balance = 0;
            this.form_data.closing_balance = 0;
            this.form_data.ref = "";
        },
        async startReconciling() {
            if (
                this.form_data.closing_balance < 0 ||
                this.form_data.closing_balance == 0
            ) {
                this.errorNotif("Closing balance should be greater than zero!");
                return;
            }
            this.start_recons_modal = true;
        },
        async fetchPreviousReconsClosing() {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchPreviousReconsClosing",
                {
                    params: {
                        ...this.form_data,
                    },
                }
            );

            if (res.status === 200) {
                this.form_data.opening_balance = res.data.opening_balance;
                this.form_data.ref = res.data.ref;
                this.form_data.closing_balance = res.data.closing_balance;
                this.show_btn = true;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([this.fetchPreviousReconsClosing()]);

            this.isLoading ? this.hideLoader() : "";
        },
    },
};
</script>
