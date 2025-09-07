<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header"><h3>Sales Exchange</h3></div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <div class="form-group d-flex flex-column">
                                        <label>Exchange Date *</label>
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
                                <div class="form-group">
                                    <div class="form-group d-flex flex-column">
                                        <label>CU Invoice No </label>
                                        <input
                                            placeholder="cu invoice no."
                                            autocomplete="off"
                                            class="form-control"
                                            v-model="form_data.cu_invoice_no"
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
                                <div class="col-md-12">
                                    <table
                                        class="table table-sm table-bordered custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
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
                                <div class="col-md-4">
                                    <div
                                        style="
                                            height: 400px;
                                            overflow-x: hidden;
                                            overflow-y: auto;
                                        "
                                    >
                                        <h3><b>Replacement Sale</b></h3>
                                        <div class="table-responsive">
                                            <table
                                                class="table table-sm table-striped table-bordered custom-table"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th>Batchno</th>
                                                        <th scope="col">
                                                            Code
                                                        </th>
                                                        <th scope="col">
                                                            Item Name
                                                        </th>

                                                        <th scope="col">
                                                            Sell Price
                                                        </th>
                                                        <th scope="col">Qty</th>
                                                        <th></th>
                                                        <th scope="col">
                                                            Sub Total
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        class="small-tr"
                                                        v-for="(
                                                            value, i
                                                        ) in order_data_offline"
                                                        :key="i"
                                                    >
                                                        <td>
                                                            {{ value.batch_no }}
                                                        </td>
                                                        <td>
                                                            {{ value.code }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                value.product_name
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                value.selling_price
                                                            }}
                                                        </td>
                                                        <td>{{ value.qty }}</td>
                                                        <td>
                                                            <div
                                                                class="d-flex justify-content-around"
                                                            >
                                                                <span>
                                                                    <v-icon
                                                                        v-if="
                                                                            isUpdatePermitted
                                                                        "
                                                                        @click="
                                                                            addReduce(
                                                                                value,
                                                                                'add'
                                                                            )
                                                                        "
                                                                        style="
                                                                            color: green;
                                                                        "
                                                                        medium
                                                                        >{{
                                                                            icons.mdiPlusThick
                                                                        }}</v-icon
                                                                    >
                                                                </span>
                                                                <span>
                                                                    <v-icon
                                                                        v-if="
                                                                            isUpdatePermitted
                                                                        "
                                                                        @click="
                                                                            addReduce(
                                                                                value,
                                                                                'reduce'
                                                                            )
                                                                        "
                                                                        style="
                                                                            color: black;
                                                                        "
                                                                        medium
                                                                        >{{
                                                                            icons.mdiMinusThick
                                                                        }}</v-icon
                                                                    >
                                                                </span>
                                                                <b>
                                                                    <v-icon
                                                                        v-if="
                                                                            isDeletePermitted
                                                                        "
                                                                        @click="
                                                                            deleteRecord(
                                                                                value,
                                                                                i
                                                                            )
                                                                        "
                                                                        style="
                                                                            color: red;
                                                                        "
                                                                        medium
                                                                        >{{
                                                                            icons.mdiTrashCanOutline
                                                                        }}</v-icon
                                                                    ></b
                                                                >
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {{
                                                                value.row_amount
                                                            }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div
                                            class="d-flex justify-content-end mt-2"
                                        >
                                            <span>
                                                <b>
                                                    TOTAL:
                                                    {{
                                                        cashFormatter(
                                                            form_data.sales_total_amount
                                                        )
                                                    }}</b
                                                >
                                            </span>
                                        </div>
                                        <div
                                            class="d-flex justify-content-end mt-1"
                                        >
                                            <span>
                                                <b>
                                                    TAX:
                                                    {{
                                                        cashFormatter(
                                                            form_data.sale_total_tax
                                                        )
                                                    }}</b
                                                >
                                            </span>
                                        </div>
                                    </div>
                                    <!-- //end cart items -->
                                </div>
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
                                            <td colspan="4"></td>
                                            <td>
                                                <v-btn
                                                v-if="false"
                                                    color="info"
                                                    small
                                                    @click="
                                                        exchangeWithPartialOriginalReceipt()
                                                    "
                                                    :loading="btn_loading"
                                                    >Generate Credit Note</v-btn
                                                >
                                            </td>
                                            <td>
                                                <v-btn
                                                    color="warning"
                                                    small
                                                    @click="completePrint()"
                                                    :loading="btn_loading"
                                                    >Complete</v-btn
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
                <search-replacement
                    v-if="active.stock_search"
                    v-on:dashboard-active="setActiveRefresh"
                />
            </div>
            <div class="row">
                <top-up-exchange-cashout
                    v-if="active.cashout"
                    @dismiss-diag="dismissDiag"
                    :order_data_offline="order_data_offline"
                    :formData="form_data"
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
export default {
    components: { PrintPOSCreditNote, SearchReplacement, TopUpExchangeCashout },
    props: ["receipt_details"],
    data() {
        return {
            active: {
                dashboard: true,
                stock_search: false,
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
            order_data_offline: [],
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

        order_data_offline: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.generateCreditNo();
                this.form_data.sales_total_amount =
                    this.order_data_offline.reduce(function (sum, val) {
                        return sum + val.row_amount;
                    }, 0);
                this.form_data.sale_total_tax = this.order_data_offline.reduce(
                    function (sum, val) {
                        return sum + val.row_vat;
                    },
                    0
                );
            }, 500),
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
        searchStockToReplace() {
            this.setActiveComponent("stock_search");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        generateReceiptNo() {
            const transTime = this.$moment().format("YYMMDDhhmm");
            const random = Math.floor(Math.random() * 9000 + 1000);
            const receptNo = random + "" + transTime;
            if (this.order_data_offline.length > 0) {
                this.form_data.receipt_no = this.order_data_offline[0].order_no;
            } else {
                this.form_data.receipt_no = receptNo;
            }
        },
        dismissDiag() {
            //  this.setActive(this.active, "dashboard");

            this.$emit("dismiss-diag");
        },
        setActiveRefresh: function (data = null) {
            this.setActive(this.active, "dashboard");

            var value = data;
            this.generateReceiptNo();
            var row_amount = value.selling_price * value.qty;
            var row_vat = this.calculateTax(value.tax_rate, row_amount);
            this.order_data_offline.push({
                batch_no: value.batch_no,
                stock_id: value.id,
                code: value.code,
                product_name: value.product_name,
                selling_price: parseFloat(value.selling_price),
                tax_rate: value.tax_rate,
                tax_dept_id: value.tax_dept.id,
                row_amount: parseFloat(row_amount),
                row_vat: parseFloat(row_vat),
                qty: parseFloat(value.qty),
                order_date: this.getCurrentDate(),
                order_no: this.form_data.receipt_no,
                user_id: this.form_data.user_id,
                customer_id: value.customer_id,
                customer_name: value.customer_name,
                item_id: value.item_id,
            });

            let result = this.order_data_offline.reduce(
                (
                    r,
                    {
                        batch_no,
                        stock_id,
                        code,
                        product_name,
                        selling_price,
                        tax_rate,
                        tax_dept_id,
                        row_amount,
                        row_vat,
                        qty,
                        order_date,
                        order_no,
                        user_id,
                        customer_id,
                        customer_name,
                        item_id,
                    }
                ) => {
                    let key = `${stock_id}`;

                    if (r.has(key)) {
                        {
                            r.get(key).qty =
                                parseFloat(r.get(key).qty) + parseFloat(qty);
                            var amount =
                                r.get(key).selling_price * r.get(key).qty;
                            r.get(key).row_vat = this.calculateTax(
                                r.get(key).tax_rate,
                                amount
                            );
                            r.get(key).row_amount = amount;
                        }
                    } else {
                        r.set(key, {
                            stock_id,
                            selling_price,
                            batch_no,
                            tax_rate,
                            qty: qty,
                            stock_id,
                            code,
                            product_name,
                            selling_price,
                            tax_rate,
                            tax_dept_id,
                            row_amount,
                            row_vat,
                            qty,
                            order_date,
                            order_no,
                            user_id,
                            customer_id,
                            customer_name,
                            item_id,
                        });
                    }

                    return r;
                },
                new Map()
            );

            this.order_data_offline = [...result.values()];

            for (var i = 0; i < this.order_data_offline.length; i++) {
                this.order_data_offline[i].customer_id =
                    this.receipt_details.customer_id;
                this.order_data_offline[i].customer_name =
                    this.receipt_details.company_name;
            }
            console.log(JSON.stringify(this.order_data_offline));
            //  this.$store.commit("setOrderData", this.order_data_offline);
            this.s("Added...");
        },
        async exchangeWithPartialOriginalReceipt() {
            let totalCurrentReceipt =
                parseFloat(this.form_data.total_receipt) -
                parseFloat(this.form_data.total_cr) +
                parseFloat(this.form_data.sales_total_amount);
            let totalCurrentCreditNote = parseFloat(this.form_data.total_cr);

            console.log(
                "this.form_data.total_receipt " +
                    this.form_data.total_receipt +
                    " " +
                    totalCurrentReceipt
            );

            if (this.form_data.total_receipt != totalCurrentReceipt) {
                //exchange with original receipt
                this.btn_loading = true;
                const res = await this.callApi(
                    "POST",
                    "data/pos_credit/exchangeWithPartialOriginalReceipt",
                    {
                        ...this.form_data,
                        order_data_offline: this.order_data_offline,
                    }
                );
                this.btn_loading = false;
                if (res.status == 200) {
                    this.s("Completed");
                    this.$emit("dismiss-diag");
                } else {
                    this.form_error(res);
                }
            } else {
                this.errorNotifN("Use complete option");
            }
        },
        async completePrint() {
            //validate sale
            let totalCurrentReceipt =
                parseFloat(this.form_data.total_receipt) -
                parseFloat(this.form_data.total_cr) +
                parseFloat(this.form_data.sales_total_amount);
            let totalCurrentCreditNote = parseFloat(this.form_data.total_cr);

            if (totalCurrentReceipt > this.form_data.total_receipt) {
                this.form_data.topupReceiptAmount =
                    parseFloat(totalCurrentReceipt) -
                    parseFloat(this.form_data.total_receipt);

                this.setActive(this.active, "cashout");
                return;
            }
            if (this.form_data.total_receipt != totalCurrentReceipt) {
                this.errorNotifN(
                    "You need to add sale equal with receipt amount"
                );
                return;
            } else {
                //exchange with original receipt
                this.btn_loading = true;
                const res = await this.callApi(
                    "POST",
                    "data/pos_credit/exchangeWithOriginalReceipt",
                    {
                        ...this.form_data,
                        order_data_offline: this.order_data_offline,
                    }
                );
                this.btn_loading = false;
                if (res.status == 200) {
                    this.s("Completed");
                    this.$emit("dismiss-diag");
                } else {
                    this.form_error(res);
                }
            }
        },
        async credit() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/pos_credit/completeCr",
                {
                    ...this.form_data,
                    order_data_offline: this.order_data_offline,
                }
            );
            this.btn_loading = false;
            if (res.status == 200) {
                this.s("Completed");
                this.load_printer_component = true;
                setTimeout(() => {
                    //disable browser printing
                    this.$refs.ReceiptPrintComponent.printReceipt();
                    this.$emit("dismiss-diag");
                }, 1000);
            } else {
                this.form_error(res);
            }
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
        deleteRecord(data, i) {
            this.order_data_offline.splice(i, 1);
            this.$store.commit("setOrderData", this.order_data_offline);
        },
        addReduce(val, type) {
            var nwdata = this.order_data_offline
                .map((val) => val.stock_id)
                .indexOf(val.stock_id);
            if (type == "add") {
                this.order_data_offline[nwdata].qty =
                    parseFloat(this.order_data_offline[nwdata].qty) + 1;
            }
            if (type == "reduce") {
                if (val.qty < 2) {
                    return;
                }
                this.order_data_offline[nwdata].qty -= 1;
            }

            var amount =
                this.order_data_offline[nwdata].selling_price *
                this.order_data_offline[nwdata].qty;
            this.order_data_offline[nwdata].row_vat = this.calculateTax(
                val.tax_rate,
                amount
            );
            this.order_data_offline[nwdata].row_amount = amount;
        },
    },
};
</script>
