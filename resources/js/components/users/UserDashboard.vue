<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-md-12">
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="col-md-3">
                                <v-btn
                                    v-if="false"
                                    class="ma-2 v-btn-primary"
                                    @click="setActiveComponent('add_user')"
                                    color="primary"
                                    dark
                                >
                                    <v-icon class="mr-2"
                                        >mdi-account-plus</v-icon
                                    >
                                    Add
                                </v-btn>
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

                        <div class="card-body">
                            <v-btn
                                v-if="
                                    selectedUsers.length > 0 &&
                                    isDeletePermitted
                                "
                                class="ma-2"
                                color="danger"
                                dark
                                @click="deleteSelectedUsers"
                            >
                                <v-icon class="mr-2">mdi-delete</v-icon>
                                Delete Selected ({{ selectedUsers.length }})
                            </v-btn>
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">
                                            <input
                                                type="checkbox"
                                                v-model="selectAll"
                                                @change="toggleSelectAll"
                                            />
                                        </th>
                                        <th scope="col">
                                            Name<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_name
                                                "
                                                sort-key="sort_name"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Email</th>
                                        <th>
                                            Username<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_username
                                                "
                                                sort-key="sort_username"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Phone<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_phone
                                                "
                                                sort-key="sort_phone"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th>
                                            Branch<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_branch
                                                "
                                                sort-key="sort_branch"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">
                                            Role<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_role
                                                "
                                                sort-key="sort_role"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            />
                                        </th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody v-if="user_data.data">
                                    <tr
                                        class="small-tr"
                                        v-for="(user, i) in user_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <input
                                                type="checkbox"
                                                :value="user.id"
                                                v-model="selectedUsers"
                                                @input="updateSelectAll"
                                            />
                                        </td>
                                        <td>{{ user.name }}</td>
                                        <td>{{ user.email }}</td>
                                        <td>{{ user.username }}</td>
                                        <td>{{ user.phone }}</td>
                                        <td>
                                            {{
                                                user.branch
                                                    ? user.branch.branch
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                user.role
                                                    ? user.role.roleName
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            <div class="btn-container">
                                                <v-btn
                                                    v-if="isUpdatePermitted"
                                                    color="warning"
                                                    title="update"
                                                    small
                                                    @click="editUser(user)"
                                                >
                                                    Edit
                                                </v-btn>
                                                <v-btn
                                                    color="secondary"
                                                    small
                                                    title="update"
                                                    @click="
                                                        updatePassword(user.id)
                                                    "
                                                    v-if="isUpdatePermitted"
                                                >
                                                    Edit Password
                                                </v-btn>
                                                <v-btn
                                                    small
                                                    color="danger"
                                                    v-if="isDeletePermitted"
                                                    @click="deleteUser(user, i)"
                                                >
                                                    Delete
                                                </v-btn>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="d-flex">
                                <Pagination
                                    v-if="user_data !== null"
                                    v-bind:results="user_data"
                                    v-on:get-page="getUsers"
                                ></Pagination>
                                Items Count {{ user_data.total }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <notifications position="bottom right" classes="my-custom-class" /> -->
            <add-user
                v-if="active.add_user"
                v-on:dashboard-active="setActiveRefresh"
                :roles="roles_data"
                :dept_data="dept_data"
                :branch_data="branch_data"
            >
            </add-user>
            <edit-user
                v-if="active.edit_user"
                v-bind:data="edit_user_data"
                :roles="roles_data"
                :dept_data="dept_data"
                :branch_data="branch_data"
                v-on:dashboard-active="setActiveRefresh"
            >
            </edit-user>
            <update-password
                v-if="active.update_password"
                :data="update_data"
                v-on:dashboard-active="setActiveRefresh"
            />
        </div>
    </v-app>
</template>

<script>
import AddUser from "./AddUser.vue";
import UpdatePassword from "./UpdatePassword.vue";
import EditUser from "./EditUser.vue";
import Pagination from "../utilities/Pagination";
import SortButtons from "../utilities/SortButtons.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_user: false,
                edit_user: false,
                update_password: false,
            },
            user_data: [],
            dept_data: null,
            branch_data: null,
            edit_user_data: null,
            search: "",
            filter: "",
            params: {},
            excel_header: {
                "Complete name": "name",
                Email: "email",
                Telephone: "phone",
                Role: "role",
            },
            sort_params: {
                sort_name: "",
                sort_username: "",
                sort_branch: "",
                sort_role: "",
                sort_phone:""
            },
            selectedUsers: [], // Array to store selected user IDs
            selectAll: false // State for "Select All" checkbox
        };
    },
    created() {
        this.concurrentApiReq();
    },
    components: {
        AddUser,
        EditUser,
        Pagination,
        UpdatePassword,
        SortButtons,
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.searchUser();
            }, 500),
        },
        search: {
            handler: _.debounce(function (val, old) {
                this.searchUser();
            }, 500),
        },
        sort_params: {
            deep: true,
            handler: _.debounce(function () {
                this.getUsers();
            }, 500),
        },
    },
    methods: {
        async deleteSelectedUsers() {
            if (!this.isDeletePermitted) {
                this.swr("You do not have permission to delete users");
                return;
            }
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "POST",
                    "data/users/multiDelete",
                    { user_ids: this.selectedUsers }
                );
                if (res.status === 200) {
                    this.s(`${this.selectedUsers.length} user(s) deleted successfully!`);
                    // Remove deleted users from user_data.data
                    this.user_data.data = this.user_data.data.filter(
                        user => !this.selectedUsers.includes(user.id)
                    );
                    this.user_data.total -= this.selectedUsers.length;
                    this.selectedUsers = [];
                    this.selectAll = false;
                } else {
                    this.swr("Server error, please try again later");
                }
            }
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedUsers = this.user_data.data.map(user => user.id);
            } else {
                this.selectedUsers = [];
            }
        },
        updateSelectAll() {
            this.selectAll = this.selectedUsers.length === this.user_data.data.length;
        },
        updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },

        async concurrentApiReq() {
            const res = await Promise.all([
                this.getUsers(),
                this.getRoles(),
                this.fetchDept(),
                this.fetchBranch(),
            ]).then(function (results) {
                return results;
            });
        },
        updatePassword(data) {
            this.update_data = data;
            this.setActive(this.active, "update_password");
        },
        async getRoles() {
            const res = await this.getApi("data/admin/fetchRoles", "");

            if (res.status == 200) {
                this.roles_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchBranch() {
            const res = await this.getApi("data/branch/fetch", "");

            if (res.status == 200) {
                this.branch_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getAllUsers() {
            return await this.getApi("data/users/getUserInfo", "");
        },

        async deleteUser(user, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "DELETE",
                    "data/users/delete/" + user.id,
                    ""
                );
                if (res.status === 200) {
                    this.s(" user has been deleted!");
                    this.user_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        editUser(user) {
            this.edit_user_data = user;

            this.setActiveComponent("edit_user");
        },
        async getUsers(page) {
            this.showLoader();
            if (typeof page === "undefined") {
                page = 1;
            }

            const res = await this.getApi("data/users/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                     ...this.sort_params,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                this.user_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.getUsers();
        },
        async searchUser() {
            let page = 1;
            const res = await this.getApi("data/users/fetch", {
                params: {
                    page,
                    search: this.search,
                    ...this.params,
                    ...this.sort_params,
                },
            });

            if (res.status === 200) {
                this.user_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>

<style scoped>
.table-striped tbody tr td {
    height: 14px !important;
}
.btn-container {
    display: flex;
    flex-direction: row;
}
.page-count {
    font-weight: 600;
    color: #000;
    font-size: 4dp;
}
.card-header {
    height: 50px;
}
</style>
