<template>
    <div>
        <vue-html2pdf
            :show-layout="true"
            :float-layout="true"
            :enable-download="true"
            :preview-modal="true"
            :paginate-elements-by-height="1400"
            filename="products_details"
            :pdf-quality="2"
            :manual-pagination="false"
            pdf-format="a4"
            pdf-orientation="landscape"
            pdf-content-width="95%"
            ref="html2Pdf"
        >
            <!-- <generate-production-pdf slot="pdf-content" v-bind:pdf_data="pdf_data" />
            -->
            <section slot="pdf-content">
                <span class="pdf-header">
                    <h2>WEAVER MANAGEMENT</h2>

                    <div></div>
                </span>
                <table class="table table-sm table-striped table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Prod Date</th>
                            <th scope="col">Product</th>
                            <th scope="col">Employee</th>
                            <th scope="col">qty</th>
                            <th scope="col">Total Pay</th>
                            <th scope="col">Size</th>
                            <th scope="col">colour</th>
                            <th scope="col">description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            class="small-tr"
                            v-for="(data, i) in pdf_data"
                            :key="i"
                        >
                            <td>
                                {{ data.produced_on }}
                            </td>
                            <td>{{ data.product.name }}</td>
                            <td>
                                {{
                                    data.employee.first_name +
                                        " " +
                                        data.employee.last_name
                                }}
                            </td>
                            <td>{{ data.qty }}</td>
                            <td>{{ cashFormatter(data.total_pay) }}</td>
                            <td>{{ data.size }}</td>
                            <td>{{ data.colour }}</td>
                            <td>{{ data.description }}</td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </vue-html2pdf>
    </div>
</template>
<script>
import VueHtml2pdf from "vue-html2pdf";
export default {
    //props:['pdf_data'],
    data() {
        return {
            pdf_data: []
        };
    },
    components: {
        VueHtml2pdf
    },
    mounted() {
        this.generateReport();
    },
    methods: {
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllProduction();
           
            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllProduction() {
            const res = await this.getApi(
                "data/production/getAllProduction",
                ""
            );

            return res.data.results;
        }
    }
};
</script>
