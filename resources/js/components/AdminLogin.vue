<template>
    <div class="login_wrapper">
        <div class="row justify-content-center">
            <div class="col-md-4 login_main">
                <div class="login_header">
                    <!-- <img
                        height="80"
                        src="../assets/images/develiz_logo.png"
                        alt=""
                    /> -->
                    <img
                        height="80"
                        src="../assets/images/beyond_logo.png"
                        alt=""
                    />

                    <h1 class="anton login-title">{{ app_name }}</h1>
                </div>
                <div class="form-group d-flex flex-column">
                    <label for="" class="login-title">Select Branch</label>

                    <select class="form-control" v-model="data.branch_id">
                        <option
                            v-for="item in branch_data"
                            :value="item.id"
                            :key="item.id"
                            >{{ item.branch }}</option
                        >
                    </select>
                </div>
                <div class="form-group">
                    <label for="" class="login-title">Username*</label>
                    <input
                        type="text"
                        autocomplete="off"
                        class="form-control"
                        v-model="data.email"
                        placeholder="username"
                        @keyup.enter="login"
                    />
                </div>
                <div class="form-group">
                    <label for="" class="login-title">Password</label>
                    <input
                        type="password"
                        class="form-control"
                        autocomplete="off"
                        v-model="data.password"
                        placeholder="******"
                        @keyup.enter="login"
                    />
                </div>
                <div class="login_footer">
                    <button class="btn btn-primary" @click="login">
                        Login
                    </button>
                </div>
            </div>
        </div>

        <div style="position:absolute,bottom:0px">
            <center>
                <a target="_blank" href="https://develizsolutions.com/">
                    <span style="color: #fff">
                        Developed & Maintained by

                        <!-- <h2>Develiz Solutions</h2> -->
                        <h2>Beyond Business Technologies</h2>
                        <br />
                    </span>
                    <!-- <img
                                height="60"
                                src="../assets/images/develiz_logo.png"
                                alt=""
                        /> -->
                </a>
            </center>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
export default {
    data() {
        return {
            data: {
                email: "",
                password: "",
                branch_id: null,
            },
            isLogging: false,
            branch_data: [],
        };
    },
    mounted() {
        this.getBranches();
    },

    methods: {
        async getBranches() {
            this.showLoader();
            const res = await this.getApi("fetchBranches", "");
            this.hideLoader();
            if (res.status == 200) {
                this.branch_data = res.data;
               this.data.branch_id = localStorage.getItem("LOGIN_BRANCH");
              
            } else {
                this.swr("Server error occurred");
            }
        },
        async login() {
            this.showLoader();
            const res = await this.callApi("post", "admin_login", this.data);
            this.hideLoader();

            if (res.status === 200) {
                localStorage.setItem("LOGIN_BRANCH", this.data.branch_id);
                this.s(res.data.msg);
                this.$router.push("/landing_page");
                //this.$router.push("/");
                window.location.reload();
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
    margin-top: 20px;
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
