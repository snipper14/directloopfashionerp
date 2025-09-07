<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <POSideNav />
            </div>
            <div class="col-md-10">
                <div class="container">
                    <div v-if="active.dashboard">
                        <v-btn color="primary" @click="viewItemizedReport()"
                            >View Itemized Report</v-btn
                        >
                        <div class="card">
                            <div class="card-header">
                                <h3><b> GRN RETURNS REPORT</b></h3>
                            </div>

                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <div class="form-group">
                                        <label for="">Search</label>
                                        <input
                                            type="text"
                                            v-model="params.search"
                                            class="form-control"
                                        />
                                    </div>
                                   
                                   
                                </div>
                                <div class="row">
                                    <div class="d-flex flex-column">
                                        <label> From Datetime *</label>

                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.from"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column mr-2">
                                        <label> To Datetime *</label>
                                        <date-picker
                                            valueType="format"
                                            width="300px"
                                            v-model="params.to"
                                        ></date-picker>
                                    </div>
                                    <div class="d-flex flex-column mr-2">
                                        <span
                                            class="badge badge-info totals-badge"
                                        >
                                           Qty:
                                            {{
                                                cashRounded(
                                                    grn_totals_data.total_qty
                                                )
                                            }}</span
                                        >
                                    </div>
                                     <div class="d-flex flex-column mr-2">
                                        <span
                                            class="badge badge-info totals-badge"
                                        >
                                            Total Tax:
                                            {{
                                                cashRounded(
                                                    grn_totals_data.sum_tax_amount
                                                )
                                            }}</span
                                        >
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="badge badge-primary totals-badge"
                                        >
                                            Total  :
                                            {{
                                                cashRounded(
                                                    grn_totals_data.total_sub_total
                                                )
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div
                                    class="table-responsive"
                                    v-if="grouped_report"
                                >
                                    <table
                                        class="table table-sm table-striped table-bordered mt-3 custom-table"
                                    >
                                        <thead>
                                            <tr>
                                                <th scope="col">Delivery No</th>
                                                <th scope="col">Order No</th>
                                                <th>Vendor</th>
                                                <th>Return Date</th>

                                                <th>User</th>

                                                <th scope="col">Qty Return</th>
                                                <th>Total</th>
                                                <th>Tax Total</th>

                                                <th>Download</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                class="small-tr"
                                                v-for="(
                                                    data, i
                                                ) in order_data.data"
                                                :key="i"
                                            >
                                                <td>
                                                    <router-link
                                                        to="#"
                                                        @click.native="
                                                            viewDetails(data)
                                                        "
                                                    >
                                                        {{
                                                            data.return_code
                                                        }}</router-link
                                                    >
                                                </td>
                                                <td>
                                                    {{ data.delivery_no }}
                                                </td>
                                                <td>
                                                    {{
                                                        data.supplier
                                                            ? data.supplier
                                                                  .company_name
                                                            : ""
                                                    }}
                                                </td>
                                                <td>
                                                    {{ data.return_date }}
                                                </td>

                                                <td>
                                                    {{
                                                        data.user
                                                            ? data.user.name
                                                            : ""
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.total_qty
                                                        )
                                                    }}
                                                </td>

                                                <td>
                                                    {{
                                                        cashFormatter(
                                                            data.total_sub_total
                                                        )
                                                    }}
                                                </td>
                                                <td>
                                                    {{
                                                        cashRounded(
                                                            data.sum_tax_amount
                                                        )
                                                    }}
                                                </td>
                                                 <td>
                                                    <v-btn
                                                      
                                                        @click="
                                                            downloadPO(data)
                                                        "
                                                        color="primary"
                                                        x-small
                                                    >
                                                        Download GRN
                                                    </v-btn>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <Pagination
                                    v-if="order_data !== null"
                                    v-bind:results="order_data"
                                    v-on:get-page="fetchDeliveryNotes"
                                ></Pagination>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" small
                                        >Export Excel</v-btn
                                    >
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <v-btn color="primary" small
                                        >Export CSV</v-btn
                                    >
                                </export-excel>
                            </div>
                        </div>
                    </div>
                   
                </div>
            </div>
        </div>
<Modal
            title="Returns"
            v-model="itemized_modal"
            width="1200px"
        >  <GRNReturnsItemizedReport
                        v-if="itemized_modal"
                      
                    />
                   </Modal>
        <Modal
            title="Returns Details"
            v-model="grn_returns_modal"
            width="1000px"
        >
            <ReturnGRNDetailsReport
                v-if="grn_returns_modal"
                :delivery_details="delivery_details"
            />
        </Modal>
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";
import POSideNav from "../purchase/POSideNav.vue";
import Pagination from "../../utilities/Pagination.vue";
import DownloadGrn from "./DownloadGrn.vue";
import DeliveryDetails from "./DeliveryDetails.vue";
import GRNReturnsItemizedReport from "./GRNReturnsItemizedReport.vue";
import AddgrnReturnsComponent from "./AddgrnReturnsComponent.vue";
import ReturnGRNDetailsReport from "./ReturnGRNDetailsReport.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                itemized_report: false,
                print_grn: false,
                return_grn: false,
            },
itemized_modal:false,
            grouped_report: true,
            itemized_report: false,
            show_loading: true,
            grn_returns_modal: false,
            grn_download_data: null,
            order_details: null,
            delivery_details: null,
            grn_totals_data: {},
            params: {
                order_no: "",
                delivery_no: "",
                supplier_id: null,
                from: null,
                to: null,
            },
            order_data: [],
            supplier_data: [],
            supplier_options: null,
        };
    },
    components: {
        Treeselect,
        POSideNav,
        Pagination,
        DownloadGrn,
        DeliveryDetails,
        GRNReturnsItemizedReport,
        AddgrnReturnsComponent,
        ReturnGRNDetailsReport,
    },
    watch: {
        params: {
            handler() {
                this.show_loading = false;
                this.concurrentApiReq();
            },
            deep: true,
        },
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        generateReturnsGRN(data) {
            this.delivery_details = data;
            this.setActive(this.active, "return_grn");
        },
        viewItemizedReport() {
            this.itemized_modal=true
          
           // this.setActive(this.active, "itemized_report");
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.concurrentApiReq();
        },
        viewDetails(data) {
            this.delivery_details = data;
            this.grn_returns_modal = true;
        },
    async downloadPO(data) {
            this.showLoader();
            const dataObj = {
                return_code: data.return_code,
              
            };
            axios({
                url: "data/grn_returns/downloadDN",
                method: "POST",
                data: dataObj,
                responseType: "blob", // important
            }).then((response) => {
                this.hideLoader();
                const url = window.URL.createObjectURL(
                    new Blob([response.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.setAttribute(
                    "download",
                    this.getTimeStamp() + "_returns" + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
        },
      

        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },

        async concurrentApiReq() {
            this.show_loading ? this.showLoader() : null;
            await Promise.all([
                this.fetchDeliveryNotes(1),
                this.getAllSuppliers(),
                this.fetchTotal(),
            ])
            this.show_loading ? this.hideLoader() : null;
        },
        async fetchTotal() {
            const res = await this.getApi(
                "data/grn_returns/fetchTotal",
                {
                    params: {
                        ...this.params,
                    },
                }
            );
            this.grn_totals_data = res.data;
        },
        async fetchExcel() {
            const res = await this.getApi("data/grn_returns/fetchGrouped", {
                params: {
                    ...this.params,
                },
            });
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    delivery_no: data.delivery_no,
                    order_no: data.order_no,
                    supplier: data.supplier.company_name,

                    user: data.user.name,

                    total_qty: data.total_qty,
                    total_sub_total: data.total_sub_total,
                    sum_tax_amount: data.sum_tax_amount,

                    return_date: data.return_date,
                });
            });
            return data_array;
        },
        modifyProductKey() {
            let modif = this.supplier_data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.company_name,
                };
            });
            return modif;
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

        async fetchDeliveryNotes(page) {
            const res = await this.getApi("data/grn_returns/fetchGrouped", {
                params: {
                    page,
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.order_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped>
.supp_dropdown {
    width: 40% !important;
}
#printMe {
    visibility: hidden;
}
</style>
