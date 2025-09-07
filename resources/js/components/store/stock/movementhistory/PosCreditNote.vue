<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">POS CREDIT NOTE DETAILS</div>

                    <div class="card-body">
                        <div class="row">
                            <p>User {{ data[0].user.name }}</p>
                           
                            <br />

                            <br />
                               <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Timestamp</th>
                                            <th scope="col">Credit Date</th>
                                            <th>Code</th>
                                            <th>Product</th>
                                            <th>Receipt No</th>
                                            <th>Credit No</th>
                                            <th scope="col">Cr.SP</th>
                                            <th scope="col">Cr.qty</th>
                                            <th>Cr.Vat</th>
                                            <th>Cr.Amount</th>
                                            <th>User</th>
                                            <th>Add Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in data"
                                            :key="i"
                                        >
                                            <td>
                                                {{
                                                    formatDateTime(
                                                        data.created_at
                                                    )
                                                }}
                                            </td>
                                            <td>{{ data.credit_date }}</td>
                                            <td>{{ data.stock.code }}</td>
                                            <td   :class="{
                                                current_stock:
                                                    data.stock_id == stock_id,
                                            }">{{ data.stock.name }}</td>
                                            <td>{{ data.receipt_no }}</td>
                                            <td>{{ data.credit_no }}</td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            data.credit_sp
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.credit_vat
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                <b>{{
                                                    cashFormatter(
                                                        data.credit_amount
                                                    )
                                                }}</b>
                                            </td>

                                            <td>{{ data.user.name }}</td>
                                            <td>{{ data.deduct_stock }}</td>
                                            <td>
                                                <v-btn
                                                    title="delete"
                                                    x-small
                                                    color="danger"
                                                    @click="
                                                        deleteData(data.id, i)
                                                    "
                                                    v-if="isDeletePermitted"
                                                    >Delete</v-btn
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
        </div>
    </div>
</template>

<script>
export default {
    props: ["info_data"],
    data() {
        return {
            data: [],
            stock_id: null,
        };
    },
    mounted() {
        console.log("Component mounted." + JSON.stringify(this.info_data));
        this.stock_id = this.info_data.stock_id;
        this.fetchCredit();
    },
    methods: {
        async fetchCredit() {
            const res = await this.getApi("data/pos_credit/fetch", {
                params: {
                    credit_no: this.info_data.ref,
                },
            });
         
            if (res.status === 200) {
                this.data = res.data;
            }
        },
    },
};
</script>
<style scoped>
.current_stock {
    color: rgb(255, 145, 0);
}
</style>
