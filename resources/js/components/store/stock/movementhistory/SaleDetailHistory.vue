<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Sale History</div>

                    <div class="card-body">
                        <div class="row">
                            <p>User {{ data[0].user.name }}</p>
                            "----"
                            <br />
                            <p>Customer {{ data[0].customer.company_name }}</p>
                            <br />
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Item ID</th>
                                            <th scope="col">Product Code</th>
                                            <th scope="col">Product Name</th>

                                            <th>qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th>Vat Amount</th>
                                            <th>Batchno</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.item_id }}
                                            </td>
                                            <td>{{ data.stock.code }}</td>
                                            <td
                                                :class="{
                                                    current_stock:
                                                        data.stock_id ==
                                                        stock_id,
                                                }"
                                            >
                                                {{ data.stock.product_name }}
                                            </td>
                                            <td>{{ data.qty }}</td>
                                            <td>
                                                {{ cashFormatter(data.price) }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.row_total
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(data.row_vat)
                                                }}
                                            </td>
                                            <td>{{ data.batch_no }}</td>
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
        this.getSalesData();
    },
    methods: {
        async getSalesData() {
            const res = await this.getApi("data/pos_sale/fetchSaleItems", {
                params: {
                    order_no: this.info_data.ref,
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
