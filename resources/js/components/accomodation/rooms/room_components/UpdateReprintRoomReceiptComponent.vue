<template>
    <div class="container">
        <div id="printReceipt">
            <div class="row">
                <div class="col-5">
                    <receipt-header />
                    <table width="100%" class="head">
                        <tr>
                            <td>
                                <center>
                                    <h3><b>ACCOMMODATION RECEIPT</b></h3>
                                </center>
                            </td>
                        </tr>
                        <tr>
                            <td class="printer-header">
                                <center>REPRINT</center>
                            </td>
                        </tr>
                    </table>

                    -------------------------------------------------------------------------
                    <table width="100%" class="mt-3">
                        <tr>
                            <td class="room-details">From:</td>
                            <td class="room-details">{{ form_data.guest_company.name }}</td>
                        </tr>
                          <tr>
                            <td class="room-details">Client Name</td>
                            <td class="room-details">{{ form_data.name }}</td>
                        </tr>

                        <tr>
                            <td class="room-details">Room No</td>
                            <td class="room-details">
                                {{ form_data.room.door_name }}
                            </td>
                        </tr>
                    </table>

                    ------------------------------------------------------------------------

                    <table width="100%" class="mt-3">
                        <tr>
                            <td class="room-details">Reserved From</td>
                            <td class="room-details">
                                {{ form_update_data.from }}
                            </td>
                        </tr>
                        <tr>
                            <td class="room-details">Reserved To</td>
                            <td class="room-details">
                                {{ form_update_data.to }}
                            </td>
                        </tr>

                        <tr>
                            <td class="room-details">Standard</td>
                            <td class="room-details">
                                {{ form_data.room.room_standard.name }} /
                                {{ form_data.room_package.name }}
                            </td>
                        </tr>
                        <tr>
                            <td class="room-details">Guests</td>
                            <td class="room-details">
                                {{ form_update_data.no_guest }}
                            </td>
                        </tr>
                    </table>

                    -------------------------------------------------------------------------
                    <table width="100%">
                        <tr>
                            <td class="room-details">
                                VAT ( {{ form_data.tax_rate }}%)
                            </td>
                            <td class="room-details">
                                {{ cashFormatter(form_update_data.tax_amount) }}
                            </td>
                        </tr>
                    </table>

                    <table width="70%">
                        <tr>
                            <td class="room-details">
                                <h3><b> TOTAL AMOUNT:</b></h3>
                            </td>
                            <td class="room-details">
                                <h3>
                                    <b>
                                        {{
                                            cashFormatter(form_update_data.amount_paid)
                                        }}</b
                                    >
                                </h3>
                            </td>
                        </tr>
                    </table>

                    -------------------------------------------------------------------------
                    <table width="100%">
                        <tr>
                            <td class="room-details">
                                Served By: {{  $store.state.user.name}}
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-8"></div>
                <div>
                    <div class="d-flex">
                        <div></div>
                    </div>

                    <hr />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ReceiptHeader from "../../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";
export default {
    components: { ReceiptHeader },
    props: ["form_update_data", "room_data",'form_data'],
    data() {
        return {};
    },
    mounted() {
        // printJS("printReceipt", "html");
    },
    methods: {
        initPrintReceipt() {
            printJS("printReceipt", "html");
        },
    },
};
</script>
<style scoped>
#printReceipt {
    visibility: hidden;
}
</style>
