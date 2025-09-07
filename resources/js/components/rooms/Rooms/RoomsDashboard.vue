<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><rooms-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Rooms Management</div>

                    <div class="card-body">
                        <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="create_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                        </Button>

                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">DOOR NAME</th>
                                    <th scope="col">STANDARD</th>
                                    <th scope="col">NO. BEDS</th>
                                    <th>FLOOR</th>
                                    <th scope="col">DETAILS</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in rooms_data"
                                    :key="i"
                                >
                                    <td>{{ data.door_name }}</td>
                                    <td>{{ data.room_standard.name }}</td>

                                    <td>
                                        {{ data.qty_bed }}
                                    </td>
                                    <td>
                                        {{ data.floor_no }}
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
                                    class="btn btn-default btn-export ml-2 "
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
                                    class="btn btn-default btn-export ml-2 "
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
                        <Modal v-model="create_modal" title="Add Room ">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">ROOM STANDARD *</label>

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
                                    <div class="form-group">
                                        <label for="">Door Name</label>
                                        <input
                                            type="text"
                                            v-model="form_data.door_name"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for=""> NO. BEDS</label>
                                        <input
                                            type="number"
                                            v-model="form_data.qty_bed"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for=""> FLOOR NO.</label>
                                        <input
                                            type="text"
                                            v-model="form_data.floor_no"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Details(Optional)</label>
                                        <input
                                            type="text"
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
                                        @click="saveInfo()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <Modal v-model="update_modal" title="Update Room ">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">ROOM STANDARD *</label>

                                        <Select
                                            v-model="
                                                update_form_data.room_standard_id
                                            "
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
                                    <div class="form-group">
                                        <label for="">Door Name</label>
                                        <input
                                            type="text"
                                            v-model="update_form_data.door_name"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for=""> NO. BEDS</label>
                                        <input
                                            type="number"
                                            v-model="update_form_data.qty_bed"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for=""> FLOOR NO.</label>
                                        <input
                                            type="text"
                                            v-model="update_form_data.floor_no"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Details(Optional)</label>
                                        <input
                                            type="text"
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
import RoomsNav from "../RoomsNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            update_modal: false,
            create_modal: false,
            isLoading: false,
            room_standard_data: [],
            rooms_data: [],
            form_data: {
                door_name: "",
                details: "",
                room_standard_id: null,
                qty_bed: 1,
                floor_no: ""
            },
            update_form_data: {
                door_name: "",
                details: "",
                room_standard_id: null,
                qty_bed: 0,
                floor_no: "",
                id: null
            }
        };
    },
    components: {
        RoomsNav,
        Unauthorized
    },
    methods: {
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/rooms/fetch", "");
            this.hideLoader();
            if (res.status == 200) {
                var data_array = [];
                res.data.map(array_item => {
                    data_array.push({
                        door_name: array_item.door_name,
                        no_bed: array_item.qty_bed,
                        Floor: array_item.floor_no,
                        type: array_item.room_standard.name
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchRoomStandard() {
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
                "data/rooms/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.create_modal = false;
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );

                this.rooms_data = res.data;
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
                "data/rooms/update",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been updated successfully!");
                this.rooms_data = res.data;
                this.update_modal = false;
                Object.keys(this.update_form_data).forEach(
                    key => (this.update_form_data[key] = "")
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
                    "data/rooms/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.rooms_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getData() {
            const res = await this.getApi("data/rooms/fetch", "");

            if (res.status == 200) {
                this.rooms_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getData(),
                this.fetchRoomStandard()
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        }
    },
    mounted() {
        this.concurrentApiReq();
    }
};
</script>
