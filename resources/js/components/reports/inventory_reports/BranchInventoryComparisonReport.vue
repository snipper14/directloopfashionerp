<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><branch-inventory-nav/></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">
                        <h3 style="color: red">
                            Branch Inventory Comparison Reports
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label for="">Search Code</label>
                                <input
                                    type="text"
                                    v-model="params.code"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                             <div class="form-group">
                                <label for="">Search</label>
                                <input
                                    type="text"
                                    v-model="params.search"
                                    placeholder="Search"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Stock Code</th>
                                        <th scope="col">Stock Name</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.code }}</td>
                                        <td>{{ data.stock_name }}</td>
                                        <td
                                            v-for="(value, x) in data.data"
                                            :key="x"
                                        >
                                            <tr>
                                                <td>{{ value.branch }}</td>
                                                <td class="cust-qty">{{ value.branch_qty }}</td>
                                            </tr>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchData"
                        ></pagination>
                        Items Count {{ sale_data.total }}
                 
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import BranchInventoryNav from './BranchInventoryNav.vue';

export default {
    data() {
        return {
            modal: {
                details: false,
            },
            package_data: null,

            room_data: null,
            params: {
                search: "",
            },
            order_data_details: null,
            sum_row_total: 0,
            sum_row_vat: 0,
            total_cost: 0,
            sum_service_charge_amount: 0,
            isLoading: true,
            search: "",

            sale_data: [],
            all_sale_data: [],
        };
    },
    components: {
        Treeselect,
        Unauthorized,
        Pagination,
        BranchInventoryNav,
        
    },
    watch: {
        params: {
             deep: true,
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
      
    },
    methods: {
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/pos_sale/detailsReport", {
                params: {
                    search: this.search,
                    ...this.params,
                    currentRoute: this.$route.name,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        stock: array_item.stock.product_name,
                        order_no: array_item.order_no,
                        batch_no: array_item.batch_no,
                        order_date: array_item.order_date,
                        qty: array_item.qty,
                        cost_price: array_item.cost_price,
                        price: array_item.price,
                        row_total: array_item.row_total,
                        row_vat: array_item.row_vat,

                        cashier: array_item.cashier
                            ? array_item.cashier.name
                            : "",
                        sales_person: array_item.user
                            ? array_item.user.name
                            : "",
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },

        async fetchData(page) {
            const res = await this.getApi(
                "data/inventory/fetchBranchComparisonReport",

                {
                    params: {
                        page,
                        ...this.params,
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
.cust-qty{
    background: rgb(215, 110, 35);
    color: aliceblue;
}
</style>