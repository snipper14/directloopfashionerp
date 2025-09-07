<template>
    <div class="container row" data-app>
        <div class="col-2"><RoomReportsNav /></div>
        <div class="col-10">
            <div class="row" >
                <div class="col-12" v-if="active.dashboard">
                    <div class="d-flex justify-content-around">
                        <h4><b>In-House Report</b></h4>
                    </div>
                    <hr />

                 
                    <div v-for="(val,i) in resevation_data" :key="i">
                        <br>
                    <p><b>{{(val.room_package)}} Total:</b> <b>{{cashFormatter(val.sum_total)}}</b> </p>
                     <b>  No Of Guest: {{val.sum_no_guest}}</b>
                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Guest</th>
                                 <th>No Guest</th>
                                    <th scope="col">Room</th>
                                    <th>Room Type</th>
                                    <th scope="col">Package</th>
                                    <th>Price</th>
                                    <th>Meal Rate</th>
                                    <th scope="col">Total</th>

                                   
                                    <th>Period</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in val.data"
                                    :key="i"
                                   
                                >
                                    <td>
                                      {{ data.name }}
                                    </td>
                                  <td>{{data.no_guest}}</td>

                                    <td>
                                        {{ data.room.door_name }}
                                    </td>
                                    <td>
                                        {{ data.room.room_standard.name }}
                                    </td>
                                    <td>
                                        {{ data.room_package.details }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.price) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.room_rate.meal_rate) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total) }}
                                    </td>

                                  
                                  
                                    <td>{{ data.from + " - " + data.to }}</td>
                                    <td>{{ data.clear_status }}</td>

                                </tr>
                             
                            </tbody>
                        </table>
                    </div>
                   
                </div>

                    <div class="row">
                        <div class="col-8 d-flex" >
                     
                      
                            <button
                                class="btn btn-info btn-sm mr-2"
                                @click="downLoadPdf()"
                            >
                                Generate In-House Guest Report
                            </button>
                    
                        </div>
                    </div>
                </div>
              
            </div>

           
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>


import RoomReportsNav from "./RoomReportsNav.vue";
import VueCtkDateTimePicker from "vue-ctk-date-time-picker";
import "vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css";

export default {
    data: () => ({
        active: {
            dashboard: true,
            edit_bookings: false,
        },
        curr_date: null,
        edit_data: null,
        house_keeping_report: null,
        focus: "",
        room_data: null,
        isLoading: true,
        guest_select_data: null,
        resevation_data: [],
        room_select_data: null,
        sum_total: 0,
        sum_tax_amount: 0,
        form_data: {
            guest_id: null,
        },
        format: "YYYY-MM-DD HH:mm:ss",
        search: "",
        search_params: {
            room_id: null,
            guest_id: null,
            clear_status: null,
        },
        params: {
            checkin_to: null,
            checkin_from: null,
            checkout_to: null,
            checkout_from: null,
        },
    }),
    components: {
   
   
        RoomReportsNav,
        VueCtkDateTimePicker,
    },
    created() {
           this.curr_date = this.getDateTime();
        this.concurrentApiReq();
        //this.$refs.calendar.checkChange();
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                // this.search_params.to=this.formatDateTime( this.search_params.to)
                // this.search_params.from=this.formatDateTime( this.search_params.from)

                this.concurrentApiReq();
            }, 1000),
        },
        search: {
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.fetchReservations(1);
            }, 500),
        },
    },
    methods: {
        async downLoadPdf() {
            this.showLoader();
            axios({
                url: "data/reservation/downLoadInhouseGuestReport",
                params: {
                    curr_date: this.formatDateTime(this.curr_date),
                  
                },
                method: "GET",
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    "stock movement" + this.getTimeStamp() + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },

       
      
     

        async concurrentApiReq() {
            this.isLoading = false;
            this.showLoader();
            const res = await Promise.all([
                this.fetchReservations(1),
               
            ]).then(function (results) {
                return results;
            });
            this.hideLoader();
        },

        async fetchReservations() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/reservation/kitchenReport",
                {
                    params: {
                        curr_date: this.curr_date,
                       
                    },
                }
            );
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.resevation_data = res.data;
            } else {
                this.swr("Server error could not fetch employees");
            }
        },

       

    
      
    },
};
</script>
<style scoped>
.small-tr > td {
    font-family: "signika-light", sans-serif !important;
}
.pending-payment {
    background: red !important;
}
</style>
