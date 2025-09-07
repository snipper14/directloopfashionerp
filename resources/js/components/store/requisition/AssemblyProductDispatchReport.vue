<template>
    <div>
        <fieldset class="border">
            <legend class="text-center">
                Assembly Product Dispatch Report
            </legend>

            <div class="row">
                <div class="col-3">
                    <div class="form-group d-flex flex-column">
                        <label for="">Ordered By *</label>

                        <Select
                            v-model="search_query.user_id"
                            style="width:200px"
                            @on-change="changeUser"
                        >
                            <Option value="">All</Option>
                            <Option
                                v-for="item in user_data"
                                :value="item.id"
                                :key="item.id"
                                >{{ item.name }}</Option
                            >
                        </Select>
                    </div>
                </div>

                <div class="col-3">
                    <div class="form-group">
                        <div class="form-group">
                            <label>Issue Date From *</label>
                            <date-picker
                                v-model="search_query.from"
                                valueType="format"
                                type="datetime"
                            ></date-picker>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <div class="form-group">
                            <label>Issue Date To *</label>
                            <date-picker
                                v-model="search_query.to"
                                valueType="format"
                                type="datetime"
                            ></date-picker>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-4 form-group">
                    <label>Req No. </label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="search_query.req_id"
                        autocomplete="new-user-street-address"
                        placeholder="Code"
                    />
                </div>

                <div class=" col-4 form-group">
                    <label>Product Name </label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="search_query.product_name"
                        autocomplete="new-user-street-address"
                        placeholder="Product Name"
                    />
                </div>
                <div class=" col-4 form-group">
                    <label>Raw Materials </label>
                    <input
                        type="text"
                        class="form-control"
                        v-model="search_query.raw_material"
                        autocomplete="new-user-street-address"
                        placeholder="Product Name"
                    />
                </div>
                <div class="col-3">
                    <button
                        class="btn btn-secondary btn-sm mt-3"
                        @click="clearSearchFields()"
                    >
                        Clear Search
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12 " v-if="req_data">
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <td>Date Requested</td>
                                    <td>Req No</td>
                                    <th scope="col">Order By</th>
                                    <th scope="col">Dispatched By</th>
                                    <th>ManufacturedItem Code</th>
                                    <th scope="col">Manufactured Item</th>
                                    <th scope="col">Manufactured Qty</th>
                                    <th>Raw Material Code</th>
                                    <th scope="col">Raw Material</th>

                                    <th scope="col">Order Qty</th>
                                    <th scope="col">Dispatched Qty</th>
                                    <th scope="col">pending qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in req_data.data"
                                    :key="i"
                                    :class="{
                                        'low-prod':
                                            data.available_qty < data.qty_issued
                                    }"
                                >
                                    <td>{{ data.issue_date }}</td>
                                    <td>{{ data.req_id }}</td>
                                    <td>
                                        {{ data.user ? data.user.name : "" }}
                                    </td>
                                    <td>
                                        {{
                                            data.issuer ? data.issuer.name : ""
                                        }}
                                    </td>
                                    <td>
                                        {{ data.stock ? data.stock.code : "" }}
                                    </td>
                                    <td>
                                        {{
                                            data.stock
                                                ? data.stock.product_name
                                                : ""
                                        }}
                                    </td>
                                    <td>{{ cashFormatter(data.final_qty) }}</td>
                                    <td>
                                        {{
                                            data.ingredient
                                                ? data.ingredient.code
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{
                                            data.ingredient
                                                ? data.ingredient.product_name
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.order_qty) }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.dispatch_qty) }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.pending_qty) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        v-if="req_data !== null"
                        v-bind:results="req_data"
                        v-on:get-page="fetchReport"
                    ></Pagination>
                    Items Count {{ req_data.total }}
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

            <!-- </form> -->
        </fieldset>
    </div>
</template>
<script>
import Pagination from "../../utilities/Pagination.vue";
export default {
    props: ["user_data"],
    data() {
        return {
            search_query: {
                req_id: "",
                product_name: "",
                user_id: "",
                from: "",
                to: "",
                raw_material: ""
            },
            req_data: null,

            search_results: []
        };
    },
    components: {
        Pagination
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.searchRequests();
            }, 500)
        }
    },
    mounted() {
        this.fetchReport(1);
    },
    methods: {
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/dispatch_assembly_req/fetchDispatchReport",
                {
                    params: {
                        ...this.search_query
                    }
                }
            );
            this.hideLoader();
            var manufacture_product_array = res.data;

            for (var i = 0; i < manufacture_product_array.length; i++) {
                manufacture_product_array[i].stock = manufacture_product_array[
                    i
                ].stock
                    ? manufacture_product_array[i].stock.product_name
                    : "";
                manufacture_product_array[
                    i
                ].ingredient = manufacture_product_array[i].ingredient
                    ? manufacture_product_array[i].ingredient.product_name
                    : "";

                manufacture_product_array[i].branch = manufacture_product_array[
                    i
                ].branch
                    ? manufacture_product_array[i].branch.branch
                    : "";
                manufacture_product_array[i].user = manufacture_product_array[i]
                    .user
                    ? manufacture_product_array[i].user.name
                    : "";
                manufacture_product_array[i].issuer = manufacture_product_array[
                    i
                ].issuer
                    ? manufacture_product_array[i].issuer.name
                    : "";
                manufacture_product_array[
                    i
                ].department = manufacture_product_array[i].department
                    ? manufacture_product_array[i].department.department
                    : "";
            }

            return manufacture_product_array;
        },
        clearSearchFields() {
            Object.keys(this.search_query).forEach(
                key => (this.search_query[key] = "")
            );
        },

        async changeUser() {
            let index = this.user_data.findIndex(
                user => user.id == this.search_query.user_id
            );
            this.search_query.user_id = this.user_data[index].id;
            this.fetchReport(1);
        },
        async fetchReport(page) {
            this.showLoader();
            this.search_query.from = this.formatDateTime(
                this.search_query.from
            );
            this.search_query.to = this.formatDateTime(this.search_query.to);
            const res = await this.getApi(
                "data/dispatch_assembly_req/fetchDispatchReport",
                {
                    params: {
                        page,

                        ...this.search_query
                    }
                }
            );

            this.hideLoader();
            if (res.status === 200) {
                this.req_data = res.data;
            } else {
                this.swr("Server error");
            }
        },

        async searchRequests() {
            if (
                this.search_query.req_id.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                this.fetchRequest(1);
            } else {
                this.fetchRequest(1);
            }
        },
        async fetchRequest(page) {
            this.search_query.from = this.formatDateTime(
                this.search_query.from
            );
            this.search_query.to = this.formatDateTime(this.search_query.to);

            const res = await this.getApi(
                "data/dispatch_assembly_req/fetchDispatchReport",
                {
                    params: {
                        page,

                        ...this.search_query
                    }
                }
            );

            this.req_data = res.data;
        }
    }
};
</script>
<style scoped>
.low-prod {
    background: #ff8a80 !important;
}
</style>
