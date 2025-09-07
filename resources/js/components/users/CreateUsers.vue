<template>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Add User</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Username(unique) *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.username"
                                        placeholder="User Name"
                                    />
                                </div>
                                 <div class="form-group">
                                    <label>Phone No *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.phone"
                                        placeholder="Phone"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>First Name *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.first_name"
                                        placeholder="First Name"
                                    />
                                </div>
                                 
                                <div class="form-group">
                                    <label>Last Name *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.last_name"
                                        placeholder="Last Name"
                                    />
                                    <small
                                        v-if="formErrorMessages.last_name"
                                        class="form-text text-muted form_error"
                                        >{{
                                            formErrorMessages.last_name
                                        }}</small
                                    >
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label for="">Role*</label>

                                    <Select
                                        v-model="form_data.role_id"
                                        style="width: 200px"
                                    >
                                        <Option
                                            v-for="item in role_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.roleName }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                            
                            <div class="col-6">
                                 <div class="form-group d-flex flex-column">
                                    <label for="">Branch*</label>

                                    <Select
                                        v-model="form_data.branch_id"
                                        style="width: 200px"
                                    >
                                        <Option
                                            v-for="item in branch_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.branch }}</Option
                                        >
                                    </Select>
                                </div>
                                <div class="form-group d-flex flex-column">
                                    <label for="">Location*</label>

                                    <Select
                                        v-model="form_data.department_id"
                                        style="width: 200px"
                                            :disabled="!form_data.branch_id"
                                    >
                                        <Option
                                           v-for="item in filteredDepartments"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.department }}</Option
                                        >
                                    </Select>
                                </div>
                               
                                <div class="form-group d-flex flex-column">
                                    <label for="">Login Type*</label>

                                    <Select
                                        v-model="form_data.login_status"
                                        style="width: 200px"
                                    >
                                        <Option
                                            
                                            value="Other"
                                           
                                            >Other</Option
                                        >
                                         <Option
                                            
                                            value="Admin"
                                           
                                            >Admin</Option
                                        >
                                    </Select>
                                </div>
                                <div class="form-group">
                                    <label>Password (optional)</label>
                                    <input
                                        type="password"
                                        v-model="form_data.password"
                                        autocomplete="new-user-street-address"
                                        class="form-control"
                                        placeholder="Password"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary btn-lg"
                            @click="submitUser()"
                        >
                            Save User
                        </button>
                    </form>
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
            value: null,
            window: 0,
            showContent: true,
            role_data: [],
            dept_data: [],
            branch_data: [],
            formErrorMessages: {
                first_name: "",
                last_name: "",
                email: "",
                role_id: null,
                branch_id: null,
                department_id: null,
                phone: "",
                password: "",
                confirm_password: "",
            },
            form_data: {
                name: "",
                first_name: "",
                last_name: "",
                email: "",
                role_id: null,
                branch_id: null,
                department_id: null,
                phone: "",
                password: "",
                confirm_password: "",
                username: "",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
   computed: {
    filteredDepartments() {
        return this.form_data.branch_id
            ? this.dept_data.filter(
                  (dept) => dept.branch_id === this.form_data.branch_id
              )
            : [];
    },
},
    methods: {
        async concurrentApiReq() {
            const res = await Promise.all([
                this.getRoles(),
                this.fetchDept(),
                this.fetchBranch(),
            ]).then(function (results) {
                return results;
            });
        },
        async fetchBranch() {
            const res = await this.getApi("data/branch/fetch", "");

            if (res.status == 200) {
                this.branch_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetchAll", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getRoles() {
            const res = await this.getApi("data/admin/fetchRoles", "");

            if (res.status == 200) {
                this.role_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async submitUser() {
            this.form_data.name =
                this.form_data.first_name + " " + this.form_data.last_name;
            if (this.form_data.name.trim() == "")
                return this.e("Full name is required");

            if (!this.form_data.role_id)
                return this.e("User type  is required");

            const res = await this.callApi(
                "post",
                "data/users/create",
                this.form_data
            );

            if (res.status === 200) {
                //	this.users.unshift(res.data)
                this.successNotif(" user has been added successfully!");
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                        if (
                            res.data.errors[i][0] ===
                            "The password format is invalid."
                        ) {
                            this.invalid_password = true;
                        }
                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
    },
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
