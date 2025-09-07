<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <dept-location-nav />
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Location Management</div>

                    <div class="card-body">
                        <Button
                            v-if="isWritePermitted"
                            type="primary"
                            @click="add_dept_modal = true"
                        >
                            <i class="fa fa-plus" aria-hidden="true"></i> Add
                            Location</Button
                        >

                        <table
                            class="table table-sm table-striped table-bordered custom-table"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Details</th>
                                    <th>Show POS</th>
                                    <th>Location Type</th>
                                    <th scope="col">Created At</th>
                                    <th>Branch</th>
                                    <th scope="col">Edit</th>
                                    <th>Del</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in department_data"
                                    :key="i"
                                >
                                    <td>{{ data.id }}</td>
                                    <td>{{ data.department }}</td>
                                    <td>{{ data.details }}</td>
                                    <td>
                                        <p v-if="data.show_pos == 1">Show</p>
                                        <p v-else>Hide</p>
                                    </td>
                                    <td>{{ data.location_type }}</td>
                                    <td>
                                        {{
                                            formatMonthDateTime(data.created_at)
                                        }}
                                    </td>
                                    <td>{{ data.branch.branch }}</td>
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
                                            @click="deleteDept(data.id, i)"
                                            class="btn btn-danger custom-button"
                                            v-if="isDeletePermitted"
                                        >
                                            del
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Modal v-model="add_dept_modal" title="Add Location">
                            <div class="row">
                                <div class="col-md-11 form-group">
                                    <label for="">Branch</label>
                                    <Select
                                        v-model="form_data.branch_id"
                                    >
                                        <Option
                                            v-for="(
                                                value, i
                                            ) in branch_select_data"
                                            :key="i"
                                            :value="value.id"
                                        >
                                            {{ value.branch }}
                                        </Option>
                                    </Select>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            placeholder="Location"
                                            v-model="form_data.department"
                                            class="form-control"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-11 form-group">
                                    <label for="">Location Type</label>
                                    <Select v-model="form_data.location_type">
                                        <Option
                                            v-for="(
                                                value, i
                                            ) in location_type_options"
                                            :key="i"
                                            :value="value.value"
                                        >
                                            {{ value.name }}
                                        </Option>
                                    </Select>
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
                                        <input
                                            type="text"
                                            placeholder="Details"
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
                                        v-if="isWritePermitted"
                                        @click="saveRecord()"
                                    >
                                        Save
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <Modal
                            v-model="update_dept_modal"
                            title="Update Location"
                        >
                            <div class="row">
                                  <div class="col-md-11 form-group">
                                    <label for="">Branch</label>
                                    <Select
                                        v-model="update_form_data.branch_id"
                                    >
                                        <Option
                                            v-for="(
                                                value, i
                                            ) in branch_select_data"
                                            :key="i"
                                            :value="value.id"
                                        >
                                            {{ value.branch }}
                                        </Option>
                                    </Select>
                                </div>
                                <div class="col-11">
                                    <div class="form-group">
                                        <input
                                            type="text"
                                            placeholder="Location"
                                            v-model="
                                                update_form_data.department
                                            "
                                            class="form-control"
                                        />
                                    </div>
                                </div>

                                <div class="col-md-11 form-group">
                                    <label for="">Location Type</label>
                                    <Select
                                        v-model="update_form_data.location_type"
                                    >
                                        <Option
                                            v-for="(
                                                value, i
                                            ) in location_type_options"
                                            :key="i"
                                            :value="value.value"
                                        >
                                            {{ value.name }}
                                        </Option>
                                    </Select>
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
                                        <input
                                            type="text"
                                            placeholder="Details"
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
                                        v-if="isUpdatePermitted"
                                        @click="updateDept()"
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
import DeptLocationNav from "./DeptLocationNav.vue";

export default {
    components: { DeptLocationNav },
    data() {
        return {
            update_dept_modal: false,
            add_dept_modal: false,
            isLoading: false,
            location_type_options: [
                { name: "Physical", value: "physical" },
                { name: "Virtual", value: "virtual" },
            ],
            form_data: {
                branch_id:null,
                department: "",
                show_pos: false,
                details: "",
                location_type: null,
            },
            branch_select_data:null,
            update_form_data: {
                branch_id:null,
                id: null,
                department: "",
                show_pos: false,
                details: "",
                location_type: null,
            },

            department_data: [],
        };
    },

    methods: {
        async deleteDept(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/dept/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.department_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
         async fetchbranch() {
           
            const res = await this.getApi("data/branch/fetch", "");
           
            if (res.status == 200) {
                this.branch_select_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async saveRecord() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/dept/create",
                this.form_data
            );

            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.add_dept_modal = false;
                this.form_data.department = "";
                this.form_data.details = "";
                this.department_data = res.data;
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
            this.update_dept_modal = true;
            this.update_form_data = data;
            this.update_form_data.show_pos = data.show_pos == 1 ? true : false;
        },
        async updateDept() {
            this.showLoader();

            const res = await this.callApi(
                "put",
                "data/dept/update",
                this.update_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s(" Records  has been added successfully!");

                this.update_dept_modal = false;
                this.update_form_data.department = "";
                this.update_form_data.details = "";
                this.update_form_data.id = null;
                this.department_data = res.data;
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
        async fetchDept() {
            this.showLoader();
            const res = await this.getApi("data/dept/fetchAll", "");
            this.hideLoader();
            if (res.status == 200) {
                this.department_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
    mounted() {
        this.fetchDept();
        this.fetchbranch()
    },
};
</script>
