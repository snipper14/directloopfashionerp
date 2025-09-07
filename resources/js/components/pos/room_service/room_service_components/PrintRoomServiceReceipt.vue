<template>
    
          
                <div id="printReceipt">
                    <receipt-header/>
                    <table width="100%" class="head">
                        <tr>
                            <td
                                class="printer-header"
                                style="font-size: 2.4rem"
                            >
                                <center>
                                    <h3><b>Sales Receipt</b></h3>
                                </center>
                            </td>
                        </tr>
                    </table>

                    <div>
                        <table width="100%">
                            <tr>
                                <td>
                                    Served By :
                                    {{ waiter }}
                                </td>
                            </tr>

                            <tr>
                                <td v-if="order_data[0]">
                                    receipt no :
                                    {{ form_data.receipt_no }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Room Package :
                                    {{ order_data[0].room_package.name }}
                                </td>
                            </tr>
                            <tr v-if="order_data[0].room_package.details">
                                <td>
                                    Room Package Details :
                                    {{ order_data[0].room_package.details }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Room Name :
                                    {{ order_data[0].room.door_name }}
                                </td>
                            </tr>
                        </table>
                        <receipt-body-component :data="order_data" />

                        <hr />

                        <table>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            TAX AMOUNT :
                                            {{
                                                cashFormatter(
                                                    round(form_data.total_vat)
                                                )
                                            }}
                                        </p>
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <p class="t-amount">
                                            SERVICE CHARGE ({{
                                                service_charge_rate
                                            }}%):
                                            {{
                                                cashFormatter(
                                                    round(
                                                        form_data.service_charge_total
                                                    )
                                                )
                                            }}
                                        </p>
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td class="printer-footer">
                                    <center>
                                        <h3 class="t-amount">
                                            RECEIPT TOTAL:
                                            {{
                                                cashFormatter(
                                                    form_data.receipt_total
                                                )
                                            }}
                                        </h3>
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.cash_pay.length > 0"
                                    >
                                        CASH:
                                        {{
                                            cashFormatter(
                                                round(form_data.cash_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.mpesa_pay.length > 0"
                                    >
                                        M-PESA:
                                        {{
                                            cashFormatter(
                                                round(form_data.mpesa_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <center
                                        class="t-amount"
                                        v-if="form_data.card_pay.length > 0"
                                    >
                                        Card:
                                        {{
                                            cashFormatter(
                                                round(form_data.card_pay)
                                            )
                                        }}
                                    </center>
                                </td>
                            </tr>

                            <tr>
                                ================================================
                            </tr>
                            <tr>
                                <td>
                                    Thank you for trusting in us please welcome
                                </td>
                            </tr>
                            <tr>
                                <td>Cashier : {{ this.$store.state.user.name }}</td>
                            </tr>
                            <tr>
                                ================================================
                            </tr>
                        </table>
                    </div>
                </div>
            
</template>

<script>
import print from "print-js";
import ReceiptHeader from '../../menu_components/dinerscomponents/ReceiptHeader.vue';
import ReceiptBodyComponent from '../../menu_components/dinerscomponents/ReceiptBodyComponent.vue';


export default {
    components: {
        ReceiptHeader,
        ReceiptBodyComponent
       
    },
    props: ["form_data",'service_charge_rate','order_data','waiter'],
    data() {
        return {};
    },

    created() {
       
        
    },
    methods: {
        printReceipt: function () {
              console.log(">>>>working");
            printJS("printReceipt", "html");
            // this.value = value;
        },
    },
};
</script>
<style scoped>
.receipt_header {
    display: flex;
    flex-direction: column;
}
.receipt-menu td {
    color: #000;
    font-weight: 700;
}
.top_row {
    display: table;
    width: 100% !important;
}

.top_row > div {
    display: table-cell;
    width: 50%;
    border-bottom: 1px solid #eee;
}
#printReceipt {
     visibility: hidden;
    
}
.printer-header {
    text-align: center;
    font-weight: 600;
    color: #000;
    text-transform: uppercase;
}
.t-amount {
    color: #000;
    font-weight: 600;
}
</style>
