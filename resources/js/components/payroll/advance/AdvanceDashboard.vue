<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-7">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_advance')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color:red">Advance Records</h5>
                        </div>
                        <div class="col-md-4 float-md-right ">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-between">
                            <div class="form-group">
                                <label> FROM</label
                                ><date-picker
                                    v-model="from"
                                    valueType="format"
                                ></date-picker>
                            </div>

                            <div class="form-group">
                                <label> TO</label
                                ><date-picker v-model="to"></date-picker>
                            </div>

                            <div>
                                <button
                                    @click="concurrentApiReq()"
                                    class="btn btn-secondary btn-sm "
                                >
                                    Filter
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 mb-3">
                            <div class="badge badge-secondary text-wrap">
                                <h4>
                                    Paid Advance:
                                    {{ cashFormatter(paid_total) }}
                                </h4>
                            </div>
                            <div class="badge badge-secondary text-wrap">
                                <h4>
                                    Unpaid Advance:
                                    {{ cashFormatter(unpaid_total) }}
                                </h4>
                            </div>
                        </div>
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Employee</th>
                                    <th scope="col">Payment Amount</th>
                                    <th scope="col">Payment Month</th>
                                    <th>Deduction Status</th>
                                    <th>Account Paid</th>
                                    <th>Payment Ref</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in advance_data.data"
                                    :key="i"
                                >
                                    <td>
                                      
                                            {{
                                               data.employee? (data.employee.first_name +
                                                    " " +
                                                    data.employee.last_name):""
                                            }}
                                       
                                    </td>

                                    <td>{{ cashFormatter(data.amount) }}</td>

                                    <td>{{ data.payment_month }}</td>

                                    <td>
                                        {{
                                            data.isDeducted == 1
                                                ? "Paid"
                                                : "Unpaid"
                                        }}
                                    </td>
                                    <td>{{data.payroll_account.account}}</td>
                                    <td>{{data.ref_no}}</td>
                                    <td>
                                        <button
                                            v-if="
                                                isDeletePermitted &&
                                                    data.isDeducted == 0
                                            "
                                            class="btn btn-danger btn-sm"
                                            @click="deleteRecord(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="advance_data !== null"
                            v-bind:results="advance_data"
                            v-on:get-page="fetchAdvance"
                        ></Pagination>
                        Items Count {{ advance_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-secondary btn-sm">
                                        CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <add-advance
            v-if="active.add_advance"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-advance>
        <edit-advance
            v-if="active.edit_advance"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-advance>
    </div>
</template>

<script>
import AddAdvance from "./AddAdvance.vue";
import EditAdvance from "./EditAdvance.vue";
import Pagination from "../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_advance: false,
                edit_advance: false
            },
            advance_data: [],
            edit_data: null,
            pdf_data: [],
            from: null,
            to: null,
            unpaid_total: 0,
            paid_total: 0,
            search: "",
            params: {},
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        AddAdvance,
        EditAdvance,
        VueHtml2pdf,
        Pagination
    },
    mounted() {
        this.concurrentApiReq();
    },

    watch: {
        params: {
            handler() {
                this.searchService();
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
        async concurrentApiReq() {
            const res = await Promise.all([
                this.fetchAdvance(1),
                this.fetchTotal()
            ]).then(function(results) {
                return results;
            });
        },
        async fetchTotal() {
            const res = await this.getApi("data/advance/fetchTotals", {
                params: {
                    to: this.formatDate(this.to),
                    from: this.formatDate(this.from)
                }
            });

            if (res.status === 200) {
                this.unpaid_total = res.data.total_unpaid;
                this.paid_total = res.data.total_paid;
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/advance/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.advance_data.data.splice(i, 1);
                    this.fetchTotal();
                } else {
                    this.swr("Server error try again later");
                }
            }
        },

        async generateExcel() {
            const res = await this.fetchAll();
            var data_array = [];
            res.map(array_item => {
                data_array.push({
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,
                    payment_month: array_item.payment_month,
                    amount: array_item.amount
                });
            });

            return data_array;
        },
        editAdvance(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_advance");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.fetchAll();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async fetchAll() {
            const res = await this.getApi("data/advance/fetchAll",{
                params: {
                    
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });

            return res.data.results;
        },

        async searchService() {
            let page = 1;
            const res = await this.getApi("data/advance/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.advance_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },

        async fetchAdvance(page) {
            this.showLoader();
            const res = await this.getApi("data/advance/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    to: this.formatDate(this.to),
                    from: this.formatDate(this.from),
                    ...this.params
                }
            });
            this.hideLoader();
            if (res.status === 200) {
                this.advance_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
