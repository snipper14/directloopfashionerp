<template>
    <div class="container">
        <div class="row justify-content-center" v-if="isReadPermitted">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h3>POS ORDERS</h3></div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search..."
                                    class="form-control input-sm"
                                />
                            </div>
                            <div class="col-md-3">
                                <v-btn color="danger" @click="trashed_modal=true">View Processed Orders</v-btn>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Created at</th>
                                        <th>Seller</th>
                                        <th><b>Customer</b></th>
                                        <th>Order No</th>
                                        <th>qty</th>

                                        <th>amount</th>

                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in order_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.user.name }}</td>
                                        <td>
                                            <b>{{
                                                data.customer.company_name
                                            }}</b>
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                                >{{
                                                    data.order_no
                                                }}</router-link
                                            >
                                        </td>
                                        <td>{{ data.sum_qty }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.sum_row_amount
                                                )
                                            }}
                                        </td>
                                        <td>
                                            <v-btn
                                                color="primary"
                                                title="Update"
                                                x-small
                                                @click="recallOrder(data)"
                                                v-if="isUpdatePermitted"
                                                >Recall</v-btn
                                            >

                                            <v-btn
                                                color="info"
                                                title="Approve"
                                                x-small
                                                @click="cashoutSale(data, i)"
                                                v-if="isApprovePermitted"
                                                >Cashout</v-btn
                                            >

                                            <v-btn
                                                title="Delete"
                                                color="danger"
                                                x-small
                                                @click="deletedOrder(data, i)"
                                                v-if="isDeletePermitted"
                                                >Delete</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <pagination
                                v-if="order_data !== null"
                                v-bind:results="order_data"
                                v-on:get-page="fetchRecords"
                            ></pagination>
                            Items Count {{ order_data.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else><unauthorized /></div>
        <Modal title="Order details" v-model="order_details_modal" width="700px"
            ><order-details
                v-if="order_details_modal"
                :order_details="order_details"
        /></Modal>
        <Modal v-model="trashed_modal" width="1000px">
            <POSOrdersTrashed
              
               
               
               
                v-if="trashed_modal"
            />
        </Modal>
         <Modal v-model="cashout_modal" width="800px">
            <cashout-retail
                :total_order="total_order"
                :total_tax="total_tax"
                :order_data_offline="order_data_offline"
                @dismiss-diag="dismissDiag"
                v-if="cashout_modal && order_data_offline.length > 0"
            />
        </Modal>
    </div>
</template>

<script>
import Pagination from "../utilities/Pagination.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
import CashoutRetail from "./CashoutRetail.vue";
import OrderDetails from "./OrderDetails.vue";
import POSOrdersTrashed from './POSOrdersTrashed.vue';
export default {
    components: { OrderDetails, Unauthorized, CashoutRetail, Pagination, POSOrdersTrashed },
    data() {
        return {
            trashed_modal:false,
            index: -1,
            cashout_modal: false,
            total_order: 0,
            total_tax: 0,
            order_data_offline: [],
            order_details_modal: false,
            order_details: null,
            show_loader: true,
            order_data: [],
            search: "",
        };
    },
    mounted() {
        this.fetchRecords(1);
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.show_loader = false;
                this.fetchRecords(1);
            }, 500),
        },
    },
    methods: {
        dismissDiag() {
            this.cashout_modal = false;
            this.order_data.data.splice(this.index, 1);
            this.order_data_offline = [];
        },
        async cashoutSale(data, i) {
            this.show_loader ? this.showLoader() : "";
            const res = await this.getApi("data/pos_order/fetchDetails", {
                params: { order_no: data.order_no },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.index = i;
                this.order_data_offline = res.data;
                this.calculateTotals();
                this.cashout_modal = true;
            } else {
                this.form_error(res);
            }
        },
        calculateTotals() {
            this.total_order = this.order_data_offline.reduce(function (
                sum,
                val
            ) {
                return sum + val.row_amount;
            },
            0);
            this.total_tax = this.order_data_offline.reduce(function (
                sum,
                val
            ) {
                return sum + val.row_vat;
            },
            0);
        },
        async deletedOrder(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/pos_order/destroy/" + data.order_no,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.order_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async recallOrder(data) {
            this.show_loader ? this.showLoader() : "";
            const res = await this.getApi("data/pos_order/fetchDetails", {
                params: { order_no: data.order_no },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
                this.$store.commit("setOrderData", this.order_data);
                this.$router.push("retail_pos");
            } else {
                this.form_error(res);
            }
        },
        viewDetails(data) {
            this.order_details = data;
            this.order_details_modal = true;
        },
        async fetchRecords(page) {
            this.show_loader ? this.showLoader() : "";
            const res = await this.getApi("data/pos_order/fetch", {
                params: { page, search: this.search },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.order_data = res.data;
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
