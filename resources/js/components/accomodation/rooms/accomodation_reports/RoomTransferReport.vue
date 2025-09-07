<template>
    <div class="container row" data-app>
        <div class="col-2"><RoomReportsNav /></div>
        <div class="col-10">
            <div class="row" v-if="isReadPermitted">
                <div class="col-12" v-if="active.dashboard">
                    <div class="d-flex justify-content-around">
                      
                        <h4><b> Guest Tranfers</b></h4>
                    </div>
                    <hr />
                  
                 
                    <div class="d-flex justify-content around mt-3">
                        <div class="form-group">
                            <label> from Date *</label>
                            <date-picker
                                v-model="params.from"
                                valueType="format"
                            />
                        </div>
                        <div class="">
                            <label> to date *</label>
                            <date-picker
                                v-model="params.to"
                                valueType="format"
                            />
                        </div>
                        <div>
                            <button
                                class="btn btn-primary btn-sm mt-2"
                                @click="concurrentApiReq()"
                            >
                                Filter
                            </button>
                        </div>
                    </div>
                  
                   
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered  "
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Guest</th>
                                    <th>Phone</th>
                                    <th scope="col">Room</th>
                                    <th scope="col">Package</th>
                                    <th>Price</th>
                                    <th>Tax</th>
                                    <th scope="col">Total</th>

                                    <th scope="col">Amt Paid</th>
                                    <th scope="col">Amt Pending</th>

                                    <th>From</th>
                                    <th>to</th>
                                    <th>Checkout Date</th>
                                   
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in guest_resevation_data.data"
                                    :key="i"
                               
                                >
                                    <td>
                                     {{ data.name }}
                                    </td>
                                    <td>{{ data.phone }}</td>

                                    <td>
                                        {{ data.room.door_name }}
                                    </td>
                                    <td>
                                        {{ data.room_package.name }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.price) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.tax_amount) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total) }}
                                    </td>

                                    <td>
                                        {{ cashFormatter(data.amount_paid) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(
                                                data.total - data.amount_paid
                                            )
                                        }}
                                    </td>
                                    <td>
                                        {{ data.from }}
                                    </td>
                                    <td>
                                        {{ data.to }}
                                    </td>
                                    <td>
                                        {{ data.checkout_date }}
                                    </td>
                                  
                                    <td>
                                        {{ data.user.name }}
                                    </td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" @click="viewDetails(data)">Updated Info</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        v-if="guest_resevation_data !== null"
                        v-bind:results="guest_resevation_data"
                        v-on:get-page="fetchTransfers"
                    ></Pagination>
                    Items Count {{ guest_resevation_data.total }}
                    <div class="row">
                        <div class="col-4 d-flex" v-if="isDownloadPermitted">
                            <export-excel
                                v-if="isDownloadPermitted"
                                class="btn btn-default btn-export ml-2 "
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                name="filename.xls"
                            >
                                <button class="btn btn-primary btn-sm">
                                    Export Excel
                                </button>
                            </export-excel>

                            <export-excel
                                v-if="isDownloadPermitted"
                                class="btn btn-default btn-export ml-2 "
                                :fetch="fetchExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="filename.xls"
                            >
                                <button class="btn btn-success btn-sm">
                                    Export CSV
                                </button>
                            </export-excel>
                        </div>
                    </div>
                </div>
                <Modal v-model="details_modal">
<ul class="list-group" v-if="details_data">
    <b>Transfers From</b>
  <li class="list-group-item"><b>Room: </b>{{details_data.room.door_name}}</li>
  <li class="list-group-item"><b>Package: </b>{{details_data.room_package.name}}</li>
  <li class="list-group-item"><b>No Days:</b> {{details_data.qty}}</li>
  <li class="list-group-item"><b>Price:</b> {{cashFormatter(details_data.price)}}</li>
    <li class="list-group-item"><b>Total:</b> {{cashFormatter(details_data.total)}}</li>
      
       <li class="list-group-item"><b>Amount Paid: </b> {{cashFormatter(details_data.amount_paid)}}</li>
        <li class="list-group-item"><b>Cash Paid: </b> {{cashFormatter(details_data.cash_paid)}}</li>
        <li class="list-group-item"><b>Mobile Money : </b> {{cashFormatter(details_data.mobile_money_paid)}}</li>
      <li class="list-group-item"><b>Card Payments : </b> {{cashFormatter(details_data.card_paid)}}</li>
       <li class="list-group-item"><b>Cheque Payments : </b> {{cashFormatter(details_data.cheque_paid)}}</li>
              <li class="list-group-item"><b>Transfers : </b> {{cashFormatter(details_data.bank_transfer_paid)}}</li>
       
      

</ul>
                </Modal>
               
            </div>

            <div v-else>
                <unauthorized />
            </div>
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import CreateGuest from "../../guest/CreateGuest.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import UpdateRoomBooking from "../UpdateRoomBooking.vue";
import _ from "lodash";
import RoomReportsNav from "../RoomReportsNav.vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";

export default {
    data: () => ({
        active: {
            dashboard: true,
            
        },
       details_modal:false,
       details_data:null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        guest_resevation_data: [],
        room_select_data: null,
        sum_total: 0,
        sum_tax_amount: 0,
        form_data: {
            guest_id: null
        },
        format: "YYYY-MM-DD HH:mm:ss",
        search: "",
      
        params: {
            to: null,
            from: null,
           
        }
    }),
    components: {
        CreateGuest,
        Treeselect,
        Unauthorized,
        UpdateRoomBooking,
        Pagination,
        RoomReportsNav,
        VueCtkDateTimePicker
    },
    created() {
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch: {
        
        search: {
            handler: _.debounce(function(val, old) {
                this.isLoading = false;
                this.fetchTransfers(1);
            }, 500)
        }
    },
    methods: {
        viewDetails(data){
        this.details_modal=true
        this.details_data=data.room_reservation
        console.log(JSON.stringify(data.room_reservation));
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
     
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/room_transfer/fetch", {
                params: {
                   
                    ...this.params,
                    search: this.search
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                   
                    data_array[i].room = data_array[i].room.door_name;
                    data_array[i].room_package =
                        data_array[i].room_package.name;
                    data_array[i].guest = data_array[i].name;
                }

                return data_array;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },
     
      
        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchTransfers(1),
               
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },

        async fetchTransfers(page) {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/room_transfer/fetch", {
                params: {
                    page: page,

                  
                    search: this.search,
                    ...this.params
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.guest_resevation_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

    

     
     
    }
};
</script>
<style scoped>
table {
    font-family: "signika-light", sans-serif !important;
}
.pending-payment {
    background: red !important;
}
td{
    font-size: 0.8rem  !important;
}
</style>
