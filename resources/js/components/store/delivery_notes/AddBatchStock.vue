<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><b>Update Batch Items</b></h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group d-flex flex-column mr-2">
                                    <label for=""> Location*</label>

                                    <Select v-model="form_data.department_id">
                                        <Option
                                            v-for="item in department_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                               <div class="col-3">
                                  <label>Supplier Name*</label>

                                    <div class="form-group">
                                        <treeselect
                                            :auto-load-root-options="false"
                                            v-model="form_data.supplier_id"
                                            :multiple="false"
                                            :options="supplier_data"
                                            :show-count="true"
                                            placeholder="Select "
                                            :load-options="getAllSuppliers"
                                        />
                                    </div>
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
                                                <th>Expiry</th>
                                                <th scope="col">BatchNo</th>
                                                <th scope="col">Avail Qty</th>

                                                <th></th>
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
                                                    <date-picker
                                                        v-model="
                                                            data.expiry_date
                                                        "
                                                        valueType="format"
                                                    ></date-picker>
                                                </td>
                                                <td>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        style="width: 100px"
                                                        v-model="data.batch_no"
                                                    />
                                                </td>

                                                <td>
                                                    <input
                                                        style="width: 80px"
                                                        class="form-control"
                                                        type="number"
                                                        v-model="
                                                            data.qty_delivered
                                                        "
                                                    />
                                                </td>

                                                <td>
                                                    <button
                                                        v-if="isWritePermitted"
                                                        class="btn btn-primary btn-sm"
                                                        @click="
                                                            addBatchItems(data)
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
export default {
    data() {
        return {
            search_results: [],
            search_query: {
                code: "",
                product_name: "",
            },
            department_data: [],
              supplier_data: null,
            form_data: {
                department_id: null,
                supplier_id: null,
            },
        };
    },
     components: {
        Treeselect,

    },
    mounted() {
        this.fetchDepartment();
        console.log("Component mounted.");
    },
    watch: {
        search_query: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchProduct();
            }, 500),
        },
    },
    methods: {
        async getAllSuppliers() {
            const res = await this.getApi("data/suppliers/fetchAll", "");

            if (res.status === 200) {
                this.supplier_data = res.data.results;
                this.supplier_data = this.modifyProductKey();
            } else {
                this.swr("Server error try again later");
            }
        },
         modifyProductKey() {
            let modif = this.supplier_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.company_name,
                };
            });
            return modif;
        },
        async fetchDepartment() {
            const res = await this.getApi("data/dept/fetch", "");

            this.department_data = res.data;
        },
        async searchProduct() {
            if (
                this.search_query.code.length > 0 ||
                this.search_query.product_name.length > 0
            ) {
                const res = await this.getApi("data/stock/searchItem", {
                    params: {
                        ...this.search_query,
                    },
                });

                this.search_results = this.modifyResData(res.data);
            } else {
                this.search_results = [];
            }
        },
        modifyResData(data) {
            let modif = data.map((obj) => {
                return {
                    id: obj.id,

                    code: obj.code,
                    product_name: obj.product_name,
                    qty_delivered: 1,
                    expiry_date: null,
                };
            });
            return modif;
        },
        async addBatchItems(data) {
            const mergedJSON = { ...data, ...this.form_data };
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/po_receivable/addBatchItems",
                mergedJSON
            );
            this.hideLoader();

            if (res.status == 200) {
                this.s("saved");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
