<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card" >
                    <div class="card-header">Voided Report</div>

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
                                >Total Sales:
                                {{ cashFormatter(total_sales) }}</span
                            >

                            <span class="badge badge-secondary totals-badge"
                                >Total VAT: {{ cashFormatter(total_vat) }}</span
                            >
                             <span class="badge badge-secondary totals-badge"
                                >Total Cost: {{ cashFormatter(total_cost) }}</span
                            >

                         
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                        <th scope="col">Sale Date</th>
                                        <th scope="col">Amount</th>
                                        <th>Vat</th>
                                      <th>Product</th>
                                      <th>Unit</th>
                                        <th>Order No</th>
                                        <th>Reason</th>
                                        <th scope="col">Guest</th>
                                       
                                        <th scope="col">Waiter</th>

                                        <th>Voider</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in sale_data.data"
                                        :key="i"
                                    >
                                        <td>{{ data.order_date }}</td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.row_total
                                                )
                                            }}
                                        </td>
                                        <td>
                                            {{ cashFormatter(data.row_vat) }}
                                        </td>
                                      
                                     <td>
                                            {{ data.stock.product_name }} <br>
                                             <span
                                                        v-if="
                                                            data.accompaniment_id
                                                        "
                                                    >
                                                        Served With
                                                        {{
                                                            data.accompaniment
                                                                .product_name
                                                        }}</span
                                                    >
                                        </td>
                                        <td>
                                            {{ data.unit.name }}
                                        </td>
                                       
                                        <td>
                                            {{ data.order_no }}
                                        </td>
                                         <td>
                                            {{ data.notes }}
                                        </td>
                                        <td>
                                            {{
                                                data.guest
                                                    ? data.guest.name
                                                    : ""
                                            }}
                                        </td>
                                      
                                        <td>
                                            {{
                                                data.user ? data.user.name : ""
                                            }}
                                        </td>
 <td>
                                            {{
                                                 data.cashier.name 
                                            }}
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
                               
                            >
                                <export-excel
                                   
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
              
            </div>
        </div>
        
        <notifications group="foo" />
    </div>
</template>

<script>

import POSReportNav from "./POSReportNav.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import Pagination from "../../utilities/Pagination.vue";

export default {
    data() {
        return {
            modal: {
                details: false
            },
            order_data_details: null,
            total_vat: 0,
            total_sales: 0,
           
           
            
            
            isLoading: true,
            search: "",
            to: "",
            from: "",
            sale_data: [],
            all_sale_data: []
        };
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
       
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
        viewDetails(data) {
            this.order_data_details = data;
            this.modal.details = true;
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/void_sale/fetch", {
                params: {
                    search: this.search,
                    from: this.from,
                    to: this.to
                }
            });
            this.hideLoader();
            if (res.status == 200) {
                var data = res.data;

                var data_array = [];
                res.data.map(array_item => {
                    data_array.push({
                        order_no: array_item.order_no,
                         order_date: array_item.order_date,
                        row_total: array_item.row_total,
                        row_vat: array_item.row_vat,
                        total_vat: array_item.total_vat,
                         cost_price: array_item.cost_price,
                          cost_total: array_item.cost_total,
                       
                        guest: array_item.guest
                            ? array_item.guest.name
                            : "",
                        cashier: array_item.cashier
                            ? array_item.cashier.name
                            : "",
                     
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
        async fetchTotals() {
            const res = await this.getApi(
                "data/void_sale/voidTotals",

                {
                    params: {
                        search: this.search,

                        from: this.from,
                        to: this.to
                    }
                }
            );

            if (res.status == 200) {
                const data = res.data;
                if (data) {
                    this.total_vat = data.sum_row_vat;
                    this.total_sales = data.sum_row_total;
                     this.total_cost = data.total_cost;
                   
                   
                }
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchData(page) {
            const res = await this.getApi(
                "data/void_sale/fetch",

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
                this.sale_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
                this.fetchTotals()
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
