<template>
    <div><v-app style="margin-top:0px;">
        <router-view> </router-view>
        <notifications group="foo" /></v-app>
    </div>
</template>

<script>
export default {
    props: ["user", "permission", "role", "department", "branch"],

    created() {
        console.log("pos menu");
      //  console.log(JSON.stringify(this.permission));
        this.$store.commit("setUpdateUser", this.user);
        this.$store.commit("setUserPermission", this.permission);
        this.$store.commit("setBranch", this.branch);
        this.$store.commit("setDepartment", this.department);

        if (!this.user) {
            this.$router.push("waiter_logout");
            window.location.reload();
        }
    },
};
</script>
