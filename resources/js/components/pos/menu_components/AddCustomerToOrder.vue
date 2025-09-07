<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row">
                    <div class="form-group mr-2">
                        <label for="">Customer Name/Phone</label>
                        <input
                            type="text"
                            v-model="search"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-12 border border-secondary">
                    <table class="table table-sm table-striped ">
                        <thead>
                            <tr>
                                <th scope="col">company name</th>
                                <th scope="col">Phone</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="small-tr"
                                v-for="(data, i) in search_results"
                                :key="i"
                            >
                                <td>
                                    {{ data.company_name }}
                                </td>
                                <td>{{ data.company_phone }}</td>

                                <td>
                                    <button
                                        v-if="isWritePermitted"
                                        class="btn btn-secondary btn-sm"
                                        @click="submitRecords(data)"
                                    >
                                        Add Customer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import _ from "lodash";
export default {
    data() {
        return {
            search_results: [],
            search: ""
        };
    },
    mounted() {},
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.searchCustomer();
            }, 500)
        }
    },
    methods: {
        submitRecords(data) {
            this.$emit("customerSearchResult", data);
        },
        async searchCustomer() {
            if (this.search.length > 0) {
                const res = await this.getApi("data/customers/fetch", {
                    params: {
                        search: this.search
                    }
                });

                this.search_results = res.data.results;
                console.log(JSON.stringify(this.search_results));
            } else {
                this.search_results = [];
            }
        }
    }
};
</script>
