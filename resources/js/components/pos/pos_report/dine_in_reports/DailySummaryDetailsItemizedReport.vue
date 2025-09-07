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
                    <div class="card-header">
                        <h3>
                            Sales With Item Details On
                            {{ order_data_details.paid_date }}
                        </h3>
                    </div>

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
                                    <th>Created at</th>
                                    <th scope="col">Sales Person</th>
                                    <th>Item</th>
                                    <th>
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_sum_qty
                                            "
                                            sort-key="sort_sum_qty"
                                            @update-sort="updateSortParameter"
                                        />Items Qty Sold
                                    </th>
                                    <th>
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_total_purchase_price
                                            "
                                            sort-key="sort_total_purchase_price"
                                            @update-sort="updateSortParameter"
                                        />
                                        Total Cost of Goods Sold
                                    </th>
                                    <th>
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_sum_total_sales
                                            "
                                            sort-key="sort_sum_total_sales"
                                            @update-sort="updateSortParameter"
                                        />
                                        Total Sales
                                    </th>
                                    <th>
                                        <sort-buttons
                                            :initial-sort="
                                                sort_params.sort_sum_profit
                                            "
                                            sort-key="sort_sum_profit"
                                            @update-sort="updateSortParameter"
                                        />
                                        Profit
                                    </th>
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
                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>
                                    <td>{{ data.user.name }}</td>
                                    <td>{{ data.stock.name }}</td>
                                    <td>{{ data.sum_qty }}</td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.total_purchase_price
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.sum_total_sales)
                                        }}
                                    </td>
                                    {{
                                        data.sum_profit < 0
                                            ? `(${cashFormatter(
                                                  data.sum_profit
                                              )})`
                                            : cashFormatter(data.sum_profit)
                                    }}
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
                            <button
                                class="btn btn-primary"
                                @click="printeQtyReport()"
                            >
                                Print Qty Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4" v-if="show_print">
                <div id="printMe">
                    <table  class="head">
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
                                            ? "Address: " + branchInfo.address
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
                                    Printed On
                                    {{
                                        new Date() | moment(" YYYY-MM-DD, h:mm")
                                    }}
                                </center>
                                <br />
                                <center>
                                    Report For Date
                                    {{ order_data_details.paid_date }}
                                </center>
                            </td>
                        </tr>
                    </table>

                    <table width="50%">
                        <tr>
                            <td>ITEM</td>
                            <td>Qty</td>
                            <td>Total</td>
                        </tr>
                        <tr v-for="(data, i) in item_array" :key="i">
                            <td style="font-family: Courier New, Courier, monospace;">
                                {{ data.stock.product_name }}
                            </td>
                            <td>
                                {{ data.sum_qty }}
                            </td>

                            <td>{{ data.sum_total_sales }}</td>
                        </tr>
                    </table>

                    <table>
                        <tr>
                            ================================================
                        </tr>
                        <tr>
                            <td class="printer-footer">
                                <b>
                                    TOTAL SALES AMOUNT:
                                    {{
                                        cashFormatter(
                                            order_data_details.sum_receipt_total
                                        )
                                    }}</b
                                >
                            </td>
                        </tr>
                        <tr>
                            ================================================
                        </tr>
                        <tr>
                            <td class="printer-footer">
                                <b>
                                    TOTAL CASH RECEIVED:
                                    {{
                                        cashFormatter(
                                            order_data_details.sum_cash_pay
                                        )
                                    }}</b
                                >
                            </td>
                        </tr>
                        <tr>
                            ================================================
                        </tr>
                        <tr>
                            <td class="printer-footer">
                                <b>
                                    TOTAL MPESA RECEIVED:
                                    {{
                                        cashFormatter(
                                            order_data_details.sum_mpesa_pay
                                        )
                                    }}</b
                                >
                            </td>
                        </tr>
                        <tr>
                            ================================================
                        </tr>
                        <tr>
                            <td class="printer-footer">
                                <b>
                                    TOTAL CARD RECEIVED:
                                    {{
                                        cashFormatter(
                                            order_data_details.sum_card_pay
                                        )
                                    }}</b
                                >
                            </td>
                        </tr>
                         <tr>
                            ================================================
                        </tr>
                         <tr>
                            <td class="printer-footer">
                                <b>
                                    TOTAL CREDIT :
                                    {{
                                        cashFormatter(
                                            order_data_details.sum_credit_pay
                                        )
                                    }}</b
                                >
                            </td>
                        </tr>

                        <tr>
                            <td>Printed By: {{ curr_user }}</td>
                        </tr>
                        <tr>
                            ================================================
                        </tr>
                    </table>
                </div>
            </div>
        </div>
 <div>
       <!-- <button @click="printReceipt">Print Receipt</button> -->
       <!-- <div id="receipt" class="receipt">
           <h2 class="header">Store Name</h2>
           <p>Date: {{ new Date().toLocaleDateString() }}</p>
           <table class="receipt-table">
               <tr>
                   <th>Item</th>
                   <th>Price</th>
               </tr>
               <tr v-for="item in items" :key="item.name">
                   <td>{{ item.name }}</td>
                   <td>{{ item.price }}</td>
               </tr>
           </table>
           <p class="footer">Thank you for your purchase!</p>
       </div> -->
   </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import print from "print-js";
import Pagination from "../../../utilities/Pagination.vue";
import SortButtons from "../../../utilities/SortButtons.vue";
export default {
    props: ["order_data_details"],
    data() {
        return {
              items: [
               { name: "Item 1", price: "$10" },
               { name: "Item 2", price: "$15" }
           ],
            curr_user:this.$store.state.user.name,
            item_array: [],
            summary_details_data: null,
            search: "",
            print_val: "",
            sales_person: "",
            sale_items_modal: false,
            sales_receipt_data: [],
            print_status: ["printed", "unprinted"],
            sort_params: {
                sort_sum_qty: "",
                sort_sum_total_sales: "DESC",
                sort_total_purchase_price: "",
                sort_sum_profit: "",
            },
            params: {
                search: "",
            },
        
            show_print: false,
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

            isLoading: true,
        };
    },
    components: {
        Pagination,
        SortButtons,
    },
    mounted() {
        console.log(JSON.stringify(this.order_data_details));
        this.getSaleSummaryDetails(1);
    },
    methods: {
          printReceipt() {
           const printWindow = window.open('', '_blank', 'width=500,height=700');
           printWindow.document.write('<html><head><title>Receipt</title>');
           printWindow.document.write('<link rel="stylesheet" href="print.css">');
           printWindow.document.write('</head><body>');
           printWindow.document.write(document.getElementById('printMe').innerHTML);
           printWindow.document.write('</body></html>');
           printWindow.document.close();
           printWindow.focus();
           printWindow.print();
           printWindow.close();
       },
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        onChange(event) {
            console.log(event.target.value);
        },
        async printeQtyReport() {
            this.showLoader();
            const currentRoute = this.$route.name;
            const res = await this.getApi(
                "data/sales/fetchSaleDetailsWithItems",
                {
                    params: {
                        currentRoute: currentRoute,

                        ...this.sort_params,
                        paid_date: this.order_data_details.paid_date,
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.item_array = res.data;
                this.show_print = true;

                setTimeout(() => {
                    printJS("printMe", "html");

                    this.show_print = false;
                }, 1000);
            } else {
                this.form_error(res);
            }
        },

        async fetchExcel() {
            this.showLoader();
            const currentRoute = this.$route.name;
            const res = await this.getApi(
                "data/sales/fetchSaleDetailsWithItems",
                {
                    params: {
                        currentRoute: currentRoute,

                        ...this.sort_params,
                        paid_date: this.order_data_details.paid_date,
                        ...this.params,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data_array = [];

                res.data.map((data) => {
                    data_array.push({
                        created_at: this.formatDateTime(data.created_at),

                        user: data.user.name,
                        item_code: data.stock.code,
                        item: data.stock.name,
                        sum_qty: data.sum_qty,
                        total_purchase_price: data.total_purchase_price,
                        sum_total_sales: data.sum_total_sales,
                        sum_profit: data.sum_profit,
                    });
                });

                return data_array;
            } else {
                this.swr("Server error please contact administrator");
            }
        },
        async getSaleSummaryDetails(page) {
            this.isLoading ? this.showLoader() : "";

            const res = await this.getApi(
                "data/sales/fetchSaleDetailsWithItems",
                {
                    params: {
                        page,
                        ...this.sort_params,
                        paid_date: this.order_data_details.paid_date,
                        ...this.params,
                    },
                }
            );
            this.isLoading ? this.hideLoader() : "";
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
        search_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.getSaleSummaryDetails(1);
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.getSaleSummaryDetails(1);
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

.top_row {
    display: table;
    width: 100%;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
@media print {
   .receipt  {
       font-family: "Courier New", monospace;
       font-size: 12px;
       width: 80mm;
   }
   .printer-header {
     font-family: "Courier New", monospace;
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}

   .receipt .header {
       text-align: center;
   }
   .receipt-table th,
   .receipt-table td {
       border: 1px solid black;
       padding: 5px;
   }
}
</style>
