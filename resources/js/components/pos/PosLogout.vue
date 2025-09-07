<template>
    <div class="">
        <p>Logging out .....</p>
    </div>
</template>

<script>
export default {
    mounted() {
        this.$store.state.user = null;
        this.signout();
    },
    methods: {
        async signout() {
            this.showLoader();
            const res = await this.callApi("post", "logoutPOS", {});
            this.hideLoader();
            if (res.status == 200) {
                this.$store.state.user = null;
                this.$router.push("login_pos");
            } else {
                this.servo();
                this.$router.push("login_pos");
                 window.location.reload();
            }
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
