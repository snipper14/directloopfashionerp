<template>
    <div class="container">
        <div class="report-button-wrapper">
            <button
                @click="$router.push('generic_payslips')"
                class="btn btn-secondary"
                color="primary"
                dark
            >
                Full Report
            </button>
            <button
                @click="$router.push('grouped_payslips')"
                class="btn btn-secondary"
                color="primary"
                dark
            >
                Grouped Report
            </button>
            <button
                @click="$router.push('summarised_payslips')"
                class="btn btn-secondary"
                color="primary"
                dark
            >
                Summarised Report
            </button>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                class="ma-2 v-btn-primary"
                                @click="generatePaySlips()"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Generate Payslips
                            </v-btn>
                        </div>
                        <div class="col-md-3">
                            <v-btn
                                class="ma-2 v-btn-primary"
                                @click="generateP9Form()"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Generate P9
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color: red">Full Report</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Employee</th>
                                        <th scope="col">Pay Type</th>
                                        <th scope="col">From</th>
                                        <th scope="col">To</th>
                                        <th scope="col">Basic Sal.</th>
                                        <th scope="col">Allowance</th>
                                        <th scope="col">Comm. /Bonuses</th>
                                        <th>Hse Levy</th>
                                        <th>Hse Levy Relief</th>
                                        <th scope="col">NSSF</th>
                                        <th scope="col">SHIF</th>

                                        <th scope="col">Tax Relief</th>
                                        <th scope="col">Insurance Relief</th>
                                        <th scope="col">Net Paye</th>
                                        <th scope="col">Pay After Tax</th>
                                        <th scope="col">T. Advance</th>
                                        <th scope="col">T. Loans</th>
                                        <th scope="col">T. Deduction</th>
                                        <th scope="col">T. Overtime</th>
                                        <th scope="col">Hrs</th>
                                        <th scope="col">Net Salary</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in payslips_data.data"
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
                                            {{ data.employee.payment_type }}
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
                                                    cashFormatter(
                                                        data.basic_salary
                                                    )
                                                }}
                                            </strong>
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_allowance
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_rewards
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.housing_levy)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.housing_levy_relief
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.nssf) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.nhif) }}
                                        </td>

                                        <td>
                                            {{ cashFormatter(data.tax_relief) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.insurance_relief
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.net_paye) }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.pay_after_tax
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_advance
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.total_loans)
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_deduction
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.total_overtime
                                                )
                                            }}
                                        </td>

                                        <td>{{ data.no_hrs }}</td>
                                        <td>
                                            {{ cashFormatter(data.net_salary) }}
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewPaySlips(data)
                                                "
                                                >View/Print</router-link
                                            >
                                        </td>
                                        <td>
                                            <button
                                                v-if="isDeletePermitted"
                                                class="btn btn-danger btn-sm"
                                                @click="
                                                    deletePayslip(data.id, i)
                                                "
                                            >
                                                Del
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="payslips_data !== null"
                            v-bind:results="payslips_data"
                            v-on:get-page="fetchPayslips"
                        ></Pagination>
                        Items Count {{ payslips_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
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
                                    class="btn btn-default btn-export ml-2"
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
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
export default {
    data() {
        return {
            payslips_data: [],
            edit_data: null,
            pdf_data: [],

            search: "",
            params: {},
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        VueHtml2pdf,
        Pagination,
    },
    mounted() {
        this.fetchPayslips(1);
    },

    watch: {
        params: {
            handler() {
                this.searchService();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchService();
            }
        },
    },
    methods: {
        async deletePayslip(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/payroll/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.payslips_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        viewPaySlips(data) {
            this.$router.push({
                name: "view_payslip",
                params: { payslip_data: data },
            });
        },
        async generateExcel() {
            const res = await this.fetchAll();
            var data_array = [];
            res.map((array_item) => {
                data_array.push({
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,
                    payroll_from: array_item.payroll_from,
                    payroll_to: array_item.payroll_to,
                    basic_salary: array_item.basic_salary,
                    total_allowance: array_item.total_allowance,
                    total_rewards: array_item.total_rewards,
                    nssf: array_item.nssf,
                    shif: array_item.nhif,
                    housing_levy: array_item.housing_levy,
                    housing_levy_relief: array_item.housing_levy_relief,
                    tax_relief: array_item.tax_relief,
                    net_paye: array_item.net_paye,
                    pay_after_tax: array_item.pay_after_tax,
                    total_advance: array_item.total_advance,
                    total_loans: array_item.total_loans,
                    total_deduction: array_item.total_deduction,
                    total_overtime: array_item.total_overtime,
                    no_hrs: array_item.no_hrs,
                    net_salary: array_item.net_salary,
                    payment_type: array_item.payment_type,
                });
            });

            return data_array;
        },
        generatePaySlips() {
            this.$router.push("generate_payroll");
        },
        generateP9Form() {
            this.$router.push("generate_p9");
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
            const res = await this.getApi("data/payroll/fetchAll", "");

            return res.data.results;
        },

        async searchService() {
            let page = 1;
            const res = await this.getApi("data/payroll/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.payslips_data = res.data.results;
            }
        },

        async fetchPayslips(page) {
            this.showLoader();
            const res = await this.getApi("data/payroll/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.payslips_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
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
td {
    font-size: 0.8rem !important;
}
</style>
