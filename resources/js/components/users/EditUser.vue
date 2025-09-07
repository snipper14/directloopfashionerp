<template>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
            <div class="card">
                <div class="card-header">
                    <h4 ml-2>UPDATE USER</h4>
                </div>

                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="email">Email address *</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        v-model="form_data.email"
                                        placeholder="Enter email"
                                    />
                                </div>
                                <div class="form-group">
                                    <label>Username(unique) *</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        autocomplete="new-user-street-address"
                                        v-model="form_data.username"
                                        placeholder="User Name"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Full Name *</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="last_name"
                                        v-model="form_data.name"
                                        placeholder=" Name"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="phone"
                                        >Phone (+2547xx xxx xxx) *</label
                                    >
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="phone"
                                        v-model="form_data.phone"
                                        placeholder="Phone"
                                    />
                                </div>

                                <div class="form-group d-flex flex-column">
                                    <label for="">Role</label>

                                    <Select
                                        v-model="form_data.role_id"
                                        style="width:200px"
                                    >
                                        <Option
                                            v-for="item in role_data"
                                            :value="item.id"
                                            :key="item.id"
                                            >{{ item.roleName }}</Option
                                        >
                                    </Select>
                                </div>
  <div class="form-group d-flex flex-column">
                                    <label for="">Branch*</label>

                                    <Select
                                        v-model="form_data.branch_id"
                                        style="width:200px"
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
                                        style="width:200px"
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
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="editUser()"
                        >
                            Save
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
    props: ["data", "roles", "branch_data"],
    data() {
        return {
            value: null,
            window: 0,
            showContent: true,
            role_data: [],
            dept_data:[],
            form_data: {
                name: "",
                department_id: null,
                branch_id: null,
                email: "",
                role_id: "",
                phone: "",
                id: null,
                username: "",
                login_status:''
            }
        };
    },
    mounted() {
        this.form_data = this.data;
        this.role_data = this.roles;
        this.fetchDept()
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
        async fetchDept() {
            const res = await this.getApi("data/dept/fetchAllDept", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async editUser() {
            this.form_data.name;
          
            const res = await this.callApi(
                "put",
                "data/users/update",
                this.form_data
            );

            if (res.status === 200) {
                //	this.users.unshift(res.data)
                this.s(" user has been updated successfully!");

                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                this.$emit("dashboard-active");
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
        }
    }
};
</script>
<style scoped>
.card-body {
    background-color: #f5f5f5;
}
</style>
