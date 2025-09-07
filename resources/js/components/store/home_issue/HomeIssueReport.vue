<template>
    <div class="row">
        <div class="col-12">
            <div class="container">
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
                                        @click="$router.push('home_issue')"
                                    >
                                        Back
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <h3>
                                        <b>Home Issue Report</b>
                                    </h3>
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
                                                    <th>Date</th>

                                                    <th>Code</th>
                                                    <th>ItemName</th>
                                                    <th>Qty Item Issued</th>

                                                    <th>Avail Qty.</th>
                                                    <th>Item c.P</th>

                                                    <th>User</th>
                                                    <th></th>
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
                                                        {{ data.qty }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            data.stock
                                                                .main_store_qty
                                                        }}
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
                                                        <v-btn
                                                            color="danger"
                                                            dark
                                                            small
                                                            @click="
                                                                deleteAction(
                                                                    data,
                                                                    i
                                                                )
                                                            "
                                                            >delete</v-btn
                                                        >
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
        async deleteAction(data, i) {
            const del = await this.deleteDialogue();
            if (del) {
                this.showLoader();
                const res = await this.callApi(
                    "POST",
                    "data/issue_home/destroy",
                    data
                );
                this.hideLoader();
                if (res.status == 200) {
                    this.s("deleted successfully");
                    this.issue_data.data.splice(i, 1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async fetchAll() {
            this.showLoader();
            const res = await this.getApi("data/issue_home/fetch", {
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    to: this.to,
                    from: this.from,
                },
            });
            this.hideLoader();
            if (res.status === 200) {
                var data_array = res.data;
                for (var i = 0; i < data_array.length; i++) {
                    data_array[i].user = data_array[i].user
                        ? data_array[i].user.name
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
            const res = await this.getApi("data/issue_home/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    to: this.to,
                    from: this.from,
                },
            });
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
