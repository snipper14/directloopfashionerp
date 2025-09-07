<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Delivery No: {{ delivery_data[0].delivery_no }}/ Supplier: {{delivery_data[0].supplier.company_name}}
                    </div>

                    <div class="card-body">
                        <b>
                            <p>User {{ delivery_data[0].user.name }}</p></b
                        >
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Delivery No</th>
                                        <th scope="col">Order No</th>
                                        <th>Stock</th>
                                        <th>P.Price</th>
                                        <th>S.Price</th>
                                        <th>Qty Ordered</th>
                                        <th>Qty Delivered</th>
                                        <th>Sold Qty</th>
                                        <th>Batch No</th>
                                        <th>Expiry</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in delivery_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.delivery_no }}
                                        </td>
                                        <td>
                                            {{ data.order_no }}
                                        </td>
                                        <td
                                            :class="{
                                                current_stock:
                                                    data.stock_id == stock_id,
                                            }"
                                        >
                                            {{ data.stock.name }}
                                        </td>
                                        <td>
                                            {{cashFormatter( data.unit_price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.stock.selling_price) }}
                                        </td>
                                        <td>
                                            {{ data.qty_ordered }}
                                        </td>
                                        <td>
                                            {{ data.qty_delivered }}
                                        </td>
                                        <td>{{ data.qty_sold }}</td>
                                        <td>{{ data.batch_no }}</td>
                                        <td>{{ data.expiry_date }}</td>
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
</template>

<script>
import { mdiFeatureSearch } from "@mdi/js";
export default {
    props: ["delivery_details"],
    data() {
        return {
            delivery_data: [],
            stock_id: null,
        };
    },
    mounted() {
        
        this.stock_id = this.delivery_details.stock_id;
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetch()]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetch() {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryDetails",
                { params: { delivery_no: this.delivery_details.ref } }
            );
            if (res.status === 200) {
                this.delivery_data = res.data;
            } else {
                this.swr("Server error try again later");
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
