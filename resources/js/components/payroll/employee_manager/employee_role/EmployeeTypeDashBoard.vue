<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <EmployeeManagerNav />
            </div>
            <div class="col-md-8">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_employee_type')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Employee Types</h5>
                        </div>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Details</th>

                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in etype_data"
                                    :key="i"
                                >
                                    <td>{{ data.name }}</td>
                                    <td>{{ data.details }}</td>

                                    <td>
                                        <div class="btn-container">
                                            <button
                                                v-if="isUpdatePermitted"
                                                type="button"
                                                class="btn btn-warning btn-sm"
                                                @click="editEtype(data)"
                                            >
                                                Edit
                                            </button>
                                             <button
                                                v-if="isDeletePermitted"
                                                type="button"
                                                class="btn btn-danger btn-sm"
                                                @click="deleteData(data.id, i)"
                                            >
                                                Delete
                                            </button>
                                        </div>
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
        <!-- <notifications position="bottom right" classes="my-custom-class" /> -->
        <add-employee-type
            v-if="active.add_employee_type"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-employee-type>
        <edit-employee-type
            v-if="active.edit_employee_type"
            v-bind:data="edit_dept_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-employee-type>
    </div>
</template>

<script>
import AddEmployeeType from "./AddEmployeeType.vue";
import EditEmployeeType from "./EditEmployeeType.vue";
import { mdiPlusThick } from "@mdi/js";
import EmployeeManagerNav from "../EmployeeManagerNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_employee_type: false,
                edit_employee_type: false
            },
            etype_data: [],
            edit_etype_data: null,
            icons: {
                mdiPlusThick
            }
        };
    },
    components: {
        AddEmployeeType,
        EditEmployeeType,
        Unauthorized,
        EmployeeManagerNav
    },
    mounted() {
        this.getEtype();
    },
    methods: {
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/employee_type/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.etype_data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editEtype(data) {
            this.edit_dept_data = data;

            this.setActiveComponent("edit_employee_type");
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        async getEtype() {
            this.showLoader();
            const res = await this.getApi("data/employee_type/fetch", "");
            this.hideLoader();

            if (res.status === 200) {
                this.etype_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },

        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.getEtype();
        }
    }
};
</script>
