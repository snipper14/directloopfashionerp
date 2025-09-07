<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><ledger-nav /></div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">VAT REPORT</div>

                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h5 class="text-uppercase font-weight-bold">
                                Net VAT Summary
                            </h5>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter From Date:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="from"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="datePicker" class="form-label"
                                    >Filter To Date:</label
                                >
                                <div class="input-group date" id="datepicker">
                                    <input
                                           type="date"
                                        class="form-control"
                                        id="datePicker"
                                        v-model="to"
                                        placeholder="Select a date"
                                    />
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                        <!-- Bootstrap Icon (optional) -->
                                    </span>
                                </div>
                                <v-btn
                                    x-small
                                    color="info"
                                    @click="filterDate()"
                                    >Filter Date</v-btn
                                >
                                <v-btn
                                    x-small
                                    color="secondary"
                                    @click="fetchAllData()"
                                    >Reset Date Filter</v-btn
                                >
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="">Branch Filter</label>
                                <treeselect
                                    :load-options="fetchBranch"
                                    :options="branch_select_data"
                                    :auto-load-root-options="false"
                                    v-model="search_params.branch_id"
                                    :multiple="false"
                                    :show-count="true"
                                    placeholder="Select   Branch "
                                />
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-4 mb-3">
                                <div
                                    class="card text-white bg-success shadow-sm"
                                >
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-uppercase">
                                            VAT Input
                                        </h6>
                                        <h4 class="card-text">
                                            Ksh
                                            {{
                                                formatCurrency(
                                                    vatData.vat_input
                                                )
                                            }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div
                                    class="card text-white bg-danger shadow-sm"
                                >
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-uppercase">
                                            VAT Output
                                        </h6>
                                        <h4 class="card-text">
                                            Ksh
                                            {{
                                                formatCurrency(
                                                    vatData.vat_output
                                                )
                                            }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div
                                    class="card text-white bg-primary shadow-sm"
                                >
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-uppercase">
                                            Net VAT(Output-Input)
                                        </h6>
                                        <h4 class="card-text">
                                            Ksh {{ formatCurrency(netVat) }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LedgerNav from "../LedgerNav.vue";

export default {
    components: { LedgerNav },
    data() {
        return {
            is_loading: true,
            vatData: {
                vat_input: 0,
                vat_output: 0,
            },
            from: null,
            to: null,
            search_params:{
                branch_id:null
            }
        };
    },
    computed: {
        netVat() {
            return this.vatData.vat_output - this.vatData.vat_input;
        },
    },
    mounted() {
        this.fetchVatReport();
    },
    watch:{
        search_params:{
            deep:true,
            handler:_.debounce(function(val,old){
                this.fetchVatReport();
            })
        }
    },
    methods: {
          async filterDate() {
            this.is_loading = true;
            this.fetchVatReport();
        },
        async fetchAllData() {
            this.is_loading = true;
            this.from = null;
            this.to = null;
         
            this.fetchVatReport();
        },
        async fetchVatReport() {
            this.is_loading ? this.showLoader() : "";
            const res = await this.getApi(
                "data/general_ledger/fetchVatReport",
                {
                    params: {
                        from:this.from,
                        to:this.to,
                        ...this.search_params
                    },
                }
            );
            this.is_loading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.vatData = res.data;
            }
        },
        formatCurrency(value) {
            return value.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },
    },
};
</script>
