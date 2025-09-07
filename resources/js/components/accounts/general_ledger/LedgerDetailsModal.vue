<template>
    <div class="row justify-content-center">
        <v-app>
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
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Ledger Entries</h3>
                        </div>

                        <div class="card-body">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Cr Acc.</th>

                                        <th scope="col">Db Acc</th>
                                        <th scope="col">Value</th>
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
                                            {{ data.credit_account.account }}
                                        </td>
                                        <td>
                                            {{ data.debit_account.account }}
                                        </td>
                                        <td>
                                            {{ data.amount }}
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
                                        <td colspan="3">
                                            <b>Total: </b>
                                        </td>
                                        <b>
                                            {{ cashFormatter(total_amount) }}</b
                                        >
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </v-app>
        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    props: ["code"],
    components: {
        Treeselect,
    },
    data() {
        return {
            isLoading: true,
            loading_btn: false,
            delete_loading_btn: false,
            compelete_loading_btn: false,
            accounts_select_data: null,
            ledger_data: [],
            total_amount: 0,
            form_data: {
                amount: 0,
                debit_account_id: null,
                credit_account_id: null,
                entry_code: null,
                entry_date: null,
            },

            search_params: {
                accounts_id: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        ledger_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_amount = this.ledger_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.amount;
                },
                0);
            }, 500),
        },
    },
    methods: {
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

            const res = await Promise.all([this.fetchPending()]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchPending() {
            const res = await this.getApi(
                "data/general_ledger/fetchDetailsEntries",
                {
                    params: {
                        entry_code: this.code,
                    },
                }
            );

            if (res.status == 200) {
                this.ledger_data = res.data;
            } else {
                this.swr("Server error occurred");
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
