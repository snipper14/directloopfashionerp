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
                        <h4><b> Room Service Orders</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Search Room</label>

                                    <treeselect
                                        :load-options="fetchTable"
                                        :options="room_select_data"
                                        :auto-load-root-options="false"
                                        v-model="params.room_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Room"
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
                                        <th>Room</th>
                                        <th>Package</th>
                                        <th>Waiter</th>
                                        <th>Guest</th>

                                        <th>Status</th>
                                        <th>checkout</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data"
                                        :key="i"
                                    >
                                        <td>{{formatDateTime( data.created_at) }}</td>
                                        <td 
                                        
                                          :class="[
                                                {
                                                    'printed-bills':
                                                        data.isBillPrinted ==
                                                        '1',
                                                },
                                            ]">{{ data.order_no }}</td>

                                        <td>
                                            {{ cashFormatter(data.sum_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_vat) }}
                                        </td>
                                        <td>
                                            {{ data.room.door_name }}
                                        </td>
                                        <td>
                                            {{ data.room_package.name }}
                                        </td>
                                        <td>
                                            {{ data.user.name }}
                                        </td>
                                        <td>
                                            {{ data.guest.name }}
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
        <room-service-cashout
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
import RoomServiceCashout from "./RoomServiceCashout.vue";
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
        RoomServiceCashout
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
           async enableBillReprint(order_no, i) {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/room_order/enableBillReprint",
                {
                    order_no: order_no,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.successNotif("Reprint enabled");
                let index = this.sale_data.findIndex(function (item, i) {
                    return item.order_no === order_no;
                });

                this.sale_data[index].isBillPrinted = "0";
            } else {
                this.servo();
            }
        },
        async fetchTable() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/rooms/fetch", "");
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.room_select_data = this.modifyRoomKey(res.data);
            } else {
                this.servo();
            }
        },
        modifyRoomKey(data) {
            let modif = data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.door_name
                };
            });
            return modif;
        },
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
        async fetchData() {
            const res = await this.getApi(
                "data/room_order/fetchOrders",

                {
                    params: {
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
.printed-bills {
    background: #ffb74d !important;
}
</style>
