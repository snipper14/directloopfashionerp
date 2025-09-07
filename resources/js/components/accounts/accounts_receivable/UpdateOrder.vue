<template>
    <div class="container">
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
                                    <label for=""> Department*</label>

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
                            <v-btn
                                v-if="isDeletePermitted"
                                class="ma-2 v-btn-primary ml-2"
                                @click="cancelOrder()"
                                color="primary"
                                dark
                            >
                                Cancel Order
                            </v-btn>
                        </div>
                        <div class="row">
                            <product-search-discount-amount
                                @stockSearchResult="addCart"
                                :data="customer_data"
                                v-if="false"
                            />
                        </div>
                    </div>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>

                                <th scope="col">Qty</th>
                                <th scope="col">Discount</th>
                                <th scope="col">Unit Price</th>
                                <th scope="col">Tax Amount</th>
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
                                    {{ data.stock.product_name }}
                                </td>
                                <td>{{ data.qty }}</td>
                                <td>{{ data.discount }}</td>
                                <td>{{ cashFormatter(data.item_price) }}</td>
                                <td>{{ cashFormatter(data.tax_amount) }}</td>
                                <td>{{ cashFormatter(data.row_total) }}</td>

                                <td>
                                    <router-link
                                        v-if="isDeletePermitted"
                                        to="#"
                                        @click.native="removeOrder(data, i)"
                                        >Remove</router-link
                                    >
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <strong>GRAND TOTAL</strong>
                                </td>
                                <td colspan="2">
                                    <strong>{{
                                        cashFormatter(grand_total)
                                    }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import ProductSearch from "../../utilities/ProductSearch.vue";
import ProductSearchDiscountAmount from "../../utilities/ProductSearchDiscountAmount.vue";
import ProductSearchWithDiscount from "../../utilities/ProductSearchWithDiscount.vue";
export default {
    props: ["data"],
    data() {
        return {
            select: {
                product_options: [],
                unit_options: [],
            },
            select_data: {
                product_id: null,
                unit_id: null,
            },
            order_data: [],
            grand_total: 0,
            available_qty: 0,
            form_data: {
                department_id: null,
                order_no: "",
                stock_id: null,
                name: "",
                code: null,
                customer_id: "",
                order_date: "",
                item_price: 0,
                qty: 1,
                row_total: 0,
                order_total: 0,
                tax_amount: 0,
                discount: 0,
                product_category_id: null,
            },
            deduct_vat: null,
            department_data: null,
            cart_array: [],
            isOpen: false,
            results: [],
            search_item: "",
            isLoading: false,
            arrowCounter: -1,
            select_item: null,
            customer_data:null
        };
    },
    components: {
        ProductSearch,
        ProductSearchWithDiscount,
        ProductSearchDiscountAmount,
    },
    mounted() {
       this.customer_data=this.data.customer
        this.deduct_vat = this.data.customer.vat_deduction;
        this.form_data.customer_id = this.data.customer_id;
        this.form_data.department_id = this.data.department_id;
        this.form_data.order_date = this.getCurrentDate();

        this.concurrentApiReq();
    },
    methods: {
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },

        async filterResults() {
            this.isLoading = true;
            var page = 1;
            const res = await this.getApi("data/stock/searchItem", {
                params: {
                    page,
                    search:
                        this.search_item.length >= 2 ? this.search_item : "",
                },
            });
            this.isLoading = false;
            if (res.status === 200) {
                this.results = res.data;
            } else {
                this.swr("error occured");
            }
        },

        async cancelOrder() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/sales/cancelOrders",
                    {
                        customer_id: this.data.customer_id,
                        stock_id: this.data.stock_id,

                        order_no: this.data.order_no,
                    }
                );

                if (res.status === 200) {
                    this.s("orders cancelled!!!!");
                    this.order_data = this.orders();
                    this.grand_total = 0;
                    this.$emit("dashboard-active");
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async removeOrder(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                var obj = Object.assign(data, {
                    department_id: this.form_data.department_id,
                });
                const res = await this.callApi(
                    "post",
                    "data/sales/deleteOrderItem",
                    data
                );

                if (res.status === 200) {
                    this.s("removed ");
                    this.order_data.splice(i, 1);
                    this.orderTotal(this.order_data);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async addCart(value) {
        
            this.form_data.stock_id = value.id;
            this.form_data.name = value.product_name;
            this.form_data.qty = value.qty;
            this.form_data.product_category_id = value.product_category_id;
            this.form_data.discount = value.discount;
            this.form_data.item_price = value.cost_price;

            this.form_data.purchase_price = value.purchase_price;
            this.form_data.row_total =
                (this.form_data.item_price * value.qty) - value.discount;
            
            var tax_total = 0;
            if (this.deduct_vat == "vatable") {
                tax_total = this.calculateTax(
                    value.tax_rate,
                    this.form_data.row_total
                );
            }

            this.form_data.tax_amount = tax_total;
            this.showLoader();
            this.form_data.order_total =
                parseFloat(this.grand_total) +
                parseFloat(this.form_data.row_total);

            const res = await this.callApi(
                "post",
                "data/sales/updateOrder",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Successfully recorded");
                this.order_data = res.data;
                this.orderTotal(this.order_data);
            } else {
                this.form_error(res);
            }
        },

        calculatePrice(product_price) {
            const qty = parseFloat(this.form_data.qty);
            const line_total = product_price * qty;
            this.form_data.item_price = product_price;
            this.form_data.row_total = line_total;
        },
        async orders() {
            const res = await this.callApi("post", "data/sales/orders", {
                customer_id: this.data.customer_id,
                order_no: this.data.order_no,
            });
            if (res.status == 200) {
                return res.data.results;
            } else {
                this.swr("Server during fetch record please reload page");
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.orders(),
                this.fetchDepartment(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();

            this.order_data = res[0];

            this.form_data.order_date = this.order_data[0].order_date;
            this.form_data.order_no = this.order_data[0].order_no;
            this.orderTotal(this.order_data);
        },
        orderTotal(data) {
            if (data.length > 0) {
                var res = data
                    .map((ordeer_total) => ordeer_total.row_total)
                    .reduce((acc, ordeer_total) => ordeer_total + acc);
                this.grand_total = res;
            }
        },
    },
    watch: {},
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
