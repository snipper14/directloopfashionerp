<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <etim-stock-nav />
            </div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">   Item Types Code</div>

                    <div class="card-body">
                        <Button
                            type="primary"
                            @click="add_product_type_code_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                            Item Type</Button
                        >

                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Desc</th>
                                    <th>Code</th>

                                    <th scope="col">Edit</th>
                                    <th>Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in item_class_data.data"
                                    :key="i"
                                >
                                    <td>{{ data.id }}</td>
                                    <td>{{ data.name }}</td>
                                    <td>
                                        {{ data.description }}
                                    </td>
                                    <td>
                                        {{ data.code }}
                                    </td>

                                    <td>
                                        <button
                                            class="btn btn-secondary custom-button"
                                            to="#"
                                            @click="updateDialogue(data)"
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
                                        >
                                            del
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Modal
                            v-model="add_product_type_code_modal"
                            title="Add Item Type Code "
                        >
                            <div class="row">
                                <div
                                    v-for="(value, key) in form_data"
                                    :key="key"
                                    class="col-md-9 form-group"
                                >
                                    <label :for="key">{{
                                        key.replace(/_/g, " ").toUpperCase()
                                    }}</label>
                                    <input
                                        v-if="typeof value === 'boolean'"
                                        v-model="form_data[key]"
                                        type="checkbox"
                                        class="form-control"
                                    />
                                    <input
                                        v-else
                                        v-model="form_data[key]"
                                        type="text"
                                        class="form-control"
                                        :id="key"
                                    />
                                </div>

                                <div
                                    class="col-9 d-flex justify-content-center"
                                >
                                    <button
                                        class="btn btn-secondary"
                                        type="secondary"
                                        @click="saveRecord()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>
                        <Modal
                            v-model="update_product_category_modal"
                            title="Update Item Type Code  "
                        >
                            <div class="row">
                                <div
                                    v-for="(value, key) in update_form_data"
                                    :key="key"
                                    class="col-md-9 form-group"
                                >
                                    <label :for="key">{{
                                        key.replace(/_/g, " ").toUpperCase()
                                    }}</label>
                                    <input
                                        v-if="typeof value === 'boolean'"
                                        v-model="update_form_data[key]"
                                        type="checkbox"
                                        class="form-control"
                                    />
                                    <input
                                        v-else
                                        v-model="update_form_data[key]"
                                        type="text"
                                        class="form-control"
                                        :id="key"
                                    />
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
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import EtimStockNav from "../EtimStockNav.vue";
export default {
    components: { EtimStockNav },
    data() {
        return {
            update_product_category_modal: false,
            add_product_type_code_modal: false,
            isLoading: false,
            dept_data: null,
            update_id: null,
            params: {
                search: "",
            },
            form_data: {
                name: "",

                code: "",
                description: "",
            },
            update_form_data: {
                name: "",
                code: "",
                description: "",
            },
            item_class_data: [],
        };
    },
    methods: {
        async deleteProductCategory(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/etims_item_type_code/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.item_class_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async saveRecord() {
            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/etims_item_type_code/create",
                this.form_data
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

              this.form_data.name = "";
                this.form_data.code = "";
                this.form_data.description = "";
            } else {
                this.form_error(res);
            }
        },
        async updateProductCategory() {
            this.showLoader();
            const obj1 = { id: this.update_id };
            const combinedObject = Object.assign(
                {},
                obj1,
                this.update_form_data
            );
            const res = await this.callApi(
                "put",
                "data/etims_item_type_code/update",
                combinedObject
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.update_product_category_modal = false;
                this.form_data.code = "";
                this.form_data.description = "";
                this.update_id = null;
                this.concurrentApiReq() 
            } else {
                this.form_error(res);
            }
        },
        updateDialogue(data) {
            this.update_id = data.id;
            this.update_form_data.name = data.name;
            this.update_form_data.description = data.description;
            this.update_form_data.code = data.code;
            this.update_product_category_modal = true;
        },

        async getItemType(page) {
            //   this.showLoader();
            const res = await this.getApi("data/etims_item_type_code/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });
            //   this.hideLoader();
            if (res.status == 200) {
                this.item_class_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([this.getItemType(1)]).then(
                function (results) {
                    return results;
                }
            );
            this.hideLoader();
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
