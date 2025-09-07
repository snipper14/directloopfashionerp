<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <asset-manager-nav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-5">
                            <h5>Asset Categories</h5>
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
                    <div class="row"></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <v-btn
                                    small
                                    color="secondary"
                                    @click="add_asset_category_modal = true"
                                    >Add New Category</v-btn
                                >
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Category</th>

                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in asset_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.category }}
                                    </td>

                                    <td>
                                        {{ data.description }}
                                    </td>

                                    <td>
                                        <button
                                        v-if="isDeletePermitted"
                                        title="delete"
                                            class="btn btn-danger custom-button"
                                            @click="deleteCategory(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                        <button
                                        v-if="isUpdatePermitted"
                                        title="update"
                                            class="btn btn-primary custom-button"
                                            @click="editCate(data)"
                                        >
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <Modal
            v-model="add_asset_category_modal"
            title="Asset Category"
            width="600px"
        >
            <div class="row">
                <div class="col-md-10 form-group">
                    <label for="">Asset Category</label>
                    <input
                        placeholder="Asset Category"
                        type="text"
                        v-model="form_data.category"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10 form-group">
                    <label for="">Description</label>
                    <input
                        placeholder="Description"
                        type="text"
                        v-model="form_data.description"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10">
                    <button
                        class="btn btn-primary btn-small"
                        small
                        title="write"
                        v-if="isWritePermitted"
                        @click="saveInfo()"
                    >
                        Save
                    </button>
                </div>
            </div>
        </Modal>
        <Modal v-model="edit_modal" title="Edit Asset Category" width="600px">
            <div class="row">
                <div class="col-md-10 form-group">
                    <label for="">Asset Category</label>
                    <input
                        placeholder="Asset Category"
                        type="text"
                        v-model="edit_form_data.category"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10 form-group">
                    <label for="">Description</label>
                    <input
                        placeholder="Description"
                        type="text"
                        v-model="edit_form_data.description"
                        class="form-control"
                    />
                </div>
                <div class="col-md-10">
                    <button
                        class="btn btn-primary btn-small"
                        small
                        title="update"
                        v-if="isUpdatePermitted"
                        @click="updateInfo()"
                    >
                        Update
                    </button>
                </div>
            </div>
        </Modal>

        <notifications group="foo" />
    </div>
</template>

<script>
import AssetManagerNav from "./AssetManagerNav.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";
import NewAssetModal from "./NewAssetModal.vue";
export default {
    data() {
        return {
            add_asset_category_modal: false,
            active: {
                view_details: false,
                dashboard: true,
            },
            edit_modal: false,
            search: "",
            sum_amount_paid: 0,
            asset_data: [],
            form_data: {
                category: "",
                description: "",
            },
            edit_form_data: {
                category: "",
                description: "",
                id: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        AssetManagerNav,
        Unauthorized,
        Pagination,
        NewAssetModal,
    },
    mounted() {
        this.fetchAssetsCategory();
    },
    watch: {},
    methods: {
        async editCate(data) {
            this.edit_form_data = data;
            this.edit_modal = true;
        },
        async deleteCategory(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/assets_category/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.w("deleted ");
                    this.asset_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async saveInfo() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/assets_category/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.add_asset_category_modal = false;
                this.asset_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async updateInfo() {
            this.showLoader();
            const res = await this.callApi(
                "PUT",
                "data/assets_category/update",
                this.edit_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.edit_modal = false;
                this.asset_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        async fetchAssetsCategory(page) {
            this.showLoader();
            const res = await this.getApi("data/assets_category/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.asset_data = res.data;

                this.sum_amount_paid = this.asset_data.data.reduce(function (
                    sum,
                    val
                ) {
                    return sum + val.total_amount_paid;
                },
                0);
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
