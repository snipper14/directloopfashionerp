<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">DASHBOARD</div>

                    <div class="card-body">
                        <!-- /.card.mb-4-->
                        <div class="row" v-if="isDisplayPermitted">
                            <div
                                class="col-sm-6 col-lg-4"
                                v-for="(data, i) in trial_balance_arr"
                                :key="i"
                                v-if="data.cr_amount != 0"
                            >
                                <div
                                    class="card mb-4"
                                    style="background-color: #3f51b5"
                                >
                                    <div
                                        class="card-header position-relative d-flex justify-content-center align-items-center"
                                    >
                                        <svg
                                            class="icon icon-3xl text-white my-4"
                                        >
                                            <use
                                                xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-facebook-f"
                                            ></use>
                                        </svg>
                                        <div
                                            class="chart-wrapper position-absolute top-0 start-0 w-100 h-100"
                                        >
                                            <canvas
                                                id="social-box-chart-1"
                                                height="90"
                                            ></canvas>
                                        </div>
                                    </div>
                                    <div class="card-body row text-center">
                                        <div class="col">
                                            <div
                                                class="fs-5 fw-semibold"
                                                style="color: #fff"
                                            >
                                                {{ data.account }}
                                            </div>
                                            <div
                                                class="text-uppercase text-medium-emphasis small"
                                                style="color: #fff"
                                            >
                                                KES.
                                                {{
                                                    cashFormatter(
                                                        data.cr_amount
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="vr"></div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-sm-6 col-lg-4"
                                v-for="(data, i) in trial_balance_arr"
                                :key="i"
                                v-if="data.dr_amount != 0"
                            >
                                <div
                                    class="card mb-4"
                                    style="background-color: #bf360c"
                                >
                                    <div
                                        class="card-header position-relative d-flex justify-content-center align-items-center"
                                    >
                                        <svg
                                            class="icon icon-3xl text-white my-4"
                                        >
                                            <use
                                                xlink:href="vendors/@coreui/icons/svg/brand.svg#cib-facebook-f"
                                            ></use>
                                        </svg>
                                        <div
                                            class="chart-wrapper position-absolute top-0 start-0 w-100 h-100"
                                        >
                                            <canvas
                                                id="social-box-chart-1"
                                                height="90"
                                            ></canvas>
                                        </div>
                                    </div>
                                    <div class="card-body row text-center">
                                        <div class="col">
                                            <div
                                                class="fs-5 fw-semibold"
                                                style="color: #fff"
                                            >
                                                {{ data.account }}
                                            </div>
                                            <div
                                                class="text-uppercase text-medium-emphasis small"
                                                style="color: #fff"
                                            >
                                                KES.
                                                {{
                                                    cashFormatter(
                                                        data.dr_amount
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="vr"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
    mdiCash,
} from "@mdi/js";
export default {
    data() {
        return {
            select_debit_account_modal: false,
            select_credit_account_modal: false,
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                new_invoice: false,
                view_invoice: false,
            },
            total_accounts_payable: 0,
            isLoading: true,
            ledger_total: {},
            total_debit_amount: 0,
            total_credit_amount: 0,
            invoice_details: null,
            trial_balance_arr: [],
            edit_data: null,
            pdf_data: [],
            invoice_data: [],
            search: "",
            code: null,
            params: {
                search: "",
                from: null,
                to: null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
                mdiCash,
            },
            form_data: {
                credit_account_type: "",
                debit_account_type: "",
                debit_account_id: null,
                credit_account_id: null,

                credit_account_name: "",
                debit_account_name: "",
            },
        };
    },
    mounted() {
        //  this.$router.push('retail_pos')
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetch(1)]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        async fetch(page) {
            const res = await this.getApi("data/dashboard/fetch", {
                params: {
                    page,

                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.trial_balance_arr = res.data;
                //  this.total_accounts_payable=this.trial_balance_arr[0].
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.menu-icon {
    height: 70px;
    width: 70px;
    border-radius: 50%;
}
.first-elem {
    background-color: #cfd8dc !important;
    text-align: center;
}
.module-text {
    font-weight: 700;
    font-size: 1rem;
}
.second-elem {
    background-color: #d7ccc8 !important;
    text-align: center;
}
.third-elem {
    background-color: #b2dfdb !important;
    text-align: center;
}
.fourth-elem {
    background-color: #b2dfdb !important;
    text-align: center;
}
.fifth-elem {
    background-color: #80cbc4 !important;
    text-align: center;
}
.sixth-elem {
    background-color: #cfd8dc !important;
    text-align: center;
}
</style>
