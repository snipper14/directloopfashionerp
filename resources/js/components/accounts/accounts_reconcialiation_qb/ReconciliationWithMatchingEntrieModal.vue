<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="modern-card card-header">Ledger Entries</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="">Fetch Match</label>
                                <input
                                    type="checkbox"
                                    class="form-control"
                                    v-model="params.matching_records"
                                />
                            </div>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(
                                                value, i
                                            ) in ledger_statement_data"
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
                                                <Button
                                                    type="primary"
                                                    @click="reconcileMatch(value, i)"
                                                    >Reconcile Match</Button
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
    </div>
</template>

<script>
export default {
    props: ["details_data",'form_field'],
    data() {
        return {
            ledger_statement_data: [],
            params: { matching_records: true },
            form_data: {
                general_ledger_id: null,
                recon_bank_statement_id: this.details_data.id,
                opening_balance:this.form_field.opening_balance,
                closing_balance:this.form_field.closing_balance
            },
        };
    },
    mounted() {
        console.log("Component mounted." + JSON.stringify(this.details_data));
        this.fetchMatchingTransaction();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.fetchMatchingTransaction();
            }, 20),
        },
    },
    methods: {
        async reconcileMatch(value, i) {
            
            this.form_data.general_ledger_id = value.id;
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/qb_bank_reconciliation/reconcileMatch",
                {
                    ...this.form_data,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.ledger_statement_data.splice(i, 1);
                this.$emit('dismiss-modal')
            } else {
                this.form_error(res);
            }
        },
        async fetchMatchingTransaction() {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchMatchingTransaction",
                {
                    params: {
                        ...this.details_data,
                        ...this.params,
                    },
                }
            );
            if (res.status == 200) {
                this.ledger_statement_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
