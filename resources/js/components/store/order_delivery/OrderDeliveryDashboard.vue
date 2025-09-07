<template>
    <div class="">
        <div class="row">
            <div class="col-md-1">
                <POSideNav />
            </div>
            <div class="col-md-10">
                <div class="" v-if="isReadPermitted">
                    <div class="card" v-if="active.dashboard">
                        <div class="card-header">Order Deliveries</div>

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
                                <div class="supp_dropdown">
                                    <label>Supplier Name*</label>

                                    <div class="form-group">
                                        <treeselect
                                            v-model="params.supplier_id"
                                            :multiple="false"
                                            :options="supplier_options"
                                            :show-count="true"
                                            placeholder="Select "
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Order No</th>
                                            <th>Vendor</th>
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
                                            <td>
                                                {{
                                                    data.supplier
                                                        ? data.supplier
                                                              .company_name
                                                        : ""
                                                }}
                                            </td>
                                            <td>{{ data.order_deadline }}</td>
                                            <td>{{ data.receipt_date }}</td>
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
                                    <tfoot>
                                        <tr>
                                            <td
                                                colspan="7"
                                                class="text-end fw-bold"
                                            >
                                                Totals:
                                            </td>
                                            <td>
                                                {{
                                                    cashFormatter(totalSubTotal)
                                                }}
                                            </td>
                                           
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <Pagination
                                v-if="order_data !== null"
                                v-bind:results="order_data"
                                v-on:get-page="fetchOrders"
                            ></Pagination>
                        </div>
                    </div>
                    <add-delivery
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
import POSideNav from "../purchase/POSideNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import AddDelivery from "./AddDelivery.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_delivery: false,
            },
            order_details: null,
            params: {
                order_no: "",
                supplier_id: null,
            },
            order_data: [],
            supplier_data: [],
            supplier_options: null,
        };
    },
    components: {
        Treeselect,
        POSideNav,
        Pagination,
        AddDelivery,
        Unauthorized,
    },
    watch: {
        params: {
            handler() {
                this.searchRecords();
            },
            deep: true,
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    computed: {
        totalSubTotal() {
            if (!this.order_data?.data) return 0;
            return this.order_data.data.reduce(
                (acc, item) => acc + (item.sum_sub_total || 0),
                0
            );
        },
      
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
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        async searchRecords() {
            const res = await this.getApi("data/po_receivable/fetchOrders", {
                params: {
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.getAllSuppliers(),
                this.fetchOrders(1),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
        async getAllSuppliers() {
            const res = await this.getApi("data/suppliers/fetchAll", "");

            if (res.status === 200) {
                this.supplier_data = res.data.results;
                this.supplier_options = this.modifyProductKey();
            } else {
                this.swr("Server error try again later");
            }
        },

        async fetchOrders(page) {
            const res = await this.getApi("data/po_receivable/fetchOrders", {
                params: {
                    page,
                },
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        modifyProductKey() {
            let modif = this.supplier_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.company_name,
                };
            });
            return modif;
        },
    },
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
</style>
