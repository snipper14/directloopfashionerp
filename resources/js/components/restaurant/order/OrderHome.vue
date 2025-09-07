<template>
    <div class="">
        <!-- ======= Top Bar ======= -->
        <header-orders />

        <!-- ======= Hero Section ======= -->
        <section id="hero-order" class="d-flex align-items-center">
            <div
                class="container position-relative text-center text-lg-start"
                data-aos="zoom-in"
                data-aos-delay="100"
            ></div>
        </section>
        <!-- End Hero -->
        <!--start banner -->

        <section>
            <div class="col-12 banner">
                <span class="maintitle">
                    <h3>Order For Delivery</h3>
                    <p>
                        Order from our restaurants online and enjoy daily,
                        on-demand delivery or pickup.
                    </p>
                </span>
            </div>
        </section>
        <!-- End banner -->
        <!-- order menu -->
        <order-menu-category
            v-if="show_menu_category"
            :menus_data="menus_data"
        />
        <!-- end menu  -->
        <section style="margin-top: 2rem; margin-left: 1rem">
            <div class="row">
                <div class="col-md-7">
                    <div
                        :class="
                            'col-12 product-content scroll-' + value.category_id
                        "
                        :id="'scroll-' + value.category_id"
                        v-for="(value, i) in menus_data"
                        :key="i"
                    >
                        <div class="d-flex flex-column">
                            <center>
                                <h3>{{ value.category }}</h3>
                            </center>
                            <img
                                v-lazy="value.category_img"
                                alt="No Image"
                                class="category-img mt-2"
                            />
                        </div>
                        <div class="row mt-3">
                            <div
                                class="col-md-6 col-sm-12"
                                v-for="(data, x) in value.product_data"
                                :key="x"
                            >
                                <div
                                    class="d-flex flex-row justify-content-between"
                                >
                                    <p class="product-name">
                                        {{ data.product_name }}
                                    </p>
                                    <v-icon
                                        @click="addCart(data)"
                                        class="v-icon"
                                        medium
                                        >{{ icons.mdiPlusBoxOutline }}</v-icon
                                    >
                                </div>
                                <p class="description-color mt-2">
                                    {{ data.menu_description }}
                                </p>
                                <p class="price-color mt-2">
                                    <b
                                        >Ksh.
                                        {{
                                            cashFormatter(data.selling_price)
                                        }}</b
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 cart-wrapper">
                    <cart-items
                        :orderArray="orderArray"
                        :showCheckoutBtn="showCheckoutBtn"
                    />
                </div>
                <Modal v-model="show_cart_modal">
                    <cart-items
                        v-if="show_cart_modal"
                        :orderArray="orderArray"
                        :showCheckoutBtn="showCheckoutBtn"
                    />
                </Modal>
                <Modal v-model="accomp_modal">
                    <accompaniment
                        v-if="accomp_modal && accomp_data"
                        :accomp_data="accomp_data"
                        @accompSelect="accompanimentSelect"
                    />
                </Modal>
            </div>
            <v-btn
                @click="showCart()"
                class="cart-floating-btn"
                color="error"
                fab
                large
            >
                <v-icon>{{ icons.mdiCartArrowRight }}</v-icon>
            </v-btn>
        </section>
        <contacts-components />
    </div>
</template>

<script>
import HeaderOrders from "../HeaderOrders.vue";
import OnlineHeaderComponent from "../OnlineHeaderComponent.vue";
import OrderMenuCategory from "./OrderMenuCategory.vue";
import {
    mdiPlusBoxOutline,
    mdiCartArrowRight,
    mdiPlusThick,
    mdiMinusThick,
} from "@mdi/js";
import ContactsComponents from "../home_components/ContactsComponents.vue";
import CartItems from "./components/CartItems.vue";
import Accompaniment from "./components/Accompaniment.vue";

export default {
    components: {
        OnlineHeaderComponent,
        HeaderOrders,
        OrderMenuCategory,
        ContactsComponents,
        CartItems,
        Accompaniment,
    },
    data() {
        return {
            accomp_modal: false,
            accomp_data: null,
            showCheckoutBtn: true,
            show_cart_modal: false,
            show_menu_category: false,
            row_order_item: null,
            menus_data: [],
            stock_data: [],
            orderArray: {},
            icons: {
                mdiPlusBoxOutline,
                mdiCartArrowRight,
                mdiPlusThick,
                mdiMinusThick,
            },
        };
    },
    mounted() {
        this.orderArray = this.$store.state.orderArray;
        this.concurrentApiReq();
    },
    methods: {
        accompanimentSelect(val) {
            this.accomp_modal = false;
            this.parseOrderData(
                val.accompaniment_id,
                val.accompaniment.product_name
            );
        },
        async showCart() {
            this.show_cart_modal = true;
        },
        async concurrentApiReq() {
            //  this.showLoader();
            const res = await Promise.all([
                this.getProductCategories(),
                this.getMenuProduct(),
            ]).then(function (results) {
                return results;
            });
            //  this.hideLoader();
        },
        async getProductCategories() {
            const res = await this.getApi("online_menu/fetch", {
                params: {},
            });

            if (res.status == 200) {
                this.menus_data = res.data;
                this.show_menu_category = true;
            } else {
                this.swr("Server error occurred");
            }
        },
        async addCart(data) {
            this.row_order_item = data;
            if (data.accompaniment.length > 0) {
                this.accomp_data = data;
                this.accomp_modal = true;
                return;
            }

            this.parseOrderData(null, "");
        },
        parseOrderData(accompaniment_id, accompaniment_item) {
            this.orderArray.push({
                product_category_id: this.row_order_item.product_category_id,
                unit_id: this.row_order_item.unit_id,
                product_name: this.row_order_item.product_name,
                selling_price: this.row_order_item.selling_price,
                stock_id: this.row_order_item.id,
                accompaniment_id: accompaniment_id,
                accompaniment_item: accompaniment_item,
                qty: 1,
            });

            let result = this.orderArray.reduce(
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
                            accompaniment_id: accompaniment_id,
                            accompaniment_item: accompaniment_item,
                        });
                    return r;
                },
                new Map()
            );
            this.orderArray = [...result.values()];
            this.$store.commit("setCartArray", this.orderArray);
        },
        async getMenuProduct() {
            const res = await this.getApi("online_menu/fetchStock", {
                params: {},
            });

            if (res.status == 200) {
                this.stock_data = res.data;
                this.show_menu_category = true;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
<style scoped>
.banner {
    min-height: 120px !important;
    background: #322312;
}
.maintitle {
    text-align: center;
    margin-top: 25%;
}
.maintitle h3 {
    color: #ffffff;
}
.maintitle p {
    color: #ffffff;
}
.category-img {
    max-height: 25rem !important;
}
.product-name {
    font-weight: bold;
    font-family: "yanone-kaffeesatz", sans-serif !important;
}
.description-color {
    font-style: italic;
    color: #564a3c;
    font-weight: 600;
}
.price-color {
    color: #564a3c;
    font-weight: 600;
}
</style>
