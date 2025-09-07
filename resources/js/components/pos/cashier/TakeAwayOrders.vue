<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2"><cashier-orders-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header"
                        style="background:#ef6c00;color:#fff"
                    >
                        <h4><b> TakeAway Orders</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
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
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Order Date</th>
                                        <th scope="col">Order No</th>
                                        <th>Receipt Amount</th>
                                        <th scope="col">Total Vat</th>

                                        <th>Waiter</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>checkout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.order_date }}</td>
                                        <td>{{ data.order_no }}</td>

                                        <td>
                                            {{ cashFormatter(data.sum_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_vat) }}
                                        </td>

                                        <td>
                                            {{ data.user.name }}
                                        </td>
                                        <td>
                                            {{ data.customer.company_name }}
                                        </td>

                                        <td>
                                            <p
                                                style="color:green"
                                                v-if="data.isComplete == '1'"
                                            >
                                                complete
                                            </p>
                                            <p style="color:red" v-else>
                                                progress
                                            </p>
                                        </td>

                                        <td>
                                            <button
                                                class="btn btn-primary btn-sm"
                                                @click="cashout(data)"
                                            >
                                                Cash-Out
                                            </button>
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
        <take-away-cashout
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
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import TakeAwayCashout from "./TakeAwayCashout.vue";
export default {
    data() {
        return {
            search: "",
            active: {
                dashboard: true,
                checkout: false
            },
            params: {
                room_id: null
            },
            room_select_data: null,
            order_details: null,
            isLoading: true,
            sale_data: []
        };
    },
    components: {
        Pagination,
        CashierOrdersNav,

        Treeselect,

        TakeAwayCashout
    },

    watch: {
        search: {
            handler: _.debounce(function() {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500)
        },
        params: {
            deep: true,
            handler() {
                this.isLoading = true;
                this.concurrentApiReq();
            }
        }
    },
    methods: {
        cashout(data) {
            this.order_details = data;
            this.setActiveComponent("checkout");
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/take_away/fetchOrders",

                {
                    params: {
                        page: page,
                        search: this.search,
                        ...this.params,
                        currentRoute: this.$route.name
                    }
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

            const res = await Promise.all([this.fetchData(1)]).then(function(
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        }
    },
    mounted() {
        this.concurrentApiReq();
    }
};
</script>
<style scoped>
.is_deducted {
    background: red !important;
}
</style>
