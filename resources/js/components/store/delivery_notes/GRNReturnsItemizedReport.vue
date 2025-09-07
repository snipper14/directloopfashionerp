<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                       <h3><b>Itemized GRN Returns</b></h3>
                    </div>

                    <div class="card-body">
                         <div class="row">
                                    <div class="d-flex flex-column">
                                        <label> From Datetime *</label>

                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.from"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column mr-2">
                                        <label> To Datetime *</label>
                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.to"
                                        ></date-picker>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Search</label>
                                        <input
                                            type="text"
                                            v-model="params.search"
                                            class="form-control"
                                        />
                                    </div>
                         </div>
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
                                        v-for="(data, i) in returns_data.data"
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
              params: {
                order_no: "",
                delivery_no: "",
                supplier_id: null,
                from: null,
                to: null,
            },
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
            await Promise.all([this.fetch(1)]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async fetch(page) {
            const res = await this.getApi(
                "data/grn_returns/fetchItemized",
                { params: { page:page,...this.params } }
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
