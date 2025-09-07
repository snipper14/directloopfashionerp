<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b> Generate P9</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label
                                        ><b>SELECT TAX YEAR *</b>:
                                        <h3>
                                            <b>{{ params.tax_year }}</b>
                                        </h3></label
                                    >
                                    <date-picker
                                        v-model="params.tax_year"
                                        type="year"
                                    ></date-picker>
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search Employee</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Employee</th>
                                        <th>Month</th>
                                        <th scope="col">Basic Salary</th>
                                        <th scope="col">Total Allowance</th>
                                        <th scope="col">
                                            Commissions /Bonuses
                                        </th>
                                        <th scope="col">NSSF</th>
                                        <th scope="col">NHIF</th>
                                        <th scope="col">Income Tax</th>

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
                                        v-for="(data, i) in payslips_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                data.first_name +
                                                    " " +
                                                    data.last_name
                                            }}
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
                                            {{ cashFormatter(data.nssf) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.nhif) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.income_tax) }}
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
                                        <td>
                                            {{ cashFormatter(data.net_salary) }}
                                        </td>
                                        <td>
                                            <button
                                                class="btn btn-secondary btn-small"
                                                @click="PrintP9Form(data)"
                                            >
                                                Print Form
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
export default {
    components: { Pagination },
    data() {
        return {
            params: {
                from: null,
                to: null,
                tax_year: null
            },
            search: "",
            payslips_data: []
        };
    },
    mounted() {
        console.log("Component mounted.");
    },
    watch: {
        params: {
            handler() {
                this.params.tax_year = this.formatYear(this.params.tax_year);
                this.searchService(1);
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
        async PrintP9Form(data) {
            this.showLoader();
            axios({
                url: "data/payslips/downloadP9",
                params: {
                    employee_id: data.employee_id,
                    ...this.params
                },
                method: "GET",
                responseType: "blob" // important
            }).then(response => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    data.first_name + data.last_name + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
            //  this.$refs.html2Pdf.generatePdf();
        },
        async searchService() {
            let page = 1;

            if (this.params.tax_year == null) {
                this.errorNotif("Select year");
                return;
            }

            const res = await this.getApi("data/payslips/fetchGrouped", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.payslips_data = res.data.results;
            }
        },

        async fetchPayslips(page) {
            console.log(this.params.tax_year);
            if (this.params.tax_year == null) {
                this.errorNotif("Select year");
                return;
            }

            this.showLoader();
            const res = await this.getApi("data/payslips/fetchGrouped", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params
                }
            });
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
