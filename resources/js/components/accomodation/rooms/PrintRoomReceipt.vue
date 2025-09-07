<template>
    <div class="row">
        <div class="col-4">
            <table width="100%" class="head">
                <tr>
                    <td class="printer-header" style="font-size:2.4rem;">
                        <center>
                            <h3><b>Receipt</b></h3>
                        </center>
                    </td>
                </tr>
                <tr>
                    <td class="printer-header" style="font-size:1.4rem;">
                        <center>
                            {{ branchInfo.business_name }}
                        </center>
                    </td>
                </tr>

                <tr>
                    <td class="printer-header">
                        <center>
                            {{ branchInfo.name }}
                        </center>
                    </td>
                </tr>

                <tr>
                    <td class="printer-header">
                        <center>
                            {{
                                branchInfo.address
                                    ? "Address: " + branchInfo.address
                                    : ""
                            }}
                        </center>
                    </td>
                </tr>

                <tr>
                    <td class="printer-header">
                        <center>
                            {{ branchInfo.postal_address }}
                        </center>
                    </td>
                </tr>

                <tr>
                    <td class="printer-header">
                        <center>
                            {{
                                branchInfo.phone_no
                                    ? "TEL: " + branchInfo.phone_no
                                    : ""
                            }}
                        </center>
                    </td>
                </tr>
                <tr>
                    <td class="printer-header">
                        <center>
                            {{ new Date() | moment(" YYYY-MM-DD, h:mm") }}
                        </center>
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
</template>

<script>
import print from "print-js";
export default {
    props: ["reservation_data"],
    data() {
        return {
            branchInfo: "",
            userInfo: "",
            waiter: "",
            bill_total: 0,
            tax_total: 0,
            data: null,
            receipt_details: null,
            form_data: {
                order_no: "",
                order_total: 0,
                vat_paid: 0
            }
        };
    },
    watch: {
        data: {
            deep: true,
            handler(val, old) {
                this.bill_total = this.data.reduce(function(sum, val) {
                    return sum + val.row_total;
                }, 0);
                this.tax_total = this.data.reduce(function(sum, val) {
                    return sum + val.row_vat;
                }, 0);
            }
        }
    },
    created() {
        this.branchInfo = this.$store.state.branch;
        this.userInfo = this.$store.state.user;
        this.receipt_details = this.reservation_data;
    },
    methods: {}
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
/* #printReceipt {
    visibility: hidden;
}  */
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
