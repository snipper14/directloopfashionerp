<template>
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
                    <h3><b>RECEIVE ORDERS</b></h3>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <h6>Order No: {{ order_details.order_no }}</h6>

                        <div class="form-group">
                            <div class="form-group">
                                <label>Delivery No. *</label>
                                <input
                                    v-model="form_data.delivery_no"
                                    class="form-control"
                                    type="text"
                                />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-group d-flex flex-column">
                                <label>Deadline Date *</label>
                                <date-picker
                                    v-model="form_data.received_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                        </div>
                        <div class="form-group">
                            <button
                                v-if="false"
                                class="btn btn-primary btn-sm"
                                @click="receiveAllDownloadDN()"
                            >
                                Receive All
                            </button>
                        </div>
                    </div>
                    <table
                        class="table table-sm table-striped table-bordered mt-3"
                    >
                        <thead>
                            <tr>
                                <th scope="col">Code</th>
                                <th scope="col">Item Name</th>
                                <th scope="col">Unit Price</th>

                                <th scope="col">Qty Ordered</th>
                                <th scope="col">Qty Delivered</th>
                                <th scope="col">Qty Received</th>
                                <td>Receive</td>
                                <th scope="col">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in modify_results"
                                :key="i"
                            >
                                <td>
                                    {{ data.code }}
                                </td>
                                <td>
                                    {{ data.product_name }}
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        v-model="data.unit_price"
                                    />
                                </td>

                                <td>
                                    <input type="text" v-model="data.qty" />
                                </td>
                                <td>
                                    {{ cashFormatter(data.qty_delivered) }}
                                </td>
                                <td>
                                    <input
                                        class="form-control"
                                        type="number"
                                        v-model="data.qty_received"
                                    />
                                </td>
                                <td>
                                    <button
                                        v-if="isWritePermitted"
                                        class="btn btn-secondary btn-sm"
                                        @click="receiveOrder(data)"
                                    >
                                        Receive
                                    </button>
                                </td>
                                <td>
                                    {{ cashFormatter(data.sub_total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered mt-3"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item Name</th>
                                    <th scope="col">Unit Price</th>

                                    <th scope="col">Qty Delivered</th>

                                    <th scope="col">Sub Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in delivery_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock ? data.stock.code : "" }}
                                    </td>
                                    <td>
                                        {{ data.stock ? data.stock.name : "" }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.unit_price) }}
                                    </td>

                                    <td>
                                        {{ data.qty_delivered }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.sub_total) }}
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-danger btn-sm"
                                            @click="delRecord(data, i)"
                                        >
                                            <v-icon dark left>
                                                mdi-trash </v-icon
                                            >Trash
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td> <b> QTY RECEIVING </b></td>
                                    <td><b>{{cashFormatter(total_qty_received)}}</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                 <tr>
                                    <td> <b> TOTAL AMOUNT</b></td>
                                    <td><b>{{cashFormatter(total_amount_received)}}</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <button
                                            class="btn btn-primary btn-sm"
                                            v-if="
                                                isWritePermitted &&
                                                delivery_data.length > 0
                                            "
                                            @click="downloadDN()"
                                        >
                                            Complete Delivery
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    props: ["order_details"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            modify_results: [],
            taxes: 0,
            order_data: [],
            delivery_data: [],
            total_amount_received: 0,
            total_qty_received: 0,
            form_data: {
                stock_id: null,
                order_no: "",
                direct_purchase_order_id: null,
                qty_delivered: 0,
                qty_ordered: 0,
                sub_total: 0,
                unit_price: 0,
                received_date: "",
                delivery_no: "",
            },
        };
    },
    watch: {
        delivery_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.total_amount_received = this.delivery_data.reduce(
                    function (sum, val) {
                        return sum + val.sub_total;
                    },
                    0
                );
                   this.total_qty_received = this.delivery_data.reduce(
                    function (sum, val) {
                        return sum + val.qty_delivered;
                    },
                    0
                );
            }, 500),
        },
    },

    mounted() {
        this.form_data.order_no = this.order_details.order_no;

        this.form_data.received_date = this.getCurrentDate();
        this.concurrentApiReq();
    },

    methods: {
        async delRecord(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi(
                    "data/direct_po_receivable/destroy",
                    {
                        params: {
                            qty_delivered: data.qty_delivered,

                            id: data.id,
                        },
                    }
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.delivery_data.splice(i, 1);
                    this.fetchOrders();
                } else {
                    this.swr("Server error try later!!");
                }
            }
        },
        async downloadDN() {
            this.showLoader();
            const dataObj = {
                order_no: this.form_data.order_no,

                delivery_no: this.form_data.delivery_no,
            };
            axios({
                url: "data/direct_po_receivable/downloadDN",
                method: "POST",
                data: dataObj,
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.getTimeStamp() + "_DN" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
                this.$emit("dashboard-active");
            });
        },
        async receiveAllDownloadDN() {
            this.showLoader();
            const dataObj = {
                order_no: this.form_data.order_no,

                delivery_no: this.form_data.delivery_no,
            };
            axios({
                url: "data/direct_po_receivable/receiveAll",
                method: "POST",
                data: dataObj,
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.getTimeStamp() + "_DN" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
                this.$emit("dashboard-active");
            });
        },

        async receiveOrder(data) {
            this.form_data.sub_total = data.unit_price * data.qty_received;
            this.form_data.direct_purchase_order_id = data.id;
            this.form_data.unit_price = data.unit_price;
            this.form_data.stock_id = data.stock_id;
            this.form_data.qty_delivered = data.qty_received;
            this.form_data.qty_ordered = data.qty;
            this.form_data.received_date = this.formatDate(
                this.form_data.received_date
            );

            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/direct_po_receivable/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == "200") {
                this.s("Record added successfully");
                this.delivery_data = res.data;
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    qty_received: obj.qty - obj.qty_delivered,
                    qty: obj.qty,
                    qty_delivered: obj.qty_delivered,
                    code: obj.stock.code,
                    stock_id: obj.stock_id,
                    product_name: obj.stock.product_name,
                    unit_price: obj.unit_price,
                    sub_total: obj.sub_total,
                };
            });
            return modif;
        },

        async fetchOrders() {
            const res = await this.getApi("data/direct_po/fetchPendingOrder", {
                params: {
                    order_no: this.form_data.order_no,
                },
            });

            if (res.status === 200) {
                this.order_data = res.data;
                this.modify_results = this.modifyResData(this.order_data);
            } else {
                this.swr("Server Error Reload");
            }
        },
        async fetchPendingDelivery() {
            const res = await this.getApi(
                "data/direct_po_receivable/fetchPendingDelivery",
                {
                    params: {
                        order_no: this.form_data.order_no,
                    },
                }
            );

            if (res.status === 200) {
                this.delivery_data = res.data;
            } else {
                this.swr("Server Error Reload");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.fetchOrders(),
                this.fetchPendingDelivery(),
                this.fetchDeliveryNo(),
            ]);
            this.hideLoader();
        },
        async fetchDeliveryNo() {
            const res = await this.getApi(
                "data/direct_po_receivable/fetchDeliveryNo",{
                    params:{
                      order_no: this.form_data.order_no,  
                    }
                }
            );
            if (res.status === 200) {
                this.form_data.delivery_no = res.data;
            } else {
                this.swr("could not fetch delivery no");
            }
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
.border {
    padding: 1rem !important;
}
</style>
