<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><b>Cashout / Payment</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div
                                    style="
                                        height: 300px;
                                        overflow-x: hidden;
                                        overflow-y: auto;
                                    "
                                >
                                    <div class="col-10">
                                        Waiter {{ order_data[0].user.name }}
                                    </div>
                                    <div
                                        class="d-flex flex-column mt-2 mb-2"
                                        v-for="(value, i) in order_data"
                                        :key="i"
                                    >
                                        <div class="row">
                                            <div class="col-5">
                                                <b>{{
                                                    value.stock
                                                        ? value.stock
                                                              .product_name
                                                        : "NA"
                                                }}</b>
                                            </div>
                                            <div class="col-2">
                                                <b> X </b>
                                                <b> {{ value.qty }}</b>
                                            </div>
                                            <div class="col-3">
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            value.row_total
                                                        )
                                                    }}</b
                                                >
                                            </div>
                                            <div
                                                class="col-4 d-flex justify-content-around"
                                            ></div>
                                        </div>
                                        <div
                                            class="d-flex justify-content-start"
                                        >
                                            <p class="mr-2">Unit Price /</p>

                                            <p>
                                                {{ cashFormatter(value.price) }}
                                            </p>
                                            <p class="ml-2">
                                                Guest
                                                <span
                                                    class="badge badge-secondary"
                                                    >{{ value.no_guest }}</span
                                                >
                                            </p>
                                            <p class="ml-2">
                                                {{ value.notes }}
                                            </p>
                                        </div>
                                        <hr />
                                    </div>

                                    <div
                                        class="d-flex justify-content-end mt-2"
                                    >
                                        <span>
                                            <b>
                                                TOTAL:
                                                {{
                                                    cashFormatter(total_order)
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
                                                    cashFormatter(total_tax)
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6" style="background: #d7ccc8">
                                <fieldset class="border mb-4">
                                    <legend class="text-center">
                                        <b>Payments</b>
                                    </legend>
                                    <div
                                        class="col-md-12 d-flex flex-column justify-content-between"
                                    >
                                        <div class="form-group d-flex flex-row">
                                            <label for="">Cash Pay</label>
                                            <input
                                                type="number"
                                                v-model="form_data.cash_pay"
                                                placeholder="Cash Pay"
                                                class="form-control"
                                            />
                                        </div>
                                        <div class="form-group d-flex flex-row">
                                            <label for="">Mpesa</label>
                                            <input
                                                type="number"
                                                v-model="form_data.mpesa_pay"
                                                placeholder="Mpesa"
                                                class="form-control"
                                            />
                                        </div>
                                        <div class="form-group d-flex flex-row">
                                            <label for="">Card</label>
                                            <input
                                                type="number"
                                                v-model="form_data.card_pay"
                                                placeholder="Card"
                                                class="form-control"
                                            />
                                        </div>
                                        <div class="form-group d-flex flex-row">
                                            <label for="">ref No</label>
                                            <input
                                                type="text"
                                                v-model="form_data.payment_ref"
                                                placeholder="Reference No"
                                                class="form-control"
                                            />
                                        </div>
                                        <div
                                            class="d-flex flex-row justify-content-between"
                                        >
                                            <button
                                                v-if="isDownloadPermitted"
                                                class="btn btn-secondary"
                                                @click="printReceipt(true)"
                                            >
                                                SAVE & PRINT
                                            </button>

                                            <button
                                                class="btn btn-primary"
                                                @click="printReceipt(false)"
                                            >
                                                SAVE
                                            </button>
                                        </div>
                                        <div
                                            class="d-flex flex-row mt-3 justify-content-between"
                                        >
                                            <button
                                                v-if="isApprovePermitted"
                                                class="btn btn-warning"
                                                @click="cashoutCredit()"
                                            >
                                                Clear with Credit
                                            </button>
                                            <button
                                                v-if="isApprovePermitted"
                                                class="btn btn-warning"
                                                @click="
                                                    showEmployeeModal = true
                                                "
                                            >
                                                Deduct From Employee
                                            </button>
                                        </div>
                                    </div>
                                </fieldset>
                                <div
                                    class="col-md-11"
                                    style="background: #827717"
                                >
                                    <h3 class="t-amount">
                                        RECEIPT TOTAL:
                                        {{
                                            cashFormatter(
                                                form_data.receipt_total
                                            )
                                        }}
                                    </h3>
                                </div>

                                <div
                                    class="col-md-11"
                                    style="background: #827717"
                                >
                                    <h3 class="t-amount">
                                        BALANCE:
                                        {{
                                            cashFormatter(
                                                form_data.receipt_balance
                                            )
                                        }}
                                    </h3>
                                </div>
                                <div
                                    class="col-md-11"
                                    style="background: #827717"
                                >
                                    <p class="t-amount">
                                        TAX AMOUNT():
                                        {{
                                            cashFormatter(
                                                round(form_data.total_vat)
                                            )
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="col-md-11"
                                    style="background: #827717"
                                >
                                    <p
                                        class="t-amount"
                                        v-if="form_data.cash_pay > 0"
                                    >
                                        CASH:
                                        {{
                                            cashFormatter(
                                                round(form_data.cash_pay)
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="t-amount"
                                        v-if="form_data.mpesa_pay > 0"
                                    >
                                        M-PESA:
                                        {{
                                            cashFormatter(
                                                round(form_data.mpesa_pay)
                                            )
                                        }}
                                    </p>
                                    <p
                                        class="t-amount"
                                        v-if="form_data.card_pay > 0"
                                    >
                                        Card:
                                        {{
                                            cashFormatter(
                                                round(form_data.card_pay)
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-5">
                <div id="printReceipt">
                    <table width="100%" class="head">
                        <tr>
                            <td
                                class="printer-header"
                                style="font-size: 2.4rem"
                            >
                                <center>
                                    <h3><b>Sales Receipt</b></h3>
                                </center>
                            </td>
                        </tr>
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
                                    {{
                                        new Date() | moment(" YYYY-MM-DD, h:mm")
                                    }}
                                </center>
                            </td>
                        </tr>
                    </table>

                    <div>
                        <table width="100%">
                            <tr>
                                <td>
                                    Served By :
                                    {{ waiter }}
                                </td>
                            </tr>

                            <tr>
                                <td v-if="order_data[0]">
                                    receipt no :
                                    {{ form_data.receipt_no }}
                                </td>
                            </tr>
                        </table>

                        <table width="100%">
                            <tr>
                                <td>
                                    <div class="top_row">
                                        <div style="width: 20%">ITEM</div>
                                        <div style="width: 20%"></div>
                                        <div style="width: 20%">QTY</div>
                                        <div style="width: 20%">AMOUNT</div>
                                    </div>
                                    <hr />
                                </td>
                            </tr>
                            <tr v-for="(data, i) in order_data" :key="i">
                                <td>
                                    <div class="top_row">
                                        <div style="width: 80%">
                                            {{
                                                data.stock
                                                    ? data.stock.product_name
                                                    : ""
                                            }}
                                        </div>
                                        <div style="width: 20%">
                                            {{ cashFormatter(data.row_total) }}
                                        </div>
                                    </div>
                                    <div class="top_row">
                                        <div style="width: 20%">
                                            {{
                                                data.stock
                                                    ? data.stock.code
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

                        <hr />

                        <table>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            TAX AMOUNT :
                                            {{
                                                cashFormatter(
                                                    round(form_data.total_vat)
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
                                                    form_data.receipt_total
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
                                        v-if="form_data.cash_pay > 0"
                                    >
                                        CASH:
                                        {{
                                            cashFormatter(
                                                round(form_data.cash_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.mpesa_pay > 0"
                                    >
                                        M-PESA:
                                        {{
                                            cashFormatter(
                                                round(form_data.mpesa_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.card_pay > 0"
                                    >
                                        Card:
                                        {{
                                            cashFormatter(
                                                round(form_data.card_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                ================================================
                            </tr>
                            <tr>
                                <td>
                                    Thank you for dining with us please welcome
                                </td>
                            </tr>
                            <tr>
                                <td>Cashier : {{ userInfo.name }}</td>
                            </tr>
                            <tr>
                                ================================================
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="showEmployeeModal">
            <div class="row p-4">
                <div class="col-12">
                    <div class="form-group">
                        <input
                            type="text"
                            v-model="search_employee"
                            placeholder="Search Employee"
                            class="form-control"
                        />
                        <div class="col-12 border border-secondary">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">name</th>
                                        <th scope="col">Phone</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in empl_search_results.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                data.first_name +
                                                " " +
                                                data.last_name
                                            }}
                                        </td>
                                        <td>{{ data.phone }}</td>

                                        <td>
                                            <button
                                                v-if="isWritePermitted"
                                                class="btn btn-secondary btn-sm"
                                                @click="
                                                    addAsEmplDeduction(data)
                                                "
                                            >
                                                Add As Deduction
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>
        <PrintTakeAwaySalesReceipt
            :order_data="order_data"
            ref="printReceipt"
        />
    </div>
</template>

<script>
import PrintTakeAwaySalesReceipt from "./PrintTakeAwaySalesReceipt.vue";
export default {
    props: ["order_no", "order_data", "total_order", "total_tax"],
    data() {
        return {
            branchInfo: "",
            userInfo: "",
            waiter: "",
            bill_total: 0,
            tax_total: 0,
            data: [],
            search_employee: "",
            empl_search_results: "",
            showEmployeeModal: false,
            form_data: {
                card_pay: 0,
                mpesa_pay: 0,
                cash_pay: 0,
                credit_pay: 0,
                payment_ref: "",
                order_no: "",
                receipt_no: "",
                receipt_balance: 0,
                paid_date: "",
                receipt_total: 0,
                total_vat: 0,
                customer_id: null,
                user_id: null,
                order_date: "",
                order_date: "",
                order_type: "",
            },
        };
    },
    components: { PrintTakeAwaySalesReceipt },
    mounted() {
        this.form_data.order_no = this.order_no;
        this.form_data.receipt_total = this.total_order;
        this.form_data.total_vat = this.total_tax;
        this.form_data.customer_id = this.order_data[0].customer_id;
        this.form_data.user_id = this.order_data[0].user_id;
        this.form_data.order_date = this.order_data[0].order_date;
        this.form_data.order_type = this.order_data[0].order_type;

        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.data = this.order_data;
    },
    watch: {
        form_data: {
            handler() {
                this.calculateBalances();
            },
            deep: true,
        },
        search_employee: {
            handler: _.debounce(function () {
                this.searchEmployee();
            }, 500),
        },
    },
    methods: {
        async searchEmployee() {
            const res = await this.getApi("data/employee/fetch", {
                params: {
                    page: 1,
                    search: this.search_employee,
                },
            });
            if (res.status == 200) {
                this.empl_search_results = res.data.results;
            } else {
                this.servo();
            }
        },
        async addAsEmplDeduction(data) {
            this.form_data.employee_id = data.id;

            if (!this.form_data.employee_id) {
                this.errorNotif("Select employee to deduct");
            } else {
                this.showLoader();
                const res = await this.callApi(
                    "POST",
                    "data/takeaway_sale/completeOrderByEmployeeDeduction",
                    this.form_data
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.successNotif("Transaction completed ");
                    this.$emit("dismiss-diag");
                } else {
                    this.form_error(res);
                }
            }
        },
        async cashoutCredit() {
            if (confirm("Do you really want to proceed?")) {
                if (this.order_data[0].customer.company_name == "Walk in") {
                    this.errorNotif(
                        "Error you need to update customer details"
                    );
                } else {
                    this.showLoader();
                    const res = await this.callApi(
                        "POST",
                        "data/takeaway_sale/completeCreditSale",
                        this.form_data
                    );
                    this.hideLoader();
                    if (res.status == 200) {
                        this.successNotif("Transaction completed ");
                        this.$emit("dismiss-diag");
                    } else {
                        this.errorNotif("Server error occurred call Admin!!!");
                    }
                }
            }
        },
        calculateBalances() {
            var pending_balance = 0;

            if (this.form_data.cash_pay == "") {
                this.form_data.cash_pay = 0;
            }
            if (this.form_data.mpesa_pay == "") {
                this.form_data.mpesa_pay = 0;
            }
            if (this.form_data.card_pay == "") {
                this.form_data.card_pay = 0;
            }

            var cash = parseFloat(this.form_data.cash_pay);
            var card = parseFloat(this.form_data.card_pay);
            var mpesa = parseFloat(this.form_data.mpesa_pay);
            var total = parseFloat(this.form_data.receipt_total);

            this.form_data.receipt_balance = total - (cash + card + mpesa);
        },

        generateReceiptNo() {
            const receptNo =
                this.$store.state.branch.id +
                "_" +
                this.$store.state.user.id +
                "_" +
                (
                    Date.now().toString(36) +
                    Math.random().toString(36).substr(2, 5)
                ).toUpperCase();

            this.form_data.receipt_no = receptNo;
        },
        async printReceipt(print_receipt) {
            this.generateReceiptNo();

            this.waiter = this.order_data[0].user.name;
            this.form_data.paid_date = this.getCurrentDate();

            if (parseFloat(this.form_data.receipt_balance) > 0) {
                this.swr("Pending payments...");
                return;
            }
            if (!parseFloat(this.form_data.receipt_total) > 0) {
                this.swr("Empty receipt totals...");
                return;
            }
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/takeaway_sale/create",
                this.form_data
            );

            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully saved");
                if (print_receipt) {
                    // printJS("printMe", "html");
                    //    this.$refs.printReceipt.printCashier();
                    printJS("printReceipt", "html");
                }

                this.order_data = [];
                this.filtered_data = [];
                this.data = null;
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = 0)
                );

                this.$emit("dismiss-diag");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
<style scoped>
.border {
    padding: 2rem !important;
}
.t-amount {
    color: #fff;
}
.receipt_header {
    display: flex;
    flex-direction: column;
}
.receipt-menu td {
    color: #000;
    font-weight: 700;
}
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
#printReceipt {
    visibility: hidden;
}
.printer-header {
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}
.t-amount {
    color: #000;
    font-weight: 600;
}
</style>
