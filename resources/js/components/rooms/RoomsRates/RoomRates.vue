<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><rooms-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Accomodation Rates</div>

                    <div class="card-body">
                        <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="create_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                        </Button>
                        <input
                            type="text"
                            class="form-control"
                            v-model="search"
                        />
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th>STANDARD</th>

                                    <th>PACKAGE</th>
                                    <th>RATE</th>
                                    <th>ROOM RATE</th>
                                    <th>MEAL RATE</th>
                                    <th scope="col">DETAILS</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in rooms_rate_data"
                                    :key="i"
                                >
                                    <td>{{ data.room_standard.name }}</td>

                                    <td>
                                        {{ data.room_package.name }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.rate) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.room_rate) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.meal_rate) }}
                                    </td>
                                    <td>
                                        {{ data.details }}
                                    </td>

                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="updateDialogue(data)"
                                            v-if="isUpdatePermitted"
                                        >
                                            Edit
                                        </router-link>
                                        <router-link
                                            to="#"
                                            class="danger"
                                            @click.native="
                                                deleteRecord(data.id, i)
                                            "
                                            v-if="isDeletePermitted"
                                        >
                                            Delete
                                        </router-link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                        <Modal v-model="create_modal" title="Add Rates ">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Room Standard*</label>

                                        <Select
                                            v-model="form_data.room_standard_id"
                                        >
                                            <Option
                                                v-for="item in room_standard_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.name }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Room Package *</label>

                                        <Select
                                            v-model="form_data.room_package_id"
                                        >
                                            <Option
                                                v-for="item in room_package_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.name }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>

                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Rates</label>
                                        <input
                                            type="number"
                                            v-model="form_data.rate"
                                            class="form-control"
                                        />
                                    </div>
                                </div>

                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Room Rates</label>
                                        <input
                                            type="number"
                                            v-model="form_data.room_rate"
                                            class="form-control"
                                        />
                                    </div>
                                </div>

                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Meal Rates</label>
                                        <input
                                            type="number"
                                            v-model="form_data.meal_rate"
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
                                        @click="saveInfo()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <Modal v-model="update_modal" title="Update Rates ">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Room Standard*</label>

                                        <Select
                                            v-model="
                                                update_form_data.room_standard_id
                                            "
                                            disabled
                                        >
                                            <Option
                                                v-for="item in room_standard_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.name }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Room Package *</label>

                                        <Select
                                            v-model="
                                                update_form_data.room_package_id
                                            "
                                            disabled
                                        >
                                            <Option
                                                v-for="item in room_package_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.name }}</Option
                                            >
                                        </Select>
                                    </div>
                                </div>

                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Rates</label>
                                        <input
                                            type="number"
                                            v-model="update_form_data.rate"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Room Rate</label>
                                        <input
                                            type="number"
                                            v-model="update_form_data.room_rate"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Meal Rate</label>
                                        <input
                                            type="number"
                                            v-model="update_form_data.meal_rate"
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
                                        @click="updateInfo()"
                                    >
                                        Update
                                    </button>
                                </div>
                            </div>
                        </Modal>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>

<script>
import _ from "lodash";
import RoomsNav from "../RoomsNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            update_modal: false,
            create_modal: false,
            isLoading: false,
            room_standard_data: [],
            room_package_data: [],

            rooms_rate_data: [],
            search: "",
            form_data: {
                rate: 0,
                meal_rate: 0,
                room_rate: 0,
                room_standard_id: null,
                room_package_id: null,
            },
            update_form_data: {
                room_standard_id: null,
                room_package_id: null,
                meal_rate: 0,
                room_rate: 0,
                id: null,
            },
        };
    },
    components: {
        RoomsNav,
        Unauthorized,
    },
    watch: {
        search: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchRates();
            }, 500),
        },
    },
    methods: {
        async fetchPackages() {
            const res = await this.getApi("data/room_package/fetch", "");

            if (res.status == 200) {
                this.room_package_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchStandard() {
            const res = await this.getApi("data/room_standard/fetch", "");

            if (res.status == 200) {
                this.room_standard_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async saveInfo() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/room_rate/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.create_modal = false;
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );

                this.rooms_rate_data = res.data;
            } else {
                this.form_error(res);
            }
        },
        updateDialogue(data) {
            this.update_modal = true;
            this.update_form_data = data;
        },
        async updateInfo() {
            this.showLoader();

            const res = await this.callApi(
                "put",
                "data/room_rate/update",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been updated successfully!");
                this.rooms_rate_data = res.data;
                this.update_modal = false;
                Object.keys(this.update_form_data).forEach(
                    (key) => (this.update_form_data[key] = "")
                );
            } else {
                this.form_error(res);
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/room_rate/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.rooms_rate_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getData() {
            const res = await this.getApi("data/room_rate/fetch", "");

            if (res.status == 200) {
                this.rooms_rate_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async searchRates() {
            const res = await this.getApi("data/room_rate/fetch", {
                params: {
                    search: this.search,
                },
            });

            if (res.status == 200) {
                this.rooms_rate_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/room_rate/fetch", "");
            this.hideLoader();
            if (res.status == 200) {
                var data_array = [];
                res.data.map((array_item) => {
                    data_array.push({
                        standard: array_item.room_standard.name,

                        room_package: array_item.room_package.name,
                        rate: array_item.rate,
                    });
                });

                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getData(),
                this.fetchStandard(),
                this.fetchPackages(),
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
