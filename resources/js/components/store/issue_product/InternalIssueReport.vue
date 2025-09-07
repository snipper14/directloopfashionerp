<template>
    <div class="row">
        <div class="col-12">
            <div class="container" v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="col-md-3">
                                    <button
                                        class="btn btn-primary btn-sm"
                                        @click="
                                            $router.push(
                                                'internal_issue_detailed_report'
                                            )
                                        "
                                    >
                                        View Detailed Report
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <h3><b>Internal Issue Report</b></h3>
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
                                <div class="row">
                                    <div
                                        class="d-flex flex-row justify-content-around"
                                    >
                                        <div class="form-group">
                                            <label> From *</label
                                            ><date-picker
                                                v-model="from"
                                                valueType="format"
                                            ></date-picker>
                                        </div>
                                        <div class="form-group">
                                            <label> To *</label
                                            ><date-picker
                                                v-model="to"
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
                                                     <th scope="col">
                                                       Internal Issue No
                                                    </th>
                                                    <th>Date</th>
                                                    <th>User</th>
                                                    <th>Issued to</th>
                                                    <th>Source</th>
                                                    <th>Dest</th>

                                                    <th>Total Item Issued</th>
                                                    <th>Total Item Worth</th>

                                                    <th scope="col">
                                                        Download
                                                    </th>
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
                                                        {{ data.internal_issue_no }}
                                                    </td>
                                                    <td>
                                                        {{ data.date_issued }}
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

                                                    <td>
                                                        {{
                                                            cashFormatter(data.sum_qty_issued)
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.sum_row_total
                                                            )
                                                        }}
                                                    </td>

                                                    <td>
                                                        <button
                                                            title="download"
                                                            v-if="
                                                                isDownloadPermitted
                                                            "
                                                            class="btn btn-primary btn-sm"
                                                            @click="
                                                                downloadIssueNote(
                                                                    data.issue_no
                                                                )
                                                            "
                                                        >
                                                            Download
                                                        </button>
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

                                    <div class="col-4 d-flex">
                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchAll"
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
                                            :fetch="fetchAll"
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

            search: "",
            issue_data: [],
            to: "",
            from: "",
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    watch: {
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.fetchIssueData(1);
            }
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async downloadIssueNote(issue_no) {
            this.showLoader();
            axios({
                url: "data/internal_issue/downloadIssueNote/" + issue_no,

                method: "GET",
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute("download", issue_no + ".pdf");
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
                }

                return data_array;
            } else {
                this.swr("Server Error occurred");
            }
        },
        async fetchIssueData(page) {
            const res = await this.getApi(
                "data/internal_issue/fetchIssueData",
                {
                    params: {
                        page,
                        search: this.search.length >= 2 ? this.search : "",
                        to: this.to,
                        from: this.from,
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
            await Promise.all([this.fetchIssueData(1)]).then(function (
                results
            ) {
                return results;
            });
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
