<template>
    <div
        class="container"
        :class="[value.orders.length > 0 ? 'has-orders' : '']"
    >
        <div>
            <h4>
                <b>{{ value.name }}</b>
            </h4>
            <span class="badge badge-light">Seats>> {{ value.no_seats }}</span>
        </div>
        <div class="text-center" v-if="value.orders.length > 0">
            <v-menu open-on-hover top offset-y>
                <template v-slot:activator="{ on, attrs }">
                    <v-btn
                        v-bind="attrs"
                        v-on="on"
                        small
                        color="warning"
                        class="badge badge-warning"
                        >occupied{{ value.orders.length }} {{
                    }}</v-btn>
                </template>

                <v-list>
                    <v-list-item
                        ><v-btn
                            dark
                            x-small
                            color="warning"
                            @click="newOrder(value)"
                            >New Order</v-btn
                        ></v-list-item
                    >
                    <v-list-item
                        v-for="(item, index) in value.orders"
                        :key="index"
                    >
                        <v-list-item-title @click="recallOrder(item)">{{
                            item.order_no
                        }}</v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
        </div>
        <div v-else>
            <v-btn dark x-small color="warning" @click="newOrder(value)"
                >New Order</v-btn
            >
        </div>
    </div>
</template>

<script>
export default {
    props: ["value"],
    data() {
        return {
            items: [
                { title: "Click Me" },
                { title: "Click Me" },
                { title: "Click Me" },
                { title: "Click Me 2" },
            ],
            isActive: false,
        };
    },
    mounted() {
        console.log(JSON.stringify(this.value));
        //this.value = this.items_details;
    },
    methods: {
        recallOrder(value) {
            var data_obj = {
                department: this.value.department.department,
                id: value.department_id,
            };
            localStorage.setItem("tableInfo", JSON.stringify(this.value));
            this.$store.commit("setOrderNo2", value.order_no);
            this.$store.commit("setDineDepartment", data_obj);
            this.$store.commit("setOrderType", value.order_type);
            this.$router.push("create_dineorder");
        },
        newOrder() {
            localStorage.setItem("tableInfo", JSON.stringify(this.value));
            this.$store.commit("setDineDepartment", this.value.department);

            this.$router.push("create_dineorder");
        },
        mainOrder() {
            if (this.value.orders.length <= 0) {
                this.$store.commit("setDineDepartment", this.value.department);

                this.$router.push("create_dineorder");
            }
        },
        toggle() {
            console.log(">>>>" + JSON.stringify(value.order_details));
            this.isActive = !this.isActive;
        },
    },
};
</script>
<style scoped>
.has-orders {
    background: #ffcc80 !important;
}
</style>
