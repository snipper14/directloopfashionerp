<template>
  
    <div class="col-12" id="print_document" data-app>
        <div class="row d-flex justify-content-between mr-2">
            <v-btn
                class="ma-2 v-btn-primary ml-2"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left> mdi-arrow-left </v-icon>
                Back
            </v-btn>
  </div>
        <div style="border: 2px solid black">
            <receipt-header />
            <hr />
            <br />
            <br /><b>From {{ from }} To {{ to }}</b>
            <p>Printed By: {{ this.$store.state.user.name }}</p>
            <br />
            <p><b>In House Guest</b></p>
            <br /><br />
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Room</th>
                        <th>Guest Name</th>
                        <th>Package</th>
                        <th>No. Guest</th>
                        <th>No. Days</th>
                        <th scope="col">Check-In Type</th>
                        <th scope="col">Check-In Time</th>
                        <th>Price</th>

                        <th scope="col">Total</th>
                          <th scope="col">Total Paid</th>
                    </tr>
                </thead>
            </table>
            <div class=" small-tr" v-for="(data_1, i) in grouped_data" :key="i">
              <div class="table-responsive"> 
                 <table class="table table-sm table-striped table-bordered">
                     <tr>
                         <td> <b>{{ data_1.name }}</b></td>
                     </tr>
                     <tr>
                         <td> <b> Total Guest : {{data_1.no_guest}}</b></td>
                          <td> <b> Total Rate : {{ cashFormatter(data_1.sum_rate)}}</b></td>
                          <td><b> Total Paid : {{cashFormatter(data_1.sum_total)}}</b></td>
                     </tr>
                 </table>
                 </div>
                 <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered">
                    <tbody>
                        <tr
                            class="small-tr"
                            v-for="(data, i) in data_1.details"
                            :key="i"
                        >
                            <td>{{ data.door_name }}</td>

                            <td>
                                {{ data.guest }}
                            </td>
                            <td>
                                {{data.room_package}}
                            </td>
                            <td>
                                {{ data.no_guest }}
                            </td>
                             <td>
                                {{ data.qty }}
                            </td>
                            <td>
                                {{ data.guest_company }}
                            </td>
                            <td>
                                {{ formatDateTime(data.created_at) }}
                            </td>
                            <td>
                                {{ cashFormatter(data.price) }}
                            </td>

                            <td>
                                {{ cashFormatter(data.price) }}
                            </td>
                            <td>
                                {{ cashFormatter(data.total) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                 </div>
           
            </div>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from "../../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    props: ["grouped_data", "from", "to"],
    data() {
        return {};
    },
    components: {
        ReceiptHeader,
    },
created(){
    this.scrollTop()
},
    methods: {
        printDocument: function () {
            printJS("print_document", "html");
            // this.value = value;
        },
    },
    mounted() {},
};
</script>

<style scoped>
#print_document {
    /* visibility: hidden; */
}

</style>
