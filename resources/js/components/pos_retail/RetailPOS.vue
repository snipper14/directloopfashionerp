<template>
    <div class="container-fluid">
        <div v-if="active.dashboard">
            <div v-if="isReadPermitted">
                <div class="card">
                    <div class="row">
                        <!-- <v-btn @click="testPrint()">Test print</v-btn> -->
                        <div class="col-md-12">
                            <RetailProductSearch
                                @stockSearchResult="submitRecords"
                                @customerSearch="updateCustomer"
                                :key="stockSearchKey"
                            />
                        </div>
                        <div class="col-md-12">
                            <RetailCartItems
                                v-if="order_data_offline.length > 0"
                                :order_data_offline="order_data_offline"
                                @emit-clear-customer="clearCustomerInfo"
                                :key="retailCartKey"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
import {
    mdiAccountPlus,
    mdiFoodForkDrink,
    mdiCashRegister,
    mdiCashPlus,
    mdiLogoutVariant,
} from "@mdi/js";
import Unauthorized from "../utilities/Unauthorized.vue";
import RetailProductSearch from "./RetailProductSearch.vue";
import RetailCartItems from "./RetailCartItems.vue";

export default {
    data() {
        return {
            retailCartKey: 1,
            stockSearchKey: 10,
            active: {
                dashboard: true,
                change_password: false,
            },
            cashier_options: this.$store.state.branch.cashier_options,
            form_data: {
                table_id: null,
                order_no: "",
                user_id: null,
            },
            cashier_details: null,
            order_data_offline: [],
            icons: {
                mdiCashPlus,
                mdiAccountPlus,
                mdiFoodForkDrink,
                mdiCashRegister,
                mdiLogoutVariant,
            },
        };
    },

    mounted() {
        this.order_data_offline = this.$store.state.order_data;
   
        this.form_data.user_id = this.$store.state.user.id;
    },
    components: { Unauthorized, RetailProductSearch, RetailCartItems },

    methods: {
        async testPrint() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "http://127.0.0.1:8070/api/testPrint",
                {}
            );
            this.hideLoader();
        },
        clearCustomerInfo() {
            this.stockSearchKey += 1;
            this.order_data_offline = [];
        },
        async updateCustomer(value) {
            console.log(JSON.stringify(value));

            this.order_data_offline.forEach((data, x) => {
                this.order_data_offline[x].customer_id = value.customer_id;
                this.order_data_offline[x].customer_name = value.customer_name;
                this.order_data_offline[x].customer_pin = value.pin;
            });

            this.$store.commit("setOrderData", this.order_data_offline);

            this.retailCartKey += 1;
            const currentDate = new Date().toISOString().split("T")[0];

            if (
                value.loyalty_program == "active" &&
                value.is_discount_qualified == "active" &&
                value.created_at !== currentDate
            ) {
                const res = await this.getApi(`data/promos/fetch`, {});
                if (res.status == 200) {
                    this.$store.commit("setPromoData", res.data);
                } else {
                    this.form_error(res);
                }
            } else {
                this.$store.commit("setPromoData", []);
            }
        },
        generateReceiptNo() {
            const transTime = this.$moment().format("YYMMDDhhmm");
            const random = Math.floor(Math.random() * 9000 + 1000);
            const receptNo = random + "" + transTime;
            if (this.order_data_offline.length > 0) {
                this.form_data.order_no = this.order_data_offline[0].order_no;
            } else {
                this.form_data.order_no = receptNo;
            }
        },
        submitRecords(value) {
            console.log("value>>>" + JSON.stringify(value));
            this.order_data_offline = this.$store.state.order_data;
            if (value.qty < 0) {
                this.errorNotif("Enter Qty");
                return;
            }
            if (value.selling_price < 0) {
                this.errorNotif("Selling Price required");
                return;
            }
            this.generateReceiptNo();
            var row_amount = value.selling_price * value.qty;
             row_amount=  Math.ceil( row_amount - (row_amount * (value.discount / 100)));
            var row_vat = this.calculateTax(value.tax_rate, row_amount);
            this.order_data_offline.push({
                hs_code: value.hs_code,
                batch_no: value.batch_no,
                stock_id: value.id,
                code: value.code,
                product_name: value.product_name,
                selling_price: parseFloat(value.selling_price),
                discount: parseFloat(value.discount),
                tax_rate: value.tax_rate,
                tax_dept_id: value.tax_dept_id,
                row_amount: parseFloat(row_amount),
                row_vat: parseFloat(row_vat),
                qty: parseFloat(value.qty),
                order_date: this.getCurrentDate(),
                order_no: this.form_data.order_no,
                user_id: this.form_data.user_id,
                customer_id: value.customer_id,
                customer_name: value.customer_name,
                customer_pin: value.customer_pin,
                item_id: value.item_id,
            });

            let result = this.order_data_offline.reduce(
                (
                    r,
                    {
                        hs_code,
                        batch_no,
                        stock_id,
                        code,
                        product_name,
                        selling_price,
                        discount,
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
                        customer_pin,
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
                            //     amount=    Math.ceil(amount - (amount * ( r.get(key).discount / 100)))
                            r.get(key).row_vat = this.calculateTax(
                                r.get(key).tax_rate,
                                amount
                            );
                            r.get(key).row_amount = amount;
                        }
                    } else {
                        r.set(key, {
                            hs_code,
                            stock_id,
                            selling_price,
                            batch_no,
                            tax_rate,
                            qty: qty,
                            stock_id,
                            code,
                            product_name,
                            selling_price,
                            discount,
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
                            customer_pin,
                            item_id,
                        });
                    }

                    return r;
                },
                new Map()
            );

            this.order_data_offline = [...result.values()];
            let customer_data = this.$store.state.customer_data;
            // console.log("order offline>>>>" + JSON.stringify(this.order_data_offline));
            for (var i = 0; i < this.order_data_offline.length; i++) {
                this.order_data_offline[i].customer_id =
                    customer_data.customer_id;
                this.order_data_offline[i].customer_name =
                    customer_data.customer_name;
                this.order_data_offline[i].customer_pin = customer_data.pin;
            }

            this.$store.commit("setOrderData", this.order_data_offline);
            this.s("Added...");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.getShift();
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
.menu-icon {
    height: 150px;
    width: 200px;
    border-radius: 20px;
    margin-top: 4px;
}
.order-type {
    background: #1e88e5;
    margin-right: 2px;
}
.menu-text {
    color: #ffffff;
}
.elem-wrapper {
    margin-top: 2rem;
    display: flex;
    flex-direction: row;
    align-items: stretch;
    width: 100%;
}
.elem-wrapper > div {
    flex-grow: 1;
    border-radius: 4px;
}
.elem-wrapper .active {
    background: #4caf50;
    -webkit-transform: rotate(-8deg);
    -moz-transform: rotate(-8deg);
    -o-transform: rotate(-8deg);
    -ms-transform: rotate(-8deg);
    transform: rotate(-8deg);

    /* z-index: -1;
     bottom: 25px;
    left: 10px;
    width: 150%;
    top: 80%;
    max-width: 300px;
    -webkit-box-shadow: 0 35px 20px #777;
    -moz-box-shadow: 0 35px 20px #777;
    box-shadow: 0 35px 20px #777;
    -webkit-transform: rotate(-8deg);
    -moz-transform: rotate(-8deg);
    -o-transform: rotate(-8deg);
    -ms-transform: rotate(-8deg);
    transform: rotate(-8deg);  */
}
</style>
