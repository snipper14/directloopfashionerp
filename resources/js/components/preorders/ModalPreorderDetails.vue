<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Preorder Details</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table modern-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>
                                        <th>Description</th>
                                        <th scope="col">Qty</th>
                                        <th scope="col">Sell Price</th>

                                        <th scope="col">Sub Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(value, i) in order_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ value.stock.code }}
                                        </td>
                                        <td>
                                            {{ value.stock.name }}
                                        </td>
                                        <td>{{ value.description }}</td>
                                        <td>{{ value.qty }}</td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    value.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(value.row_total) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <Button
                                            type="primary"
                                            title="APPROVE"
                                            :loading="isSending"
                                            :disabled="isSending"
                                            v-if="
                                                isApprovePermitted &&
                                                details_data.order_status ==
                                                    'completed'
                                            "
                                            @click="completeOrder"
                                            >Complete Order</Button
                                        >
                                        <Button
                                            type="primary"
                                            title="APPROVE"
                                            :loading="isSending"
                                            :disabled="isSending"
                                            v-if="
                                                isApprovePermitted &&
                                                details_data.order_status ==
                                                    'ready'
                                            "
                                            @click="completeDelivery"
                                            >Complete Delivery</Button
                                        >
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["details_data"],
    data() {
        return {
            order_data: [],
            isSending: false,
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchDetails();
    },
    methods: {
        async completeDelivery() {
              this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/preorders/completeDelivery",
                {
                    order_no: this.details_data.order_no,
                }
            );
            this.isSending = false;
            if (res.status === 200) {
                this.successNotif("completed ");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
        async completeOrder() {
            this.isSending = true;
            const res = await this.callApi(
                "POST",
                "data/preorders/completeOrder",
                {
                    order_no: this.details_data.order_no,
                }
            );
            this.isSending = false;
            if (res.status === 200) {
                this.successNotif("completed ");
                this.$emit("dismiss-modal");
            } else {
                this.form_error(res);
            }
        },
        async fetchDetails() {
            this.showLoader();
            const res = await this.getApi("data/preorders/getByOrderNo", {
                params: {
                    order_no: this.details_data.order_no,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.order_data = res.data.res;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
