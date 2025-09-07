<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Code No: {{ delivery_details.return_code }}
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Return No</th>

                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Sub Total</th>
                                        <th>Vat total</th>
                                        <th>Reasons</th>
                                        <th>Item Condition</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in returns_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.return_code }}
                                        </td>

                                        <td>
                                            {{ data.stock.name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.unit_price) }}
                                        </td>
                                        <td>
                                            {{ data.qty }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sub_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.tax_amount) }}
                                        </td>
                                        <td>{{ data.return_reasons }}</td>
                                        <td>{{ data.returns_conditions }}</td>
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
            returns_data: [],
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async deleteDetails(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (!shouldDelete) {
                return;
            }
            this.showLoader();
            const dataObj = {
                order_no: data.order_no,
                id: data.id,
                delivery_no: data.delivery_no,
                stock_id: data.stock_id,
                qty_delivered: data.qty_delivered,
                department_id: data.department_id,
            };
            const res = await this.callApi(
                "POST",
                "data/po_receivable/deleteDeliveryItem",
                dataObj
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Deleted");
                this.returns_data.splice(i, 1);
            } else {
                this.servo();
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetch()]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetch() {
            const res = await this.getApi(
                "data/grn_returns/fetchReturnDetails",
                { params: { return_code: this.delivery_details.return_code } }
            );
            if (res.status === 200) {
                this.returns_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
