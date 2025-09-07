<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-8">
                 <v-btn
                class="ma-2 v-btn-primary"
                @click="$emit('dashboard-active')"
                color="primary"
                dark
            >
                <v-icon dark left>
                    mdi-arrow-left
                </v-icon>
                Back
            </v-btn>
                <div class="card">
                    <div class="card-header">
                        <div
                            class="d-flex justify-content-between  align-items-center"
                        >
                            <h4>Statement</h4>
                            <button
                                v-if="statements"
                                @click="downLoadStatement()"
                                class="btn btn-secondary custom-button "
                                color="primary"
                                dark
                            >
                                Print Statement
                            </button>

                            <p><b>Customer</b> :{{ data.customer }}</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div
                            class="d-flex justify-content-between  align-items-center"
                        >
                            <div class="form-group">
                                <label> From*</label>
                                <date-picker
                                    v-model="from"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To*</label>
                                <date-picker
                                    v-model="to"
                                    valueType="format"
                                ></date-picker>
                            </div>

                            <button
                                class="btn btn-primary custom-button mb-2"
                                color="primary"
                                dark
                                @click="filter()"
                            >
                                Filter
                            </button>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Amount Paid</th>
                                    <th scope="col">Pending Balance</th>
                                    <th>Date Paid</th>
                                    <th scope="col">Ref no</th>
                                    <th scope="col">Details</th>
                                </tr>
                            </thead>
                            <tbody 
                                v-if="statements">
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in statements"
                                    :key="i"
                                >
                                    <td>
                                        {{ cashFormatter(data.amount_paid) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.amount_due) }}
                                    </td>
                                    <td>
                                        {{ data.date_paid }}
                                    </td>
                                    <td>
                                        {{ data.ref_no }}
                                    </td>
                                    <td>
                                        {{ data.payment_details }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <b> {{ cashFormatter(amount_paid) }}</b>
                                    </td>
                                    <td>
                                        <b> {{ cashFormatter(totalDue) }}</b>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <vue-html2pdf
                :show-layout="false"
                :float-layout="true"
                :enable-download="true"
                :preview-modal="true"
                :paginate-elements-by-height="1400"
                filename="STATEMENT"
                :pdf-quality="2"
                :manual-pagination="false"
                pdf-format="a4"
                pdf-orientation="landscape"
                pdf-content-width="1200px"
                ref="html2Pdf"
            >
                <section slot="pdf-content">
                    <span class="pdf-header2">
                        

                        <div>
                            <img
                              height="100" width="100"
                                src="../../../assets/images/logo.png"
                            />
                        </div>
                       
                    </span>
                    <table class="table-address">
                        <tr>
                            <td class="left-cont">
                                <p>Thika Light Industrial Area</p>
                                <p>Opp. Thika High School</p>
                                <p>Along Muindi Mbingu Street</p>
                                <p>P.0 Box 4137-01002- Thika</p>
                            </td>
                            <td class="right-cont">
                                <span>
                                    <p>Tel: 020-2051487</p>
                                    <p>Mobile: 0722-264 464</p>
                                    <p>Email: sales@weaverBirdKenya.com</p>
                                    <p>weaver.garmentsm@yahoo.com</p>
                                    <p>www.weaverbirdkenya.com</p>
                                </span>
                            </td>
                        </tr>
                    </table>
                     <table class="table-to-address">
        <tr>
            <td class="left-cont" v-if="statements">
                <h4>TO</h4>
             
                <p>{{ statements[0].customer.company_name}}</p>
               
                <p><b>Phone: </b>{{ statements[0].customer.company_phone}} </p>
                <p><b>Email: </b>{{ statements[0].customer.company_email}} </p>
                <p><b>Country: </b>{{ statements[0].customer.country}}/{{ statements[0].customer.city}} </p>
                <p>{{ statements[0].customer.postal_address}} / {{ statements[0].customer.address}} </p>
               

            </td>
            <td class="right-cont">


            </td>
        </tr>
    </table>
                     <div> <center><h2>STATEMENT</h2> <b><span v-if="to && from">From: {{from}} - {{to}}</span></b></center></div>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">Amount Paid</th>
                                <th scope="col">Pending Balance</th>
                                <th>Date Paid</th>
                                <th scope="col">Ref no</th>
                                <th scope="col">Details</th>
                            </tr>
                        </thead>
                        <tbody v-if="statements">
                            <tr
                                class="small-tr"
                                v-for="(data, i) in statements"
                                :key="i"
                            >
                                <td>
                                    {{ cashFormatter(data.amount_paid) }}
                                </td>
                                <td>
                                    {{ cashFormatter(data.amount_due) }}
                                </td>
                                <td>
                                    {{ data.date_paid }}
                                </td>
                                <td>
                                    {{ data.ref_no }}
                                </td>
                                <td>
                                    {{ data.payment_details }}
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <b> {{ cashFormatter(amount_paid) }}</b>
                                </td>
                                <td>
                                    <b> {{ cashFormatter(totalDue) }}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </vue-html2pdf>
        </div>
    </div>
</template>

<script>
import VueHtml2pdf from "vue-html2pdf";
export default {
    props: ["data"],
    data() {
        return {
            statements: "",
            totalDue: 0,
            amount_paid: 0,
            to: "",
            from: ""
        };
    },
    created() {
      
        this.getAll();
    },
    components: {
        VueHtml2pdf
    },
    methods: {
        async filter() {
            //  if (this.to > 1 && this.from.length > 1) {
            if (this.to && this.from) {
                const param = {
                    customer_id: this.data.customer_id,
                    to: this.to,
                    from: this.from
                };
                this.getStatement(param);
            } else {
                this.getAll();
            }
        },
        async getAll() {
            if(this.data){
            const param = {
                customer_id: this.data.customer_id
            };
            this.getStatement(param);
            }
        },
        async getStatement(params) {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/payments/fetchCustomerStatement",

                params
            );
          
            this.hideLoader();
            if (res.status == 200) {

                this.statements = res.data.results;
               
                this.totalDue = this.statements.reduce(function(sum, due) {
                    return sum + due.amount_due;
                }, 0);
          
                this.amount_paid = this.statements.reduce(function(sum, val) {
                    return sum + val.amount_paid;
                }, 0);
            } else {
                this.swr("server error");
            }
        },
        async downLoadStatement() {
            this.$refs.html2Pdf.generatePdf();
        }
    }
};
</script>
<style scoped>
.table-address {
    width: 90% !important;
    margin-bottom: 1rem;
}
.left-cont p {
    line-height: 0.6em;
    font-weight: 600;
}
table{
    width: 90% !important;
}
 .right-cont span {
            float: right;
            margin-right: 1rem;

        }

        .right-cont span p {
            line-height: 0.6em;
            font-weight: 600;
        }

        .table-address p {
            font-size: 0.8em;
        }
        .pdf-header2{
            margin-left: 2rem !important;
            margin-right: 2rem  !important;

        }
</style>
