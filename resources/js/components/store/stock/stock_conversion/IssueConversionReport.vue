<template>
    <div class="row">
        <div class="col-12">
            <div class="container" v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-2">
                        <IssueConversionNav />
                    </div>
                    <div class="col-md-10">
                        <!-- <ImportExcel/> -->
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                            
                                <div class="col-md-7">
                                    <h4>
                                        <b>Stock Conversion Report</b>
                                    </h4>
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
                                            class="table table-sm table-striped table-bordered custom-table mt-3"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        Issue No
                                                    </th>
                                                    <th>Date</th>

                                                    
                                                    <th>Parent Item</th>

                                                    <th>Parent Issue Qty</th>
                                                    <th>Child Items</th>
                                                    <th>
                                                        Converted Qty 
                                                    </th>
                                                    <th>Mapping Qty</th>
                                                    

                                                    <th>User</th>
                                                    <th>Issed to</th>
                                                    <th>Loc From</th>
                                                    <th>Loc To</th>
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
                                                        {{ data.parent_stock.product_name }}
                                                    </td>
                                                   

                                                    <td>
                                                        {{ data.parent_issue_qty }}
                                                    </td>
                                                      <td>
                                                        {{ data.stock.product_name }}
                                                    </td>
                                                    <td>
                                                        {{ data.qty_issued }}
                                                    </td>
                                                    <td>
                                                        {{ data.mapping_value }}
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
                                                            data.department
                                                                .department
                                                        }}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.department_to
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

                                    <div class="col-4 d-flex">
                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchAll"
                                            worksheet="My Worksheet"
                                            name="filename.xls"
                                        >
                                            <v-btn small color="info"> Export Excel</v-btn>
                                           
                                        </export-excel>

                                        <export-excel
                                            class="btn btn-default btn-export ml-2"
                                            :fetch="fetchAll"
                                            worksheet="My Worksheet"
                                            type="csv"
                                            name="filename.xls"
                                        >
                                            <v-btn color="info" small> Export CSV</v-btn>
                                           
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
import IssueConversionNav from "./IssueConversionNav";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";

import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
export default {
    components: {
        Pagination,

        Unauthorized,

        IssueConversionNav,
    },
    data() {
        return {
            isLoading: true,
            to: "",
            from: "",
            search: "",
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
        
        async fetchAll() {
            this.showLoader();
            const res = await this.getApi(
                "data/issue_conversion/fetch",
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
                          data_array[i].department_to = data_array[i].department_to
                        ? data_array[i].department_to.department
                        : "";
                    data_array[i].stock = data_array[i].stock
                        ? data_array[i].stock.product_name
                        : "";
                         data_array[i].parent_stock = data_array[i].parent_stock
                        ? data_array[i].parent_stock.product_name
                        : "";
                }

                return data_array;
            } else {
                this.swr("Server Error occurred");
            }
        },
        async fetchIssueData(page) {
            const res = await this.getApi(
                "data/issue_conversion/fetch",
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
