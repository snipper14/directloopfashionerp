<template>
    <v-app>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-8"><center><h2><b>RECONCILIATION STATEMENT FOR {{details_data.account.account}}</b></h2></center></div>
                <div class="col-md-3">
                    <label for="datePicker" class="form-label"
                        >Filter From Date:</label
                    >
                    <div class="input-group date" id="datepicker">
                        <input
                            type="date"
                            class="form-control"
                            id="datePicker"
                            v-model="search_params.from"
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
                            v-model="search_params.to"
                            placeholder="Select a date"
                        />
                        <span class="input-group-text">
                            <i class="bi bi-calendar"></i>
                            <!-- Bootstrap Icon (optional) -->
                        </span>
                    </div>
                </div>
                <div class="col-md-3 form-group">
                    <label for="">Search</label>
                    <input
                        type="text"
                        placeholder="Search"
                        autocomplete="off"
                        v-model="search_params.search"
                        class="form-control"
                    />
                </div>
            </div>
            <div class="row">
                <v-simple-table
                    style="width: 100%"
                    dense
                    class="elevation-2 rounded-table"
                >
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Ledger Dr Amount</th>
                                <th>Ledger Cr Amount</th>
                                <th>Date</th>
                                <th>Ref</th>
                                <th>Dr Amount</th>
                                <th>Cr Amount</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(value, i) in detailed_report.data"
                                :key="i"
                            >
                                <td>
                                    {{
                                        value.general_ledger.other_account_name
                                    }}
                                </td>
                                <td>
                                    {{
                                        cashFormatter(
                                            value.general_ledger.debit_amount
                                        )
                                    }}
                                </td>
                                <td>
                                    {{
                                        cashFormatter(
                                            value.general_ledger.credit_amount
                                        )
                                    }}
                                </td>
                                <td>
                                    {{ value.transaction_date }}
                                </td>
                                <td>{{ value.ref_no }}</td>
                                <td>
                                    {{ cashFormatter(value.debit_amount) }}
                                </td>
                                <td>
                                    {{ cashFormatter(value.credit_amount) }}
                                </td>

                                <td>{{ value.user.name }}</td>
                            </tr>
                        </tbody>
                    </template>
                </v-simple-table>
                <pagination
                    v-if="detailed_report !== null"
                    v-bind:results="detailed_report"
                    v-on:get-page="fetchReconsDetailedReport"
                ></pagination>
                Items Count {{ detailed_report.total }}
                <br />

                <div class="center_button d-flex flex-row">
                    <export-excel
                        class="btn btn-default btn-export ml-2"
                        :fetch="exportExcel"
                        worksheet="My Worksheet"
                        name="filename.xls"
                    >
                        <Button type="primary"
                            >Export Excel
                        </Button></export-excel
                    >
                    <Button
                       type="success"
                        @click="printStatement"
                    >
                        Print
                    </Button>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
export default {
    components: { Pagination },
    props: ["details_data"],
    data() {
        return {
            isLoading: true,
            detailed_report: [],

            currentPage: 1, // Current page for pagination
            perPage: 10,
            params: {
                ref: this.details_data.ref,
                account_id: this.details_data.account_id,
            },
            search_params: {
                from: "",
                to: "",
                search: "",
            },
        };
    },
    watch: {
        search_params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    mounted() {
        console.log(JSON.stringify(this.details_data));
        
        this.concurrentApiReq();
    },
    methods: {
        async printStatement() {
            try {
                this.showLoader();

                // Reuse EXACT same dataset as Excel
                const exportData = await this.exportExcel();

                this.hideLoader();

                // Columns mapping (same order/names as exportExcel)
                const columns = [
                    { field: "Account", displayName: "Account" },
                    {
                        field: "Ledger Dr Amount",
                        displayName: "Ledger Dr Amount",
                    },
                    {
                        field: "Ledger Cr Amount",
                        displayName: "Ledger Cr Amount",
                    },
                    { field: "Date", displayName: "Date" },
                    { field: "Ref", displayName: "Ref" },
                    { field: "Dr Amount", displayName: "Dr Amount" },
                    { field: "Cr Amount", displayName: "Cr Amount" },
                    { field: "User", displayName: "User" },
                ];

                // Optional: header with current filters
                const hdrFrom = this.search_params?.from || "—";
                const hdrTo = this.search_params?.to || "—";
                const hdrSearch = this.search_params?.search
                    ? ` | Search: ${this.search_params.search}`
                    : "";

                printJS({
                    printable: exportData,
                    type: "json",
                    properties: columns,
                    header: `
        <div style="text-align:center; padding:8px 0;">
          <h3 style="margin:0;"><center><h2><b>RECONCILIATION STATEMENT FOR ${this.details_data.account.account}</b></h2></center></h3>
          <div style="margin-top:4px; font-size:12px;">
            Range: ${hdrFrom} → ${hdrTo}${hdrSearch}
          </div>
        </div>
      `,
                    style: `
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        @page { margin: 12mm; }
      `,
                    gridStyle: "border: 1px solid #ccc; padding: 6px;",
                    gridHeaderStyle:
                        "border: 1px solid #ccc; padding: 6px; font-weight: 600; background: #f5f5f5;",
                    documentTitle: "Bank Reconciliation Statement",
                });
            } catch (e) {
                this.hideLoader && this.hideLoader();
                this.swr
                    ? this.swr("Could not generate print preview")
                    : console.error(e);
            }
        },

        async exportExcel() {
            this.showLoader();

            // Fetch all data without pagination
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchByAccountID",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader();

            //if (res.status === 200) {
            let allData = res.data; // Handle if API returns paginated data inside `.data`

            // Format data for Excel export
            let exportData = allData.map((value) => ({
                Account: value.general_ledger.other_account_name,
                "Ledger Dr Amount": this.cashFormatter(
                    value.general_ledger.debit_amount
                ),
                "Ledger Cr Amount": this.cashFormatter(
                    value.general_ledger.credit_amount
                ),
                Date: value.transaction_date,
                Ref: value.ref_no,
                "Dr Amount": this.cashFormatter(value.debit_amount),
                "Cr Amount": this.cashFormatter(value.credit_amount),
                User: value.user.name,
            }));
            return exportData;
            // Trigger Excel download
            // this.$refs.excelExport.generateExcel(exportData);
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([this.fetchReconsDetailedReport(1)]);

            this.isLoading ? this.hideLoader() : "";
        },

        async fetchReconsDetailedReport(page) {
            const res = await this.getApi(
                "data/qb_bank_reconciliation/fetchByAccountID",
                {
                    params: {
                        ...this.params,
                        ...this.search_params,
                        page,
                    },
                }
            );

            if (res.status === 200) {
                this.detailed_report = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.table {
    width: 100%;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

.table tbody tr {
    border-bottom: 1px solid #ddd;
}

.table td {
    padding: 6px;
    text-align: left;
    border: 1px solid #ccc;
}

.table td:first-child {
    font-weight: bold;
    background-color: #f9f9f9;
}

.table td:last-child {
    text-align: right;
    background-color: #f1f1f1;
}

.table tbody tr:hover {
    background-color: #f0f0f0;
}

.table tr:last-child {
    border-bottom: 2px solid #333;
}

.table tr:first-child td {
    border-top: 2px solid #333;
}
</style>
