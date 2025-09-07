<template>
    <div class="container">
       
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                class="ma-2 v-btn-primary"
                               @click="$emit('dashboard-active')"
                                color="primary"
                                dark
                            >
                               
                               Back
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color:red">Full Monthly Report</h5>
                        </div>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Employee</th>

                                    <th scope="col">From</th>
                                    <th scope="col">To</th>
                                    <th scope="col">Basic Salary</th>
                                    <th scope="col">Total Allowance</th>
                                    <th scope="col">Commissions /Bonuses</th>
                                    <th scope="col">NSSF</th>
                                    <th scope="col">SHIF</th>
                                     <th scope="col">Hse Levy</th>
                               

                                    <th scope="col">Tax Relief</th>
                                    <th scope="col">Net Paye</th>
                                    <th scope="col">Pay After Tax</th>
                                    <th scope="col">Total Advance</th>
                                    <th scope="col">Total Loans</th>
                                    <th scope="col">Total Deduction</th>
                                    <th scope="col">Total Overtime</th>
                                    <th scope="col">Net Salary</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in payslips_data"
                                    :key="i"
                                >
                                    <td>
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    </td>

                                    <td>
                                        {{ data.payroll_from }}
                                    </td>
                                    <td>
                                        {{ data.payroll_to }}
                                    </td>
                                    <td>
                                        <strong>
                                            {{
                                                cashFormatter(data.basic_salary)
                                            }}
                                        </strong>
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.total_allowance)
                                        }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total_rewards) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.nssf) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.nhif) }}
                                    </td>
                                      <td>
                                        {{ cashFormatter(data.housing_levy) }}
                                    </td>
                                    
                                    <td>
                                        {{ cashFormatter(data.tax_relief) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.net_paye) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.pay_after_tax) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total_advance) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total_loans) }}
                                    </td>
                                    <td>
                                        {{
                                            cashFormatter(data.total_deduction)
                                        }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.total_overtime) }}
                                    </td>
                                    <td>
                                        {{ cashFormatter(data.net_salary) }}
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-secondary btn-small"
                                            @click="viewPaySlips(data)"
                                        >
                                            View Payslip
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="payslips_data !== null"
                            v-bind:results="payslips_data"
                            v-on:get-page="fetchPayslips"
                        ></Pagination>
                        Items Count {{ payslips_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFileExcel
                                    }}</v-icon>
                                    Export CSV
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
export default {
    props: ["data"],
    data() {
        return {
            payslips_data: [],
            edit_data: null,
            pdf_data: [],
            date_payload: {},
            search: "",
            params: {},
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        VueHtml2pdf,
        Pagination
    },
    mounted() {
        this.fetchPayslips(1);
         this.date_payload = {
                from: this.data.payroll_from,
                to: this.data.payroll_to,
                id:this.data.employee_id,
                search: this.search
            };
    },

    watch: {
        params: {
            handler() {
                this.searchService();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchService();
            }
        }
    },
    methods: {
        viewPaySlips(data) {
            this.$router.push({
                name: "view_payslip",
                params: { payslip_data: data }
            });
        },
        async generateExcel() {
            const res = await this.fetchAll();
            var data_array = [];
            res.map(array_item => {
                data_array.push({
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,
                    payroll_from: array_item.payroll_from,
                    payroll_to: array_item.payroll_to,
                    basic_salary: array_item.basic_salary,
                    total_allowance: array_item.total_allowance,
                    total_rewards: array_item.total_rewards,
                    nssf: array_item.nssf,
                    nhif: array_item.nhif,
                housing_levy:array_item.housing_levy,
                    tax_relief: array_item.tax_relief,
                    net_paye: array_item.net_paye,
                    pay_after_tax: array_item.pay_after_tax,
                    total_advance: array_item.total_advance,
                    total_loans: array_item.total_loans,
                    total_deduction: array_item.total_deduction,
                    total_overtime: array_item.total_overtime,
                    net_salary: array_item.net_salary,
                    payment_type: array_item.payment_type
                });
            });

            return data_array;
        },
        generatePaySlips() {
            this.$router.push("generate_payroll");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.fetchAll();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async fetchAll() {
            const res = await this.callApi(
                "post",
                "data/payslips/fetchAllPayslipsByMonth",

                this.date_payload
            );
            return res.data.results;
        },

        async searchService() {
            let page = 1;

            const res = await this.callApi(
                "post",
                "data/payslips/getPayslipsByMonth",

                this.date_payload
            );
            if (res.status === 200) {
                this.payslips_data = res.data.results;
            }
        },

        async fetchPayslips() {
            this.date_payload = {
                from: this.data.payroll_from,
                to: this.data.payroll_to,
                id:this.data.employee_id,
                search: this.search
            };
        

            this.showLoader();

            const res = await this.callApi(
                "post",
                "data/payslips/getPayslipsByMonth",
                this.date_payload
            );
            this.hideLoader();

            if (res.status === 200) {
                this.payslips_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped>
.report-button-wrapper {
    display: flex;
    max-width: 50%;
    justify-content: space-between;
}
.report-button-wrapper button {
    height: 1.6rem !important;
    font-size: 0.8rem !important;
    align-items: center !important;
    padding-top: 0px;
    padding-bottom: 0px;
}
</style>
