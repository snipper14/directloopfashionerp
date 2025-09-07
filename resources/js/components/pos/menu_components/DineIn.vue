<template>
    <div class="container-fluid">
        <v-row class="row">
            <div class="col-md-4 d-flex">
                <div>
                    <button
                        class="btn btn-primary"
                        @click="$router.push('posmenu')"
                    >
                        <h3>
                            <b>
                                <v-icon medium>{{
                                    icons.mdiBackburger
                                }}</v-icon>
                                BACK</b
                            >
                        </h3>
                    </button>
                </div>
                <div>
                    <center class="mt-3 ml-2">
                        <h4>
                            <b>{{ orderType }}</b>
                        </h4>
                    </center>
                    <button class="btn btn-warning" @click="logoutPOS()">
                        Log out
                    </button>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div
                        class="col-12 d-flex flex-row justify-content-between mt-3"
                    >
                        <button
                            v-for="(value, i) of department_data"
                            :key="i"
                            @click="locationChange(value)"
                            type="button"
                            class="btn btn-secondary btn-lg w-100 mr-2"
                        >
                            {{ value.department }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div
                            class="col-md-2 table-elem"
                            v-for="(value, i) in table_order_data"
                            :key="i"
                            @click="newOrder(value)"
                            
                        >
                        <dine-in-component-details :value="value"/></div>
                    </div>
                </div>
            </div>
        </v-row>
    </div>
</template>

<script>
import { mdiBackburger } from "@mdi/js";
import DineInComponentDetails from './DineInComponentDetails.vue';

export default {
    data() {
        return {
            item_details: null,
          
            isLoading: false,
            current: "",
            location_data: [],
            orderType: "",
            search: "",

            table_data: [],
            order_data: [],
            table_order_data: [],
            department_data: [],
            icons: {
                mdiBackburger,
            },
            no_guest: 0,
        };
    },
    mounted() {
        this.orderType = this.$store.state.order_type;

        this.concurrentApiReq();
    },
    components: {DineInComponentDetails},

    methods: {
        showSubItems(value) {
            this.item_details=value
        },
        setDept(rec) {
            //console.log(JSON.stringify(rec));
        },
        newOrder(value) {
            // console.log(JSON.stringify(value));
            // localStorage.setItem("tableInfo", JSON.stringify(value));
            // this.$store.commit("setDineDepartment", value.department);

            //   this.$router.push("create_dineorder");
        },
        async locationChange(value) {
            this.current = value.name;
            this.isLoading = true;
            this.search = value.id;
            this.getTables();
        },
        async getDept() {
            const res = await this.getApi("data/dept/fetchPOSDept", {
                params: {
                    search: this.search,
                },
            });

            if (res.status == 200) {
                this.department_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async getOrders() {
            const res = await this.getApi(
                "data/pos_order/fetchTableOrders",
                ""
            );

            if (res.status == 200) {
                this.order_data = res.data;
               // this.mergeTableOrder();
            } else {
                this.swr("Server error occurred");
            }
        },
        async getTables() {
            this.isLoading ? this.showLoader() : "";

            const res = await this.getApi("data/table/fetch", {
                params: {
                    search: this.search,
                },
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.table_data = res.data;

                this.mergeTableOrder();
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            // this.showLoader();
            const res = await Promise.all([
                this.getDept(),
                this.getTables(),
              //  this.getOrders(),
            ]).then(function (results) {
                return results;
            });
            // this.hideLoader();
        },
        mergeTableOrder() {
            var table = [];

            for (var i in this.table_data) {
                var item = this.table_data[i];

                table.push({
                    id: item.id,
                    name: item.name,
                    no_seats: item.no_seats,
                    department_id: item.department_id,
                    location_name: item.department.department,
                    department: item.department,
                    orders:item.orders,
                    // order_details: this.getByTableName(),
                    // no_guest: this.getByTableName(item.id)[0]
                    //     ? this.getByTableName(item.id)[0].total_guest
                    //     : "0",
                });
            }
            this.table_order_data = table;
        },
        getByTableName(table_id) {
           
            return this.order_data.filter(function (data) {
                return data.table_id == table_id;
            });
        },
    },
};
</script>
<style scoped>
.menu-icon {
    height: 70px;
    width: 70px;
    border-radius: 50%;
}
.active {
    background: #4caf50 !important;
    z-index: -1;
    bottom: 25px;
    left: 10px;
    width: 50%;
    top: 80%;
    max-width: 300px;
    -webkit-box-shadow: 0 35px 20px #777;
    -moz-box-shadow: 0 35px 20px #777;
    box-shadow: 0 35px 20px #777;
    -webkit-transform: rotate(-8deg);
    -moz-transform: rotate(-8deg);
    -o-transform: rotate(-8deg);
    -ms-transform: rotate(-8deg);
    transform: rotate(-8deg);
}
.table-elem {
    background: #4db6ac;
    border-radius: 4px;
    margin-right: 2px;
    margin-bottom: 2px;
}
</style>
