<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">Employee Salary Sales Report</div>

                    <div class="card-body">
                        <div class="d-flex flex-row justify-content-around">
                            <div class="form-group">
                                <label> From *</label
                                ><date-picker
                                    v-model="from"
                                    type="datetime"
                                ></date-picker>
                            </div>
                            <div class="form-group">
                                <label> To *</label
                                ><date-picker
                                    v-model="to"
                                    type="datetime"
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
                        <div class="total-display">
                            <span class="badge badge-secondary totals-badge"
                                >Total Amount:
                                {{ cashFormatter(total_amount) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total VAT: {{ cashFormatter(total_vat) }}</span
                            >
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col"> Date Created</th>
                                        <th scope="col">Order No</th>
                                        <th scope="col">Order Type</th>
                                        <th>Employee</th>
                                        <th>Amount</th>
                                        <th scope="col">Total Vat</th>
<th>View</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{formatDateTime( data.created_at) }}</td>
                                        <td>{{ data.order_no }}</td>

                                        <td>
                                            {{ data.order_type }}
                                        </td>
                                        <td>
                                            {{
                                                data.employee.first_name +
                                                    "" +
                                                    data.employee.last_name
                                            }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.total_amount)
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.total_vat) }}
                                        </td>
  <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewDetails(data)
                                                "
                                            >
                                                View Details
                                            </router-link>
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    addDeduction(data)
                                                "
                                            >
                                                Add To Deduction
                                            </router-link>
                                        </td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination
                            v-if="sale_data !== null"
                            v-bind:results="sale_data"
                            v-on:get-page="fetchData"
                        ></Pagination>
                        Items Count {{ sale_data.total }}
                        <div class="row">
                            <div
                                class="col-4 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
        <add-salary-deduction
            v-if="active.add_deduction"
            :deduction_data="deduction_data"
            v-on:dashboard-active="setActiveRefresh"
        />
         <Modal v-model="modal.details" title="Sale Details">
            <emp-salary-sale-details
                v-if="order_data_details && modal.details"
                :order_data_details="order_data_details"
                :total_sale="total_sale"
                :total_tax="total_vat"
            />
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import EmpSalarySaleDetails from '../../pos/pos_report/EmpSalarySaleDetails.vue';
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddSalaryDeduction from "./AddSalaryDeduction.vue";

export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_deduction: false,
                edit_deduction: false
            },
            modal:{
                details:false
            },
            deduction_data: null,
            order_data_details: null,

            total_amount: 0,
            total_vat: 0,
            sum_service_charge_total: 0,
            isLoading: true,
            total_sale: 0,
            search: "",
            to: "",
            from: "",
            sale_data: [],
            all_sale_data: []
        };
    },
    components: {
        Pagination,
        Unauthorized,
        AddSalaryDeduction,
        EmpSalarySaleDetails
    },
    watch: {
        search: {
            handler: _.debounce(function() {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500)
        },
        all_sale_data: {
            handler() {
                this.total_amount = this.all_sale_data.reduce(function(
                    sum,
                    val
                ) {
                    return sum + val.total_amount;
                },
                0);
                this.total_vat = this.all_sale_data.reduce(function(sum, val) {
                    return sum + val.total_vat;
                }, 0);
            }
        }
    },
    methods: {
        addDeduction(data) {
            this.deduction_data = data;
            this.setActiveComponent("add_deduction");
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },

        viewDetails(data) {
            this.order_data_details = data;
            this.modal.details = true;
        },
        async fechAll() {
            const res = await this.getApi(
                "data/pos_sale/fetchEmployeeSaleDeduction",
                {
                    params: {
                        search: this.search,
                        from: this.formatDateTime(this.from),
                       clear_status:'pending',
                        to:this.formatDateTime( this.to)
                    }
                }
            );

            if (res.status == 200) {
                this.all_sale_data = res.data;
            } else {
                this.servo();
            }
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi(
                "data/pos_sale/fetchEmployeeSaleDeduction",
                {
                    params: {
                        search: this.search,
                        from:this.formatDateTime( this.from),
                        clear_status:'pending',
                        to: this.formatDateTime(this.to)
                    }
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map(array_item => {
                    data_array.push({
                        order_no: array_item.order_no,
                        employee:
                            array_item.employee.first_name +
                            "" +
                            array_item.employee.last_name,
                        receipt_total: array_item.total_amount,
                        stock: array_item.stock
                            ? array_item.stock.product_name
                            : ""
                    });
                });
                return data_array;
            } else {
                this.swr("Server error occurred");
            }
        },
        async filterDate() {
            this.isLoading = true;
            this.concurrentApiReq();
        },

        async fetchData(page) {
            const res = await this.getApi(
                "data/pos_sale/fetchEmployeeSaleDeduction",

                {
                    params: {
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        clear_status:'pending',
                        to: this.formatDateTime(this.to)
                    }
                }
            );

            if (res.status == 200) {
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.fechAll()
            ]).then(function(results) {
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
<style scoped>
.is_deducted {
    background: red !important;
}
</style>
