<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <v-btn
                    class="ma-2 v-btn-primary"
                    @click="$emit('dashboard-active')"
                    color="primary"
                    dark
                >
                    <v-icon dark left> mdi-arrow-left </v-icon>
                    Back
                </v-btn>

                <div class="card">
                    <div class="card-header">Sales Details</div>

                    <div class="card-body">
                        <div class="col-md-4 float-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>

                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Sales Person</th>
                                    <th>Cashier</th>
                                    <th scope="col">Receipt</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Cash Payments</th>

                                    <th scope="col">Mpesa Payments</th>
                                    <th scope="col">Card Payments</th>

                                    <th scope="col">Ref No.</th>
                                    <th scope="col">TOTAL</th>
                                    <th scope="col">Vat Total</th>

                                    <th></th>
                                </tr>
                            </thead>
                            <tbody v-if="summary_details_data">
                                <tr
                                    class="small-tr"
                                    v-for="(
                                        data, i
                                    ) in summary_details_data.data"
                                    :key="i"
                                >
                                    <td>{{ data.user.name }}</td>
                                    <td>{{ data.cashier.name }}</td>
                                    <td>
                                        {{ data.receipt_no }}
                                    </td>
                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                round(
                                                    data.total_cash_pay +
                                                        data.total_receipt_balance
                                                )
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                round(data.total_mpesa_pay)
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                round(data.total_card_pay)
                                            )
                                        }}
                                    </td>

                                    <td>
                                        {{ data.payment_ref }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(round(data.vat_total))
                                        }}
                                    </td>

                                    <td>
                                        <!-- <button
                                            v-if="isWritePermitted"
                                            @click="viewReceiptDetails(data)"
                                            class="btn btn-primary btn-sm"
                                        >
                                            View Receipt
                                        </button> -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="summary_details_data !== null"
                            v-bind:results="summary_details_data"
                            v-on:get-page="getSaleSummaryDetails"
                        ></pagination>
                        <div class="d-flex flex-row">
                            <export-excel
                                v-if="isWritePermitted"
                                class="btn btn-default btn-export ml-2"
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                name="filename.xls"
                            >
                                <button class="btn btn-secondary custom-button">
                                    EXPORT EXCEL
                                </button>
                            </export-excel>
                            <export-excel
                                v-if="isWritePermitted"
                                class="btn btn-default btn-export ml-2"
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="filename.xls"
                            >
                                <button class="btn btn-info custom-button">
                                    EXPORT CSV
                                </button>
                            </export-excel>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="sale_items_modal" title="Receipt Details">
            <div class="row">
                <div class="col-9">
                    <center>
                        <v-progress-circular
                            v-if="isLoading"
                            indeterminate
                            color="primary"
                        ></v-progress-circular>
                    </center>
                </div>
                <div class="col-11">
                    <div id="printMe">
                        <table width="100%" class="head">
                            <tr>
                                <td
                                    class="printer-header"
                                    style="font-size: 1.4rem"
                                >
                                    <center>
                                        {{ branchInfo.business_name }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td class="printer-header">
                                    <center>
                                        {{ branchInfo.name }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td class="printer-header">
                                    <center>
                                        {{
                                            branchInfo.address
                                                ? "Address: " +
                                                  branchInfo.address
                                                : ""
                                        }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td class="printer-header">
                                    <center>
                                        {{ branchInfo.postal_address }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td class="printer-header">
                                    <center>
                                        {{
                                            branchInfo.phone_no
                                                ? "TEL: " + branchInfo.phone_no
                                                : ""
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td class="printer-header">
                                    <center>
                                        {{
                                            new Date()
                                                | moment(" YYYY-MM-DD, h:mm")
                                        }}
                                    </center>
                                </td>
                            </tr>
                        </table>

                        <div>
                            <table width="100%">
                                <tr>
                                    <td>REPRINT:</td>
                                </tr>
                                <tr>
                                    <td>
                                        Cashier :
                                        {{ userInfo.name }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        Sales Receipt :
                                        {{ checkout_form_data.receipt_no }}
                                    </td>
                                </tr>
                            </table>
                            <table
                                v-if="checkout_form_data.customer_pin"
                                width="100%"
                            >
                                <tr>
                                    <td>
                                        Customer PIN:{{
                                            checkout_form_data.customer_pin
                                        }}
                                    </td>
                                </tr>
                            </table>
                            <table width="100%">
                                <tr>
                                    <td>
                                        <div class="top_row">
                                            <div style="width: 20%">ITEM</div>
                                            <div style="width: 20%">SIZE</div>
                                            <div style="width: 20%">QTY</div>
                                            <div style="width: 20%">AMOUNT</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr
                                    v-for="(data, i) in sales_receipt_data"
                                    :key="i"
                                >
                                    <td>
                                        <div class="top_row">
                                            <div style="width: 80%">
                                                {{
                                                    data.stock.product_name
                                                        
                                                }}
                                            </div>
                                            <div style="width: 20%">
                                                {{ cashFormatter(data.row_total) }}
                                                {{
                                                    data.stock
                                                        ? data.stock.tax_dept
                                                            ? data.stock
                                                                  .tax_dept
                                                                  .tax_code
                                                            : ""
                                                        : ""
                                                }}
                                            </div>
                                        </div>
                                        <div class="top_row">
                                            <div style="width: 20%">
                                                {{
                                                  data.stock.computed
                                                              
                                                        
                                                }}
                                            </div>
                                            <div style="width: 20%">
                                                {{
                                                    data.stock
                                                        ? data.stock.size
                                                        : ""
                                                }}
                                            </div>
                                            <div style="width: 20%">
                                                {{ data.qty }} X
                                            </div>
                                            <div style="width: 20%">
                                                {{ data.price }}
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table>
                                <tr>
                                    <td class="printer-footer">
                                        <center>
                                            <p class="t-amount">
                                                TAX AMOUNT:
                                                {{
                                                    cashFormatter(
                                                        round(
                                                            checkout_form_data.vat_paid
                                                        )
                                                    )
                                                }}
                                            </p>
                                        </center>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="printer-footer">
                                        <center>
                                            <h3 class="t-amount">
                                                RECEIPT TOTAL:
                                                {{
                                                    cashFormatter(
                                                        checkout_form_data.receipt_total
                                                    )
                                                }}
                                            </h3>
                                        </center>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <center
                                            class="t-amount"
                                            v-if="
                                                checkout_form_data.cash_pay > 0
                                            "
                                        >
                                            CASH:
                                            {{
                                                cashFormatter(
                                                    round(
                                                        checkout_form_data.cash_pay
                                                    )
                                                )
                                            }}
                                        </center>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <center
                                            class="t-amount"
                                            v-if="
                                                checkout_form_data.mpesa_pay > 0
                                            "
                                        >
                                            M-PESA:
                                            {{
                                                cashFormatter(
                                                    round(
                                                        checkout_form_data.mpesa_pay
                                                    )
                                                )
                                            }}
                                        </center>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <center
                                            class="t-amount"
                                            v-if="
                                                checkout_form_data.card_pay > 0
                                            "
                                        >
                                            Card:
                                            {{
                                                cashFormatter(
                                                    round(
                                                        checkout_form_data.card_pay
                                                    )
                                                )
                                            }}
                                        </center>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <center
                                            class="t-amount"
                                            v-if="
                                                checkout_form_data.cheque_payment >
                                                0
                                            "
                                        >
                                            Cheque:
                                            {{
                                                cashFormatter(
                                                    round(
                                                        checkout_form_data.cheque_payment
                                                    )
                                                )
                                            }}
                                            <br />
                                            Cheque No:
                                            {{ checkout_form_data.cheque_no }}
                                        </center>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        Goods once sold will not be
                                        returned,prices where<br />, applicable
                                    </td>
                                </tr>
                                <tr>
                                    ================================================
                                </tr>
                                <tr>
                                    <td>Thank you for shopping with us</td>
                                </tr>
                                <tr>
                                    <td>Served By: {{ sales_person }}</td>
                                </tr>
                                <tr>
                                    ================================================
                                </tr>
                                <tr>
                                    <td>
                                        <div v-if="checkout_form_data.qr_link">
                                            <center>
                                                <qrcode-vue
                                                    :value="
                                                        checkout_form_data.qr_link
                                                    "
                                                    :size="150"
                                                    level="H"
                                                ></qrcode-vue>

                                                <br />

                                                <span>
                                                    CU SERIAL NO:

                                                    {{
                                                        checkout_form_data.cu_serial_number
                                                    }} </span
                                                ><br />
                                                <span>
                                                    CU INVOICE NO:

                                                    {{
                                                        checkout_form_data.cu_invoice_number
                                                    }} </span
                                                ><br />
                                                <span>
                                                    CU DATETIME:
                                                    {{
                                                        checkout_form_data.cu_timestamp
                                                    }}</span
                                                >
                                            </center>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <button
                        v-if="isUpdatePermitted"
                        class="btn btn-primary"
                        @click="reprintReceipt()"
                    >
                        REPRINT
                    </button>
                </div>
            </div>
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import print from "print-js";
import Pagination from "../../../utilities/Pagination.vue";
export default {
    props: ["order_data_details"],
    data() {
        return {
            summary_details_data: null,
            search: "",
            print_val: "",
            sales_person: "",
            sale_items_modal: false,
            sales_receipt_data: [],
            print_status: ["printed", "unprinted"],
            checkout_form_data: {
                receipt_no: null,
                receipt_total: 0,
                receipt_balance: 0,
                cash_pay: 0,
                card_pay: 0,
                mpesa_pay: 0,
                cheque_payment: 0,
                cheque_no: "",
                vat_paid: 0,
                purchase_date: null,
                branch_id: null,

                cu_invoice_number: null,
                cu_serial_number: null,
                cu_timestamp: null,
                qr_link: null,
                customer_pin: null,
            },
            data: null,

            isLoading: false,
        };
    },
    components: {
        Pagination,
    },
    mounted() {
        console.log(JSON.stringify(this.order_data_details));
        this.getSaleSummaryDetails(1);
    },
    methods: {
        onChange(event) {
            console.log(event.target.value);
        },
        reprintReceipt() {
            printJS("printMe", "html");
        },
        async viewReceiptDetails(data) {
            this.checkout_form_data.cu_invoice_number = data.cu_invoice_number;
            this.checkout_form_data.cu_serial_number = data.cu_serial_number;

            this.checkout_form_data.cu_timestamp = data.cu_timestamp;
            this.checkout_form_data.customer_pin = data.customer_pin;
            this.sales_person = data.user ? data.user.name : this.userInfo.name;
            this.isLoading = true;
            const res = await this.getApi("data/sales/fetchSoldReceiptOrder", {
                params: {
                    order_no:data.order_no,
                },
            });
            this.isLoading = false;
            this.checkout_form_data.receipt_no = data.receipt_no;
            this.checkout_form_data.purchase_date = this.getCurrentDate();

           
            this.sales_receipt_data = res.data;

            this.calculateTotal();

            this.checkout_form_data.cash_pay = data.cash_pay;
            this.checkout_form_data.mpesa_pay = data.mpesa_pay;
            this.checkout_form_data.card_pay = data.card_pay;
            this.checkout_form_data.cheque_payment = data.cheque_payment;
            this.checkout_form_data.cheque_no = data.cheque_no;
             this.sale_items_modal = true;
        },
        calculateTotal() {
            var total = 0;
            var vat_total = 0;
            for (var i = 0; i < this.sales_receipt_data.length; i++) {
                total = total + this.sales_receipt_data[i].row_total;
                vat_total += this.sales_receipt_data[i].row_vat
                   
            }

            this.checkout_form_data.receipt_total = total;
            this.checkout_form_data.receipt_balance = total;
            //  var vat_paid = total - total * 0.862068965;
            this.checkout_form_data.vat_paid = vat_total;
        },
        async fetchExcel() {
            this.showLoader();
            const currentRoute = this.$route.name;
            const res = await this.getApi("data/sale/fetchAllSaleDetails", {
                params: {
                    currentRoute: currentRoute,
                    paid_date: this.order_data_details.paid_date,
                    search: this.search.length >= 4 ? this.search : "",
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        receipt_no: array_item.receipt_no,
                        created_at: array_item.receipt_no,
                        seller_name: array_item.user.name,
                        cash_pay: array_item.cash_pay,
                        mpesa_pay: array_item.mpesa_pay,
                        card_pay: array_item.card_pay,
                        receipt_total: array_item.receipt_total,
                    });
                });

                return data_array;
            } else {
                this.swr("Server error please contact administrator");
            }
        },
        async getSaleSummaryDetails(page) {
            this.showLoader();

            const res = await this.getApi("data/sales/fetchSaleDetails", {
                params: {
                    page,

                    paid_date: this.order_data_details.paid_date,
                    search: this.search.length >= 4 ? this.search : "",
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.summary_details_data = res.data;
            } else {
                this.swr("Server error please contact administrator");
            }
        },
        async searchRecords() {
            const currentRoute = this.$route.name;
            let page = 1;
            const res = await this.getApi("data/sales/fetchSaleDetails", {
                params: {
                    page,

                    search: this.search,
                    paid_date: this.order_data_details.paid_date,
                    currentRoute,
                },
            });
            if (res.status === 200) {
                this.summary_details_data = res.data;
            }
        },
    },
    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.searchRecords();
            }, 500),
        },
    },
    computed: {
        ...mapGetters({
            userInfo: "getUser",
            branchInfo: "getBranch",
        }),
    },
};
</script>
<style scoped>
.printer-header {
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}

.top_row {
    display: table;
    width: 100%;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
</style>
