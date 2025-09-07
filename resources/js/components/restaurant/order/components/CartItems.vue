<template>
    <div class="order-cart">
        <ul class="list-group order-cart">
            <li style="background: #322312" class="list-group-item">
                <span class="d-flex flex-row justify-content-between">
                    <b class="text-white">
                        Total Orders: {{ cashFormatter(total_amount) }}</b
                    >
                    <b class="text-white">Items: {{ total_items }}</b></span
                >
            </li>
            <li
                class="list-group-item"
                style=""
                v-for="(data, i) in updatedOrderData"
                :key="i"
            >
                <div class="d-flex flex-columm justify-content-between">
                    {{ data.product_name }}
                    <span>
                        <v-icon
                            @click="increment(data)"
                            class="d-flex flex-row"
                            >{{ icons.mdiPlusThick }}</v-icon
                        >
                        <v-icon @click="decrement(data)">{{
                            icons.mdiMinusThick
                        }}</v-icon></span
                    >
                </div>
                <div class="">
                    <p style="color: A4A9B4; font-size: 0.8rem">
                        {{ data.accompaniment_item }}
                    </p>
                </div>
                X {{ data.qty }}
                <v-icon @click="trashOrder(data, i)">{{
                    icons.mdiDeleteOutline
                }}</v-icon>
                <span style="color: #322312; font-weight: 600"
                    >Ksh.
                    {{ cashFormatter(data.qty * data.selling_price) }}</span
                >
            </li>
        </ul>
        <div style="position: fixed; bottom: 20px">
            <v-btn
                style="top: 20px; width: 100%; z-index: 999978899999"
                color="error"
                large
                v-if="showCheckoutBtn"
                dark
                @click="checkout()"
            >
                Proceed checkout
            </v-btn>
        </div>
    </div>
</template>

<script>
import {
    mdiPlusBoxOutline,
    mdiCartArrowRight,
    mdiPlusThick,
    mdiMinusThick,
    mdiDeleteOutline,
} from "@mdi/js";
export default {
    props: ["showCheckoutBtn"],
    data() {
        return {
            total_amount: 0,
            total_items: 0,
            // showCheckoutBtn: true,
            orderArray: [],
            icons: {
                mdiPlusBoxOutline,
                mdiCartArrowRight,
                mdiPlusThick,
                mdiMinusThick,
                mdiDeleteOutline,
            },
        };
    },
    watch: {
        updatedOrderData: {
            deep: true,
            handler: function () {
                this.calculateTotal();
            },
        },
    },
    mounted() {
        this.calculateTotal();
    },
    methods: {
        checkout() {
            console.log(">>>>");
            window.location.href = "checkout";
        },
        calculateTotal() {
            this.total_amount = this.updatedOrderData.reduce(function (
                sum,
                val
            ) {
                return sum + val.qty * val.selling_price;
            },
            0);
            this.total_items = this.updatedOrderData.reduce(function (
                sum,
                val
            ) {
                return sum + val.qty;
            },
            0);
        },
        trashOrder(data, i) {
            this.updatedOrderData.splice(i, 1);

            this.$store.commit("setCartArray", this.updatedOrderData);
        },
        decrement(data) {
            var myOrder = this.updatedOrderData;

            myOrder.push({
                product_category_id: data.product_category_id,
                unit_id: data.unit_id,
                product_name: data.product_name,
                selling_price: data.selling_price,
                stock_id: data.stock_id,
                accompaniment_id: data.accompaniment_id,
                accompaniment_item: data.accompaniment_item,
                qty: 1,
            });
            let result = myOrder.reduce(
                (
                    r,
                    {
                        stock_id,
                        product_category_id,
                        unit_id,
                        product_name,
                        selling_price,
                        qty,
                        accompaniment_id,
                        accompaniment_item,
                    }
                ) => {
                    let key = `${stock_id}`;
                    if (r.has(key)) {
                        console.log(r.get(key).qty);
                        if (r.get(key).qty > 1) r.get(key).qty -= qty;
                    } else
                        r.set(key, {
                            stock_id,
                            product_category_id,
                            unit_id,
                            product_name,
                            qty: qty,
                            selling_price,
                            accompaniment_id,
                            accompaniment_item,
                        });
                    return r;
                },
                new Map()
            );
            myOrder = [...result.values()];

            this.updatedOrderData = myOrder;
        },

        increment(data) {
            var myOrder = this.updatedOrderData;

            myOrder.push({
                product_category_id: data.product_category_id,
                unit_id: data.unit_id,
                product_name: data.product_name,
                selling_price: data.selling_price,
                stock_id: data.stock_id,
                accompaniment_id: data.accompaniment_id,
                accompaniment_item: data.accompaniment_item,
                qty: 1,
            });
            let result = myOrder.reduce(
                (
                    r,
                    {
                        stock_id,
                        product_category_id,
                        unit_id,
                        product_name,
                        selling_price,
                        qty,
                        accompaniment_id,
                        accompaniment_item,
                    }
                ) => {
                    let key = `${stock_id}`;
                    if (r.has(key)) r.get(key).qty += +qty;
                    else
                        r.set(key, {
                            stock_id,
                            product_category_id,
                            unit_id,
                            product_name,
                            qty: +qty,
                            selling_price,
                            accompaniment_id,
                            accompaniment_item,
                        });
                    return r;
                },
                new Map()
            );
            myOrder = [...result.values()];

            this.updatedOrderData = myOrder;
        },
    },
    computed: {
        updatedOrderData: {
            get() {
                return this.$store.state.orderArray;
            },
            set(val) {
                this.$store.commit("setCartArray", val);
            },
        },
    },
};
</script>
<style scoped>
.text-white {
    color: #ffffff;
}
</style>
