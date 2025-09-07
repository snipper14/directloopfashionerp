<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-4 d-flex">
                <button
                    class="btn btn-primary"
                    @click="goBack()"
                >
                    <h3>
                        <b>
                            <v-icon medium>{{ icons.mdiBackburger }}</v-icon>
                            BACK</b
                        >
                    </h3>
                </button>
              
            </div>
            <div class="col-8 ">
                <div class="row">
                    <div
                        class="col-12 d-flex flex-row justify-content-between mt-3"
                    >
                        <button
                            v-for="(value, i) of location_data"
                            :key="i"
                            :class="[
                                value.name,
                                current === value.name ? 'active' : ''
                            ]"
                            @click="locationChange(value)"
                            type="button"
                            class="btn btn-secondary btn-lg w-100 mr-2"
                        >
                            {{ value.name }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header">
                <h4><b>Order Tranfers To  </b></h4>
            </div>

            <div class="card-body">
                <div class="row ">
                    <div
                        class="col-2 table-elem"
                        v-for="(value, i) in table_order_data"
                        :key="i"
                        @click="tranferTable(value)"
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
            no_guest: 0,
            order_no:'',
        };
    },
    mounted() {
        this.order_no=this.$store.state.order_no
        this.concurrentApiReq();
    },
    components: {},

    methods: {
    async    tranferTable(value){
           
             const isConfirmed = await this.confirmDialogue(
                "Confirm transfer "
            );
            if (isConfirmed) {
            const res=await this.callApi('POST','data/pos_order/transferTable',
            {
                order_no:this.order_no,
                table_id:value.id,
                location_id:value.location_id
            })
            if(res.status==200){
                this.s("Successfully moved")
                this.goBack()
            }else{
                this.servo()
            }
            }
        },
        newOrder(value) {
            this.$store.commit("setLocation", value);

            this.$router.push("create_dineorder");
        },
        async locationChange(value) {
            this.current = value.name;
            this.isLoading = true;
            this.search = value.id;
            this.getTables();
        },
        async getLocations() {
            const res = await this.getApi("data/location/fetch", {
                params: {
                    search: this.search
                }
            });

            if (res.status == 200) {
                this.location_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
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
                    search: this.search
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
                this.getLocations(),
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
                    location_id:item.location_id,
                    location_name:item.location.name,
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
