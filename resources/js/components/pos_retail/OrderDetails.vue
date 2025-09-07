<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Orders</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                      <th>Batchno</th>
                                        <th scope="col">Product Code</th>
                                        <th scope="col">Product Name</th>
                                       
                                        <th>qty</th>
                                        <th>Price</th>
                                        <th>amount</th>

                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in order_data"
                                        :key="i"
                                    >
                                     <td>{{ data.batch_no }}</td>
                                        <td>{{ data.stock.code }}</td>
                                        <td>{{ data.stock.product_name }}</td>
                                         <td>{{ data.qty }}</td>
                                         <td>{{cashFormatter( data.selling_price) }}</td>
                                         <td>{{cashFormatter(data.row_amount)  }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["order_details"],
    data() {
        return {
            order_data: [],
        };
    },
    mounted() {
        console.log(JSON.stringify(this.order_details));
        this.fetchDetails();
    },
    methods: {
        async fetchDetails() {
            this.showLoader();
            const res = await this.getApi("data/pos_order/fetchDetails", {
                params: { order_no: this.order_details.order_no },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
