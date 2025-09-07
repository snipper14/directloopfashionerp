<template>
    <div class="container">
        <div class="row justify-content-center">
            <v-btn color="primary" @click="$emit('dashboard-active')"
                >Back</v-btn
            >
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><b>Generate GRN </b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Delivery No</th>
                                        <th scope="col">Order No</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Qty Ordered</th>
                                        <th>Qty Delivered</th>
                                        <th>Sold Qty</th>
                                        <th>Batch No</th>
                                        <th>Expiry</th>
                                        <th></th>
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
                                        <td>
                                            {{ data.stock.name }}
                                        </td>
                                        <td>
                                            {{ data.stock.purchase_price }}
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
                                        <td>
                                            <v-btn
                                                x-small
                                                color="secondary"
                                                v-if="isUpdatePermitted"
                                                title="update"
                                                @click="addReturn(data)"
                                            >
                                                Return
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row">
                            <h3><b> Return Items</b></h3>
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">Return Conditions</th>
                                        <th>Return Reasons</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Amount</th>
                                        <th>Vat Rate</th>
                                        <th>Discount Amount</th>
                                        <th>Vat Amount</th>
                                        <th>Return Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in return_array"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.stock.product_name }}
                                        </td>
                                        <td>
                                            {{ data.returns_conditions }}
                                        </td>
                                        <td>
                                            {{ data.return_reasons }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.unit_price) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.qty) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sub_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.tax_rate) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.discount_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.tax_amount.toFixed(2)
                                                )
                                            }}
                                        </td>
                                        <td>{{ data.return_date }}</td>
                                        <td>
                                            <v-btn
                                                x-small
                                                color="danger"
                                                @click="deleteReturn(data, i)"
                                            >
                                                Delete
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <v-btn
                                :loading="btn_loader"
                                v-if="return_array.length > 0"
                                color="info"
                                @click="completeReturn()"
                                >Complete Return</v-btn
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal title="Add Returns" v-model="return_grn_modal" width="1000px">
            <add-grn-return-modal
                v-if="return_grn_modal"
                :details_data="details_data"
                @addCart="addCart"
            />
        </Modal>
    </div>
</template>

<script>
import AddGrnReturnModal from "./AddGrnReturnModal.vue";
export default {
    components: { AddGrnReturnModal },
    props: ["delivery_details"],
    data() {
        return {
            btn_loader: false,
            return_grn_modal: false,
            details_data: null,
            delivery_data: [],
            return_array: [],
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async completeReturn() {
            this.btn_loader = true;
            const res = await this.callApi("POST", "data/grn_returns/create", {
                return_array: this.return_array,
            });
            this.btn_loader = false;
            if (res.status == 200) {
                this.successNotif("Saved");
                this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        deleteReturn(data, i) {
            this.return_array.splice(i, 1);
        },
        addCart(data) {
            this.return_grn_modal = false;
         

            this.return_array = this.return_array.filter(
                (item) => item.stock_id !== data.stock_id
            );
            // Calculate the new discount_amount based on the ratio of qty to original_qty
            let discount_amount = 0;
            if (data.qty_delivered && data.discount_amount) {
                discount_amount =
                    (data.discount_amount * data.qty) / data.qty_delivered;
            }
            let sub_total = data.sub_total - discount_amount;
            let tax_amount = this.calculateTax(data.tax_rate, sub_total);
            this.return_array.push({
                stock_id: data.stock_id,
                department_id: data.department_id,
                purchase_order_receivable_id: data.id,
                supplier_id: data.supplier_id,
                unit_price: data.unit_price,
                qty: data.qty,

                tax_rate: data.tax_rate,
                tax_amount: tax_amount,
                sub_total: sub_total,
                return_date: data.return_date,
                returns_conditions: data.returns_conditions,
                return_reasons: data.return_reasons,
                return_code: "",
                delivery_no: data.delivery_no,
                stock: data.stock,
                order_no: data.order_no,
                batch_no: data.batch_no,
                discount_amount: discount_amount,
            });

            console.log("?>.>>>>" + JSON.stringify(this.return_array));
        },

        addReturn(data) {
            this.return_grn_modal = true;
            this.details_data = data;
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetch()]);
            this.hideLoader();
        },
        async fetch() {
            const res = await this.getApi(
                "data/po_receivable/fetchDeliveryDetails",
                { params: { delivery_no: this.delivery_details.delivery_no } }
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
