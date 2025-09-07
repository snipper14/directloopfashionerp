<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md">
                <etim-stock-nav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Product Categories</div>

                    <div class="card-body">
                        <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="add_product_category_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                            Product Category</Button
                        >
                        <input
                            type="text"
                            placeholder="search"
                            v-model="params.search"
                            class="form-control"
                        />

                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Location</th>
                                    <th>Show POS</th>
                                    <th>Banner</th>
                                    <th scope="col">Created At</th>

                                    <th scope="col">Edit</th>
                                    <th>Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(
                                        data, i
                                    ) in product_category_data.data"
                                    :key="i"
                                >
                                    <td>{{ data.id }}</td>
                                    <td>{{ data.name }}</td>
                                    <td>
                                        {{
                                            data.department
                                                ? data.department.department
                                                : ""
                                        }}
                                    </td>
                                    <td>
                                        <p v-if="data.show_pos == 1">Show</p>
                                        <p v-else>Hide</p>
                                    </td>
                                    <td>
                                        <img
                                            height="70"
                                            :src="data.img_url"
                                            alt="No Banner"
                                        />
                                    </td>
                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>

                                    <td>
                                        <button
                                            class="btn btn-secondary custom-button"
                                            to="#"
                                            @click="updateDialogue(data)"
                                            v-if="isUpdatePermitted"
                                        >
                                            Edit
                                        </button>
                                    </td>

                                    <td>
                                        <button
                                            @click="
                                                deleteProductCategory(
                                                    data.id,
                                                    i
                                                )
                                            "
                                            class="btn btn-danger custom-button"
                                            v-if="isDeletePermitted"
                                        >
                                            del
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="product_category_data !== null"
                            v-bind:results="product_category_data"
                            v-on:get-page="getProductCategory"
                        ></Pagination>
                        <Modal
                            v-model="add_product_category_modal"
                            title="Add ProductCategory"
                        >
                            <div class="row">
                                <div class="col-9">
                                    <center>
                                        <v-progress-circular
                                            v-if="isLoading"
                                            indeterminate
                                            color="primary"
                                        ></v-progress-circular>
                                    </center>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            autocomplete="dfdfd-ffdfd-fd"
                                            placeholder="Product Category"
                                            v-model="form_data.name"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Location*</label>

                                        <Select
                                            v-model="form_data.department_id"
                                        >
                                            <Option
                                                v-for="item in dept_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="phone">Show POS *</label>
                                        <input
                                            type="checkbox"
                                            v-model="form_data.show_pos"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Banner *</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            v-on:change="selectFile"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            autocomplete="dfdfd-ffdfd-fd"
                                            placeholder=" Category Details"
                                            v-model="form_data.details"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="col-9 d-flex justify-content-center"
                                >
                                    <button
                                        class="btn btn-secondary"
                                        type="secondary"
                                        @click="saveProductCategory()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <Modal
                            v-model="update_product_category_modal"
                            title="Update ProductCategory"
                        >
                            <div class="row">
                                <div class="col-9">
                                    <center>
                                        <v-progress-circular
                                            v-if="isLoading"
                                            indeterminate
                                            color="primary"
                                        ></v-progress-circular>
                                    </center>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            autocomplete="dsds-dsds-fdf"
                                            v-model="update_form_data.name"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Location*</label>

                                        <Select
                                            v-model="
                                                update_form_data.department_id
                                            "
                                        >
                                            <Option
                                                v-for="item in dept_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="phone">Show POS *</label>
                                        <input
                                            type="checkbox"
                                            v-model="update_form_data.show_pos"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Banner *</label>
                                        <input
                                            type="file"
                                            class="form-control"
                                            v-on:change="selectUpdateFile"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            autocomplete="dsds-dsds-fdf"
                                            v-model="update_form_data.details"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div
                                    class="col-9 d-flex justify-content-center"
                                >
                                    <button
                                        class="btn btn-secondary"
                                        type="secondary"
                                        @click="updateProductCategory()"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </Modal>
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
import Pagination from "../../../utilities/Pagination.vue";
import StockNav from "../../StockNav.vue";
import EtimStockNav from "../etims_stock/EtimStockNav.vue";
export default {
    components: { StockNav, EtimStockNav, Pagination },
    data() {
        return {
            update_product_category_modal: false,
            add_product_category_modal: false,
            isLoading: false,
            dept_data: null,
            form_data: {
                department_id: null,
                name: "",
                banner: null,
                details: "",
                show_pos: false,
            },
            update_form_data: {
                department_id: null,
                name: "",
                banner: null,
                details: "",
                show_pos: true,
            },
            params: { search: "" },
            product_category_data: [],
        };
    },
     watch: {
        params: {
            handler() {
                this.getProductCategory(1);
            },
            deep: true,
        },
       
    },
    methods: {
        selectFile(event) {
            this.form_data.banner = event.target.files[0];
        },
        selectUpdateFile(event) {
            this.update_form_data.banner = event.target.files[0];
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async deleteProductCategory(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/product_category/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.product_category_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async saveProductCategory() {
            this.showLoader();
            const data = new FormData();
            data.append("file", this.form_data.banner);
            const json = JSON.stringify(this.form_data);
            data.append("data", json);
            const res = await this.callApi(
                "post",
                "data/product_category/create",
                data
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.add_product_category_modal = false;
                this.form_data.name = "";
                this.form_data.details = "";
                this.form_data.banner = null;
                this.form_data.department_id = null;
                this.product_category_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        updateDialogue(data) {
            this.update_product_category_modal = true;
            this.update_form_data = data;
            this.update_form_data.banner = null;
            this.update_form_data.show_pos = data.show_pos == 1 ? true : false;
        },
        async updateProductCategory() {
            this.showLoader();
            const data = new FormData();
            data.append("file", this.update_form_data.banner);
            const json = JSON.stringify(this.update_form_data);
            data.append("data", json);
            const res = await this.callApi(
                "post",
                "data/product_category/update",
                data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.update_product_category_modal = false;
                this.update_form_data.name = "";
                this.update_form_data.details = "";
                this.update_form_data.banner = null;
                this.product_category_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async getProductCategory(page) {
            //   this.showLoader();
            const res = await this.getApi("data/product_category/fetch", {
                params: { page, ...this.params },
            });
            //   this.hideLoader();
            if (res.status == 200) {
                this.product_category_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getProductCategory(1),
                this.fetchDept(),
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
