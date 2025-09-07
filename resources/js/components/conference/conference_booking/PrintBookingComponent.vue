<template>
    <div class="container row" data-app>
        <div class="col-10" id="printBill" v-if="all_booking_data.length > 0">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-6"><receipt-header /></div>
                        <div class="col-6">
                            <h2><b>Conference Report</b></h2>
                            <b>Printed By {{ $store.state.user.name }}</b>
                        </div>
                    </div>
                    <hr />

                    <div class="table-responsive">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Conference Room</th>
                                    <th>Booking Date</th>
                                    <th scope="col">Cust Name</th>
                                    <th>Contacts/Email</th>
                                    <th scope="col">Amount Paid</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in all_booking_data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.conference_room }}
                                    </td>
                                    <td>
                                        {{ formatDateTime(data.created_at) }}
                                    </td>

                                    <td>
                                        {{ data.company_name }}
                                    </td>
                                    <td>{{ data.phone }}/{{ data.email }}</td>
                                    <td>
                                        {{ cashFormatter(data.total_paid) }}
                                    </td>
                                    <td>
                                        {{ data.user.name }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="width: 100%">
                            <tr>
                                <td><b> TOTAL</b></td>
                                <td>
                                    <b> {{ cashFormatter(total) }}</b>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import ReceiptHeader from "../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    props: ["all_booking_data"],
    data: () => ({
        total: 0,
    }),
    components: {
        ReceiptHeader,
    },
    created() {
        var data = this.all_booking_data; // your json data

        for (var i = 0; i < data.length; i++) {
            this.total += parseInt(data[i].total_paid);
        }
    },

    methods: {
        printBill: function () {
            printJS("printBill", "html");
            // this.value = value;
        },
    },
};
</script>
<style scoped>
.small-tr > td {
    font-family: "signika-light", sans-serif !important;
}
#printBill {
    visibility: hidden;
}
</style>
