<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><location-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header"><h4><b> Tables Manager</b></h4></div>

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
                                    <th scope="col">ID</th>
                                    <th scope="col">Table Name</th>
                                    <th scope="col">Table Section</th>
                                    <th scope="col">No Seats</th>
                                      <th scope="col"> Seats Type</th>
                                    <th scope="col">Details</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in table_data"
                                    :key="i"
                                >
                                    <td>{{ data.id }}</td>

                                    <td>
                                        {{ data.name }}
                                    </td>
                                    <td>
                                        {{ data.department.department }}
                                    </td>
                                    <td>
                                        {{ data.no_seats }}
                                    </td>
                                     <td>
                                        {{ data.seat_type }}
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
                        <Modal v-model="create_modal" title="Add Table">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Dept *</label>

                                        <Select v-model="form_data.department_id">
                                            <Option
                                                v-for="item in department_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Table Name</label>
                                        <input
                                            type="text"
                                            v-model="form_data.name"
                                            class="form-control"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label for="">Seat type</label>
                                        <select
                                            class="form-control"
                                            id=""
                                            v-model="form_data.seat_type"
                                        >
                                            <option
                                                v-for="(value, i) in seat_types"
                                                :value="value.seat_type"
                                                :key="i"
                                                >{{ value.seat_type }}</option
                                            >
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="">No of Seats</label>
                                        <input
                                            type="text"
                                            v-model="form_data.no_seats"
                                            class="form-control"
                                        />
                                    </div>

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

                        <Modal v-model="update_modal" title="Update Table">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group d-flex flex-column">
                                        <label for="">Department *</label>

                                        <Select  v-model="update_form_data.department_id">
                                            <Option
                                                v-for="item in department_data"
                                                :value="item.id"
                                                :key="item.id"
                                                >{{ item.department }}</Option
                                            >
                                        </Select>
                                    </div>

                                    <div class="form-group">
                                        <label for="">Table Name</label>
                                        <input
                                            type="text"
                                            v-model="update_form_data.name"
                                            class="form-control"
                                        />
                                    </div>

                                    <div class="form-group">
                                        <label for="">Seat type</label>
                                        <select
                                            class="form-control"
                                            id=""
                                            v-model="update_form_data.seat_type"
                                        >
                                            <option
                                                v-for="(value, i) in seat_types"
                                                :value="value.seat_type"
                                                :key="i"
                                                >{{ value.seat_type }}</option
                                            >
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="">No of Seats</label>
                                        <input
                                            type="text"
                                            v-model="update_form_data.no_seats"
                                            class="form-control"
                                        />
                                    </div>

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
                                        Update Table
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
import LocationNav from "./LocationNav.vue";
import Unauthorized from "../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            update_modal: false,
            create_modal: false,
            isLoading: false,
            search: "",
            department_data: [],
            seat_types: [{ seat_type: "Chair" }, { seat_type: "Couch" }],
            form_data: {
                department_id: null,
                name: "",
                details: "",
                no_seats: "",
                seat_type: ""
            },
            update_form_data: {
                name: "",
                details: "",
                id: null,
                department_id: null,
                no_seats: "",
                seat_type: ""
            },
            table_data: []
        };
    },
    components: {
        LocationNav,
        Unauthorized
    },
    watch: {
        search: {
            deep: true,
            handler: _.debounce(function(val, old) {
                this.getData();
            }, 500)
        }
    },
    methods: {
        async fetchLocation() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.department_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async saveInfo() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/table/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.create_modal = false;
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );

                this.table_data = res.data;
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
                "data/table/update",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.update_modal = false;
                this.s(" Records  has been updated successfully!");
                this.table_data = res.data;
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
                    "data/table/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.table_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getData() {
            const res = await this.getApi("data/table/fetch", {
                params: {
                    search: this.search
                }
            });

            if (res.status == 200) {
                this.table_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getData(),
                this.fetchLocation()
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
