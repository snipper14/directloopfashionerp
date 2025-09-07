<template>
    <div class="container">
        <div class="col-md-12">
            <div>
                <center><b>Cashier Checkout</b></center>
            </div>
            <nav class="nav flex-column">
                <button
                    :class="[
                        'cashier_order' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('cashier_order')"
                >
                    POS Checkout
                </button>
                <hr />
                <button
                    :class="[
                        'room_service_cashier' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('room_service_cashier')"
                >
                 Online Orders Clearance
                </button>
                <hr />
                <button
                    :class="[
                        'clear_waiter_bills' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('clear_waiter_bills')"
                >
                    Clear Waiter POS Payments
                </button>
                <hr />
                <!-- <button
                    :class="[
                        'clear_waiter_rs_payments' === this.$route.name
                            ? 'nav-active'
                            : '',
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('clear_waiter_rs_payments')"
                >
                    Clear Waiter RS Payments
                </button>
                <hr /> -->
                <!-- <button
                    :class="[
                        'takeaway_cashier' === this.$route.name
                            ? 'nav-active'
                            : ''
                    ]"
                    class="btn btn-secondary btn-sm"
                    @click="$router.push('takeaway_cashier')"
                >
                    Take AWay
                </button>
                <hr /> -->
            </nav>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {};
    },
    mounted() {},
};
</script>
