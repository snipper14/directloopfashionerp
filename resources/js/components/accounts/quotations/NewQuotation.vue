<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-2">
           <quotation-nav/>
        </div>
            <div class="col-md-10"  >
               
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                            v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="$router.push('customers')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Create Customer
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Generate Order</h5>
                        </div>
                        <div class="col-md-4 float-md-right ">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <table
                            class="table table-sm table-striped table-bordered"
                        >
                            <thead>
                                <tr>
                                    <th scope="col">Company</th>
                                    <th scope="col">Co. Contacts</th>
                                    <th scope="col">Co. Address</th>
                                    
                                  
                                    <th scope="col">C. Email</th>
                                    <th scope="col">Contact Person</th>
                                    <th scope="col">Contact Email</th>
                                    <th scope="col">Contact Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in customer_data.data"
                                    :key="i"
                                >
                                    <td>
                                        {{ data.company_name }}
                                    </td>
                                    <td>{{ data.company_phone }}</td>
                                    <td>{{ data.address }}</td>
                                   
                                    
                                    <td>{{ data.company_email }}</td>
                                    <td>{{ data.contact_person }}</td>
                                    <td>{{ data.contact_email }}</td>
                                    <td>{{ data.contact_phone }}</td>
                                    <td>
                                        <v-btn
                                        v-if="isWritePermitted"
                                            color="primary"
                                            x-small
                                            @click.native="newQuote(data)"
                                            >New Quotation</v-btn
                                        >
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <pagination
                            v-if="customer_data !== null"
                            v-bind:results="customer_data"
                            v-on:get-page="fetchCustomers"
                        ></pagination>
                        Items Count {{ customer_data.total }}

                        
                    </div>
                </div>
                <center v-else><b style="color:red;font-size:1.2rem"><unauthorized/></b></center>
            </div>
        </div>

      
        <add-quote-details
            v-if="active.add_order"
            :data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        />
    </div>
</template>

<script>



import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import Unauthorized from '../../utilities/Unauthorized.vue';
import QuotationNav from './QuotationNav.vue';
import Pagination from '../../utilities/Pagination.vue';
import AddQuoteDetails from './AddQuoteDetails.vue';

export default {
    data() {
        return {
            active: {
                dashboard: true,
               
                add_order: false,
              
            },
            customer_data: [],
            edit_data: null,
            pdf_data: [],

            search: "",
            params: {
                name: "",
                description: ""
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
       
        Unauthorized,
        QuotationNav,
        Pagination,AddQuoteDetails
       
    },
    mounted() {
       this.fetchCustomers(1);
    },
    watch: {
        params: {
            handler() {
                this.searchProducts();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProducts();
            }
        }
    },
    methods: {
        
        newQuote(data) {
            this.edit_data = data;
            this.setActiveComponent("add_order");
        },
     
     

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.customer_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchCustomers(1);
        },

        async fetchCustomers(page) {
            this.showLoader();
            const res = await this.getApi("data/customers/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.customer_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
