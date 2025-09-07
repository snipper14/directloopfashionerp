<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
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
                    <div class="modern-card-header card-header">
                        Upload Statement {{ form_field.ref_no }}
                    </div>

                    <div class="card-body">
                        <div class="row modern-div">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label
                                        class="form-control-label"
                                        for="input-file-import"
                                        >Upload Excel File ( csv) Bank
                                        Statement</label
                                    >
                                    <div class="d-flex flex-row">
                                        <input
                                            type="file"
                                            class="form-control"
                                            id="input-file-import"
                                            name="file_import"
                                            ref="import_file"
                                            @change="onFileChange"
                                        />

                                        <div>
                                            <v-btn
                                                :loading="btn_loading"
                                                @click="uploadExcel()"
                                                color="secondary"
                                                small
                                            >
                                                Upload
                                            </v-btn>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <div
                                        class="col-md-4 form-group d-flex flex-column"
                                    >
                                        <label for="">Transaction Date*</label>
                                        <date-picker
                                            v-model="
                                                form_field.transaction_date
                                            "
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label for=""
                                            >Transaction Details*</label
                                        >
                                        <textarea
                                            class="form-control"
                                            v-model="form_field.details"
                                            cols="30"
                                            rows="5"
                                        ></textarea>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="">Debit Amount*</label>
                                        <input
                                            type="number"
                                            v-model="form_field.debit_amount"
                                            class="form-control"
                                        />
                                    </div>
                                    <div class="form-group col-md-2">
                                        <label for="">Credit Amount*</label>
                                        <input
                                            type="number"
                                            v-model="form_field.credit_amount"
                                            class="form-control"
                                        />
                                    </div>

                                    <div class="form-group col-md-2">
                                        <label for=""></label>
                                        <v-btn
                                            :loading="btn_loading"
                                            @click="saveBankStatement"
                                            color="primary"
                                            >Save</v-btn
                                        >
                                    </div>
                                    <div>
                                        <b
                                            >Opening Balance::{{
                                                cashFormatter(
                                                    form_field.opening_balance
                                                )
                                            }}</b
                                        ><br>
                                         <b
                                            >Closing Balance::{{
                                                cashFormatter(
                                                    form_field.closing_balance
                                                )
                                            }}</b
                                        ><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <h3><b>Bank Statements</b></h3>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table modern-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date</th>

                                            <th>Description</th>

                                            <th>Debit</th>
                                            <th>Credit</th>
                                            <th></th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                value, i
                                            ) in bank_statement_data"
                                            :key="i"
                                        >
                                            <td>{{ value.id }}</td>
                                            <td>
                                                {{ value.transaction_date }}
                                            </td>
                                            <td>{{ value.details }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.debit_amount
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        value.credit_amount
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <v-btn
                                                    color="primary"
                                                    @click="reconcile(value, i)"
                                                    x-small
                                                    >Search</v-btn
                                                >
                                            </td>
                                            <td>
                                                <v-btn
                                                    color="danger"
                                                    x-small
                                                    @click="
                                                        deleteStatement(
                                                            value,
                                                            i
                                                        )
                                                    "
                                                    >delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="ledger_data_modal" width="1200px">
            <ReconciliationWithMatchingEntrieModal
                :details_data="details_data"
                :form_field="form_field"
                v-on:dismiss-modal="dismissModal"
                v-if="ledger_data_modal"
            />
        </Modal>
    </div>
</template>

<script>
import ReconciliationWithMatchingEntrieModal from "./ReconciliationWithMatchingEntrieModal.vue";
export default {
    components: { ReconciliationWithMatchingEntrieModal },
    props: ["form_data"],
    data() {
        return {
            index: 0,
            import_file: {},
            details_data: null,
            ledger_data_modal: false,
            bank_statement_data: [],
            btn_loading: false,
            form_field: {
                transaction_date: null,
                details: null,
                credit_amount: 0,
                debit_amount: 0,
                ref_no: this.form_data.ref,
                account_id: this.form_data.account_id,
                opening_balance: this.form_data.opening_balance,
                closing_balance: this.form_data.closing_balance,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        dismissModal() {
            this.ledger_data_modal = false;
            this.bank_statement_data.splice(this.index, 1);
        },
        async deleteStatement(value, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/qb_bank_reconciliation/deleteStatement/" + value.id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.bank_statement_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        reconcile(data, i) {
            this.details_data = data;
            this.index = i;
            this.ledger_data_modal = true;
        },
        async completeRecons() {},
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([this.fetchBankStatements()]);

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchBankStatements() {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchBankStatements",
                {
                    params: {
                        ...this.form_field,
                    },
                }
            );
            if (res.status == 200) {
                this.bank_statement_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async saveBankStatement() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/qb_bank_reconciliation/saveBankStatement",
                {
                    ...this.form_field,
                }
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.s("added");
                this.form_field.credit_amount = 0;
                this.form_field.debit_amount = 0;
                this.form_field.details = "";
                this.form_field.transaction_date = null;
                this.bank_statement_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        onFileChange(e) {
            this.import_file = e.target.files[0];
        },
        async uploadExcel() {
            this.btn_loading = true;
            var contx = this;
            const data = new FormData();
            data.append("import_file", this.import_file);
            const json = JSON.stringify(this.form_data);
            data.append("data", json);
            axios
                .post("data/qb_bank_reconciliation/importBankStatement", data, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        if (response.data.status == "error") {
                            this.errorNotif(response.data.message);
                        } else {
                            console.log(JSON.stringify(response.data));
                            this.bank_statement_data = response.data.results;
                            this.s("successfully uploaded");
                            this.$emit("refresh-data");
                        }
                    } else if (response.status === 500) {
                        this.errorNotif(response.data.message);
                    }
                })
                .catch((error) => {
                    console.log(JSON.stringify(error));

                    contx.swr("Server error try again later");
                    contx.hideLoader();
                    contx.btn_loading = false;
                    // code here when an upload is not valid
                    this.uploading = false;
                    this.error = error.response.data;
                });
        },
    },
};
</script>
