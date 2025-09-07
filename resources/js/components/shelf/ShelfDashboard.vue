<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <dept-location-nav />
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Shelf Management</div>

                    <div class="card-body">
                        <v-btn color="primary" x-small @click="add_modal = true"
                            >Add Shelf</v-btn
                        >
                        <input
                            type="text"
                            class="form-control"
                            placeholder="search "
                            v-model="params.search"
                        />
                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th>
                                        Name<sort-buttons
                                            :initial-sort="
                                                sort_params.sort_shelf
                                            "
                                            sort-key="sort_shelf"
                                            @update-sort="updateSortParameter"
                                        />
                                    </th>

                                    <th scope="col">Details</th>
                                    <th scope="col">Edit</th>
                                    <th>Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in shelf_data.data"
                                    :key="i"
                                >
                                    <td>{{ data.name }}</td>
                                    <td>{{ data.description }}</td>

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
                                            @click="deleteRecord(data.id, i)"
                                            class="btn btn-danger custom-button"
                                        >
                                            del
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="shelf_data !== null"
                            v-bind:results="shelf_data"
                            v-on:get-page="fetch"
                        ></pagination>
                        Items Count {{ shelf_data.total }}
                    </div>
                    <div class="row">
                        <div class="col-4 d-flex">
                            <export-excel
                                class="btn btn-default btn-export ml-2"
                                :fetch="fetchExcel"
                                type="xls"
                                worksheet="My Worksheet"
                                name="file.xls"
                            >
                                <v-btn color="primary">Export Excel</v-btn>
                            </export-excel>

                            <export-excel
                                class="btn btn-default btn-export ml-2"
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="file.csv"
                            >
                                <v-btn color="primary">Export Excel CSV</v-btn>
                            </export-excel>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="add_modal"
            ><add-shelf v-if="add_modal" @dashboard-active="dismissDialogue"
        /></Modal>
        <Modal v-model="update_modal"
            ><update-shelf-modal
                v-if="update_modal"
                :details_data="details_data"
                @dashboard-active="dismissDialogue"
        /></Modal>
    </div>
</template>

<script>
import DeptLocationNav from "../departments/DeptLocationNav.vue";
import Pagination from "../utilities/Pagination.vue";
import SortButtons from "../utilities/SortButtons.vue";
import AddShelf from "./AddShelf.vue";
import UpdateShelfModal from "./UpdateShelfModal.vue";
export default {
    components: {
        DeptLocationNav,
        AddShelf,
        Pagination,
        UpdateShelfModal,
        SortButtons,
    },
    data() {
        return {
            is_loading: true,
            add_modal: false,
            update_modal: false,
            details_data: null,
            shelf_data: [],
            params: {
                search: "",
            },
            sort_params: {
                sort_shelf: "asc",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.is_loading = false;
                this.concurrentApiReq();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.isLoading = true;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
        async fetchExcel() {
            const res = await this.getApi("data/shelves/fetch", "");
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.id,
                    name: data.name,
                    description: data.description,
                });
            });
            return data_array;
        },
        updateDialogue(data) {
            this.update_modal = true;

            this.details_data = data;
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/shelves/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.shelf_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        dismissDialogue() {
            this.add_modal = false;
            this.update_modal = false;
            this.concurrentApiReq();
        },
        async concurrentApiReq() {
            this.is_loading ? this.showLoader() : "";
            await Promise.all([this.fetch(1)]);
            this.is_loading ? this.hideLoader() : "";
        },
        async fetch(page) {
            const res = await this.getApi("data/shelves/fetch", {
                params: {
                    page,
                    ...this.params,
                    ...this.sort_params,
                },
            });

            this.shelf_data = res.data;
        },
    },
};
</script>
