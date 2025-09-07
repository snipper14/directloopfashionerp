<template>
    <div class="login">
        <div class="container">
            <div
                class="jumbotron jumbotron-fluid"
                style="background: rgba(200, 54, 54, 0.5)"
            >
                <div
                    v-if="cashier_login"
                    class="_1adminOverveiw_table_recent _box_shadow _border_radious _mar_b30 _p20 col-md-4"
                >
                    <div class="d-flex justify-content-between">
                        <h3><b>Cashier Login</b></h3>
                        <button
                            class="btn btn-secondary"
                            @click="toggleLoginType"
                        >
                            Other's
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Username*</label>
                        <Select
                            v-model="data.username"
                            clearable
                            size="large"
                            style="height: 40px"
                        >
                            <Option
                                v-for="item in username_data"
                                :value="item.username"
                                :key="item.id"
                                >{{ item.username }}</Option
                            >
                            <hr />
                        </Select>
                    </div>
                    <div class="form-group" v-if="false">
                        <label>Username*</label>
                        <input
                            type="text"
                            @focus="showKeyboard"
                            data-layout="normal"
                            autocomplete="false"
                            class="form-control"
                            v-model="data.username"
                            placeholder="username"
                        />
                    </div>
                    <div class="form-group">
                        <label for="">Password</label>
                        <input
                            type="password"
                            class="form-control"
                            v-model="data.password"
                            placeholder="******"
                        />
                    </div>

                    <div class="login_footer">
                        <button class="btn btn-primary btn-lg" @click="login">
                            Login
                        </button>
                    </div>
                    <custom-board
                        style="margin-top: 4px"
                        @pressed="data.password = $event"
                        :selfValue="data.password"
                    />
                </div>
                <div
                    v-if="cashier_login != true"
                    class="_1adminOverveiw_table_recent _box_shadow _border_radious _mar_b30 _p20 col-md-4"
                >
                    <div class="d-flex justify-content-between">
                        <h3><b>All User Login</b></h3>
                        <button
                            class="btn btn-secondary"
                            @click="toggleLoginType"
                        >
                            Cashier's
                        </button>
                    </div>

                    <div class="form-group">
                        <label>Username*</label>
                        <input
                            type="text"
                            @focus="showKeyboard"
                            data-layout="normal"
                            autocomplete="false"
                            class="form-control"
                            v-model="data.username"
                            placeholder="username"
                        />
                    </div>
                    <div class="form-group">
                        <label for="">Password</label>
                        <input
                            @focus="showKeyboardPassword"
                            data-layout="normal"
                            type="password"
                            class="form-control"
                            v-model="data.password"
                            placeholder="******"
                        />
                    </div>

                    <div class="login_footer">
                        <button class="btn btn-primary btn-lg" @click="login">
                            Login
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-10">
                        <vue-touch-keyboard
                            :options="options"
                            v-if="visible"
                            :layout="layout"
                            :cancel="hide"
                            :accept="accept"
                            :input="input"
                        />
                        <vue-touch-keyboard
                            :options="options_password"
                            v-if="visible_password"
                            :layout="layout_password"
                            :cancel="hide_password"
                            :accept="accept_password"
                            :input="input_password"
                        />
                    </div>
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import CustomBoard from "./pos/menu_components/dinerscomponents/CustomBoard.vue";
export default {
    components: { CustomBoard },
    data() {
        return {
            cashier_login: true,
            data: {
                password: "",
                username: "",
                branch_id: null,
            },
            username_data: [],
            isLogging: false,
            branch_data: [],
            visible: false,
            visible_password: false,
            layout: "normal",
            layout_password: "normal",
            input: null,
            input_password: null,
            options: {
                useKbEvents: false,
                preventClickEvent: false,
            },
            options_password: {
                useKbEvents: false,
                preventClickEvent: false,
            },
        };
    },
    created() {
        this.getUserName();
    },

    methods: {
        toggleLoginType() {
            this.cashier_login = !this.cashier_login;
        },
        hide() {
            this.visible = false;
        },
        accept(text) {
            this.hide();
        },
        hide_password() {
            this.visible_password = false;
        },
        accept_password(text) {
            this.hide_password();
        },

        showKeyboard(e) {
            this.input = e.target;
            this.layout = e.target.dataset.layout;

            if (!this.visible) this.visible = true;
            if (this.visible_password) this.visible_password = false;
        },
        showKeyboardPassword(e) {
            this.input_password = e.target;
            this.layout_password = e.target.dataset.layout;

            if (!this.visible_password) this.visible_password = true;
            if (this.visible) this.visible = false;
        },
        async getUserName() {
            this.showLoader();
            const res = await this.callApi("get", "get_username", "");
            this.hideLoader();
            if (res.status === 200) {
                this.username_data = res.data;
                 this.$store.commit("setWaiterList",res.data);
            } else {
            }
        },
        async login() {
            this.showLoader();
            const res = await this.callApi("post", "admin_login", this.data);
            this.hideLoader();

            if (res.status === 200) {
                this.s(res.data.msg);

                this.$store.commit("setUpdateUser", res.data.user);
                this.$store.commit("setUserPermission", res.data.permission);
                this.$store.commit("setBranch", res.data.branch);
                this.$store.commit("setDepartment", res.data.department);
                
                if (this.$route.path == "/posmenu") {
                        this.$router.push("posmenu");
                    window.location.reload();
                } else {

                    this.$router.push("posmenu");
                      window.location.reload();
                }

                
            } else {
                if (res.status === 403) {
                    this.swr(res.data.msg);
                } else if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);
                    }
                } else if (res.status === 419) {
                    this.swr("Server error occured please try again");
                    window.location.reload();
                } else {
                    this.swr("Server error occured");
                }
            }
            this.isLogging = false;
        },
    },
};
</script>

<style scoped>
._1adminOverveiw_table_recent {
    margin: 0 auto;
    margin-top: 0px;
}
.login_footer {
    text-align: center;
}
.login_header {
    text-align: center;
    margin-bottom: 25px;
}
label {
    font-weight: 600;
}
h1 {
    font-weight: 600;
    font-size: 2.4rem;
}
.anton {
    font-family: "Anton", sans-serif !important;
}
</style>
