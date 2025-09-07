<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th scope="col">Reg</th>
                                        <th>Gender</th>
                                        <th>Sickoff Days</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Cost</th>
                                        <th>Doctor</th>
                                        <th>DoctorS Remarks</th>
                                        <th>User</th>
                                        <th>Reprint</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.currentdate }}</td>
                                        <td>
                                            {{ data.reg_no }}
                                        </td>

                                        <td>{{ data.gender }}</td>
                                        <td>{{ data.sickoff_days }}</td>
                                        <td>{{ data.startdate }}</td>
                                        <td>{{ data.enddate }}</td>
                                        <th>{{ cashFormatter(data.cost) }}</th>
                                        <td>{{ data.doctors_name }}</td>
                                        <td>{{ data.doctors_report }}</td>
                                        <td>{{ data.user.name }}</td>
                                        <td>
                                            <Button
                                                type="primary"
                                                @click="reprint(data)"
                                                x-small
                                                v-if="isDownloadPermitted"
                                                title="Download"
                                                >Reprint</Button
                                            >
                                        </td>
                                        <td>
                                            <v-btn
                                                v-if="isDeletePermitted"
                                                @click="
                                                    deleteRecord(data.id, i)
                                                "
                                                title="DELETE"
                                                color="danger"
                                                x-small
                                            >
                                                Delete</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <pagination
                            v-if="data !== null"
                            v-bind:results="data"
                            v-on:get-page="fetch"
                        ></pagination>
                        Items Count {{ data.total }}
                        <div class="row">
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
        <PrintSickoff
            v-if="show_print"
            ref="PrintSickoff"
            :print_data="print_data"
            :branch_info="branch_info"
            :logo="logo"
            :user="user"
        />
    </div>
</template>

<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import PrintSickoff from "./PrintSickoff.vue";
export default {
    components: { PrintSickoff },
    props: ["edit_data"],
    data() {
        return {
            print_data: null,
            logo: null,
            show_print: false,
            branch_info: null,
            user: null,
            cost_total: 0,
            data: [],
            params: {
                customer_id: null,
                search: "",
            },
            isLoading: false,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    mounted() {
        this.branch_info = this.$store.state.branch;
        this.logo = this.$store.state.branch.img_url;
        this.user = this.$store.state.user;
        this.params.customer_id = this.edit_data.id;
        this.concurrentApiReq();
    },
    methods: {
        reprint(data) {
            this.print_data = data;
            this.show_print = true;
            setTimeout(() => {
                this.$refs.PrintSickoff.printBill();

                this.show_print = false;
            }, 1000);
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            await Promise.all([
                this.fetch(1),
                // this.fetchTotal(),
            ]);

            this.isLoading ? this.hideLoader() : "";
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/sickoff/fetch", {
                params: {
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                data.map((data) => {
                    data_array.push({
                        currentdate: data.currentdate,
                        reg_no: data.reg_no,
                        gender: data.gender,
                        sickoff_days: data.sickoff_days,
                        startdate: data.startdate,
                        enddate: data.enddate,
                        doctors_name: data.doctors_name,
                        doctors_report: data.doctors_report,
                        cost: data.cost,
                        name: data.user.name,
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader();
                const res = await this.callApi(
                    "DELETE",
                    "data/sickoff/destroy/" + id,
                    {}
                );
                this.hideLoader();
                if (res.status === 200) {
                    this.s("deleted");
                    this.data.data.splice(i, 1);
                } else {
                    this.form_error(res);
                }
            }
        },
        async fetch(page) {
            const res = await this.getApi("data/sickoff/fetch", {
                params: {
                    page,
                    ...this.params,
                },
            });

            if (res.status === 200) {
                this.data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async fetchTotal() {
            const res = await this.getApi(
                "data/expense_payment/fetchPaymentTotal",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status === 200) {
                this.cost_total = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
