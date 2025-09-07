<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="container" v-if="isReadPermitted">
                    <div class="card" v-if="active.dashboard">
                        <div class="card-header">
                            <h3><b>Purchase Returns</b></h3>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-center">
                                <div class="form-group">
                                    <label for="">Search </label>
                                    <input
                                        type="text"
                                        v-model="search"
                                        class="form-control"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Return Code</label>
                                    <input
                                        type="text"
                                        v-model="params.return_code"
                                        class="form-control"
                                    />
                                </div>
                                <div class="supp_dropdown">
                                    <label>Supplier Name*</label>

                                    <div class="form-group">
                                        <treeselect
                                            v-model="params.supplier_id"
                                            :multiple="false"
                                            :options="supplier_options"
                                            :show-count="true"
                                            placeholder="Select "
                                        />
                                    </div>
                                </div>
                                <div >
                                <button class="btn btn-warning btn-sm " style="margin-top:50%"  @click="concurrentApiReq()">Get Data</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered mt-3"
                                >
                                    <thead>
                                        <tr>
                                            <th scope="col">Return Code</th>
                                            <th scope="col">Order No</th>
                                            <th>Vendor</th>
                                            <th>Stock Code</th>
                                             <th>Stock </th>
                                            <th>Return Date</th>
                                           
                                            <th>User</th>

                                            <th scope="col">Qty Returned</th>
                                     
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(data, i) in order_data.data"
                                            :key="i"
                                        >
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        download(data)
                                                    "
                                                >
                                                    {{
                                                        data.return_code
                                                    }}</router-link
                                                >
                                            </td>
                                            <td>{{ data.order_no }}</td>
                                            <td>
                                                {{
                                                    data.supplier
                                                        ? data.supplier
                                                              .company_name
                                                        : ""
                                                }}
                                            </td>
                                            <td>
                                                {{data.stock.code}}
                                            </td>
                                            <td>{{
                                                data.stock.product_name}}</td>
                                            <td>{{ data.return_date }}</td>

                                            <td>
                                                {{
                                                    data.user
                                                        ? data.user.name
                                                        : ""
                                                }}
                                            </td>

                                            <td>
                                                {{ cashFormatter(data.qty) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <Pagination
                                v-if="order_data !== null"
                                v-bind:results="order_data"
                                v-on:get-page="fetchReturns"
                            ></Pagination>
                        </div>
                    </div>
                    <add-purchase-returns
                        v-if="active.add_purchase_returns"
                        :order_details="order_details"
                        v-on:dashboard-active="setActiveNoRefresh"
                    />
                </div>
                <center v-else>
                    <b style="color:red;font-size:1.2rem"
                        >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                    >
                </center>
            </div>
        </div>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSideNav from "../purchase/POSideNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import AddPurchaseReturns from "./AddPurchaseReturns.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_purchase_returns: false
            },
            order_details: null,
            search:'',
            params: {
                return_code: "",
                supplier_id: null
            },
            order_data: [],
            supplier_data: [],
            supplier_options: null
        };
    },
    components: {
        Treeselect,
        POSideNav,
        Pagination,
        AddPurchaseReturns
    },
    watch: {
        params: {
            handler() {
                this.searchRecords();
            },
            deep: true
        },
          search: {
          
            handler: _.debounce(function(val, old) {
                this.searchRecords();
            }, 500)
        }
    },
    mounted() {
       // this.concurrentApiReq();
    },
    methods: {
          async download(data) {
            this.showLoader();
            const dataObj = {
                return_code: data.return_code,
                supplier_id: data.supplier_id,
             
            };
            axios({
                url: "data/purchase_returns/downloadReturns",
                method: "POST",
                data: dataObj,
                responseType: "blob" // important
            }).then(response => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.getTimeStamp() + "_RN" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
                this.$emit("dashboard-active");
            });
        },
        viewDetails(data) {
            this.order_details = data;
            this.setActiveComponent("add_purchase_returns");
        },
        setActiveNoRefresh() {
            this.setActiveComponent("dashboard");
            this.concurrentApiReq();
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        async searchRecords() {
           var page=1
            const res = await this.getApi("data/purchase_returns/fetch", {
                params: {
                    page,
                    ...this.params,
                    search:this.search
                }
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([
                this.getAllSuppliers(),
                this.fetchReturns(1)
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();
        },
        async getAllSuppliers() {
            const res = await this.getApi("data/suppliers/fetchAll", "");

            if (res.status === 200) {
                this.supplier_data = res.data.results;
                this.supplier_options = this.modifyProductKey();
            } else {
                this.swr("Server error try again later");
            }
        },

        async fetchReturns(page) {
            const res = await this.getApi("data/purchase_returns/fetch", {
                params: {
                    page
                }
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        modifyProductKey() {
            let modif = this.supplier_data.map(obj => {
                return {
                    id: obj.id,
                    label: obj.company_name
                };
            });
            return modif;
        }
    }
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
</style>
