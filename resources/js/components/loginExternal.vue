<template>
    <div class="">
        <p>
          please wait..........
        </p>
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
                branch_id: null
            },
            form_data: {
                email: null,
                id: null
            },
            isLogging: false,
            branch_data: []
        };
    },
    created() {
        // this.form_data.id=this.$route.query.id
        console.log(">>>>>> " + this.$route.query.id);
        this.login();
    },

    methods: {
        decrypt(encodedString) {
            return window.atob(encodedString);
        },
        async login() {
            this.form_data.id = this.decrypt(this.$route.query.id);
            this.form_data.email = this.decrypt(this.$route.query.email);
            this.showLoader();
            const res = await this.callApi(
                "post",
                "externalLoginID",
                this.form_data
            );
            this.hideLoader();

            if (res.status === 200) {
                this.s(res.data.msg);
                this.$router.push("/");
                window.location.reload();
            } else {
                this.swr("Server error occured");
                this.$router.push("/login");
                 window.location.reload();
            }
            this.isLogging = false;
        }
    }
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
