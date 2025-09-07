<template>
    <div class="row">
        <div class="col-12">
            <div class="container" v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <!-- <ImportExcel/> -->
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="col-md-3">
                                    <button
                                        class="btn btn-primary btn-sm"
                                        @click="
                                            $router.push(
                                                'internal_issue_report'
                                            )
                                        "
                                    >
                                        View Grouped Report
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <h3>
                                        <b>Internal Detailed Issue Report</b>
                                    </h3>
                                </div>
                                <div class="col-md-4 float-md-right">
                                    <input
                                        type="text"
                                        placeholder="Search.."
                                        class="form-control small-input"
                                        v-model="params.search"
                                    />
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Report Type *</label>
                                            <select
                                                v-model="params.report_type"
                                                class="form-control"
                                            >
                                                <option
                                                    v-bind:value="data.value"
                                                    v-for="(
                                                        data, i
                                                    ) of type_select"
                                                    :key="i"
                                                >
                                                    {{ data.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div
                                        class="col-md-12 d-flex flex-row justify-content-around"
                                    >
                                        <div class="form-group">
                                            <label> From *</label
                                            ><date-picker
                                                v-model="params.from"
                                                valueType="format"
                                            ></date-picker>
                                        </div>
                                        <div class="form-group">
                                            <label> To *</label
                                            ><date-picker
                                                v-model="params.to"
                                                valueType="format"
                                            ></date-picker>
                                        </div>
                                        <div class="form-group">
                                            <button
                                                class="btn btn-secondary btn-sm"
                                                @click="concurrentApiReq()"
                                            >
                                                Filter Date
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered mt-3"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        Issue No
                                                    </th>
                                                    <th>Date</th>

                                                    <th>Code</th>
                                                    <th>ItemName</th>

                                                    <th>Qty Item Issued</th>
                                                    <th>
                                                        Final Qty Item Issued
                                                    </th>

                                                    <th>Item c.P</th>

                                                    <th>User</th>
                                                    <th>Issed to</th>
                                                    <th>Origin</th>
                                                    <th>Dispatch To</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    class="small-tr"
                                                    v-for="(
                                                        data, i
                                                    ) in issue_data.data"
                                                    :key="i"
                                                >
                                                    <td>
                                                        {{ data.issue_no }}
                                                    </td>
                                                    <td>
                                                        {{ data.date_issued }}
                                                    </td>

                                                    <td>
                                                        {{ data.stock.code }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.stock
                                                                .product_name
                                                        }}
                                                    </td>

                                                    <td>
                                                        <span
                                                            v-if="
                                                                params.report_type ==
                                                                'group'
                                                            "
                                                            >{{
                                                               cashFormatter( data.sum_qty_issued)
                                                            }}</span
                                                        ><span v-else>{{
                                                            data.qty_issued
                                                        }}</span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            v-if="
                                                                params.report_type ==
                                                                'group'
                                                            "
                                                            >{{
                                                               cashFormatter( data.sum_final_qty)
                                                            }}</span
                                                        ><span v-else>
                                                            {{
                                                                cashFormatter(data.final_qty)
                                                            }}</span
                                                        >
                                                    </td>

                                                    <td>
                                                        {{
                                                            data.purchase_price
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{ data.user.name }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.issued_staff
                                                                .name
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data
                                                                .source_department
                                                                .department
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.department
                                                                .department
                                                        }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <pagination
                                        v-if="issue_data !== null"
                                        v-bind:results="issue_data"
                                        v-on:get-page="fetchIssueData"
                                    ></pagination>
                                    Items Count {{ issue_data.total }}

                                    <div class="col-8 d-flex">
                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchExcel"
                                            worksheet="My Worksheet"
                                            name="filename.xls"
                                        >
                                            <v-btn color="primary" small>
                                                Export Excel</v-btn>
                                           
                                        </export-excel>

                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchExcel"
                                            worksheet="My Worksheet"
                                            type="csv"
                                            name="filename.xls"
                                        >
                                              <v-btn color="success" small>
                                                Export CSV</v-btn>
                                           
                                        </export-excel>
                                        <button
                                        v-if="false"
                                            class="btn btn-primary btn-sm"
                                            @click="downLoadPdf()"
                                        >
                                            Download pdf
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
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
import Unauthorized from "../../utilities/Unauthorized.vue";
export default {
    components: {
        Pagination,

        Unauthorized,
    },
    data() {
        return {
            isLoading: true,
            type_select: [
                { name: "Group", value: "group" },
                { name: "List", value: "list" },
            ],
            params: {
                to: null,
                from: null,
                search: "",
                report_type: "list",
            },
            issue_data: [],
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async fetchExcel() {
            this.showLoader()
            const res = await this.getApi(
                "data/internal_issue/fetchIssueDetailed",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.hideLoader()
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.id,
                    issue_no: data.issue_no,
                    date_issued: data.date_issued,
                    code: data.stock.code,
                    product_name: data.stock.product_name,
                    qty_issued:
                        this.params.report_type == "group"
                            ? data.sum_qty_issued
                            : data.qty_issued,
                    final_qty_issued:
                        this.params.report_type == "group"
                            ? data.sum_final_qty
                            : data.final_qty,
                    purchase_price: data.purchase_price,
                    user: data.user.name,
                    issued_staff: data.issued_staff.name,
                    source_department: data.source_department.department,
                    issued_department: data.department.department,
                });
            });
            return data_array;
        },
        async downLoadPdf(issue_no) {
            this.showLoader();
            axios({
                url: "data/internal_issue/downloadDetailsPdf",
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    to: this.to,
                    from: this.from,
                },
                method: "GET",
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", "detailed_issue_report" + ".pdf");
                document.body.appendChild(link);
                link.click();
            });
        },
        async fetchAll() {
            this.showLoader();
            const res = await this.getApi(
                "data/internal_issue/fetchIssueData",
                {
                    params: {
                        search: this.search.length >= 2 ? this.search : "",
                        to: this.to,
                        from: this.from,
                    },
                }
            );
            this.hideLoader();
            if (res.status === 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].branch = data_array[i].branch
                        ? data_array[i].branch.branch
                        : "";

                    data_array[i].user = data_array[i].user
                        ? data_array[i].user.name
                        : "";
                    data_array[i].issued_staff = data_array[i].issued_staff
                        ? data_array[i].issued_staff.name
                        : "";
                    data_array[i].department = data_array[i].department
                        ? data_array[i].department.department
                        : "";
                    data_array[i].stock = data_array[i].stock
                        ? data_array[i].stock.product_name
                        : "";
                }

                return data_array;
            } else {
                this.swr("Server Error occurred");
            }
        },
        async fetchIssueData(page) {
            const res = await this.getApi(
                "data/internal_issue/fetchIssueDetailed",
                {
                    params: {
                        page,
                        ...this.params,
                    },
                }
            );
            if (res.status === 200) {
                this.issue_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetchIssueData(1)]);
            this.isLoading ? this.hideLoader() : "";
        },
    },
};
</script>
<style scoped>
.approved-selected {
    background: #af4448 !important;
}
.progress-selected {
    background: #00bcd4 !important;
}
</style>
