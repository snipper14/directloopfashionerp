<template>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header">
                    <h4>Cashout Orders</h4>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="card">
                            <div class="card-body">
                                <div class="bill-wrapper">
                                    <div
                                        class="d-flex flex-column mt-2 mb-2"
                                        v-for="(value, i) in bills_data"
                                        :key="i"
                                    >
                                        <div class="row">
                                            <div class="col-5">
                                                <b>{{
                                                    value.stock
                                                        ? value.stock
                                                              .product_name
                                                        : "NA"
                                                }}</b>
                                            </div>
                                            <div class="col-2">
                                                <b> X </b>
                                                <b> {{ value.qty }}</b>
                                            </div>
                                            <div class="col-3">
                                                <b>
                                                    {{
                                                        cashFormatter(
                                                            value.row_total
                                                        )
                                                    }}</b
                                                >
                                            </div>
                                            <div class="col-2"></div>
                                        </div>
                                        <div
                                            class="d-flex justify-content-start "
                                        >
                                            <p class="mr-2">Unit Price /</p>

                                            <p>
                                                {{ cashFormatter(value.price) }}
                                            </p>
                                            <p class="ml-2">
                                                Guest
                                                <span
                                                    class="badge badge-secondary"
                                                    >{{ value.no_guest }}</span
                                                >
                                            </p>
                                            <p class="ml-2">
                                                {{ value.notes }}
                                            </p>
                                        </div>
                                        <hr />
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-1 mr-4"
                                    >
                                        <span>
                                            <b>
                                                TAX:
                                                {{
                                                    cashFormatter(total_tax)
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                    <div
                                        class="d-flex justify-content-end mt-2 mr-4"
                                    >
                                        <span>
                                            <b>
                                                TOTAL:
                                                {{
                                                    cashFormatter(total_order)
                                                }}</b
                                            >
                                        </span>
                                    </div>
                                </div>
                                <div
                                    class="row  mt-3 d-flex justify-content-between"
                                >
                                    <button
                                        @click="cashoutBill()"
                                        v-if="isUpdatePermitted"
                                        type="button"
                                        class="btn btn-warning btn-lg ml-2"
                                    >
                                        Cash-out Bill
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="cashout" fullscreen title="Payment">
            <take-away-bill-payment
                v-if="cashout"
                :order_no="order_no"
                :order_data="order_data"
                :total_tax="total_tax"
                :total_order="total_order"
                v-on:dismiss-diag="dismissDialog"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import TakeAwayBillPayment from "../takeaway/TakeAwayBillPayment.vue";
export default {
    components: { TakeAwayBillPayment },
    props: ["order_details"],
    data() {
        return {
            cashout: false,
            order_no: "",
            total_order: 0,
            total_tax: 0,
            bills_data: [],
            order_data: null
            // format: 'dd-MMM-yyyy',
        };
    },

    mounted() {
        this.total_order = this.order_details.sum_total;
        this.total_tax = this.order_details.sum_vat;
        this.order_no = this.order_details.order_no;
        this.fetchOrderDetails();
    },
    watch: {},
    methods: {
        async dismissDialog() {
            this.cashout = false;
            this.$emit("dashboard-active");
        },
        async cashoutBill() {
            if (this.bills_data.length > 0) {
                this.cashout = true;
                this.order_data = this.bills_data;

                this.total_order = this.total_order;
            } else {
                this.swr("No Sale available");
            }
        },
        async fetchOrderDetails() {
            this.showLoader();
            const res = await this.getApi("data/take_away/getByOrder", {
                params: {
                    order_no: this.order_no
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                this.bills_data = res.data;
            } else {
                this.servo();
            }
        }
    }
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
