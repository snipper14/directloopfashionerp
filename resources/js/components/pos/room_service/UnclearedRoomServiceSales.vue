<template>
    <div>
        <div v-if="isReadPermitted">
            <div class="jumbotron">
                <div class="row d-flex flex-column">
                    <div class="d-flex justify-content-between">
                        <button
                            class="btn btn-primary"
                            @click="$router.push('posmenu')"
                        >
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </button>
                        <h3><b>Uncleared Waiter Sales</b></h3>
                    </div>
                </div>
                <hr class="my-4" />

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Order No</th>
                                <th>Order Time</th>

                                <th>Guest</th>
                                <th>Waiter</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="large-tr"
                                v-for="(data, i) in sales_data"
                                :key="i"
                            >
                                <td>
                                    {{ data.order_no }}
                                </td>
                                <td>
                                    {{ formatDateTime(data.created_at) }}
                                </td>

                                <td>{{ data.guest.name }}</td>
                                <td>{{ data.waiter.name }}</td>
                                <td>
                                    {{ cashFormatter(data.receipt_total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div v-else>
            <unauthorized />
        </div>
    </div>
</template>
<script>
import { mdiBackburger } from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            sales_data: [],
            search: "",
            icons: {
                mdiBackburger,
            },
        };
    },
    components: { Unauthorized },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    methods: {
        async getWaiterSales() {
            const res = await this.getApi(
                "data/room_sale/fetchPendingWaiterSales",
                {
                    params: {
                        // department_id: this.form_data.department_id,
                        search: this.search,
                    },
                }
            );

            if (res.status == 200) {
                this.sales_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getWaiterSales()]).then(
                function (results) {
                    return results;
                }
            );
            this.hideLoader();
        },
    },
};
</script>
<style scoped>
.bill-active {
    background: #bec5b7 !important;
}
.bill-wrapper {
    height: 300px;
    overflow-x: hidden;
    overflow-y: auto;
}
.cart-items {
    font-size: 15px !important;
    font-family: "yanone-kaffeesatz" !important;
}
td {
    font-size: 1rem !important;
}
</style>
