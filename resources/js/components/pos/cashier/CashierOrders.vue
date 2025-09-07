<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2"><cashier-orders-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4><b> POS Orders</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Search Table</label>

                                    <treeselect
                                        :load-options="fetchTable"
                                        :options="table_select_data"
                                        :auto-load-root-options="false"
                                        v-model="params.table_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Table"
                                    />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <span class="badge badge-pill printed-bills"
                                    >Bill Printed</span
                                >
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Time Ordered</th>
                                        <th scope="col">Order No</th>
                                        <th>Receipt Amount</th>
                                        <th scope="col">Total Vat</th>
                                        <th>Location</th>
                                        <th>Table</th>
                                        <th>Waiter</th>
                                        <th>Customer</th>
                                        <th>Order Type</th>
                                        <th>Status</th>
                                        <th>Archive</th>

                                        <th>checkout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td
                                            :class="[
                                                {
                                                    'printed-bills':
                                                        data.isBillPrinted ==
                                                        '1',
                                                },
                                            ]"
                                        >
                                            {{ data.order_no }}
                                        </td>

                                        <td>
                                            {{ cashFormatter(data.sum_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_vat) }}
                                        </td>
                                        <td>
                                            {{ data.location.name }}
                                        </td>
                                        <td>
                                            {{ data.table.name }}
                                        </td>
                                        <td>
                                            {{ data.user.name }}
                                        </td>
                                        <td>
                                            {{
                                                data.customer
                                                    ? data.customer.company_name
                                                    : ""
                                            }}
                                        </td>
                                        <td>{{data.order_type}}</td>
                                        <td>
                                            <p
                                                style="color: green"
                                                v-if="data.isComplete == '1'"
                                            >
                                                complete
                                            </p>
                                            <p style="color: red" v-else>
                                                progress
                                            </p>
                                        </td>
                                        <td>
                                            <router-link
                                                v-if="
                                                    isDeletePermitted &&
                                                    data.isComplete == 1 &&
                                                    data.isBillPrinted == '1'
                                                "
                                                to="#"
                                                @click.native="
                                                    archiveOrder(data,i)
                                                "
                                                ><p class="danger">Archive</p>
                                            </router-link>
                                        </td>
                                        <td>
                                            <button
                                                v-if="data.isComplete == '1'"
                                                class="btn btn-primary btn-sm"
                                                @click="cashout(data)"
                                            >
                                                Cash-Out
                                            </button>
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                v-if="isAdmin"
                                                @click.native="
                                                    enableBillReprint(
                                                        data.order_no,
                                                        i
                                                    )
                                                "
                                            >
                                                Enable BillReprint
                                            </router-link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <cashout-pos-order
            v-if="active.checkout"
            v-on:dashboard-active="setActiveRefresh"
            :order_details="order_details"
        />
        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import CashierOrdersNav from "./CashierOrdersNav.vue";
import CashoutPosOrder from "./CashoutPosOrder.vue";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    data() {
        return {
            search: "",
            active: {
                dashboard: true,
                checkout: false,
            },
            params: {
                table_id: null,
            },
            table_select_data: null,
            order_details: null,
            isLoading: true,
            sale_data: [],
        };
    },
    components: {
        Pagination,
        CashierOrdersNav,
        CashoutPosOrder,
        Treeselect,
    },

    watch: {
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
        params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            },
        },
    },
    methods: {
        async archiveOrder(data,i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/archive_order/create",
                    data
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.sale_data.data.splice(i, 1);
                } else {
                    this.servo();
                }
            }
        },
        async enableBillReprint(order_no, i) {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/pos_order/enableBillReprint",
                {
                    order_no: order_no,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Reprint enabled");
                let index = this.sale_data.data.findIndex(function (item, i) {
                    return item.order_no === order_no;
                });

                this.sale_data.data[index].isBillPrinted = "0";
            } else {
                this.servo();
            }
        },
        async fetchTable() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/table/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.table_select_data = this.modifyTableKey(res.data);
            } else {
                this.servo();
            }
        },
        modifyTableKey(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
        cashout(data) {
            this.order_details = data;
            this.setActiveComponent("checkout");
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_order/fetchOrders",

                {
                    params: {
                        page: page,
                        search: this.search,
                        ...this.params,
                        currentRoute: this.$route.name,
                    },
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData(1)]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
<style scoped>
.is_deducted {
    background: red !important;
}
.printed-bills {
    background: #ffb74d !important;
}
</style>
