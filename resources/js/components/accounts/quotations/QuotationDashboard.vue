<template>
    <div>
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
                <quotation-nav />
            </div>

            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header-wrapper">
                        <h4>Quotations</h4>
                        <form class="form-inline">
                            <input
                                class="form-control mr-sm-2"
                                type="search"
                                v-model="search"
                                placeholder="Search Customer,Order,Date"
                                aria-label="Search"
                            />
                        </form>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th></th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Quote No</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Items Qty</th>
                                    <th scope="col">Total Amount</th>

                                   
                                    <th>Created at</th>
                                    <th>Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in orders_data.data"
                                    :key="i"
                                >
                                  <td>
                                        <v-btn
                                            color="danger"
                                            x-small
                                            @click="
                                                deleteQuote(data.order_no, i)
                                            "
                                            >Delete</v-btn
                                        >
                                        <v-btn
                                            x-small
                                            color="primary"
                                            v-if="isDownloadPermitted"
                                            title="download"
                                            @click="getPrintData(data)"
                                        >
                                            print quotation
                                        </v-btn>
                                    </td>
                                    <td>
                                        {{ data.customer.company_name }}
                                    </td>
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="updateOrder(data)"
                                            >{{ data.order_no }}
                                        </router-link>
                                    </td>
                                    <td>{{ data.order_date }}</td>
                                    <td>{{ data.sum_qty }}</td>
                                    <td>
                                        {{ cashFormatter(data.sum_row_total) }}
                                    </td>

                                  
                                    <td>{{formatMonthDateTime(data.created_at)}}</td>
                                     <td>{{data.user.name}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="orders_data !== null"
                            v-bind:results="orders_data"
                            v-on:get-page="fetchOrders"
                        ></Pagination>
                        Items Count {{ orders_data.total }}
                    </div>
                </div>
            </div>
            <div v-else>
                <unauthorized />
            </div>
        </div>

        <Modal v-model="update_quote_modal" width="800px">
            <update-quotation
                :data="edit_order_data"
                v-if="update_quote_modal"
                v-on:dashboard-active="setActiveRefresh"
        /></Modal>
          <QuotePrintTemplate
            v-if="show_print"
            ref="QuotePrintTemplate"
            :print_data="print_data"
        
        />
        <notifications group="foo" />
    </div>
</template>
<script>
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import QuotationNav from "./QuotationNav.vue";
import UpdateQuotation from "./UpdateQuotation.vue";
import QuotePrintTemplate from './QuotePrintTemplate.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_customer: false,
                edit_customer: false,
                edit_order: false,
                new_invoice: false,
            },
            show_print:false,
            update_quote_modal: false,
            edit_order_data: null,
            orders_data: [],
            edit_data: null,
            pdf_data: [],
            print_data:null,

            invoice_data: [],
            search: "",
            params: {
                name: "",
                description: "",
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    created() {},
    components: {
        Pagination,
        Unauthorized,
        QuotationNav,
        UpdateQuotation,
        QuotePrintTemplate,
    },
    mounted() {
        this.fetchOrders(1);
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true,
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        },
    },
    methods: {
        async deleteQuote(order_no, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/quote/cancelOrders",
                    {
                        order_no: order_no,
                    }
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.orders_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async getPrintData(data) {
            this.showLoader()
            const res = await this.getApi("data/quote/getPrintData", {
                params: {
                    order_no: data.order_no,
                },
            });
            this.hideLoader()
            if(res.status==200){
                this.print_data=res.data
              this.show_print = true;
                setTimeout(() => {
                    this.$refs.QuotePrintTemplate.printBill();
                    this.show_print=false
                }, 1000);
            }else{

            }
        },
        async generateInvoice(data) {
            this.showLoader();
            axios({
                url: "data/quote/generateQuote/" + data.order_no,
                method: "GET",
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
                    "quotation_" + data.order_no + ".pdf"
                );
                document.body.appendChild(link);
                link.click();
            });
            //  this.$refs.html2Pdf.generatePdf();
        },

        async updateOrder(data) {
            this.edit_order_data = data;

            this.update_quote_modal = true;
        },

        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllSales();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllSales() {
            const res = await this.getApi("data/sales/fetchAll", "");
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                    company_name: array_item.customer.company_name,
                    order_date: array_item.order_date,
                    order_no: array_item.order_no,
                    qty: array_item.sum_qty,
                    order_total: array_item.order_total,
                });
            });
            return data_array;
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/quote/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.orders_data = res.data;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.update_quote_modal = false;
        },

        async fetchOrders(page) {
            this.showLoader();
            const res = await this.getApi("data/quote/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.orders_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
