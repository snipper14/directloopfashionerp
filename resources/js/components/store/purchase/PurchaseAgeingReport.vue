<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3"><POSideNav /></div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Purchase Ageing Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Stock </th>
                                        <th>Last Received Date</th>
                                        <th>Total Amount</th>
                                        <th>Current (0–29 days)</th>
                                        <th>30 Days</th>
                                        <th>60 Days</th>
                                        <th>90 Days</th>
                                        <th>120+ Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(row, index) in receivable_data.data" :key="index">
                                        <td>{{ row.stock.product_name }}</td>
                                        <td>{{ row.last_received_date }}</td>
                                        <td>{{ row.total_amount | currency }}</td>
                                        <td>{{ row.bucket_current | currency }}</td>
                                        <td>{{ row.bucket_30 | currency }}</td>
                                        <td>{{ row.bucket_60 | currency }}</td>
                                        <td>{{ row.bucket_90 | currency }}</td>
                                        <td>{{ row.bucket_120_plus | currency }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                     <pagination
                                        v-if="receivable_data !== null"
                                        v-bind:results="receivable_data"
                                        v-on:get-page="purchaseAging"
                                    ></pagination>
                                    Items Count {{ receivable_data.total }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Pagination from '../../utilities/Pagination.vue';
import POSideNav from "./POSideNav.vue";

export default {
    components: { POSideNav, Pagination },
    data() {
        return {
            receivable_data: [],
            pagination: {
                current_page: 1,
                last_page: 1
            }
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async purchaseAging(page ) {
            const res = await this.getApi(`data/po_receivable/purchaseAging`, { params: { page } });
            if (res.status === 200) {
                this.receivable_data = res.data; // <-- Laravel paginator uses "data"
            
            } else {
                this.swr("Server error try again later");
            }
        },

        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([ this.purchaseAging( 1) ]);
            this.hideLoader();
        },

        changePage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.purchaseAging(page);
            }
        }
    },
    filters: {
        currency(value) {
            if (!value) return '0.00';
            return parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2 });
        }
    }
};
</script>
