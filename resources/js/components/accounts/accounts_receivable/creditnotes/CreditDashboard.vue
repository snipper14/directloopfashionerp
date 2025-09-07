<template>
    <div class="container">
        <div>
            <div class="row justify-content-center" v-if="active.dashboard">
                <div class="col-2">
                    <receivable-nav />
                </div>
                <div class="col-md-10">
                    <div class="card" v-if="isReadPermitted">
                        <div class="card-header-wrapper">
                            <span class="flex justify-space-between"
                                ><button
                                    v-if="isWritePermitted"
                                    @click="addCreditNotes()"
                                    class="btn btn-primary button-custom"
                                >
                                    Add Credit Note
                                </button>
                            </span>
                            <h4>Credit Notes</h4>
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
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">Credit Note No.</th>
                                        <th scope="col">Invoice No</th>
                                        <th scope="col">Total Credit<sort-buttons
                                                :initial-sort="
                                                    sort_params.sort_total
                                                "
                                                sort-key="sort_total"
                                                @update-sort="
                                                    updateSortParameter
                                                "
                                            /></th>
                                        <th scope="col">Total items</th>
                                        <th scope="col">Approve</th>

                                        <th scope="col">Cancel</th>
                                        <th>Add Notes</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(data, i) in credit_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            {{
                                                data.invoice.customer
                                                    ? data.invoice.customer
                                                          .company_name
                                                    : ""
                                            }}
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    viewCreditNote(data)
                                                "
                                            >
                                                {{
                                                    data.credit_no
                                                }}</router-link
                                            >
                                        </td>
                                        <td>
                                            {{ data.invoice_no }}
                                        </td>
                                        <td>
                                            {{
                                                cashFormatter(data.credit_total)
                                            }}
                                        </td>
                                        <td>{{ data.credit_qty }}</td>
                                        <td>
                                            <v-btn v-if=" data.approved == 0" color="primary" small :loading="btn_loader" @click="approveDebitNote(data)">Approve </v-btn>
                                                    
                                             
                                            <span v-else>Approved</span>
                                        </td>
                                        <td>
                                            <router-link
                                                v-if="
                                                    data.approved == 1 &&
                                                    isDeletePermitted
                                                "
                                                to="#"
                                                @click.native="
                                                    cancelDebitNote(data)
                                                "
                                            >
                                                Cancel
                                            </router-link>
                                        </td>
                                        <td>
                                            <v-btn
                                                title="write"
                                                v-if="isWritePermitted"
                                                x-small
                                                color="warning"
                                                @click="addNotes(data)"
                                            >
                                                Add Note
                                            </v-btn>
                                        </td>
                                        <td>{{data.notes}}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <Pagination
                                v-if="credit_data !== null"
                                v-bind:results="credit_data"
                                v-on:get-page="fetchCreditNote"
                            ></Pagination>
                            Items Count {{ credit_data.total }}

                            <div class="row">
                                <div class="col-4 d-flex">
                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="allCreditNotes"
                                        worksheet="My Worksheet"
                                        name="filename.xls"
                                    >
                                        <v-icon medium>{{
                                            icons.mdiMicrosoftExcel
                                        }}</v-icon>
                                        Export Excel
                                    </export-excel>

                                    <export-excel
                                        class="btn btn-default btn-export ml-2"
                                        :fetch="allCreditNotes"
                                        worksheet="My Worksheet"
                                        type="csv"
                                        name="filename.xls"
                                    >
                                        <v-icon class="v-icon" medium>{{
                                            icons.mdiFileExcel
                                        }}</v-icon>
                                        Export CSV
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
        </div>

        <add-credit-note
            v-if="active.add_credit_note"
            v-on:dashboard-active="setActiveRefresh"
        />
        <view-print-credit-notes
            v-if="active.view_print"
            :data="creditnote_details"
            v-on:dashboard-active="setActiveRefresh"
        />
        <Modal v-model="add_notes_modal">
            <div v-if="add_notes_modal">
                <div class="form-group">
                    <label for=""
                        >Credit Notes {{ line_details.credit_no }}</label
                    >
                    <input type="text" v-model="notes" class="form-control" />
                </div>
                <button class="btn btn-primary btn-small" @click="saveNotes()">
                    Save
                </button>
            </div>
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import AddCreditNote from "./AddCreditNote";
import Pagination from "../../../utilities/Pagination.vue";
import ViewPrintCreditNotes from "./ViewPrintCreditNotes";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import ViewDeliveryNotes from "../delivery/ViewDeliveryNotes.vue";
import ReceivableNav from "../ReceivableNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import SortButtons from '../../../utilities/SortButtons.vue';
export default {
    data() {
        return {
            btn_loader:false,
            active: {
                dashboard: true,
                add_credit_note: false,
                view_print: false,
            },
sort_params:{
    sort_total:""
},
            new_data: null,
            credit_data: [],
            invoice_data: null,
            creditnote_details: null,
            search: "",
            add_notes_modal: false,
            line_details: null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        Pagination,
        AddCreditNote,
        ReceivableNav,
        ViewDeliveryNotes,
        ViewPrintCreditNotes,
        Unauthorized,
        SortButtons,
        ReceivableNav,
    },
    mounted() {
        this.fetchCreditNote(1);
    },
    watch: {
        params: {
            handler() {
                this.searchRecord();
            },
            deep: true,
        },
         sort_params: {
            deep: true,
            handler: _.debounce(function () {
           
                this.fetchCreditNote(1);
            }, 500),
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchRecord();
            }
        },
    },
    methods: {
         updateSortParameter(val) {
            this.sortParameter(val.key, val.value);
        },
       
        addNotes(data) {
            this.line_details = data;
            this.add_notes_modal = true;
        },
        async saveNotes() {
            const res = await this.callApi("post", "data/credit/saveNotes", {
                notes: this.notes,
                credit_no: this.line_details.credit_no,
            });

            if (res.status === 200) {
                this.s("successfully saved");
                this.line_details = null;
                this.add_notes_modal = false;
            } else {
                this.form_error(res);
            }
        },
        async viewCreditNote(data) {
            this.creditnote_details = data;

            this.setActiveComponent("view_print");
        },
        async approveDebitNote(data) {
           this.btn_loader=true
            const res = await this.callApi("post", "data/credit/approve", {
                credit_no: data.credit_no,
                invoice_no: data.invoice_no,
                credit_total: data.credit_total,
            });
          this.btn_loader=false
            if (res.status == 200) {
                this.fetchCreditNote(1);
            } else {
                this.swr("Server error occured");
            }
        },
        async cancelDebitNote(data) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                this.showLoader;
                const res = await this.callApi("post", "data/credit/cancel", {
                    credit_no: data.credit_no,
                    invoice_no: data.invoice_no,
                    credit_total: data.credit_total,
                });
                this.hideLoader();
                if (res.status == 200) {
                    this.fetchCreditNote(1);
                } else {
                    this.swr("Server error occured");
                }
            }
        },
        addCreditNotes() {
            this.setActiveComponent("add_credit_note");
        },
        async allCreditNotes() {
            this.showLoader();
            const res = await this.getApi("data/credit/fetchAll", "");
            this.hideLoader();
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                    company_name: array_item.invoice.customer.company_name,
                    items: array_item.invoice.stock.name,

                    qty: array_item.qty,
                    item_price: array_item.price,

                    row_total: array_item.row_total,
                    invoice_no: array_item.invoice.invoice_no,
                    order_no: array_item.invoice.order_no,
                    invoice_date: array_item.invoice.invoice_date,
                    order_date: array_item.invoice.order_date,
                    user: array_item.user.name,
                });
            });
            return data_array;
        },

        async searchRecord() {
            let page = 1;
            const res = await this.getApi("data/credit/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                    ...this.sort_params
                },
            });
            if (res.status === 200) {
                this.credit_data = res.data.results;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function () {
            this.setActive(this.active, "dashboard");
            this.fetchCreditNote(1);
        },

        async fetchCreditNote(page) {
            this.showLoader();
            const res = await this.getApi("data/credit/fetch", {
                params: {
                    page,
                    search: this.search.length >= 3 ? this.search : "",
                    ...this.params,
                    ...this.sort_params
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.credit_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        },
    },
};
</script>
<style scoped></style>
