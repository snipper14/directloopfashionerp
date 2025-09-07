<template>
    <v-app style="margin-top: 0px">
        <div>
            <mainnav
                :user="user"
                :permission="permission"
                :role="role"
                :department="department"
                :branch="branch"
            />
        
            <!-- <auto-logout /> -->
            <!-- <v-app style="margin-top: 0px">
                <router-view> </router-view>
            </v-app> -->
            <notifications group="foo" /></div
    ></v-app>
</template>

<script>
import AutoLogout from "./AutoLogout.vue";
import Mainnav from "./mainnav.vue";
export default {
    props: ["user", "permission", "role", "department", "branch"],
    data() {
        return {
            change_password_modal: false,
            form_data: {
                confirm_password: "",
                new_password: "",
                old_password: "",
            },
        };
    },
    mounted() {},
    components: {
        AutoLogout,
        Mainnav,
    },
    created() {
        this.$store.commit("setUpdateUser", this.user);
        this.$store.commit("setUserPermission", this.permission);
        this.$store.commit("setBranch", this.branch);
        this.$store.commit("setDepartment", this.department);
        if (!this.user) {
            this.$router.push("logout");
            window.location.reload();
        }
    },
    methods: {
        goLanding() {
            this.$router.push("landing_page");
            window.location.reload();
        },
        async changePassword() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/admin/changePassword",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif(res.data.msg);
                this.logout();
            } else if (res.status === 400) {
                this.errorNotif(res.data.msg);
            } else {
                this.form_error(res);
            }
        },
        logout() {
            this.$router.push("logout");
            window.location.reload();
        },
    },
};
</script>
<style scoped>
.navbar-brand {
    color: rgb(24, 34, 65) !important;
    font-weight: 700;
}
.navbar {
    background-image: linear-gradient(#ef6c00, rgb(250, 250, 249));
}
.container {
    margin-top: 20px;
    max-width: 100% !important;
}
ul.navbar-nav .nav-link {
    color: #000 !important;
    font-weight: 500;
    font-size: 0.9rem;
}
#navbarNavDropdown {
    width: 100% !important;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
.btn-small {
    max-height: 1.5rem !important;
    font-size: x-small !important;
    text-align: center;
}
.search-wrapper input {
    max-height: 1.6rem !important;
}
.top-tag {
    color: #a0af22 !important;
    font-size: 12px !important;
    font-weight: 600;
}
</style>
