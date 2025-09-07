<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">Archived Sales Details</div>

                        <div class="card-body">
                           

                            <div class="row">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th>Item ID</th>
                                                <th scope="col">
                                                    Product Code
                                                </th>
                                                <th scope="col">
                                                    Product Name
                                                </th>

                                                <th>qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                                <th>Vat Amount</th>
                                                <th>Batchno</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(data, i) in order_data"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ data.stock.item_id }}
                                                </td>
                                                <td>{{ data.stock.code }}</td>
                                                <td>
                                                    {{
                                                        data.stock.product_name
                                                    }}
                                                </td>
                                                <td>{{ data.qty }}</td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.price
                                                        )
                                                    }}
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
                                                        cashFormatter(
                                                            data.row_vat
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ data.batch_no }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <ReprintPosReceipt
                        ref="ReceiptPrintComponent"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :order_data="order_data"
                        :etims_data="etims_data"
                    />
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
import Swal from "sweetalert2/dist/sweetalert2.js";
import Pagination from "../../utilities/Pagination.vue";
import ReprintPosReceipt from "./ReprintPosReceipt.vue";
export default {
    components: {
        ReprintPosReceipt,
        Pagination,
        Swal,
    },
    props: ["sale_details"],
    data() {
        return {
            order_data: [],
            form_data: {},
            etims_data: {},
            etims_response_data: null,
            load_printer_component: false,
        };
    },
    mounted() {
        this.form_data = this.sale_details;
        this.etims_data = this.sale_details;

        this.fetchDetails();
    },
    methods: {
       
     
        async fetchDetails() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/fetchArchivedSaleItems", {
                params: { order_no: this.sale_details.order_no },
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
