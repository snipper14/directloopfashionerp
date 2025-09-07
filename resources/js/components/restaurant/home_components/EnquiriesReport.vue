<template>
    <div class="container">
        <div class="row justify-content-center">
          
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div class="card-header">
                        <h3><b>Enquiries Report</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content around mt-3">
                            <div class="d-flex flex-column">
                                <label> From Datetime *</label>

                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="from"
                                ></date-picker>
                            </div>
                            <div class="d-flex flex-column">
                                <label> To Datetime *</label>
                                <date-picker
                                    type="datetime"
                                    width="300px"
                                    v-model="to"
                                ></date-picker>
                            </div>
                            <div>
                                <button
                                    class="btn btn-primary btn-sm mt-4"
                                    @click="concurrentApiReq()"
                                >
                                    Filter
                                </button>
                               
                            </div>
                        </div>
                        <div class="row">
                            
                        
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="">Search..</label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                            </div>
                        </div>

                      
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered"
                            >
                                <thead>
                                    <tr>
                                      
                                        <th>Created at</th>
                                        <th scope="col">name</th>
                                        <th>email</th>
                                      
                                        <th scope="col">details</th>

                                   
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in table_booking_data.data"
                                        :key="i"
                                    >
                                   
                                        <td>{{ formatDateTime(data.created_at) }}</td>

                                        <td>
                                         
                                         {{data.name}}
                                        </td>
                                        <td>
                                           {{data.email}}
                                        </td>
                                       
                                        
                                        <td>
                                           {{data.message}}
                                        </td>

                                     

                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <pagination
                            v-if="table_booking_data !== null"
                            v-bind:results="table_booking_data"
                            v-on:get-page="fetchData"
                        ></pagination>
                        Items Count {{ table_booking_data.total }}
                        <div class="row">
                            <div
                                class="col-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2"
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
                                    class="btn btn-default btn-export ml-2"
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
      
      
        <notifications group="foo" />
    </div>
</template>

<script>

import datetime from "vuejs-datetimepicker";
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

import Pagination from '../../utilities/Pagination.vue';
import Unauthorized from '../../utilities/Unauthorized.vue';
export default {
    components: {
    
      Pagination,
        datetime,
        Treeselect,
     
        Unauthorized,
    },
    data
        () {
        
        return {
          
           
            search: "",
            table_booking_data: [],
        
            from: null,
            to: null,
      
            isLoading: true,
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        
        search: {
            handler: _.debounce(function () {
                this.isLoading = false;
                this.concurrentApiReq();
            }, 500),
        },
    },
    methods: {
    
   
     
       
    
      
        async fetchData(page) {
            const res = await this.getApi(
                "enquiries/fetch",

                {
                    params: {
                       
                        search: this.search,
                        page,
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                this.table_booking_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.isLoading ? this.showLoader() : "";

            const res = await Promise.all([
                this.fetchData(1),
            ]).then(function (results) {
                return results;
            });

            this.isLoading ? this.hideLoader() : "";
        },
        

      

    
        async fetchExcel() {
            const res = await this.getApi(
                "enquiries/fetch",

                {
                    params: {
                       
                        search: this.search,
                    
                        from: this.formatDateTime(this.from),
                        to: this.formatDateTime(this.to),
                    },
                }
            );

            if (res.status == 200) {
                var data = res.data;

                return data;
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
