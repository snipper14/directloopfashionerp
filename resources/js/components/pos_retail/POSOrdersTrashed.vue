<template>
    <div>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 style="color: red">PROCESSED POS ORDERS</h3>
                    </div>

                    <div class="card-body" v-if="!showDetails">
                        <div class="row">
                            <div class="col-md-4">
                                <input
                                    type="text"
                                    v-model="search"
                                    placeholder="Search..."
                                    class="form-control input-sm"
                                />
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

                    <!-- DETAILS -->
                    <div class="card-body" v-if="showDetails">
                        <button
                            class="btn btn-primary"
                            @click="showDetails = false"
                        >
                            Close
                        </button>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Batchno</th>
                                        <th scope="col">Product Code</th>
                                        <th scope="col">Product Name</th>

                                        <th>qty</th>
                                        <th>Price</th>
                                        <th>amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in details_array"
                                        :key="i"
                                    >
                                        <td>{{ data.batch_no }}</td>
                                        <td>{{ data.stock.code }}</td>
                                        <td>{{ data.stock.product_name }}</td>
                                        <td>{{ data.qty }}</td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_amount) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- END -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from "../utilities/Pagination.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
import CashoutRetail from "./CashoutRetail.vue";
import OrderDetails from "./OrderDetails.vue";
export default {
    components: { OrderDetails, Unauthorized, CashoutRetail, Pagination },
    data() {
        return {
            details_array: [],
            showDetails: false,
            trashed_modal: false,
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
        async viewDetails(data) {
            const res = await this.getApi("data/pos_order/fetchDeletedOrders", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.details_array=res.data
            this.order_details = data;
            this.showDetails = true;
        },
        async fetchRecords(page) {
            this.show_loader ? this.showLoader() : "";
            const res = await this.getApi("data/pos_order/fetchDeletedOrders", {
                params: { page, search: this.search, record: "grouped" },
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
