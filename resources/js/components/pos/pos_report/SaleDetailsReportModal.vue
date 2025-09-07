<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><b>Receipt Details</b> </div>

                        <div class="card-body">
                            <div class="row">
                                <v-btn
                                    title="DELETE"
                                    v-if="isDeletePermitted"
                                    color="danger"
                                    @click="deleteSale()"
                                    >Reverse Sale (Credit)</v-btn
                                >
                                <v-btn
                                    title="DOWNLOAD"
                                    
                                    color="secondary"
                                    @click="reprintSale()"
                                    >Re-Print</v-btn
                                >
                            </div>

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
                                                 <th>Discount(%)</th>
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
                                                            data.discount
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
                <div class="col-md-4">
                    <ReprintPosReceipt
                        ref="ReceiptPrintComponent"
                     
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
            load_printer_component: true,
        };
    },
    mounted() {
        this.form_data = this.sale_details;
        this.etims_data = this.sale_details;

        this.fetchDetails();
    },
    methods: {
        reprintSale() {
            this.load_printer_component = true;
            setTimeout(() => {
                this.$refs.ReceiptPrintComponent.printReceipt();
            }, 1000);
        },
        async validateEtimsCreditnote() {
            const res = await this.callApi(
                "post",
                "data/pos_sale/validateEtimsCreditnote",
                {
                    ...this.form_data,
                    order_data: this.order_data,
                    ...this.etims_response_data,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.$emit("dismiss-details");
                if (res.data.queue_status == "completed") {
                    this.s("sale reversed");
                    this.$emit("dismiss-details");
                } else {
                    Swal.fire({
                        title: "Etims Error?",
                        showDenyButton: true,
                        showCancelButton: false,
                        confirmButtonText: "Re-Entry",
                        denyButtonText: `Cancel`,
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {
                            this.validateEtimsCreditnote();
                        } else if (result.isDenied) {
                            //Swal.fire('Canceled', '', 'info')
                        } else {
                            Swal.fire("Canceled", "", "info");
                        }
                    });
                }
            } else {
                this.swr("Server error occurred contact system administrator");
            }
        },
        async deleteSale() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "post",
                    "data/pos_sale/reverseEtimsSale",
                    {
                        ...this.form_data,
                        order_data: this.order_data,
                    }
                );
                if (res.status == 201) {
                    this.s("sale reversed");
                    this.hideLoader()
                    this.$emit("dismiss-details");
                    return;
                }
                if (res.status === 200) {
                    this.etims_response_data = res.data;

                    setTimeout(() => {
                        this.validateEtimsCreditnote();
                    }, 2000);
                } else {
                    this.swr(
                        "Server error occurred contact system administrator"
                    );
                }
            }
        },
        async fetchDetails() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/fetchSaleItems", {
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
