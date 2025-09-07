<template>
    <div class="">
        <!-- ======= Top Bar ======= -->
        <header-orders />
        <section id="hero-order" class="d-flex align-items-center">
            <div
                class="container position-relative text-center text-lg-start"
                data-aos="zoom-in"
                data-aos-delay="100"
            ></div>
        </section>
        <br /><br />
        <hr />
        <div class="row">
            <div class="col-md-7">
                <div class="col-10"><h4>Member Details</h4></div>
                <div class="col-md-10 form-group">
                    <input
                        type="text"
                        placeholder="name"
                        autocomplete="off"
                        v-model="form_data.customer_name"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10 form-group">
                    <input
                        type="email"
                        placeholder="Email"
                        autocomplete="off"
                        v-model="form_data.email"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10 form-group">
                    <input
                        type="number"
                        placeholder="Phone"
                        autocomplete="off"
                        v-model="form_data.customer_phone"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10">
                    <input
                        type="text"
                        placeholder="Delivery Address"
                        autocomplete="off"
                        v-model="form_data.customer_address"
                        class="form-control"
                    />
                </div>
                <div class="col-md-6">
                    <v-btn
                        style="top: 20px; width: 100%; z-index: 999978899999"
                        color="error"
                        large
                        dark
                        :loading='btn_loading'
                        @click="checkout()"
                        >Complete Checkout
                    </v-btn>
                </div>
            </div>
            <div class="col-md-4">
                <cart-items :showCheckoutBtn="showCheckoutBtn" />
            </div>
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
import HeaderOrders from "./HeaderOrders.vue";
import CartItems from "./order/components/CartItems.vue";
export default {
    components: { HeaderOrders, CartItems },
    data() {
        return {
            showCheckoutBtn: false,
            order_array: null,
            btn_loading:false,
            form_data: {
                customer_name: "",
                email:'',
                customer_phone:'',
                customer_address:''
            },
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
    mounted() {
        this.order_array = this.$store.state.orderArray;
    },
    methods: {
        async checkout() {
         
            if (this.order_array.length < 1) {
                this.showErrorNotif(
                    "Your cart is empty please add some orders "
                );
                return;
            }
            this.btn_loading=true
            const res=await this.callApi('POST','orders/completeOrder',{
                ...this.form_data,
                order_array:this.order_array
            })
            this.btn_loading=false
            if(res.status==200){
                 this.$store.commit("setCartArray", []);
                this.successNotif('Order completed successfully')
                 window.location.href = "online_ordering";
               
            }else{
                 if (res.status == 422) {
                for (let i in res.data.errors) {
                   
                     this.$toasted.error(res.data.errors[i][0])
                }
            } else {
                this.swr("Server error try again later");
            }
            }
        },
    },
};
</script>
<style scoped>
h4 {
    color: #322312 !important;
}
</style>
