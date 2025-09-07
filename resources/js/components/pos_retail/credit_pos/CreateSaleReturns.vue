<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3>Sales Returns</h3></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-group d-flex flex-column">
                                        <label>Return Date *</label>
                                        <date-picker
                                            v-model="form_data.credit_date"
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-group d-flex flex-column">
                                        <label>Credit No *</label>
                                        <input
                                            class="form-control"
                                            v-model="form_data.credit_no"
                                            type="text"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <v-btn
                                    color="primary"
                                    @click="searchStockToReplace()"
                                    >Add Items</v-btn
                                >
                            </div>
                        </div>
                        <div class="card-body">
                            <h4><b>ORIGINAL ITEMS</b></h4>
                            <br /><br />
                            <div class="row">
                                <div class="col-md-8">
                                    <table
                                        class="table table-sm table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Name</th>
                                                <th scope="col">R.SP</th>
                                                <th>R.Qty</th>
                                                <th scope="col">R.Amount</th>
                                                <th>R.Vat</th>

                                                <th>C.Qty</th>
                                                <th>C.SP</th>
                                                <th>C.Amt</th>
                                                <th>Add Stock</th>
                                                <th scope="col"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    data, i
                                                ) in order_details"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ data.stock.code }}
                                                </td>
                                                <td>
                                                    {{
                                                        data.stock.product_name
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.selling_price
                                                        )
                                                    }}
                                                </td>
                                                <td>{{ data.receipt_qty }}</td>
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

                                                <td>
                                                    <input
                                                        type="number"
                                                        v-model="data.qty"
                                                    />
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.credit_sp
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.credit_sp *
                                                                data.qty
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <input
                                                        type="checkbox"
                                                        v-model="
                                                            data.deduct_stock
                                                        "
                                                    />
                                                </td>
                                                <td>
                                                    <v-btn
                                                        title="write"
                                                        v-if="isWritePermitted"
                                                        x-small
                                                        color="primary"
                                                        @click="
                                                            addCreditNote(data)
                                                        "
                                                        >Add</v-btn
                                                    >
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td><b>Total VAT</b></td>
                                                <td>
                                                    <b>{{
                                                        cashFormatter(
                                                            form_data.total_vat
                                                        )
                                                    }}</b>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td><b>Total Receipt</b></td>
                                                <td>
                                                    <b>{{
                                                        cashFormatter(
                                                            form_data.total_receipt
                                                        )
                                                    }}</b>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- start cart items -->
                            </div>
                            <h4><b>RETURNED ITEMS</b></h4>
                            <br /><br />
                            <div class="row">
                                <table
                                    class="table table-sm table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th scope="col">CR.SP</th>

                                            <th>CR.Qty</th>
                                            <th>CR.Vat</th>
                                            <th scope="col">CR.Amount</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in credit_data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.code }}
                                            </td>
                                            <td>
                                                {{ data.stock.product_name }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.credit_sp
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{ data.qty }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.credit_vat
                                                    )
                                                }}
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.credit_sp *
                                                            data.qty
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                <v-btn
                                                    title="write"
                                                    v-if="isDeletePermitted"
                                                    x-small
                                                    color="danger"
                                                    @click="
                                                        deleteRecords(
                                                            data.id,
                                                            i
                                                        )
                                                    "
                                                    >Delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td>
                                                <b> TOTAL TAX </b>
                                            </td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            form_data.total_vat_cr
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4"></td>
                                            <td>
                                                <b>TOTAL CREDIT</b>
                                            </td>
                                            <td>
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            form_data.total_cr
                                                        )
                                                    }}</b
                                                >
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="5"></td>
                                            <td>
                                                <v-btn
                                                    color="warning"
                                                    small
                                                    @click="
                                                        returnFundActivate()
                                                    "
                                                    :loading="btn_loading"
                                                    >REFUND</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <return-sales-credit-funds
                    v-if="active.return_funds"
                    :form_data="form_data"
                    :receipt_details="receipt_details"
                    v-on:dashboard-active="setActiveRefresh"
                />
            </div>
            <div class="row">
                <div class="col-4">
                    <PrintPOSCreditNote
                        ref="ReceiptPrintComponent"
                        v-if="load_printer_component"
                        :form_data="form_data"
                        :order_data="credit_data"
                    />
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
import {
    mdiBackburger,
    mdiNotebookEdit,
    mdiArrowRightBold,
    mdiPrinter,
    mdiArrowSplitVertical,
    mdiBookmarkOutline,
    mdiTrashCanOutline,
    mdiPlusThick,
    mdiMinusThick,
    mdiHome,
    mdiCashPlus,
    mdiAccountPlus,
    mdiArrowDownCircleOutline,
    mdiArrowUpCircleOutline,
} from "@mdi/js";
import SearchReplacement from "./SearchReplacement.vue";
import PrintPOSCreditNote from "./PrintPOSCreditNote.vue";
import TopUpExchangeCashout from "./TopUpExchangeCashout.vue";
import ReturnSalesCreditFunds from './ReturnSalesCreditFunds.vue';
export default {
    components: { PrintPOSCreditNote, SearchReplacement, TopUpExchangeCashout, ReturnSalesCreditFunds },
    props: ["receipt_details"],
    data() {
        return {
            active: {
                dashboard: true,
                return_funds: false,
            },
            topupReceiptAmount: 0,
            load_printer_component: false,
            order_details: [],
            form_data: {
                user_id: null,
                cu_invoice_no: "",
                credit_amount: 0,
                receipt_no: "",
                credit_no: null,
                credit_date: null,
                credit_vat: 0,
                total_cr: 0,
                total_vat_cr: 0,
                total_vat: 0,
                total_receipt: 0,
                sales_total_amount: 0,
                sale_total_tax: 0,
                topupReceiptAmount: 0,
            },

            isLoading: true,
            btn_loading: false,
            credit_data: [],
            icons: {
                mdiBackburger,
                mdiNotebookEdit,
                mdiArrowRightBold,
                mdiPrinter,
                mdiArrowSplitVertical,
                mdiBookmarkOutline,
                mdiTrashCanOutline,
                mdiPlusThick,
                mdiMinusThick,
                mdiHome,
                mdiCashPlus,
                mdiAccountPlus,
                mdiArrowDownCircleOutline,
                mdiArrowUpCircleOutline,
            },
        };
    },
    mounted() {
        this.form_data.credit_date = this.getCurrentDate();

        this.form_data.user_id = this.$store.state.user.id;
        this.generateCreditNo();
        this.concurrentApiReq();
    },
    watch: {
        order_details: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.form_data.total_receipt = this.order_details.reduce(
                    function (sum, val) {
                        return sum + val.credit_sp * val.qty;
                    },
                    0
                );
            }),
        },

        credit_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.generateCreditNo();
                this.form_data.total_cr = this.credit_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.credit_amount;
                },
                0);
                this.form_data.total_vat_cr = this.credit_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.credit_vat;
                },
                0);
            }, 500),
        },
    },
    methods: {
        setActiveRefresh(){
            this.setActiveComponent('dashboard')
            this.$emit('dismiss-diag')
        },
        returnFundActivate() {
            this.setActiveComponent("return_funds");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },

        dismissDiag() {
            //  this.setActive(this.active, "dashboard");

            this.$emit("dismiss-diag");
        },

        async deleteRecords(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/pos_credit/destroy/" + id,
                    {}
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.credit_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchReceiptInfo(),
                this.fetchPendingCr(),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },

        async addCreditNote(data) {
            this.showLoader();
            this.form_data.receipt_no = data.order_no;
            this.form_data.receipt_no = data.order_no;
            this.form_data.credit_amount = data.qty * data.credit_sp;
            this.form_data.credit_vat = this.calculateTax(
                data.tax_rate,
                this.form_data.credit_amount
            );

            const data2 = Object.assign(data, this.form_data);
            const res = await this.callApi(
                "post",
                "data/pos_credit/create",
                data2
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Added");
                this.credit_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchReceiptInfo() {
            const res = await this.getApi("data/sales/fetchSoldReceiptOrder", {
                params: {
                    order_no: this.receipt_details.order_no,
                },
            });

            if (res.status === 200) {
                this.order_details = this.modifiedOrders(res.data);
            } else {
                this.form_error(res);
            }
        },
        async fetchPendingCr() {
            const res = await this.getApi("data/pos_credit/fetchPendingCr", {
                params: {
                    receipt_no: this.receipt_details.order_no,
                },
            });

            if (res.status === 200) {
                this.credit_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        modifiedOrders(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    department: obj.name,
                    all_sale_details_report_id: obj.id,
                    receipt_qty: obj.qty,
                    qty: obj.qty,
                    selling_price: obj.price,
                    row_total: obj.row_total,
                    row_vat: obj.row_vat,
                    stock_id: obj.stock_id,
                    tax_rate: obj.stock.tax_dept.tax_rate,
                    stock: obj.stock,
                    credit_sp: obj.price,
                    credit_vat: 0,
                    credit_amount: obj.price * obj.qty,
                    cu_invoice_no: this.form_data.cu_invoice_no,
                    order_no: obj.order_no,
                    deduct_stock: true,
                };
            });
            return modif;
        },
        generateCreditNo() {
            const transTime = this.$moment().format("YYMMDDhhmm");
            const random = Math.floor(Math.random() * 9000 + 1000);
            const receptNo = random + "" + transTime;
            if (this.credit_data.length > 0) {
                this.form_data.credit_no = this.credit_data[0].credit_no;
            } else {
                this.form_data.credit_no = receptNo;
            }
        },
    },
};
</script>
