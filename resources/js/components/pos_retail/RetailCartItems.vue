<template>
    <div>
        <div>
            <div class="card">

                <div class="col-md-8 offset-md-2">
                    <div class="d-flex">
                        <div class="col-md-8">
                            <b>Orders - {{ form_data.customer_name }}</b>
                        </div>

                        <br />
                    </div>
                    <div
                        class="d-flex flex-column"
                       
                    >
                      <div class="row">
                            <div class="col-md-4">
                                <v-btn
                                title="Approve"
                                    v-shortkey.once="['f1']"
                                    @shortkey="cashout()"
                                    v-if="isApprovePermitted"
                                    @click="cashout()"
                                    color="primary"
                                >
                                    <v-icon small>{{
                                        icons.mdiCashPlus
                                    }}</v-icon>
                                    Cash Out
                                </v-btn>
                            </div>
                            <div class="col-md-4">
                                <v-btn
                                title="DOWNLOAD"
                                    color="warning"
                                    v-if="isDownloadPermitted"
                                    @click="printBill()"
                                >
                                    PRINT BILL
                                </v-btn>
                            </div>
                            <div class="col-md-4">
                                <v-btn
                                 title="WRITE"
                                    v-shortkey.once="['f2']"
                                    @shortkey="completeOrder()"
                                    :loading="order_btnloading"
                                    color="secondary"
                                    @click="completeOrder()"
                                >
                                    Put OnHold
                                </v-btn>
                            </div>
                        </div>
                        
                        <div
                            style="
                            width:80%;
                                overflow-x: hidden;
                                overflow-y: auto;
                            "
                        >
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table modern-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Batchno</th>
                                            <th scope="col">Code</th>
                                            <th scope="col">Item Name</th>

                                            <th scope="col">Sell Price</th>
                                             <!-- <th scope="col">Disc%</th> -->
                                            <th scope="col">Qty</th>
                                            <th></th>
                                            <th scope="col">Sub Total</th>
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
                                                {{value.hs_code}} {{ value.product_name }}
                                            </td>
                                            <td>
                                                {{ value.selling_price }}
                                            </td>
                                             <!-- <td>
                                                {{ value.discount }}
                                            </td> -->
                                            <td>{{ value.qty }}</td>
                                            <td>
                                                <div
                                                    class="d-flex justify-content-around"
                                                >
                                                    <span>
                                                        <v-icon
                                                        title="UPDATE"
                                                            v-if="
                                                                isUpdatePermitted
                                                            "
                                                            @click="
                                                                addReduce(
                                                                    value,
                                                                    'add'
                                                                )
                                                            "
                                                            style="color: green"
                                                            medium
                                                            >{{
                                                                icons.mdiPlusThick
                                                            }}</v-icon
                                                        >
                                                    </span>
                                                    <span>
                                                        <v-icon
                                                        title="UPDATE"
                                                            v-if="
                                                                isUpdatePermitted
                                                            "
                                                            @click="
                                                                addReduce(
                                                                    value,
                                                                    'reduce'
                                                                )
                                                            "
                                                            style="color: black"
                                                            medium
                                                            >{{
                                                                icons.mdiMinusThick
                                                            }}</v-icon
                                                        >
                                                    </span>
                                                    <b>
                                                        <v-icon
                                                        title="DELETE"
                                                            v-if="
                                                                isDeletePermitted
                                                            "
                                                            @click="
                                                                deleteRecord(
                                                                    value,
                                                                    i
                                                                )
                                                            "
                                                            style="color: red"
                                                            medium
                                                            >{{
                                                                icons.mdiTrashCanOutline
                                                            }}</v-icon
                                                        ></b
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                {{ value.row_amount }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <span>
                                    <b>
                                        TOTAL:
                                        {{ cashFormatter(total_order) }}</b
                                    >
                                </span>
                            </div>
                            <div class="d-flex justify-content-end mt-1">
                                <span>
                                    <b>
                                        TAX:
                                        {{ cashFormatter(total_tax) }}</b
                                    >
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                Cashout <b>f1</b> / Puthold <b>f2</b>
                            </div>
                        </div>
               
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <print-bill-update
                    ref="BillPrintComponent"
                    v-if="load_printer_component"
                    :form_data="form_data"
                    :order_data="order_data_offline"
                />
            </div>
        </div>
        <Modal v-model="cashout_modal" width="800px">
            <cashout-retail
                :total_order="total_order"
                :total_tax="total_tax"
                :order_data_offline="order_data_offline"
                @dismiss-diag="dismissDiag"
                v-if="cashout_modal && order_data_offline.length > 0"
            />
        </Modal>
    </div>
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
import CashoutRetail from "./CashoutRetail.vue";
import SearchCustomer from "./SearchCustomer.vue";
import PrintBill from "../pos/menu_components/PrintBill.vue";
import PrintBillUpdate from '../pos/menu_components/PrintBillUpdate.vue';
export default {
    components: { CashoutRetail, SearchCustomer, PrintBill, PrintBillUpdate },
    props: [, "order_data_offline"],
    data() {
        return {
            form_data: {
                order_no: null,
                customer_name: "",
            },
            load_printer_component: false,
            order_btnloading: false,
            cashout_modal: false,
            total_order: 0,
            total_tax: 0,

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
        this.form_data.order_no = this.order_data_offline[0].order_no;
        this.form_data.customer_name = this.order_data_offline[0].customer_name;
        this.calculateTotals();
    },
    watch: {
        order_data_offline: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.calculateTotals();
            }, 500),
        },
    },
    methods: {
        async printBill() {
            if (this.order_data_offline.length > 0) {
                this.setCustomerDetails();
                this.order_btnloading = true;
                const res = await this.callApi(
                    "POST",
                    "data/pos_order/create",
                    { order_data_offline: this.order_data_offline }
                );
                this.order_btnloading = false;
                if (res.status === 200) {
                    this.load_printer_component = true;
                    setTimeout(() => {
                        //disable browser printing
                        this.$refs.BillPrintComponent.printReceipt();

                        this.s("Successfully saved");
                        this.order_data_offline = [];

                        this.$store.commit(
                            "setOrderData",
                            this.order_data_offline
                        );
                        this.$store.commit("setCustomerData", {
                            customer_name: "",
                            customer_id: null,
                        });
                        this.clearCustInfo();
                    }, 1000);
                } else {
                    this.form_error(res);
                }
            } else {
                this.errorNotif("No orders available");
            }
        },
        setCustomerDetails() {
            let customer_data = this.$store.state.customer_data;
        },
        async completeOrder() {
            
            if (this.order_data_offline.length > 0) {
               
                this.setCustomerDetails();
                this.order_btnloading = true;
                const res = await this.callApi(
                    "POST",
                    "data/pos_order/create",
                    { order_data_offline: this.order_data_offline }
                );
                this.order_btnloading = false;
                if (res.status === 200) {
                     this.$store.commit("setPromoData", []);
                    this.s("Successfully saved");
                    this.order_data_offline = [];

                    this.$store.commit("setOrderData", this.order_data_offline);
                    this.$store.commit("setCustomerData", {
                        customer_name: "",
                        customer_id: null,
                    });
                    this.clearCustInfo();
                } else {
                    this.form_error(res);
                }
            } else {
                this.errorNotif("No orders available");
            }
        },
        clearCustInfo() {
            this.form_data.customer_name = "";
            this.$emit("emit-clear-customer");
        },
        dismissDiag() {
            this.cashout_modal = false;
            this.order_data_offline = [];
            this.$store.commit("setOrderData", this.order_data_offline);
            this.$store.commit("setCustomerData", {
                customer_name: "",
                customer_id: null,
            });
            this.clearCustInfo();
        },
        cashout() {
            this.setCustomerDetails();
            this.cashout_modal = true;
        },
        calculateTotals() {
            this.total_order = this.order_data_offline.reduce(function (
                sum,
                val
            ) {
                return sum + val.row_amount;
            },
            0);
            this.total_tax = this.order_data_offline.reduce(function (
                sum,
                val
            ) {
                return sum + val.row_vat;
            },
            0);
        },
        deleteRecord(data, i) {
            this.order_data_offline.splice(i, 1);
            this.$store.commit("setOrderData", this.order_data_offline);
        },
    addReduce(val, type) {
  const idx = this.order_data_offline.map(x => x.stock_id).indexOf(val.stock_id);
  if (idx === -1) return;

  const item = this.order_data_offline[idx];

  // adjust qty
  if (type === 'add') {
    item.qty = Number(item.qty || 0) + 1;
  } else if (type === 'reduce') {
    if (Number(item.qty) < 2) return;
    item.qty = Number(item.qty) - 1;
  }

  // numbers
  const qty      = Number(item.qty) || 0;
  const price    = Number(item.selling_price) || 0;
  const discount = Number(item.discount) || 0; // % per line

  // apply discount BEFORE tax
  const unitNet  = price - (price * discount / 100); // price after discount
  const lineNet  = unitNet * qty;                     // subtotal after discount

  // tax on the discounted amount (if your tax is exclusive)
  item.row_vat    = this.calculateTax(item.tax_rate, lineNet);
  item.row_amount = lineNet; // store discounted line amount (net of discount)

  // write back
  this.order_data_offline.splice(idx, 1, { ...item });
  this.$store.commit("setOrderData", this.order_data_offline);
}
,
    },
};
</script>
