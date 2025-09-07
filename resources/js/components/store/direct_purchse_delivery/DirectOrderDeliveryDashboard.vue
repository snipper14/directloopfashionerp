<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2">
                <DPNav />
            </div>
            <div class="col-md-10">
                <div class="container" v-if="isReadPermitted">
                    <div class="card" v-if="active.dashboard">
                        <div class="card-header">
                            <h3><b>Direct Order Deliveries</b></h3>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-center">
                                <div class="form-group">
                                    <label for="">Order No</label>
                                    <input
                                        type="text"
                                        v-model="params.order_no"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Order No</th>

                                            <th>Deadline Date</th>
                                            <th>Receipt Date</th>
                                            <th>User</th>

                                            <th scope="col">Qty</th>
                                            <th scope="col">Qty Delivered</th>
                                            <th scope="col">Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in order_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        viewDetails(data)
                                                    "
                                                >
                                                    {{
                                                        data.order_no
                                                    }}</router-link
                                                >
                                            </td>

                                            <td>{{ data.order_deadline }}</td>
                                            <td>{{ data.order_date }}</td>

                                            <td>
                                                {{
                                                    data.user
                                                        ? data.user.name
                                                        : ""
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.total_qty
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.total_qty_delivered
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{
                                                    cashFormatter(
                                                        data.sum_sub_total
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination
                                v-if="order_data !== null"
                                v-bind:results="order_data"
                                v-on:get-page="fetchOrders"
                            ></Pagination>
                        </div>
                    </div>
                    <add-direct-delivery
                        v-if="active.add_delivery"
                        :order_details="order_details"
                        v-on:dashboard-active="setActiveNoRefresh"
                    />
                </div>
                <center v-else>
                    <unauthorized />
                </center>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import Pagination from "../../utilities/Pagination.vue";
import DPNav from "../direct_purchase/DPNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddDirectDelivery from "./AddDirectDelivery.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_delivery: false
            },
            order_details: null,
            params: {
                order_no: ""
            },
            order_data: []
        };
    },
    components: {
        Treeselect,
        DPNav,
        Pagination,
        Unauthorized,
        AddDirectDelivery
    },
    watch: {
        params: {
            handler: _.debounce(function() {
                this.fetchOrders(1);
            }, 500),
            deep: true
        }
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        viewDetails(data) {
            this.order_details = data;
            this.setActiveComponent("add_delivery");
        },
        setActiveNoRefresh() {
            this.setActiveComponent("dashboard");
            this.concurrentApiReq();
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchOrders(1)]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },

        async fetchOrders(page) {
            const res = await this.getApi(
                "data/direct_po_receivable/fetchOrders",
                {
                    params: {
                        page,
                        ...this.params
                    }
                }
            );
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
</style>
