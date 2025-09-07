<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><rooms-nav /></div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Room Type</div>

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
                                    <th scope="col">ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Details</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in room_standard_data"
                                    :key="i"
                                >
                                    <td>{{ data.id }}</td>

                                    <td>
                                        {{ data.name }}
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
                        <Modal v-model="create_modal" title="Add Room Type">
                            <div class="row">
                                <div class="col-9"></div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Name</label>
                                        <input
                                            type="text"
                                            v-model="form_data.name"
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

                        <Modal v-model="update_modal" title="Update Room Type">
                            <div class="row">
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Name</label>
                                        <input
                                            type="text"
                                            v-model="update_form_data.name"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <label for="">Details</label>
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
            form_data: {
                name: "",
                details: ""
            },
            update_form_data: {
                name: "",
                details: "",
                id: null
            },
            room_standard_data: []
        };
    },
    components: {
        RoomsNav,
        Unauthorized
    },
    methods: {
        async saveInfo() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/room_standard/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.create_modal = false;
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );

                this.room_standard_data = res.data;
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
                "data/room_standard/update",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been updated successfully!");
                this.room_standard_data = res.data;
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
                    "data/room_standard/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.room_standard_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getData() {
            this.showLoader();
            const res = await this.getApi("data/room_standard/fetch", "");
            this.hideLoader();
            if (res.status == 200) {
                this.room_standard_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        }
    },
    mounted() {
        this.getData();
    }
};
</script>
