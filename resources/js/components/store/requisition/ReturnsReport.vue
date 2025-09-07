<template>
    <div class="container">
        <div class="row justify-content-center">
           <div class="col-2">
            <RequisitioNav />
        </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Return Report</div>
                    
                    <div class="card-body">
                    <div class="form-group">
                        <input type="text" placeholder="Search" v-model="search" class="form-control">
                    </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Code</th>
                                    <th scope="col">Item Name</th>

                                    <th scope="col">Qty</th>
                                    <th>Returnee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in return_results.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.stock.code }}
                                    </td>
                                    <td>{{ data.stock.product_name }}</td>

                                    <td>
                                        {{data.qty_returned}}
                                        
                                    </td>
                                    <td>{{ data.return_user.name }}</td>
                                    <td>
                                        <router-link
                                            to="#"
                                            v-if="isDeletePermitted"
                                            class="danger"
                                            @click.native="delRecord(data)"
                                        >
                                            Delete
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="return_results !== null"
                            v-bind:results="return_results"
                            v-on:get-page="fetchReport"
                        ></Pagination>
                        Items Count {{ return_results.total }}
                        <div class="row" v-if="isDownloadPermitted">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
                <center v-else>
                    <b style="color:red;font-size:1.2rem"
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
import RequisitioNav from "./RequisitioNav.vue"
export default {
    data() {
        return {
            search: "",
            return_results: []
        };
    },
    mounted() {
        this.fetchReport(1);
    },
    components: {
        Pagination,
        RequisitioNav
    },
    watch: {
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchReport();
            }
        }
    },
    methods: {
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock_returns/fetch", {
                params: {
                    search: this.search.length >= 2 ? this.search : ""
                }
            });
            this.hideLoader();
            var return_array = res.data;

            for (var i = 0; i < return_array.length; i++) {
                return_array[i].stock = return_array[i].stock
                    ? return_array[i].stock.product_name
                    : "";

                return_array[i].user = return_array[i].user
                    ? return_array[i].user.name
                    : "";
                      return_array[i].return_user = return_array[i].return_user
                    ? return_array[i].return_user.name
                    : "";
            }

            return return_array;
        },
        async searchReport() {
            var page=1
            const res = await this.getApi("data/stock_returns/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : ""
                }
            });

            if (res.status === 200) {
                this.return_results = res.data;
            } else {
                this.servo();
            }
        },
        async fetchReport(page) {
            this.showLoader();

            const res = await this.getApi("data/stock_returns/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : ""
                }
            });

            this.hideLoader();
            if (res.status === 200) {
                this.return_results = res.data;
            } else {
                this.servo();
            }
        },

        async delRecord(data, i) {
            const shouldDel=await this.deleteDialogue()
            if(shouldDel){
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/stock_returns/destroy",
                {
                    qty_returned: data.qty_returned,
                    id: data.id,
                    stock_id: data.stock_id
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Deleted");
                this.return_results.data.splice(i, 1);
            } else {
                this.servo();
            }
            }
        }
    }
};
</script>
