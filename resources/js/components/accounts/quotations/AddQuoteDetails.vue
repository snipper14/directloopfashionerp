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
                                class="ma-2 v-btn-primary ml-2"
                                @click="cancelOrder()"
                                color="primary"
                                dark
                            >
                                Cancel Order
                            </v-btn>
                        </div>
                        <div class="row">
                            <SPProductSearchWithEditable
                                @stockSearchResult="addCart"
                            />
                        </div>
                    </div>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>

                                <th scope="col">Qty</th>
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
                                    {{ data.product_name }}
                                </td>

                                <td>{{ data.qty }}</td>
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
                                <td colspan="3">
                                    <strong>GRAND TOTAL</strong>
                                </td>
                                <td colspan="2">
                                    <strong>{{
                                        cashFormatter(grand_total)
                                    }}</strong>
                                </td>
                                <td>
                                    <button
                                        class="btn btn-primary"
                                        @click="completeOrder()"
                                    >
                                        Complete Order
                                    </button>
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
import ProductSearchWithDiscount from "../../utilities/ProductSearchWithDiscount.vue";
import SPProductSearchWithEditable from '../../utilities/SPProductSearchWithEditable.vue';

export default {
    components: { ProductSearchWithDiscount, SPProductSearchWithEditable },
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
            department_data: null,
            form_data: {
                department_id: null,
                order_no: "",
                stock_id: null,
                name: "",
                code: null,
                customer_id: "",
                order_date: "",
                selling_price: 0,
                qty: 1,
                row_total: 0,
                order_total: 0,
                tax_amount: 0,
                discount: 0,
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
        this.form_data.customer_id = this.data.id;

        this.concurrentApiReq();
        this.form_data.order_date = this.getCurrentDate();
    },
    methods: {
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
        async completeOrder() {
            this.showLoader();
            const res = await this.callApi("post", "data/quote/completeOrder", {
                order_no: this.form_data.order_no,
            });
            this.hideLoader();
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
                    "data/quote/cancelOrders",
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
                    "data/quote/destroy",
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
            this.form_data.stock_id = value.id;
            this.form_data.name = value.product_name;
            this.form_data.qty = value.qty;
            this.form_data.selling_price =value.selling_price- (value.selling_price*(value.discount / 100));
            this.form_data.discount = value.discount;
            this.form_data.row_total = value.row_total//this.form_data.selling_price*value.qty
            var tax_total = this.calculateTax(
                value.tax_rate,
                this.form_data.row_total
            );
            this.form_data.tax_amount = tax_total;
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/quote/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Record added successfully");
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },

        async orders() {
            const res = await this.callApi("post", "data/quote/orders", {
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
                "data/quote/orderNumber/" + this.data.id,
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
