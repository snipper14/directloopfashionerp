<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3"><etim-stock-nav/></div>
            <div class="col-md-8">
               
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h3>Deleted Stock Management</h3>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Code</th>
                                        <th scope="col">Item Name</th>

                                        <th>Department</th>
                                        <th scope="col">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in trashed_product_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.code }}
                                        </td>
                                        <td>
                                            {{ data.name }}
                                        </td>

                                        <td>
                                            {{ data.department.department }}
                                        </td>

                                        <td>
                                            <v-btn
                                                @click="restore(data, i)"
                                                v-if="isUpdatePermitted"
                                                small
                                                color="primary"
                                                dark
                                            >
                                                Restore
                                            </v-btn>
                                                 <v-btn
                                                @click="deletePermanent(data.id, i)"
                                                v-if="isAdmin"
                                                small
                                                color="error"
                                                dark
                                            >
                                                Permanent Delete
                                            </v-btn>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="trashed_product_data !== null"
                            v-bind:results="trashed_product_data"
                            v-on:get-page="fetch"
                        ></Pagination>
                        Items Count {{ trashed_product_data.total }}
                    </div>
                </div>

                <center v-else>
                    <b style="color: red; font-size: 1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";

import StockNav from "../StockNav.vue";
import EtimStockNav from './etims_stock/EtimStockNav.vue';
export default {
    data() {
        return {
            trashed_product_data: [],
            product_data: null,

            search: "",
            params: {
                name: "",
                description: "",
            },
        };
    },
    components: {
        Pagination,
        StockNav,
        EtimStockNav,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        },
    },
    methods: {
        async restore(data, i) {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/stock/restoreTrashed",
               {stock_id: data.id}
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("successfully restored");
                this.trashed_product_data.data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },

        async concurrentApiReq() {
            const res = await Promise.all([this.fetch(1)]).then(function (
                results
            ) {
                return results;
            });
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/stock/fetchTrashed", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.trashed_product_data = res.data;
            }
        },
 async deletePermanent(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/stock/destroy_permanent/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.trashed_product_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async fetch(page) {
            this.showLoader();
            const res = await this.getApi("data/stock/fetchTrashed", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.trashed_product_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
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
