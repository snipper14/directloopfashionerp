<template>
    <div class="container">
        <v-app>
                <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Add Prices to {{ edit_data.price_group }}
                    </div>

                    <div class="card-body">
                        <div class="row">
                <div class="col-md-5">
                    <upload-price-group
                        v-on:refresh-data="refreshStock"
                        :edit_data="edit_data"
                    />
                </div>
            </div>
                        <div class="row">
                            <div class="col-3 border border-secondary">
                                <div class="form-group">
                                    <label>Code. </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="search_query.code"
                                        autocomplete="new-user-street-address"
                                        placeholder="Code"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Product Name </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        v-model="search_query.product_name"
                                        autocomplete="new-user-street-address"
                                        placeholder="Product Name"
                                    />
                                </div>
                                <div class="form-group">
                                    <button
                                        class="btn btn-secondary btn-sm"
                                        @click="search_results = []"
                                    >
                                        Clear Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-9 border border-secondary">
                                <div class="table-responsive">
                                    <table
                                        class="table table-sm table-striped table-bordered"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Code</th>
                                                <th scope="col">Item Name</th>
                                                <th scope="col">
                                                    Purchase Price
                                                </th>
                                                <th scope="col">
                                                    Selling Price
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    data, i
                                                ) in search_results"
                                                :key="i"
                                            >
                                                <td>
                                                    {{ data.code }}
                                                </td>
                                                <td>{{ data.product_name }}</td>
                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.purchase_price
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    <input
                                                        class="form-control"
                                                        type="number"
                                                        style="width: 80px"
                                                        v-model="
                                                            data.selling_price
                                                        "
                                                    />
                                                </td>

                                                <td>
                                                    <button
                                                        class="btn btn-primary btn-sm"
                                                        @click="
                                                            submitRecords(data)
                                                        "
                                                    >
                                                        Add
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>code</th>
                                            <th scope="col">Item</th>

                                            <th scope="col"> S.Price</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in data.data"
                                            :key="i"
                                        >
                                            <td>{{ data.stock.code }}</td>
                                            <td>
                                                
                                                    {{
                                                        data.stock.name
                                                    }}
                                            </td>
                                            <td>
                                               {{cashFormatter(data.selling_price)}}
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        deleteData(data.id, i)
                                                    "
                                                    class="danger"
                                                >
                                                    Delete
                                                </router-link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <pagination
                                v-if="data !== null"
                                v-bind:results="data"
                                v-on:get-page="fetch"
                            ></pagination>
                            Items Count {{ data.total }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </v-app>
    
    </div>
</template>

<script>
import Pagination from '../../../utilities/Pagination.vue';
import ProductSearch from "../../../utilities/ProductSearch.vue";
import UploadPriceGroup from './UploadPriceGroup.vue';
export default {
    components: { ProductSearch,UploadPriceGroup, Pagination },
    props: ["edit_data"],
        
    data() {
        return {
            search_results: [],
            data: [],
            search_query: {
                code: "",
                product_name: "",
            },
            params: { search: "" },
        };
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    mounted() {
        console.log("Component mounted.");
        this.fetch(1);
    },
    methods: {
          async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/price_group_amount/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async fetch(page) {
            this.showLoader();
            const res = await this.getApi("data/price_group_amount/fetch", {
                params: {
                    page: page,
                    ...this.params,
                    price_group_id:this.edit_data.id
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async submitRecords(data) {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/price_group_amount/create",

                {
                    stock_id: data.stock_id,
                    price_group_id: this.edit_data.id,
                    selling_price: data.selling_price,
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Saved ");

                this.data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async searchProduct() {
            {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = res.data.map((obj) => {
                    return {
                        stock_id: obj.id,
                        qty: 1,
                        code: obj.code,
                        product_name: obj.product_name,
                        selling_price: obj.selling_price,
                        purchase_price: obj.purchase_price,
                    };
                });
                return modif;
            }
        },
    },
};
</script>
