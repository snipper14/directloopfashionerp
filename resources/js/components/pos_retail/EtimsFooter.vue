<template>
    <div v-if="etims_qrcode_data.etims_url">
        <table width="90%">
            <thead>
                <th>Tax Class</th>
                <th>A-Exempt</th>
                <th>B-VAT 16%</th>
                <th>C-Zero Rated</th>
                <th>D-Non VAT</th>
                <th>E-VAT 8%</th>
            </thead>
            <tbody>
                <tr>
                    <td>Taxable Amount</td>
                    <td>
                        {{ etims_data.sales_tax_summary.taxable_amount_a }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.taxable_amount_b }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.taxable_amount_c }}
                    </td>

                    <td>
                        {{ etims_data.sales_tax_summary.taxable_amount_d }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.taxable_amount_e }}
                    </td>
                </tr>
                <tr>
                    <td>Tax Rate(%)</td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_rate_a }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_rate_b }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_rate_c }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_rate_d }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_rate_e }}
                    </td>
                </tr>
                <tr>
                    <td>Tax Amount(KES)</td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_amount_a }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_amount_b }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_amount_c }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_amount_d }}
                    </td>
                    <td>
                        {{ etims_data.sales_tax_summary.tax_amount_e }}
                    </td>
                </tr>
            </tbody>
        </table>
        <!-- 
        <qrcode-vue
            :value="link"
            size="150"
            level="H"
        ></qrcode-vue
        ><br /> -->
         <div style="width:170px">
            <div v-html="qrCodeSvg"></div>
        </div>
        <br />
        <table width="90%">
            <tbody>
                <tr v-if="etims_qrcode_data.customer_pin">
                    <td>Customer PIN:</td>
                    <td>{{ etims_qrcode_data.customer_pin }}</td>
                </tr>
                <tr v-if="etims_qrcode_data.customer_name">
                    <td>Customer:</td>
                    <td>{{ etims_qrcode_data.customer_name }}</td>
                </tr>
                <tr>
                    <td>Invoice Number:</td>
                    <td>
                        {{ etims_qrcode_data.serial_number }}/
                        {{ etims_qrcode_data.receipt_number }}
                    </td>
                </tr>
              
            </tbody>
        </table>
        <br />
        <span>Date: {{ etims_data.date }}</span
        ><br />
        <span>Time: {{ etims_data.time }}</span
        ><br />

        <br />
    </div>
</template>

<script>
import QrcodeVue from "qrcode.vue";

import QRCode from "qrcode-generator";
export default {
    components: {
        QrcodeVue,
      
        QRCode,
    },
    props: ["etims_data", "etims_qrcode_data"],
    data() {
        return {
            link: this.etims_qrcode_data.etims_url,
            tax_breakdown: {},
            qrCodeSvg: "",
        };
    },
    created() {
        this.tax_breakdown = this.etims_data.sales_tax_summary;
       
        this.generateQRCode();
    },
    methods: {
        generateQRCode() {
            let qr = QRCode(6, "L"); // Increase version from 4 to 10
            qr.addData(this.link);
            qr.make();
              this.qrCodeSvg = qr.createSvgTag({ scalable: true, width: 40, height: 40 });
        },
    },
};
</script>
