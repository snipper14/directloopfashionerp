<template>
    <div v-if="etims_data.sale_detail_url">
        <table>
            <thead>
                <th>Tax Class</th>
                <th>A-Exempt</th>
                <th>C-Zero Rated</th>
                <th>E-VAT 8% </th>
                <th>D-Non VAT</th>
                <th>B-VAT 16%</th>
            </thead>
            <tbody>
                <tr>
                    <td>Taxable Amount</td>
                    <td>
                        {{ etims_data.taxable_amount_a }}
                    </td>
                    <td>
                        {{ etims_data.taxable_amount_b }}
                    </td>
                    <td>
                        {{ etims_data.taxable_amount_c }}
                    </td>
                    <td>
                        {{ etims_data.taxable_amount_d }}
                    </td>
                    <td>
                        {{ etims_data.taxable_amount_e }}
                    </td>
                </tr>
                <tr>
                    <td>Tax Rate(%)</td>
                    <td>
                        {{ etims_data.tax_rate_a }}
                    </td>
                    <td>
                        {{ etims_data.tax_rate_b }}
                    </td>
                    <td>
                        {{ etims_data.tax_rate_c }}
                    </td>
                    <td>
                        {{ etims_data.tax_rate_d }}
                    </td>
                    <td>
                        {{ etims_data.tax_rate_e }}
                    </td>
                </tr>
                <tr>
                    <td>Tax Amount(KES)</td>
                    <td>
                        {{ etims_data.tax_amount_a }}
                    </td>
                    <td>
                        {{ etims_data.tax_amount_b }}
                    </td>
                    <td>
                        {{ etims_data.tax_amount_c }}
                    </td>
                    <td>
                        {{ etims_data.tax_amount_d }}
                    </td>
                    <td>
                        {{ etims_data.tax_amount_e }}
                    </td>
                </tr>
            </tbody>
        </table>
        
              <div style="width:170px">
            <div v-html="qrCodeSvg"></div>
        </div><br />
            <span>Date: {{ etims_data.etims_date }}</span
            ><br />
            <span>Time: {{ etims_data.etims_time }}</span
            ><br />
           
            <span>SCU Invoice No: <br> {{ etims_data.serial_number }}/{{ etims_data.receipt_number }}</span
            ><br />
             <span v-if="etims_data.etims_customer_name">Customer: {{ etims_data.etims_customer_name }}</span
            ><br />
             <span v-if="etims_data.etims_customer_pin">Customer PIN: {{ etims_data.etims_customer_pin }}</span
            ><br />

            <br />
       
    </div>
</template>

<script>
import QrcodeVue from "qrcode.vue";
import QRCode from "qrcode-generator";
export default {
    components: {
        QrcodeVue,QRCode
    },
    props: ["etims_data"],
    data() {
        return {
            tax_breakdown: {},
            link: this.etims_data.sale_detail_url,
        };
    },
    created() {
       
      this.generateQRCode()
    },
    methods:{
         generateQRCode() {
            let qr = QRCode(6, "L"); // Increase version from 4 to 10
            qr.addData(this.link);
            qr.make();
              this.qrCodeSvg = qr.createSvgTag({ scalable: true, width: 40, height: 40 });
        },
    }
};
</script>
