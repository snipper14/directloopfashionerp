<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <etim-stock-nav />
            </div>
            <div class="col-md-10">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <h5>Merged Stock</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="params.search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Deleted Item Name</th>
                                        <th scope="col">qty</th>
                                        <th>User</th>
                                        <th>Created at</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in menu_product_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.stock.name }}
                                        </td>
                                        <td>
                                            {{ data.deleted_stock.name }}
                                        </td>
                                        <td>{{ data.qty_merged }}</td>
                                        <td>{{data.user.name}}</td>
                                        <td>{{formatMonthDateTime(data.created_at)}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="menu_product_data !== null"
                            v-bind:results="menu_product_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ menu_product_data.total }}

                        <div class="row">
                            <div class="col-8 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchNormalExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                    ><v-btn color="success" x-small>
                                        Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchNormalExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="erp_stock.csv"
                                >
                                    <v-btn color="secondary" x-small>
                                        Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import EtimStockNav from "./etims_stock/EtimStockNav.vue";

export default {
    data() {
        return {
            menu_product_data: [],
            branch: this.$store.state.branch,
            params: {
                search: "",
            },
        };
    },
    components: {
        Pagination,
        EtimStockNav,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                this.concurrentApiReq();
            },
            deep: true,
        },
    },
    methods: {
        async concurrentApiReq() {
            await Promise.all([this.fetch(1)]);
        },
        async fetch(page) {
            this.showLoader();
            const res = await this.getApi("data/merge_stock/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.menu_product_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchNormalExcel() {
            this.showLoader();
            const res = await this.getApi("data/merge_stock/fetch", {
                params: {
                   
                    ...this.params,
                },
            });
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    original: data.stock.name,

                    deleted_stock: data.deleted_stock.name,
                    qty_merged: data.qty_merged,
                });
            });
            return data_array;
        },
    },
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
</style>
