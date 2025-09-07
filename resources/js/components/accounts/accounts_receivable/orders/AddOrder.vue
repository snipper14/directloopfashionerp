<template>
    <div class="container">
        <router-view> </router-view>
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
                        <h4>
                            <strong>{{ data.company_name }}</strong>
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="row top-menu">
                            <div class="order-wrapper">
                                <div class="form-group">
                                    <label for="">Order No</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        disabled
                                        v-model="form_data.order_no"
                                    />
                                </div>
                                <div class="form-group date-picker">
                                    <label for="">Order Date</label>
                                    <date-picker
                                        v-model="form_data.order_date"
                                        valueType="format"
                                    ></date-picker>
                                </div>
                                <div class="form-group d-flex flex-column mr-2">
                                    <label for=""> Location*</label>

                                    <Select v-model="form_data.department_id">
                                        <Option
                                            v-for="item in department_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                            <div class="form-group col-md-1 nopadding">
                                <label>Show or Hide Disc.</label>
                                <Select v-model="discount_status">
                                    <Option
                                        v-bind:value="data.name"
                                        v-for="(data, i) of show_disc"
                                        :key="i"
                                    >
                                        {{ data.name }}
                                    </Option>
                                </Select>
                            </div>
                            <v-btn
                                class="ma-2 v-btn-primary ml-2"
                                @click="cancelOrder()"
                                color="primary"
                                dark
                            >
                                Cancel Order
                            </v-btn>
                        </div>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for=""> CU Invoice No</label>

                                <input
                                    type="text"
                                    v-model="cu_invoice_no"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for=""> Details</label>

                                <input
                                    type="text"
                                    v-model="notes"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for=""> D. Note</label>

                                <input
                                    type="text"
                                    v-model="d_note"
                                    class="form-control"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <product-search-discount-amount
                                @stockSearchResult="addCart"
                                :data="data"
                            />
                        </div>
                    </div>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>BatchNo</th>
                                <th scope="col">Product</th>

                                <th scope="col">Qty</th>
                                <th>Discount</th>
                                <th scope="col">Unit Price</th>
                                <th>Tax</th>
                                <th scope="col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in order_data"
                                :key="i"
                            >
                                <td>
                                    {{ data.batch_no }}
                                </td>
                                <td>
                                    {{ data.stock.product_name }}
                                </td>

                                <td>{{ data.qty }}</td>
                                <td>{{ data.discount }}</td>
                                <td>{{ cashFormatter(data.selling_price) }}</td>
                                <td>{{ cashFormatter(data.tax_amount) }}</td>
                                <td>{{ cashFormatter(data.row_total) }}</td>

                                <td>
                                    <router-link
                                        to="#"
                                        @click.native="removeOrder(data, i)"
                                        >Remove</router-link
                                    >
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <strong> Total VAT</strong>
                                </td>
                                <td colspan="1">
                                    <strong>{{
                                        cashFormatter(total_tax)
                                    }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <strong> Total Discount</strong>
                                </td>
                                <td colspan="1">
                                    <strong>{{
                                        cashFormatter(print_discount_amount)
                                    }}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <strong>GRAND TOTAL</strong>
                                </td>
                                <td colspan="1">
                                    <strong>{{
                                        cashFormatter(grand_total)
                                    }}</strong>
                                </td>
                                <td>
                                    <v-btn
                                        :loading="invoice_btn_loading"
                                        small
                                        color="primary"
                                        @click="completeOrder()"
                                    >
                                        Complete Order
                                    </v-btn>
                                </td>
                                <td>
                                  
                                </td>
                                <td>
                                    <v-btn
                                        :loading="invoice_btn_loading"
                                        small
                                        class="warning"
                                        @click="saveAndPrintInvoice()"
                                    >
                                        Generate Invoice
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <PrintInvoice
            v-if="show_print"
            ref="PrintInvoice"
            :invoice_details="invoice_details"
            :print_invoice_amount="print_invoice_amount"
            :print_tax_amount="print_tax_amount"
            :print_discount_amount="print_discount_amount"
            :logo="logo"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import ProductSearch from "../../../utilities/ProductSearch.vue";
import ProductSearchDiscountAmount from "../../../utilities/ProductSearchDiscountAmount.vue";
import ProductSearchWithDiscount from "../../../utilities/ProductSearchWithDiscount.vue";
import PrintInvoice from "../invoices/invoice_print_elem/PrintInvoice.vue";
export default {
    components: {
        ProductSearch,
        ProductSearchWithDiscount,
        PrintInvoice,
        ProductSearchDiscountAmount,
    },
    props: ["data"],
    data() {
        return {
            select: {
                product_options: [],
                unit_options: [],
            },
            show_disc: [{ name: "show" }, { name: "hide" }],
            discount_status: "hide",
            invoice_btn_loading: false,
            select_data: {
                product_id: null,
                unit_id: null,
            },
            show_print: false,
            order_data: [],
            grand_total: 0,
            total_tax: 0,
            print_invoice_amount: 0,
            print_tax_amount: 0,
            print_discount_amount: 0,
            available_qty: 0,
            department_data: null,
            d_note: "",
            notes: "",
            cu_invoice_no: "",
            logo: null,
            invoice_details: [],
            form_data: {
                department_id: null,
                order_no: "",
                stock_id: null,
                name: "",
                code: null,
                customer_id: "",
                order_date: "",
                selling_price: 0,
                purchase_price: 0,
                qty: 1,
                row_total: 0,
                order_total: 0,
                tax_amount: 0,
                discount: 0,
                product_category_id: null,
                batch_no: "",
            },

            cart_array: [],

            isOpen: false,
            results: [],
            search_item: "",
            isLoading: false,
            arrowCounter: -1,
            select_item: null,
        };
    },

    mounted() {
        console.log(JSON.stringify(this.data));
        this.logo = this.$store.state.branch.img_url;
        this.form_data.customer_id = this.data.id;

        this.concurrentApiReq();
        this.form_data.order_date = this.getCurrentDate();
    },
    methods: {
        async saveAndPrintInvoice() {
            if (!this.form_data.order_date) {
                this.e("select invoice date");
                return;
            }
            this.showLoader();
            const res = await this.getApi(
                "data/invoices/saveAndDownloadInvoice",
                {
                    params: {
                        discount_status: this.discount_status,
                        customer_id: this.form_data.customer_id,
                        grand_total: this.grand_total,
                        notes: this.notes,
                        d_note: this.d_note,
                        cu_invoice_no: this.cu_invoice_no,
                        invoice_date: this.form_data.order_date,
                    },
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.invoice_details = res.data;
                this.print_invoice_amount = this.grand_total;
                this.print_tax_amount = this.total_tax;
                this.show_print = true;
                setTimeout(() => {
                    this.$refs.PrintInvoice.printBill();
                    this.$emit("dashboard-active");
                }, 1000);
                //this.$emit("dashboard-active");
            } else {
                this.form_error(res);
            }
        },
        ////////////////////////////////**************////////////////////////////

   
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
        async completeOrder() {
            this.invoice_btn_loading = true;
            const res = await this.callApi("post", "data/sales/create", {
                id: this.data.id,
                grand_total: this.grand_total,
            });
            this.invoice_btn_loading = false;
            if (res.status === 200) {
                this.s("orders has been recorded ");
                this.$emit("dashboard-active");
            } else {
                this.swr("Server error try again later");
            }
        },
        async cancelOrder() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/orders/cancelOrders",
                    {
                        id: this.data.id,
                    }
                );

                if (res.status === 200) {
                    this.w("orders cancelled!!!! ");

                    this.order_data = this.orders();
                    this.grand_total = 0;
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async removeOrder(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/orders/destroy",
                    data
                );

                if (res.status === 200) {
                    this.s("removed ");
                    this.order_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async addCart(value) {
            this.form_data.batch_no = value.batch_no;
            this.form_data.stock_id = value.id;
            this.form_data.name = value.product_name;
            this.form_data.qty = value.qty;
            this.form_data.discount = value.discount;
            this.form_data.selling_price = value.cost_price; //- ( value.cost_price *(value.discount/100 ));
            this.form_data.purchase_price = value.purchase_price;
            this.form_data.product_category_id = value.product_category_id;
            this.form_data.row_total =
                this.form_data.selling_price * value.qty - value.discount;
            //   value.cost_price * value.qty -
            //   value.cost_price * value.qty * (value.discount / 100); //(value.cost_price*value.qty)
            var tax_total = 0;
            if (this.data.vat_deduction == "vatable") {
                tax_total = this.calculateTax(
                    value.tax_rate,
                    this.form_data.row_total
                );
            }
            if (this.data.vat_deduction == "exempted") {
                tax_total = 0;
            }

            this.form_data.tax_amount = tax_total;
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/orders/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.order_data = await this.orders();
            } else {
                this.form_error(res);
            }
        },

        async orders() {
            const res = await this.callApi("post", "data/orders/orders", {
                customer_id: this.data.id,
            });
            if (res.status == 200) {
                return res.data.results;
            } else {
                this.swr("Server during fetch record please reload page");
            }
        },
        async orderNumber() {
            const res = await this.getApi(
                "data/sales/orderNumber/" + this.data.id,
                ""
            );

            if (res.status == 200) {
                return res.data.result;
            } else {
                const goBack = await this.serverError();
                if (goBack) {
                    this.$emit("dashboard-active");
                }
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.orderNumber(),

                this.orders(),
                this.fetchDepartment(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();

            this.form_data.order_no = res[0];

            this.order_data = res[1];
            this.updateOrder(this.order_data);
        },

        updateOrder(order_data) {
            if (order_data.length > 0) {
                this.form_data.order_date = order_data[0].order_date;
            }

            //
        },
    },
    watch: {
        order_data: {
            handler() {
                var res = this.order_data
                    .map((ordeer_total) => ordeer_total.row_total)
                    .reduce((acc, ordeer_total) => ordeer_total + acc);

                this.grand_total = res;
                this.total_tax = this.order_data.reduce(function (sum, val) {
                    return sum + val.tax_amount;
                }, 0);
                this.print_discount_amount = this.order_data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.discount;
                },
                0);
            },
            deep: true,
        },
    },
};
</script>
<style scoped>
.top-menu {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
}
.order-wrapper {
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
}
.date-picker {
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
}
.autocomplete {
    position: relative;
}

.autocomplete-results {
    padding: 0;
    margin: 0;
    border: 1px solid #eeeeee;
    height: 120px;
    overflow: auto;
}

.autocomplete-result {
    list-style: none;
    text-align: left;
    padding: 4px 2px;
    cursor: pointer;
}

.autocomplete-result.is-active,
.autocomplete-result:hover {
    background-color: #4aae9b;
    color: white;
}
</style>
