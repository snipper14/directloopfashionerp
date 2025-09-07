<template>
    <div class="container-fluid">
   
        <div class="card mt-4">
            <div class="card-header">
                <h4><b> Tables </b></h4>
            </div>

            <div class="card-body">
                <div class="row ">
                    <div
                        class="col-2 table-elem"
                        v-for="(value, i) in table_order_data"
                        :key="i"
                        @click="setTableID(value)"
                    >
                        <h4>
                            <b>{{ value.name }}</b>
                        </h4>
                        <span class="badge badge-light">Seats>> {{
                            value.no_seats
                        }}</span>
                        <span v-if="value.no_guest>0" style="float:right" class="badge badge-warning">occupied>> {{
                         
                        }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mdiBackburger } from "@mdi/js";

export default {
    props:["department_id"],
    data() {
        return {
            isLoading: false,
            current: "",
            location_data: [],
            orderType: "",
            search: "",
            table_data: [],
            order_data: [],
            table_order_data:[],
            icons: {
                mdiBackburger
            },
            no_guest: 0
        };
    },
    mounted() {
        this.orderType = this.$store.state.order_type;
        
        this.concurrentApiReq();
    },
    components: {},

    methods: {
           setTableID(data) {
            this.$emit("set-table_id", data);
        },
       async getOrders() {
            const res = await this.getApi("data/pos_order/fetchTableOrders", "");

            if (res.status == 200) {
                this.order_data = res.data;
                this.mergeTableOrder();
            } else {
                this.swr("Server error occurred");
            }
        },
        async getTables() {
            this.isLoading ? this.showLoader() : "";

            const res = await this.getApi("data/table/fetch", {
                params: {
                    search: this.search,
                    department_id:this.department_id
                }
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
            this.showLoader();
            const res = await Promise.all([
               
                this.getTables(),
                this.getOrders()
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        mergeTableOrder() {
            var table = [];

            for (var i in this.table_data) {
                var item = this.table_data[i];

                table.push({
                    id: item.id,
                    name: item.name,
                    no_seats:item.no_seats,
                    department_id:item.department_id,
                    location_name:item.department.department,
                    no_guest: this.getByTableName(item.id)[0]
                        ? this.getByTableName(item.id)[0].total_guest
                        : "0"
                });
            }
            this.table_order_data=table
          
        },
        getByTableName(table_id) {
            return this.order_data.filter(function(data) {
                return data.table_id == table_id;
            });
        }
    }
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
