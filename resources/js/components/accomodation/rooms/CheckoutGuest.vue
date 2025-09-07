<template>
    <div class="container">
        <div class="row d-flex justify-content-between mr-2">
            <v-btn
                class="ma-2 v-btn-primary ml-2 "
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><b>Guest Reservation Details</b></h3>
                </div>
                <div class="card-body">
                    <div class="row  ">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <h4>
                                    <b
                                        >Guest Name:
                                        {{ checkout_data.guest.name }}</b
                                    >
                                </h4>
                            </li>
                            <li class="list-group-item">
                                <b>Room: {{ checkout_data.room.door_name }}</b>
                            </li>
                            <li class="list-group-item">
                                <b>Checkin Date: {{ checkout_data.from }}</b>
                            </li>
                            <li class="list-group-item">
                                <b>Checkout Date: {{ checkout_data.to }}</b>
                            </li>
                            <li class="list-group-item">
                                <h4>
                                    <b
                                        >Total Amount:
                                        {{ checkout_data.total }}</b
                                    >
                                </h4>
                            </li>
                            <li
                                class="list-group-item"
                                v-if="
                                    checkout_data.total >
                                        checkout_data.amount_paid
                                "
                            >
                                <button class="btn btn-warning btn-sm">
                                    Clear Room Payments
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-9">
                            <h4>Pending Orders</h4>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">Order No</th>
                                        <th>Particulars</th>
                                        <th>Qty</th>
                                        <th scope="col">Total</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Waiter</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(data, i) in pending_orders_data"
                                        :key="i"
                                    >
                                        <td scope="row">{{ data.order_no }}</td>
                                        <td>
                                            {{ data.stock.product_name }}
                                        </td>
                                        <td>
                                            {{ data.qty }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_total) }}
                                        </td>
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.user.name }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"><b> Order Total</b></td>
                                        <td>
                                            <b>
                                                {{
                                                    cashFormatter(total_orders)
                                                }}</b
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <button
                                class="btn btn-primary btn-sm"
                                v-if="
                                    pending_orders_data.length < 1 &&
                                        checkout_data.total <=
                                            checkout_data.amount_paid && isUpdatePermitted
                                "
                                @click="checkoutGuest()"
                            >
                                Checkout
                            </button>
                        </div>
                        <div class="col-6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["checkout_data"],
    data() {
        return {
            pending_orders_data: [],
            total_orders: 0
        };
    },
    watch: {
        pending_orders_data: {
            deep: true,
            handler() {
                this.total_orders = this.pending_orders_data.reduce(function(
                    sum,
                    val
                ) {
                    return sum + val.row_total;
                },
                0);
            }
        }
    },
    mounted() {
        this.fetchPendingOrders();
    },
    methods: {
        async checkoutGuest() {
            const confirm = await this.confirmDialogue("Are you Sure!!");
            if (confirm) {
                const res = await this.callApi(
                    "POST",
                    "data/reservation/checkout",
                    {
                        id: this.checkout_data.id,
                        room_id: this.checkout_data.room_id
                    }
                );
                if (res.status == 200) {
                    this.successNotif("Successfully checked out");
                    this.$emit("dashboard-active");
                } else {
                    this.form_error(res);
                }
            }
        },
        async fetchPendingOrders() {
            this.showLoader();
            const res = await this.getApi("data/room_order/getByReservation", {
                params: {
                    reservation_id: this.checkout_data.id
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                this.pending_orders_data = res.data;
            } else {
                this.servo();
            }
        }
    }
};
</script>
