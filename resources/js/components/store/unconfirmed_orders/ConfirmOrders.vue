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
                <div class="card-header"></div>

                <div class="card-body">
                   
                    <div class="d-flex justify-content-between">
                         <h3>Order No: {{pending_order.order_no}}</h3>
                          <h4>Vendor: {{pending_order.supplier.company_name}}</h4>
                        <div class="col-2 d-flex align-items-center">
                                <button
                                    v-if="isDeletePermitted"
                                    @click="cancelOrders()"
                                    class="btn btn-danger btn-sm "
                                >
                                    Cancel Order
                                </button>
                            </div>
                            <div class="col-2 d-flex align-items-center">
                                <button
                                    v-if="isApprovePermitted"
                                    @click="approveOrder()"
                                    class="btn btn-secondary btn-sm "
                                >
                                    Approve Order
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

                                <th scope="col">Qty</th>
                                <th scope="col">Qty Delivered</th>
                                <th scope="col">Sub Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in order_data"
                                :key="i"
                            >
                                <td>
                                    {{ data.stock ? data.stock.code : "" }}
                                </td>
                                <td>
                                    {{
                                        data.stock
                                            ? data.stock.product_name
                                            : ""
                                    }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.unit_price) }}
                                </td>

                                <td>
                                    <input type="text" v-model="data.qty" />
                                </td>
                                  <td>
                                    <input type="text" v-model="data.qty_delivered" />
                                </td>
                                <td>
                                    {{ cashFormatter(data.sub_total) }}
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><b>TOTAL ORDERS</b></td>
                                <td>
                                    <b>{{ cashFormatter(total_order) }}</b>
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
export default {
    props: ["pending_order"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            total_order: 0,
            search_results: [],
            taxes: 0,
            order_data: [],

            select_data: {
                supplier_options: null
            },
            form_data: {
                supplier_id: null,
                order_no: ""
            }
        };
    },
    watch: {
        order_data: {
            deep: true,
            handler() {
                this.total_order = this.order_data.reduce(function(sum, val) {
                    return sum + val.sub_total;
                }, 0);
            }
        }
    },

    mounted() {
        this.form_data.order_no = this.pending_order.order_no;
        this.form_data.supplier_id = this.pending_order.supplier_id;

        this.fetchOrders();
    },

    methods: {
        async cancelOrders(){
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.getApi("data/po/destroy", {
                    params: {
                        order_no: this.form_data.order_no,
                        supplier_id: this.form_data.supplier_id
                    }
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Deleted ");
                    this.$emit("dashboard-active");
                }
            }
        },
       async approveOrder() {
            const approve = await this.confirmDialogue(
                "Do you want to approve orders"
            );
            if (approve) {
                this.showLoader();
                const res = await this.getApi("data/po/approveOrder", {
                    params: {
                        order_no: this.form_data.order_no,
                        supplier_id: this.form_data.supplier_id
                    }
                });
                this.hideLoader();
                if (res.status === 200) {
                    this.s("Approved ")
                    this.$emit("dashboard-active");
                }
            }
        },
        async fetchOrders() {
            this.showLoader();
            const res = await this.getApi("data/po/fetchByOrder", {
                params: {
                    order_no: this.form_data.order_no,
                    supplier_id: this.form_data.supplier_id
                }
            });
            this.hideLoader();
            if (res.status === 200) {
                this.order_data = res.data;
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([]);
            this.hideLoader();
        },
        modifyProductKey() {
            let modif = this.supplier_data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.company_name
                };
            });
            return modif;
        }
    }
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
