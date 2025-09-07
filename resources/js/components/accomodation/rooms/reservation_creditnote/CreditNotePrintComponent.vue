<template>
    <div>
        <div class="row">
            <div class="col-5">
                <receipt-header />
                <table width="100%" class="head">
                    <tr>
                        <td>
                            <center>
                                <h3><b>Credit Note</b></h3>
                            </center>
                        </td>
                    </tr>
                    <tr></tr>
                </table>

                -------------------------------------------------------------------------
                <table width="100%" class="mt-3">
                    <tr>
                        <td class="room-details">Credit No.</td>
                        <td class="room-details">{{ form_data.credit_no }}</td>
                    </tr>

                    <tr>
                        <td class="room-details">Client Name</td>
                        <td class="room-details">{{ checkout_data.name }}</td>
                    </tr>

                    <tr>
                        <td class="room-details">Room No</td>
                        <td class="room-details">
                            {{ checkout_data.room.door_name }}
                        </td>
                    </tr>
                </table>

                ------------------------------------------------------------------------

                <table width="100%" class="mt-3">
                    <tr>
                        <td class="room-details">Reserved From</td>
                        <td class="room-details">
                            {{ checkout_data.from }}
                        </td>
                    </tr>
                    <tr>
                        <td class="room-details">Reserved To</td>
                        <td class="room-details">
                            {{ checkout_data.to }}
                        </td>
                    </tr>

                    <tr>
                        <td class="room-details">Standard</td>
                        <td class="room-details">
                            {{ checkout_data.room.room_standard.name }} /
                            {{ checkout_data.room_package.name }}
                        </td>
                    </tr>
                    <tr>
                        <td class="room-details">Details</td>
                        <td class="room-details">
                            {{ form_data.details }}
                        </td>
                    </tr>
                </table>

                -------------------------------------------------------------------------

                <table width="100%">
                    <tr>
                        <td class="room-details">
                            <h3><b> Credit Amount</b></h3>
                        </td>
                        <td class="room-details">
                            <h3>
                                <b> {{ cashFormatter(form_data.total) }}</b>
                            </h3>
                        </td>
                    </tr>
                </table>

                -------------------------------------------------------------------------
                <table width="100%">
                    <tr>
                        <td class="room-details">
                            Processed By: {{ $store.state.user.name }}
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
</template>
<script>
import ReceiptHeader from "../../../pos/menu_components/dinerscomponents/ReceiptHeader.vue";

export default {
    components: { ReceiptHeader },
    props: ["form_data", "checkout_data"],
    data() {
        return {
            userInfo: null,
        };
    },
    mounted() {
 
       
    },
};
</script>
