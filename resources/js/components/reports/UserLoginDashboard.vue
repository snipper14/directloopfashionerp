<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div
                        class="card-header"
                        style="background: #ef6c00; color: #fff"
                    >
                        <h4>
                            <b>User Logins Reports</b>
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        placeholder="Search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                            <div class="col-3">
                                <label for="">From</label>
                                <date-picker v-model="from" type="datetime" />
                            </div>
                            <div class="col-3">
                                <label for="">To</label>
                                <date-picker v-model="to" type="datetime" />
                            </div>
                            <div class="col-2">
                                <button
                                    class="btn btn-primary btn-sm"
                                    @click="fetchData()"
                                >
                                    filter
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">time</th>
                                        <th scope="col">IP Addr</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in login_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                formatDateTime(data.created_at)
                                            }}
                                        </td>
                                        <td>{{ data.location_ip }}</td>
                                        <td>{{ data.user.name }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="login_data !== null"
                            v-bind:results="login_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ login_data.total }}
                    </div>
                </div>
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import Pagination from "../utilities/Pagination.vue";

export default {
    data() {
        return {
            search: "",
            active: {
                dashboard: true,
                reception_balance: false,
            },
            total_data: {},
            from: null,
            to: null,
            opening_time: null,
            closing_time: null,
            total_opening_balance: 0,
            total_closing_balance: 0,
            details_data: null,
            isLoading: true,
            login_data: [],
        };
    },
    components: {
        Pagination,
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.fetchData();
            }, 500),
        },
    },
    methods: {
        async fetchData(page) {
            const res = await this.getApi(
                "data/system_reports/fetchLogins",

                {
                    params: {
                        page: page,
                        search: this.search,
                        to: this.formatDateTime(this.to),
                        from: this.formatDateTime(this.from),
                    },
                }
            );

            if (res.status == 200) {
                this.login_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },

        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData(1)]).then(function (
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
};
</script>
<style scoped>
.is_deducted {
    background: red !important;
}
.short-alert {
    background: #ff8f00 !important;
}
</style>
