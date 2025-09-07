<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Receive Stock</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th>Date issued</th>
                                        <th scope="col">Product Name</th>
                                        <th>Qty Issued</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in issue_data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>
                                            {{ data.stock.product_name }}
                                        </td>

                                        <td>
                                            <input
                                                type="number"
                                                v-model="data.qty_issued"
                                            />
                                        </td>
                                        <td>
                                            <v-btn
                                                :loading="btn_loading"
                                                small
                                                color="primary"
                                                dark
                                                @click="receiveStock(data, i)"
                                            >
                                                <v-icon class="v-icon" medium>{{
                                                    icons.mdiPlusThick
                                                }}</v-icon
                                                >Receive
                                            </v-btn>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div>
                                <v-btn
                                    v-if="issue_data.length > 0"
                                    :loading="btn_loading1"
                                    small
                                    color="info"
                                    dark
                                    @click="receiveBulkStock()"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiPlusThick
                                    }}</v-icon
                                    >Receive All</v-btn
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mdiPlusThick } from "@mdi/js";
export default {
    data() {
        return {
            btn_loading: false,
            btn_loading1: false,
            issue_data: [],
            icons: {
                mdiPlusThick,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async receiveBulkStock() {
            this.btn_loading1 = true;
            const res = await this.callApi(
                "POST",
                "data/main_store_issue/receiveBulkStock",
                { issue_data: this.issue_data }
            );
            this.btn_loading1 = false;
            if (res.status === 200) {
                this.s("received");
                this.issue_data = [];
            } else {
                this.form_error(res);
            }
        },
        async receiveStock(data, i) {
            this.btn_loading = true;
            const res = await this.callApi(
                "POST",
                "data/main_store_issue/receiveStock",
                data
            );
            this.btn_loading = false;
            if (res.status === 200) {
                this.s("received");
                this.issue_data.splice(i, 1);
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchIssuedRecords()]);
            this.hideLoader();
        },

        async fetchIssuedRecords() {
            const res = await this.getApi(
                "data/main_store_issue/fetchIssueDetailed",
                {
                    params: {
                        isReceived: "not_received",
                    },
                }
            );

            if (res.status == 200) {
                this.issue_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
