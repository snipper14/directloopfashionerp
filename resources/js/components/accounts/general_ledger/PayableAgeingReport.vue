<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <ledger-nav />
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <h3><b>Creditors Ageing Report</b></h3>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group d-flex flex-row">
                                    <label for=""> Select supplier</label>
                                    <treeselect
                                        width="300"
                                        :load-options="fetchSupplier"
                                        :options="account_select_data"
                                        :auto-load-root-options="false"
                                        v-model="params.supplier_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select Acc. "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Account Name</th>
                                        <th scope="col">Current  <v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('current')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'current'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                        <th scope="col">30 DAYS <v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('thirty')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'thirty'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                        <th scope="col">60 DAYS<v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('sixty')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'sixty'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                        <th scope="col">90 DAYS<v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('ninety')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'ninety'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                        <th scope="col">120 DAYS<v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('one_twenty')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'one_twenty'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                        <th scope="col">150 DAYS<v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('one_fifty')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'one_fifty'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                        <th scope="col">180 DAYS<v-btn
                                                color="primary"
                                                x-small
                                                @click="setSort('one_eighty')"
                                                >Sort</v-btn
                                            > <span
                                                v-if="sort_column === 'one_eighty'"
                                            >
                                                {{
                                                    sort_direction === "asc"
                                                        ? "▲"
                                                        : "▼"
                                                }}
                                            </span>
                                            <span v-else class="text-muted"
                                                >⇅</span
                                            ></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in aging_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{ data.supplier.company_name }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.current) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.thirty) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sixty) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.ninety) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.one_twenty) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.one_fifty) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.one_eighty) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <pagination
                            v-if="aging_data !== null"
                            v-bind:results="aging_data"
                            v-on:get-page="getAgingReport"
                        ></pagination>
                        Items Count {{ aging_data.total }}
                    </div>
                    <div class="row">
                        <div class="col-4 d-flex">
                            <export-excel
                                class="btn btn-default btn-export ml-2"
                                :fetch="exportExcel"
                                worksheet="My Worksheet"
                                name="filename.xls"
                            >
                                <v-btn small color="info"> Export Excel</v-btn>
                            </export-excel>

                            <export-excel
                                class="btn btn-default btn-export ml-2"
                                :fetch="exportExcel"
                                worksheet="My Worksheet"
                                type="csv"
                                name="filename.xls"
                            >
                                <v-btn small color="success">
                                    Export Excel</v-btn
                                >
                            </export-excel>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import LedgerNav from "./LedgerNav.vue";
export default {
    components: {
        Unauthorized,
        Pagination,
        LedgerNav,
        Treeselect
    },
    data() {
        return {
            sort_column: "",
            sort_direction: "asc",
            account_select_data:null,
            aging_data: [],
            isLoading: true,
            params: { search: "" },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            deep: true,
            handler: _.debounce(function (val, old) {
                
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.getAgingReport(1)]).then(
                function (results) {
                    return results;
                }
            );

            this.isLoading ? this.hideLoader() : "";
        },
          setSort(column) {
            
            if (this.sort_column === column) {
                this.sort_direction =
                    this.sort_direction === "asc" ? "desc" : "asc";
            } else {
                this.sort_column = column;
                this.sort_direction = "asc";
            }
            this.getAgingReport(1);
        },
        async getAgingReport(page) {
            const res = await this.getApi("data/supp_ledger/getAgingReport", {
                params: {
                    page,

                    ...this.params,
                    sort_column: this.sort_column,
                    sort_direction: this.sort_direction,
                },
            });

            if (res.status === 200) {
                this.aging_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
         async fetchSupplier() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/suppliers/fetchAll", {});
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.account_select_data = res.data.results.map((obj) => {
                    return {
                        id: obj.id,
                        label: obj.company_name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        async exportExcel() {
            const res = await this.getApi(
                "data/supp_ledger/exportAgingReport",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            var data_array = [];
            res.data.map((data) => {
                data_array.push({
                    company_name: data.supplier.company_name,
                    current: data.current,
                    thirty: data.thirty,
                    sixty: data.sixty,
                    ninety: data.ninety,
                    one_twenty: data.one_twenty,
                    one_fifty: data.one_fifty,
                    one_eighty: data.one_eighty,
                });
            });
            return data_array;
        },
    },
};
</script>
