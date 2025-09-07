<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Invoice Sales History</div>

                    <div class="card-body">
                        <div class="row">
                            <p>User {{ data[0].user.name }}</p>
                            "----"
                            <br />
                            <p>Customer {{ data[0].customer.company_name }}</p>
                            <br />
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">BatchNo</th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item</th>
                                            <th scope="col">Qty</th>

                                            <th scope="col">Item Price</th>
                                            <th scope="col">Disc</th>
                                            <th scope="col">Total Amount</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in data"
                                            :key="i"
                                        >
                                            <td>{{ data.batch_no }}</td>
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td
                                                :class="{
                                                    current_stock:
                                                        data.stock_id ==
                                                        stock_id,
                                                }"
                                            >
                                                {{ data.stock.name }}
                                            </td>
                                            <td>{{ data.qty }}</td>

                                            <td>
                                                {{ cashFormatter(data.price) }}
                                            </td>
                                            <td>-{{ data.discount }}</td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.row_total
                                                    )
                                                }}
                                            </td>
                                            <td>{{formatDateTime(data.created_at)}}</td>
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
        this.getInvoice();
    },
    methods: {
        async getInvoice() {
            const res = await this.getApi(
                "data/invoices/invoiceItems/" + this.info_data.ref,
                ""
            );

            if (res.status === 200) {
                this.data = res.data.results;
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
