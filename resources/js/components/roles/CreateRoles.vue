<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Role Managment</div>

                    <div class="card-body">
                        <Button v-if="isWritePermitted" type="primary" @click="add_role_modal = true">
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                            Role</Button
                        ><v-btn color="warning" small  @click="$router.push('model_logs')">view logs</v-btn>

                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Created At</th>
                                   
                                    <th scope="col">Edit</th>
                                    <th>Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in role_data"
                                    :key="i"
                                >
                                    <td>{{ data.roleName }}</td>
                                    <td>{{ data.created_at }}</td>
                                   

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
                                            @click="deleteRole(data.id, i)"
                                            class="btn btn-danger custom-button"
                                            v-if="isDeletePermitted"
                                        >
                                            del
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Modal v-model="add_role_modal" title="Add Role">
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
                                            placeholder="Role"
                                            v-model="form_data.roleName"
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
                                        @click="saveRole()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <Modal v-model="update_role_modal" title="Update Role">
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
                                            placeholder="Role"
                                            v-model="update_form_data.roleName"
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
                                        @click="updateRole()"
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
export default {
    data() {
        return {
            update_role_modal: false,
            add_role_modal: false,
            isLoading: false,
            form_data: {
                roleName: ""
            },
            update_form_data: {
                roleName: ""
            },
            role_data: []
        };
    },
    methods: {
        async deleteRole(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/admin/destroyRole/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.role_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async saveRole() {
            this.isLoading = true;
            const res = await this.callApi(
                "post",
                "data/admin/createRole",
                this.form_data
            );
            this.isLoading = false;

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.add_role_modal = false;
                this.form_data.roleName = "";
                this.role_data = res.data;
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        updateDialogue(data) {
            this.update_role_modal = true;
            this.update_form_data = data;
        },
        async updateRole() {
            this.isLoading = true;

            const res = await this.callApi(
                "put",
                "data/admin/updateRole",
                this.update_form_data
            );
            this.isLoading = false;

            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.update_role_modal = false;
                this.update_form_data.roleName = "";
                this.role_data = res.data;
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getRoles() {
            this.showLoader();
            const res = await this.getApi("data/admin/fetchRoles", "");
            this.hideLoader();
            if (res.status == 200) {
                this.role_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        }
    },
    mounted() {
        this.getRoles();
    }
};
</script>
