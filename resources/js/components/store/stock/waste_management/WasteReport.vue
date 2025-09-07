<template>
    <div class="row">
        <div class="col-12">
            <div class="" v-if="isReadPermitted">
                <div class="row justify-content-center">
                    <div class="col-md-2">
                        <WasteNav />
                    </div>

                    <div class="col-md-10">
                        <!-- <ImportExcel/> -->
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="col-md-2">
                                    <h5>
                                        <b>Waste Report</b>
                                    </h5>
                                </div>
                                <div class="col-md-5">
                                    <div class="total-display">
                                        <span
                                            class="badge badge-info totals-badge"
                                            >Total Purchase:
                                            {{
                                                cashFormatter(
                                                    totals_summary.sum_sub_total
                                                )
                                            }}</span
                                        >
                                        <span
                                            class="badge badge-info totals-badge"
                                            >Qty Wasted:
                                            {{ totals_summary.total_qty }}</span
                                        >
                                    </div>
                                </div>
                                <div class="col-md-3 float-md-right">
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
                                    
                                    <div class="row">
                                        <div
                                            class="d-flex flex-row justify-content-around"
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
                                          
                                        </div>
                                        <div class="table-responsive">
                                            <table
                                                class="table table-sm table-striped table-bordered custom-table mt-3"
                                            >
                                                <thead>
                                                    <tr>
                                                        <th scope="col">No</th>
                                                        <th>Date</th>

                                                        <th>Code</th>
                                                        <th>ItemName</th>

                                                        <th>Qty</th>

                                                        <th>Item c.P</th>
                                                        <th>Total</th>
                                                        <th>Created By</th>
                                                        <th>Staff to</th>
                                                        <th>Dept</th>
                                                        <th>Purpose</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr
                                                        class="small-tr"
                                                        v-for="(
                                                            data, i
                                                        ) in waste_data.data"
                                                        :key="i"
                                                    >
                                                        <td>
                                                            {{ data.waste_no }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                data.date_wasted
                                                            }}
                                                        </td>

                                                        <td>
                                                            {{
                                                                data.stock.code
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                data.stock
                                                                    .product_name
                                                            }}
                                                        </td>

                                                        <td>
                                                            {{ data.qty }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                cashFormatter(
                                                                    data.purchase_price
                                                                )
                                                            }}
                                                        </td>

                                                        <td>
                                                            {{
                                                                cashFormatter(
                                                                    data.total
                                                                )
                                                            }}
                                                        </td>
                                                        <td>
                                                            {{ data.user.name }}
                                                        </td>
                                                        <td>
                                                            {{
                                                                data.staff.name
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
                                                                data
                                                                    .waste_reason
                                                                    .reasons
                                                            }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <pagination
                                            v-if="waste_data !== null"
                                            v-bind:results="waste_data"
                                            v-on:get-page="fetchData"
                                        ></pagination>
                                        Items Count {{ waste_data.total }}

                                        <div class="col-4 d-flex">
                                            <export-excel
                                                class="btn btn-default btn-export ml-2"
                                                :fetch="fetchExcel"
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
                                                :fetch="fetchExcel"
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
            </div>
            <div v-else>
                <unauthorized />
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
} from "@mdi/js";
import Pagination from "../../../utilities/Pagination.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import WasteNav from "./WasteNav.vue";
export default {
    components: {
        Pagination,
        WasteNav,
        Unauthorized,
    },
    data() {
        return {
            isLoading: true,
            params: {
                to: "",
                from: "",
                search: "",
            },
            totals_summary: {},
            waste_data: [],
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
                this.concurrentApiReq();
            }, 500),
        },
    },

    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async fetchTotal() {
            const res = await this.getApi("data/waste_record/fetchTotals", {
                params: {
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.totals_summary = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/waste_record/fetch", {
                params: {
                    ...this.params,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].user = data_array[i].user
                        ? data_array[i].user.name
                        : "";

                    data_array[i].staff = data_array[i].staff
                        ? data_array[i].staff.name
                        : "";

                    data_array[i].department = data_array[i].department
                        ? data_array[i].department.department
                        : "";
                    data_array[i].stock = data_array[i].stock
                        ? data_array[i].stock.product_name
                        : "";
                    data_array[i].unit = data_array[i].unit
                        ? data_array[i].unit.name
                        : "";
                    data_array[i].waste_reason = data_array[i].waste_reason
                        ? data_array[i].waste_reason.reasons
                        : "";
                }

                return data_array;
            } else {
                this.swr("Server Error occurred");
            }
        },
        async fetchData(page) {
            const res = await this.getApi("data/waste_record/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.waste_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";
            await Promise.all([this.fetchData(1), this.fetchTotal()]).then(
                function (results) {
                    return results;
                }
            );
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
