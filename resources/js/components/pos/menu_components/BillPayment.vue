<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4><b>Cashout / Payment</b></h4>

                        <div class="  ">
                            <span>
                                <h3>
                                    <b>
                                        BILL TOTAL:
                                        {{ cashFormatter(total_order) }}</b
                                    >
                                </h3>
                            </span>
                        </div>
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
                                    <div
                                        class="col-10"
                                        v-if="order_data_new.length > 0"
                                    >
                                        Waiter {{ order_data_new[0].user.name }}
                                    </div>
                                    <div
                                        class="d-flex flex-column mt-2 mb-2"
                                        v-for="(value, i) in order_data_new"
                                        :key="i"
                                    >
                                        <div class="row">
                                            <div class="col-5">
                                                <b
                                                    >{{
                                                        value.stock
                                                            ? value.stock
                                                                  .product_name
                                                            : "NA"
                                                    }}
                                                    <span
                                                        v-if="
                                                            value.accompaniment_id
                                                        "
                                                    >
                                                        Served With
                                                        {{
                                                            value.accompaniment
                                                                .product_name
                                                        }}</span
                                                    >
                                                </b>
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
                                            <h3>
                                                <b>
                                                    TOTAL:
                                                    {{
                                                        cashFormatter(
                                                            total_order
                                                        )
                                                    }}</b
                                                >
                                            </h3>
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
                                                class="btn btn-secondary"
                                                v-if="isDownloadPermitted"
                                                :disabled="
                                                    isSaveAndPrintBtnDisabled
                                                "
                                                @click="printReceipt(true)"
                                            >
                                                SAVE & PRINT
                                            </button>
                                            <v-btn
                                                v-if="isApprovePermitted"
                                                title="approve"
                                                dark
                                                :loading="
                                                    btn_save_print_loading
                                                "
                                                color="secondary"
                                                @click="printFiscalReceipt()"
                                            >
                                                Save Fiscal
                                            </v-btn>
                                            <button
                                                class="btn btn-primary"
                                                :disabled="
                                                    isSaveAndPrintBtnDisabled
                                                "
                                                @click="printReceipt(false)"
                                            >
                                                SAVE
                                            </button>
                                        </div>
                                        <div
                                            class="d-flex flex-row justify-content-between mt-3"
                                        >
                                            <button
                                                v-if="isAdmin"
                                                class="btn btn-warning"
                                                @click="cashoutCredit()"
                                            >
                                                Clear with Credit
                                            </button>
                                            <button
                                                class="btn btn-warning"
                                                v-if="isUpdatePermitted"
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
                                        v-if="form_data.cash_pay.length > 0"
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
                                        v-if="form_data.mpesa_pay.length > 0"
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
                                        v-if="form_data.card_pay.length > 0"
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
        <div class="row">
            <div class="col-4">
                <PrintReceipt
                    ref="ReceiptPrintComponent"
                    v-if="load_printer_component"
                    :form_data="form_data"
                    :order_data="order_data_new"
                    :waiter="waiter"
                />
            </div>
        </div>
    </div>
</template>

<script>
import ReceiptBodyComponent from "./dinerscomponents/ReceiptBodyComponent.vue";
import ReceiptHeader from "./dinerscomponents/ReceiptHeader.vue";

import PrintReceipt from "./PrintReceipt.vue";
export default {
    props: ["order_no", "order_data", "total_order", "total_tax"],
    data() {
        return {
            btn_save_print_loading:false,
            isSaveAndPrintBtnDisabled: false,
            order_data_new: this.order_data,
            load_printer_component: false,
            branchInfo: "",
            userInfo: "",
            waiter: "",
            bill_total: 0,
            showEmployeeModal: false,
            search_employee: "",
            tax_total: 0,
            empl_search_results: "",
            data: [],

            form_data: {
                card_pay: 0,
                mpesa_pay: 0,
                cash_pay: 0,
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
                employee_id: null,
            },
        };
    },
    components: {
        PrintReceipt,
        ReceiptHeader,
        ReceiptBodyComponent,
    },
    mounted() {
        this.form_data.order_no = this.order_no;
        this.form_data.receipt_total = this.total_order;
        this.form_data.total_vat = this.total_tax;
        this.form_data.customer_id = this.order_data_new[0].customer_id;
        this.form_data.user_id = this.order_data_new[0].user_id;
        this.form_data.order_date = this.order_data_new[0].order_date;
        this.form_data.order_type = this.order_data_new[0].order_type;

        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.data = this.order_data_new;
        this.form_data.mpesa_pay = this.total_order;
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
        async addAsEmplDeduction(data) {
            this.form_data.employee_id = data.id;

            if (!this.form_data.employee_id) {
                this.errorNotif("Select employee to deduct");
            } else {
                this.showLoader();
                const res = await this.callApi(
                    "POST",
                    "data/pos_sale/completeOrderByEmployeeDeduction",
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
        async cashoutCredit() {
            if (confirm("Do you really want to proceed?")) {
                if (this.order_data_new[0].customer.company_name == "Walk in") {
                    this.errorNotif(
                        "Error you need to update customer details"
                    );
                } else {
                    this.showLoader();
                    const res = await this.callApi(
                        "POST",
                        "data/pos_sale/completeCreditSale",
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
            this.form_data.receipt_no = this.order_no; //this.getUniqueId();
        },
        async printFiscalReceipt() {
            const stat = await this.validateSales();
            if (!stat) {
                return;
            }

            this.btn_save_print_loading = true;

            let kra_result = this.order_data_new.reduce(
                (ac, { id, qty, price, stock }) => {
                    let key = `${id}`;
                    var priceWCL = 0;
                    if (price > 0) {
                        priceWCL = price - price * 0.02;
                    }

                    ac.set(key, {
                        vat_group: stock.tax_dept.tax_code,
                        item_name: stock.product_name,
                        qty: parseFloat(qty),
                        price: parseFloat(priceWCL),
                        vat_rate: parseFloat(stock.tax_dept.tax_rate),
                        hs_code: "",
                        discount: 0.0,
                    });

                    return ac;
                },
                new Map()
            );
            const kra_data = [...kra_result.values()];
            var invoice_number = this.order_data_new[0].order_no.replace(
                "-",
                ""
            );

            var post_kra = {
                customer_exid: "",
                rel_doc_number: "",
                customer_pin: this.form_data.customer_pin,
                invoice_number: invoice_number,
                zfp_host: "localhost:4444",
                device_port: this.branchInfo.cu_port,
                device_ip: this.branchInfo.cu_ip,
                password: this.branchInfo.cu_password,
                invoice_pin: this.branchInfo.kra_pin,
                grand_total: this.form_data.receipt_total,
                items_list: kra_data,
            };
            if (!this.branchInfo.fisc_api) {
                this.e("Device api not found");
                return;
            }
            const res = await this.callApi(
                "post",
                this.branchInfo.fisc_api,
                post_kra
            );

            this.btn_save_print_loading = false;
            if (res.status === 200) {
                this.fiscal_data = res.data;
                
                this.printReceipt(true);
            } else {
                Swal.fire({
                    title: "Fiscal Device Error?",
                    showDenyButton: true,
                    showCancelButton: false,
                    confirmButtonText: "Re-Entry",
                    denyButtonText: `Print Receipt`,
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        this.printFiscalReceipt();
                    } else if (result.isDenied) {
                        this.printReceipt(true);
                        //Swal.fire('Canceled', '', 'info')
                    } else {
                        Swal.fire("Canceled", "", "info");
                    }
                });
            }
        },
        async printReceipt(print_receipt) {
            this.generateReceiptNo();

            this.waiter = this.order_data_new[0].user.name;
            this.form_data.paid_date = this.getCurrentDate();
            if (
                parseFloat(this.form_data.mpesa_pay) >
                parseFloat(this.total_order)
            ) {
                this.errorNotif(
                    "Mobile Money Payment cannot be greater than bill total"
                );
                return;
            }
            if (
                parseFloat(this.form_data.card_pay) >
                parseFloat(this.total_order)
            ) {
                this.errorNotif(
                    "Card Payment cannot be greater than bill total"
                );
                return;
            }
            if (parseFloat(this.form_data.receipt_balance) > 0) {
                this.swr("Pending payments...");
                return;
            }
            if (!parseFloat(this.form_data.receipt_total) > 0) {
                this.swr("Empty receipt totals...");
                return;
            }
            this.showLoader();
            this.isSaveAndPrintBtnDisabled = true;
            this.load_printer_component = true;
            const res = await this.callApi(
                "post",
                "data/pos_sale/create",
                this.form_data
            );

            this.hideLoader();

            if (res.status === 200) {
                this.s("Successfully saved");
                if (print_receipt) {
                    setTimeout(() => {
                        //disable browser printing
                        //  this.$refs.ReceiptPrintComponent.printReceipt();
                        this.$emit("dismiss-diag");
                    }, 1000);
                } else {
                    this.$emit("dismiss-diag");
                }
            } else {
                this.isSaveAndPrintBtnDisabled = false;
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
