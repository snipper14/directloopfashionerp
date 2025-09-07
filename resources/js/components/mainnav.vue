<template>
    <div>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <a class="navbar-brand" href="#">{{ company_name }}</a>
            <v-btn color="warning" x-small @click="goLanding()"
                >Landing Page</v-btn
            >
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav nav-left">
                    <li class="nav-item active">
                        <router-link
                            class="nav-link"
                            to="home"
                            @click.native="linkPage('home')"
                            >Home</router-link
                        >
                    </li>
              <li
                        class="nav-item dropdown"
                        v-for="(parentMenu, i) in filteredPermissions"
                        :key="i"
                        v-if="parentMenu.parent != 'Dashboard'"
                    >
                        <a
                            class="nav-link dropdown-toggle"
                            href="#"
                            id="navbarDropdownMenuLink"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            {{ parentMenu.parent }}
                        </a>
                        <div
                            class="dropdown-menu"
                            aria-labelledby="navbarDropdownMenuLink"
                        >
                            <div
                                v-for="(menuItem, i) in parentMenu.children"
                                :key="i"
                                v-if="
                                    parentMenu.children.length && menuItem.read
                                "
                            >
                                <router-link
                                    v-if="!menuItem.menu_hide"
                                    class="dropdown-item"
                                    to="#"
                                    @click.native="
                                        linkPage('/' + menuItem.name)
                                    "
                                >
                                    {{ menuItem.resourceName }}</router-link
                                >
                            </div>
                        </div>
                    </li>
                </ul>

                <ul class="navbar-nav nav-right">
                    <li>
                        <span class="nav-link" aria-expanded="false">
                            <p>{{ branch.branch }}</p>
                        </span>
                    </li>
                    <li>
                        <span class="nav-link" aria-expanded="false">
                            <b>{{ role.roleName }}</b>
                        </span>
                    </li>
                    <li class="nav-item dropdown">
                        <a
                            id="navbarDropdown"
                            class="nav-link dropdown-toggle"
                            href="#"
                            role="button"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false"
                        >
                            {{ user.name }}
                        </a>

                        <div
                            class="dropdown-menu dropdown-menu-right"
                            aria-labelledby="navbarDropdown"
                        >
                            <p class="top-tag ml-3">
                                Dept: {{ department.department }}
                            </p>
                            <p class="top-tag ml-3">
                                Branch: {{ branch.branch }}
                            </p>
                            <router-link
                                class="dropdown-item"
                                @click.native="change_password_modal = true"
                                to="#"
                                >Change Password</router-link
                            >
                            <router-link
                                class="dropdown-item"
                                @click.native="logout()"
                                to="#"
                                >Logout</router-link
                            >
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
        <Modal v-model="change_password_modal" title="Change Password">
            <div class="row">
                <div class="col-11">
                    <div class="form-group">
                        <input
                            type="password"
                            autocomplete="5fdufdfdo-fggfi"
                            placeholder="Old Password*"
                            v-model="form_data.old_password"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-11">
                    <div class="form-group">
                        <input
                            type="password"
                            autocomplete="5fdufdfdo-fggfi"
                            placeholder="New Password*"
                            v-model="form_data.new_password"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-11">
                    <div class="form-group">
                        <input
                            type="password"
                            autocomplete="5fdufdfdo-fggfi"
                            placeholder="Confirm Pasword"
                            v-model="form_data.confirm_password"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-9 d-flex justify-content-center">
                    <button
                        class="btn btn-secondary"
                        type="secondary"
                        @click="changePassword()"
                    >
                        Change Password
                    </button>
                </div>
            </div>
        </Modal>
        <router-view> </router-view>
    </div>
</template>

<script>
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
    mounted() {
      
    },
    methods: {
        linkPage(slug) {
            this.$router.push(slug);
            this.$emit("dismiss-landing");
        },
        goLanding() {
           
            if (this.$parent) {
                // Check if a parent component exists

                this.$emit("dashboard-active");
                this.$router.push("landing_page");
               
            } else {
                console.warn("No parent component to emit to.");
            }
            //      window.location.reload();
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
    computed: {
        filteredPermissions() {
            return this.permission.filter((parentMenu) => {
                return (
                    parentMenu.children &&
                    parentMenu.children.length &&
                    parentMenu.children.some((child) => child.read)
                );
            });
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
