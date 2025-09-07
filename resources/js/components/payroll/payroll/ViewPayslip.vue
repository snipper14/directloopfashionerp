<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <v-btn
                            class="ma-2 v-btn-primary"
                            @click="redirectToPayslip()"
                            color="primary"
                            dark
                        >
                            <v-icon dark left> mdi-arrow-left </v-icon>
                            Back
                        </v-btn>
                        <h4>
                            {{
                                payroll_data.employee.first_name +
                                " " +
                                payroll_data.employee.last_name
                            }}
                        </h4>
                        <button
                            @click="downLoadPayslips()"
                            class="btn btn-secondary btn-small"
                        >
                            Print Payslip
                        </button>
                    </div>

                    <div
                        class="card-body"
                        style="background-color: #7f1416; color: #fff"
                    >
                        <div>
                            <center>
                                <h6>{{ company_name }}</h6>
                                <p>
                                    Payslip for the month of {{ payslip_month }}
                                </p>
                            </center>
                            <hr />
                            <div class="payslip-wrapper">
                                <div class="line-wrapper">
                                    <div>Name</div>
                                    <div>
                                        {{
                                            payroll_data.employee.first_name +
                                            " " +
                                            payroll_data.employee.last_name
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper">
                                    <div>Job Number</div>
                                    <div>{{ payroll_data.job_no }}</div>
                                </div>

                                <div class="line-wrapper">
                                    <div>Job title</div>
                                    <div>
                                        {{
                                            payroll_data.employee.employee_type
                                                .name
                                        }}
                                    </div>
                                </div>

                                <div class="line-wrapper">
                                    <div>Dept./Region:</div>
                                    <div>
                                        {{
                                            payroll_data.employee.department
                                                .department
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper">
                                    <div>Pay Type:</div>
                                    <div>
                                        {{ paytype(payroll_data.payment_type) }}
                                    </div>
                                </div>
                                <hr />
                                <div class="line-wrapper mt-3">
                                    <div>Basic Pay:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.employee.salary
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="line-wrapper">
                                    <div>Gross Pay:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.basic_salary
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="line-wrapper mt-2">
                                    <div>
                                        <strong>Deductions Before Tax:</strong>
                                    </div>
                                    <div></div>
                                </div>
                                <div class="line-wrapper">
                                    <div>Tier I Employee NSSF</div>
                                    <div>
                                        -{{
                                            cashFormatter(
                                                payroll_data.nssf_tier1
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper">
                                    <div>Tier II Employee NSSF</div>
                                    <div>
                                        -{{
                                            cashFormatter(
                                                payroll_data.nssf_tier2
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper">
                                    <div>N.S.S.F:</div>
                                    <div>
                                        -{{ cashFormatter(payroll_data.nssf) }}
                                    </div>
                                </div>
  <div class="line-wrapper">
                                    <div>S.H.I.F.:</div>
                                    <div>
                                        -{{ cashFormatter(payroll_data.nhif) }}
                                    </div>
                                </div>
                                <div class="line-wrapper">
                                    <div>Housing Levy</div>

                                    <div class="attribute">
                                        -{{
                                            cashFormatter(
                                                payroll_data.housing_levy
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper mt-2">
                                    <div><strong>Taxable Pay:</strong></div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.basic_salary -
                                                    payroll_data.nssf-payroll_data.housing_levy-payroll_data.nhif
                                            )
                                        }}
                                    </div>
                                </div>

                              
                                <div class="line-wrapper">
                                    <div>Tax Relief:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.tax_relief
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper mt-2">
                                    <div><strong>P.A.Y.E:</strong></div>
                                    <div>
                                        -{{
                                            cashFormatter(payroll_data.net_paye)
                                        }}
                                    </div>
                                </div>
                                <hr />
                                <div class="line-wrapper">
                                    <div>Gross Pay after Tax:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.pay_after_tax
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="line-wrapper">
                                    <div>
                                        <strong>Deductions After Tax:</strong>
                                    </div>
                                    <div></div>
                                </div>
                              
                                <div
                                    v-if="payroll_data.total_loans"
                                    class="line-wrapper"
                                >
                                    <div>Loans:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.total_loans
                                            )
                                        }}
                                    </div>
                                </div>
                                <div
                                    v-if="payroll_data.total_advance"
                                    class="line-wrapper"
                                >
                                    <div>Advance:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.total_advance
                                            )
                                        }}
                                    </div>
                                </div>
                                <div
                                    v-if="payroll_data.total_deduction"
                                    class="line-wrapper"
                                >
                                    <div>Other Deductions:</div>
                                    <div>
                                        {{
                                            cashFormatter(
                                                payroll_data.total_deduction
                                            )
                                        }}
                                    </div>
                                </div>

                                <hr />
                                <div class="line-wrapper">
                                    <div><strong>Net Pay:</strong></div>
                                    <div>
                                        <strong
                                            >{{
                                                cashFormatter(
                                                    payroll_data.net_salary
                                                )
                                            }}
                                        </strong>
                                    </div>
                                </div>
                                <hr />
                                <div
                                    v-if="
                                        payroll_data.employee.pay_method ==
                                        'bank_transfer'
                                    "
                                >
                                    <div class="line-wrapper mt-2">
                                        <div>
                                            <strong>Personal Info:</strong>
                                        </div>
                                        <div></div>
                                    </div>
                                    <div class="line-wrapper mt-2">
                                        <div>Payment Mode::</div>
                                        <div>Bank Transfer</div>
                                    </div>
                                    <div class="line-wrapper">
                                        <div>Bank And Branch::</div>
                                        <div>
                                            {{
                                                payroll_data.employee.bank_name
                                            }}
                                            /
                                            {{
                                                payroll_data.employee
                                                    .bank_branch
                                            }}
                                        </div>
                                    </div>
                                    <div class="line-wrapper">
                                        <div>Acc Name:: Account No</div>
                                        <div>
                                            {{
                                                payroll_data.employee
                                                    .account_name
                                            }}
                                            /
                                            {{
                                                payroll_data.employee.account_no
                                            }}
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-if="
                                        payroll_data.employee.pay_method ==
                                        'mobile_money'
                                    "
                                >
                                    <div class="line-wrapper mt-2">
                                        <div>
                                            <strong>Personal Info:</strong>
                                        </div>
                                        <div></div>
                                    </div>
                                    <div class="line-wrapper">
                                        <div>Payment Mode::</div>
                                        <div>Mobile Money</div>
                                    </div>
                                    <div class="line-wrapper">
                                        <div>Phone Number::</div>
                                        <div>
                                            {{
                                                payroll_data.employee
                                                    .payment_phone
                                            }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- PDF PRINTING -->
                        <div>
                            <vue-html2pdf
                                :show-layout="false"
                                :float-layout="true"
                                :enable-download="true"
                                :preview-modal="true"
                                :paginate-elements-by-height="1800"
                                filename="Payslip"
                                :pdf-quality="2"
                                :manual-pagination="false"
                                pdf-format="a4"
                                pdf-orientation="landscape"
                                pdf-content-width="100%"
                                ref="html2Pdf"
                            >
                                <section slot="pdf-content" class="pdf-wrapper">
                                    <center>
                                        <h6>Weaver Payroll System</h6>
                                        <p>
                                            Payslip for the month of
                                            {{ payslip_month }}
                                        </p>
                                    </center>
                                    <hr />
                                    <div class="payslip-wrapper">
                                        <div class="line-wrapper">
                                            <div>Name</div>
                                            <div>
                                                {{
                                                    payroll_data.employee
                                                        .first_name +
                                                    " " +
                                                    payroll_data.employee
                                                        .last_name
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>Job Number</div>
                                            <div>{{ payroll_data.job_no }}</div>
                                        </div>

                                        <div class="line-wrapper">
                                            <div>Job title</div>
                                            <div>
                                                {{
                                                    payroll_data.employee
                                                        .employee_type.name
                                                }}
                                            </div>
                                        </div>

                                        <div class="line-wrapper">
                                            <div>Dept./Region:</div>
                                            <div>
                                                {{
                                                    payroll_data.employee
                                                        .department.department
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>Pay Type:</div>
                                            <div>
                                                {{
                                                    paytype(
                                                        payroll_data.payment_type
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="line-wrapper mt-3">
                                            <div>Basic Pay:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.employee
                                                            .salary
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>Gross Pay:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.basic_salary
                                                    )
                                                }}
                                            </div>
                                        </div>

                                        <div class="line-wrapper mt-2">
                                            <div>
                                                <strong
                                                    >Deductions Before
                                                    Tax:</strong
                                                >
                                            </div>
                                            <div></div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>N.S.S.F:</div>
                                            <div>
                                                -{{
                                                    cashFormatter(
                                                        payroll_data.nssf
                                                    )
                                                }}
                                            </div>
                                        </div>

                                        <div class="line-wrapper mt-2">
                                            <div>
                                                <strong>Taxable Pay:</strong>
                                            </div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.basic_salary -
                                                            payroll_data.nssf
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>Income Tax:</div>
                                            <div>
                                                -{{
                                                    cashFormatter(
                                                        payroll_data.income_tax
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>Tax Relief:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.tax_relief
                                                    )
                                                }}
                                            </div>
                                        </div>

                                        <div class="line-wrapper mt-2">
                                            <div><strong>P.A.Y.E:</strong></div>
                                            <div>
                                                -{{
                                                    cashFormatter(
                                                        payroll_data.net_paye
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="line-wrapper">
                                            <div>Gross Pay after Tax:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.pay_after_tax
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>
                                                <strong
                                                    >Deductions After
                                                    Tax:</strong
                                                >
                                            </div>
                                            <div></div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>N.H.I.F.:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.nhif
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="payroll_data.total_loans"
                                            class="line-wrapper"
                                        >
                                            <div>Loans:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.total_loans
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="payroll_data.total_advance"
                                            class="line-wrapper"
                                        >
                                            <div>Advance:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.total_advance
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="payroll_data.total_deduction"
                                            class="line-wrapper"
                                        >
                                            <div>Other Deductions:</div>
                                            <div>
                                                {{
                                                    cashFormatter(
                                                        payroll_data.total_deduction
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="line-wrapper">
                                            <div>
                                                <strong
                                                    >Total Deductions After
                                                    Tax:</strong
                                                >
                                            </div>
                                            <div>
                                                -{{
                                                    cashFormatter(
                                                        payroll_data.nhif +
                                                            payroll_data.total_loans +
                                                            payroll_data.total_advance +
                                                            payroll_data.total_deduction
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="line-wrapper">
                                            <div><strong>Net Pay:</strong></div>
                                            <div>
                                                <strong>
                                                    {{
                                                        cashFormatter(
                                                            payroll_data.net_salary
                                                        )
                                                    }}
                                                </strong>
                                            </div>
                                        </div>
                                        <hr />
                                        <div
                                            v-if="
                                                payroll_data.employee
                                                    .pay_method ==
                                                'bank_transfer'
                                            "
                                        >
                                            <div class="line-wrapper mt-2">
                                                <div>
                                                    <strong
                                                        >Personal Info:</strong
                                                    >
                                                </div>
                                                <div></div>
                                            </div>
                                            <div class="line-wrapper mt-2">
                                                <div>Payment Mode::</div>
                                                <div>Bank Transfer</div>
                                            </div>
                                            <div class="line-wrapper">
                                                <div>Bank And Branch::</div>
                                                <div>
                                                    {{
                                                        payroll_data.employee
                                                            .bank_name
                                                    }}
                                                    /
                                                    {{
                                                        payroll_data.employee
                                                            .bank_branch
                                                    }}
                                                </div>
                                            </div>
                                            <div class="line-wrapper">
                                                <div>Acc Name:: Account No</div>
                                                <div>
                                                    {{
                                                        payroll_data.employee
                                                            .account_name
                                                    }}
                                                    /
                                                    {{
                                                        payroll_data.employee
                                                            .account_no
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            v-if="
                                                payroll_data.employee
                                                    .pay_method ==
                                                'mobile_money'
                                            "
                                        >
                                            <div class="line-wrapper mt-2">
                                                <div>
                                                    <strong
                                                        >Personal Info:</strong
                                                    >
                                                </div>
                                                <div></div>
                                            </div>
                                            <div class="line-wrapper">
                                                <div>Payment Mode::</div>
                                                <div>Mobile Money</div>
                                            </div>
                                            <div class="line-wrapper">
                                                <div>Phone Number::</div>
                                                <div>
                                                    {{
                                                        payroll_data.employee
                                                            .payment_phone
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </vue-html2pdf>
                        </div>
                        <!-- END PDF PRINTING -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VueHtml2pdf from "vue-html2pdf";
import print from "print-js";
import moment from "moment";
export default {
    props: ["payslip_data"],
    data() {
        return {
            payroll_data: null,
            payslip_month: "",
        };
    },
    components: {
        VueHtml2pdf,
    },
    created() {
        if (this.payslip_data) {
            localStorage.setItem(
                "payslip_pref",
                JSON.stringify(this.payslip_data)
            );
        }
        this.payroll_data = JSON.parse(localStorage.getItem("payslip_pref"));
        this.payslip_month = moment(this.payroll_data.payroll_to).format(
            " MMMM Do YYYY"
        );

        console.log(this.payroll_data);
    },
    methods: {
        async downLoadPayslips() {
            this.showLoader();
            axios({
                url: "data/payroll/downLoadPayslip",
                method: "POST",
                data: { payroll_data: this.payroll_data },
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.payroll_data.employee.first_name +
                        this.payroll_data.employee.last_name +
                        ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },
        paytype(type) {
            return type == "piece_work" ? "Piece Work" : "Monthly";
        },
        async printPayslip() {
            // printJS("print_section", "html");
            // this.downLoadPayslips()
            // this.$refs.html2Pdf.generatePdf();
            this.showLoader();
            var conxt = this;
            await setTimeout(function () {
                conxt.$refs.html2Pdf.generatePdf();
                conxt.hideLoader();
            }, 2000);
        },
        redirectToPayslip() {
            this.$router.push("generic_payslips");
        },
    },
    computed: {},
};
</script>
<style scoped>
.line-wrapper {
    display: flex;
    width: 100%;
    justify-content: space-between;
    font-size: 0.8rem !important;
}
.card-header {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between;
}
.pdf-wrapper {
    text-align: center !important;
    padding-left: 80px;
    padding-right: 80px;
}
.pdf-wrapper .line-wrapper {
    display: flex;
    width: 100%;
    justify-content: space-between;
    font-size: 1rem !important;
}
.top_row {
    display: table;
    width: 100%;
}
.top_row > div {
    display: table-cell;

    border-bottom: 1px solid #eee;
}
.top_row > .item-val {
    text-align: right !important;
}
hr {
    background-color: #fff;
}
</style>
