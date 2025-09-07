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
                    <div class="card-header">
                        <div class="row">
                            Account:
                            <h2>{{ edit_data.account.account }}</h2>
                        </div>
                    </div>

                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between align-items-center"
                        >
                            <div class="form-group">
                                <label> From*</label>
                                <date-picker
                                    v-model="from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To*</label>
                                <date-picker
                                    v-model="to"
                                    valueType="format"
                                ></date-picker>
                            </div>

                            <button
                                class="btn btn-primary custom-button mb-2"
                                color="primary"
                                dark
                                @click="fetchLadgerInfo(1)"
                            >
                                Filter
                            </button>
                        </div>
                        <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Ref.</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Details</th>

                                    <th scope="col">Acc.</th>
                                    <th scope="col">Debit</th>
                                    <th scope="col">Credit</th>

                                    <th scope="col">balance</th>
                                </tr>
                            </thead>
                            <tbody v-if="ledger_data">
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in ledger_data.data"
                                    :key="i"
                                >
                                    <td>{{ data.entry_date }}</td>
                                    <td>
                                        {{ data.entry_code }}
                                    </td>
                                    <td>{{ data.account_type }}</td>
                                    <td>{{ data.details }}</td>

                                    <td>{{ data.other_account_name }}</td>
                                    <td>{{ cashFormatter(data.debit) }}</td>
                                    <td>{{ cashFormatter(data.credit) }}</td>

                                    <td>{{ cashFormatter(data.balance) }}</td>
                                    <td>
                                        <v-btn
                                            @click="deleteStmt(data.id, i)"
                                            x-small
                                            color="danger"
                                            v-if="isDeletePermitted"
                                            title="Delete"
                                            >Delete</v-btn
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <pagination
                            v-if="ledger_data !== null"
                            v-bind:results="ledger_data"
                            v-on:get-page="fetchLadgerInfo"
                        ></pagination>
                        <span v-if="ledger_data">
                            Items Count {{ ledger_data.total }}</span
                        >

                        <div class="row">
                            <div class="col-8 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button
                                        class="btn btn-secondary custom-button"
                                    >
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button
                                        class="btn btn-primary custom-button"
                                    >
                                        Export CSV
                                    </button>
                                </export-excel>

                                <button
                                    class="btn btn-success custom-button"
                                    @click="downLoad"
                                >
                                    Download Statement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../../utilities/Pagination.vue";

export default {
    props: ["edit_data"],
    data() {
        return {
            ledger_data: null,
            from: "",
            to: "",
            search: "",
            account_id: null,
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
    },
    mounted() {
        this.account_id = this.edit_data.account_id;
        this.fetchLadgerInfo(1);
    },
    methods: {
        async deleteStmt(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/general_ledger/deleteStatement/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.fetchLadgerInfo(1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async downLoad() {
            const params_data = {
                from: this.from,
                to: this.to,
                account_id: this.account_id,
            };
            this.showLoader();
            axios({
                url: "data/general_ledger/downloadStatment",
                method: "GET",
                params: params_data,
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.edit_data.account.account +
                        "_ledger_statement" +
                        ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },
        async fetchExcel() {
            const params_data = {
                from: this.from,
                to: this.to,
                account_id: this.account_id,
            };

            this.showLoader();
            const res = await this.getApi("data/general_ledger/fetchAll", {
                params: params_data,
            });
            this.hideLoader();

            if (res.status === 200) {
                const data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        ref: data.ref,
                  
                        entry_date: data.entry_date,
                        entry_code: data.entry_code,
                        account_type: data.account_type,
                        description: data.details,
                      account_name:  data.other_account_name,
                        debit: data.debit,

                        credit: data.credit,
                        balance: data.balance,
                    });
                });
                return data_array;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
        async fetchLadgerInfo(page) {
            const params_data = {
                from: this.from,
                to: this.to,
                account_id: this.account_id,
                page,
                search: this.search.length >= 4 ? this.search : "",
            };

            this.showLoader();
            const res = await this.getApi(
                "data/general_ledger/fetchLedgerStatement",
                {
                    params: params_data,
                }
            );
            this.hideLoader();

            if (res.status === 200) {
                this.ledger_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
