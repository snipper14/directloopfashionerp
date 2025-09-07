<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <etim-stock-nav />
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">Shelf Allocation</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for=""> Search Stock</label>
                                <Select
                                    v-model="form_data.stock_id"
                                    filterable
                                    :remote-method="searchProducts"
                                    :loading="loading1"
                                >
                                    <Option
                                        v-for="(
                                            option, index
                                        ) in search_stock_list"
                                        :value="option.value"
                                        :key="index"
                                        >{{ option.label }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group d-flex flex-column">
                                    <label for=""> Search Shelf</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchShelf"
                                        :options="shelf_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.shelf_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Search Shelf "
                                    />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <v-btn
                                    @click="saveShelf()"
                                    color="primary"
                                    x-small
                                    :loading="btn_loading"
                                    >Save</v-btn
                                >
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12"><import-shelf /></div>
                        </div>
                        <div class="row">
                            <input
                                type="text"
                                class="form-control"
                                v-model="params.search"
                            />
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>Stock<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_stock
                                                "
                                                sort-key="sort_stock"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                            <th>Shelf<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_shelf
                                                "
                                                sort-key="sort_shelf"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>

                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in allocation_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                {{ data.stock.name }}
                                            </td>
                                            <td>
                                                {{ data.shelf.name }}
                                            </td>
                                            <td>
                                                <v-btn
                                                    @click="
                                                        destroyAllocation(
                                                            data.id,
                                                            i
                                                        )
                                                    "
                                                    color="danger"
                                                    x-small
                                                    >Delete</v-btn
                                                >
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <pagination
                                v-if="allocation_data !== null"
                                v-bind:results="allocation_data"
                                v-on:get-page="fetchAllocation"
                            ></pagination>
                            Items Count {{ allocation_data.total }}
                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="fetchExcel"
                                        type="xls"
                                        worksheet="My Worksheet"
                                        name="file.xls"
                                    >
                                        <v-btn color="primary" x-small
                                            >Export Excel</v-btn
                                        >
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="fetchExcel"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="file.csv"
                                    >
                                        <v-btn color="primary" x-small
                                            >Export Excel CSV</v-btn
                                        >
                                    </export-excel>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import EtimStockNav from "./etims_stock/EtimStockNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import ImportShelf from "./ImportShelf.vue";
import SortButtons from '../../utilities/SortButtons.vue';
export default {
    components: { EtimStockNav, Treeselect, Pagination, ImportShelf, SortButtons },
    data() {
        return {
            form_data: {
                stock_id: null,
                shelf_id: null,
            },
            params: {
                search: "",
            },
            sort_params: {
                sort_stock: "",
                sort_shelf: "",
            },
            btn_loading: false,
            search_stock_list: [],
            loading1: false,
            is_loading: true,
            shelf_select_data: null,
            allocation_data: [],
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchAllocation(1);
    },
    watch: {
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
               this.fetchAllocation(1);
            }, 500),
        },
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.is_loading = false;
                this.fetchAllocation(1);
            }, 500),
        },
    },
    methods: {
         updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async fetchExcel() {
            this.showLoader();

            const res = await this.getApi("data/shelves/fetchAllocation", {
                params: {
                    ...this.params,
                    ...this.sort_params,
                },
            });
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    stock: data.stock.name,
                    shelf: data.shelf.name,
                    branch: data.branch.branch,
                });
            });
            return data_array;
        },
        async destroyAllocation(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/shelves/destroyAllocation/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.allocation_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async fetchAllocation(page) {
            this.is_loading ? this.showLoader() : "";
            const res = await this.getApi("data/shelves/fetchAllocation", {
                params: {
                    page,
                    ...this.params,
                    ...this.sort_params,
                },
            });
            this.is_loading ? this.hideLoader() : "";

            if (res.status === 200) {
                this.allocation_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async saveShelf() {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/shelves/allocate",
                this.form_data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.s("Saved");
                this.form_data.shelf_id = null;
                this.form_data.stock_id = null;
                this.allocation_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchShelf() {
            const res = await this.getApi("data/shelves/fetch", {});

            if (res.status == 200) {
                this.shelf_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        searchProducts: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;
                const res = await this.getApi("data/stock/search_items", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_stock_list = this.modifyKey(res.data).filter(
                    (item) =>
                        item.label.toLowerCase().indexOf(query.toLowerCase()) >
                        -1
                );
            }
        }, 500),

        modifyKey(data) {
            let modif = data.map((obj) => {
                return {
                    value: obj.id,
                    label: obj.product_name,
                    cost_price: obj.purchase_price,
                    production_cost: obj.production_cost,
                };
            });
            return modif;
        },
    },
};
</script>
