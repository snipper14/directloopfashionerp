<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card" >
                    <div class="card-header">Merged Bills Report</div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
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
                                    @click="filterDate()"
                                >
                                    Filter Date
                                </button>
                            </div>
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

                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">Total Amount</th>
                                        <th>Total Vat</th>

                                        <th>Order No</th>

                                        <th scope="col">Customer</th>

                                        <th scope="col">Waiter</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in order_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.order_date }}</td>

                                        <td>
                                            {{ cashFormatter(data.sum_total) }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.sum_vat) }}
                                        </td>

                                        <td>
                                            {{
                                                data.order_no +
                                                    " merged from " +
                                                    data.merged_order_no
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                data.customer
                                                    ? data.customer.company_name
                                                    : ""
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                data.user ? data.user.name : ""
                                            }}
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    displayDetails(data)
                                                "
                                                >Details</router-link
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="order_data !== null"
                            v-bind:results="order_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ order_data.total }}
                    </div>
                </div>
              
            </div>
        </div>
        <Modal v-model="modal_details" title="Sale Details">
            <POSMergeBillDetails
                v-if="order_data_details && modal_details"
                :order_data_details="order_data_details"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import POSReportNav from "../POSReportNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import POSMergeBillDetails from "./POSMergeBillDetails.vue";

export default {
    data() {
        return {
            modal: {
                details: false
            },
            order_data_details: null,
            total_vat: 0,
            total_sales: 0,
            modal_details: false,
            isLoading: true,
            search: "",
            to: "",
            from: "",
            order_data: [],
            all_order_data: []
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        POSMergeBillDetails
    },
    watch: {
        search: {
            handler: _.debounce(function() {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500)
        }
    },
    methods: {
        async displayDetails(data) {
            this.order_data_details = data;
            this.modal_details = true;
        },

        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },

        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_order/fetchMergeBills",

                {
                    params: {
                        search: this.search,
                        page,
                        from: this.from,
                        to: this.to
                    }
                }
            );

            if (res.status == 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([this.fetchData(1)]).then(function(
                results
            ) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        }
    },
    mounted() {
        this.concurrentApiReq();
    }
};
</script>
