<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <production-nav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_production')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5 style="color:red">Production</h5>
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
                                    <th scope="col">Prod Date</th>
                                    <th scope="col">Product</th>
                                      <th scope="col">Code</th>
                                    <th scope="col">Employee</th>
                                    <th scope="col">qty</th>
                                    <th scope="col">Total Pay</th>
                                  
                                    <th scope="col">description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in production_data.data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="editProduction(data)"
                                        >
                                            {{ data.produced_on }}</router-link
                                        >
                                    </td>
                                    <td>
                                        <span>{{
                                            data.stock.name
                                        }}</span>
                                    </td>
                                     <td>
                                        <span >{{
                                            data.stock.code
                                        }}</span>
                                    </td>
                                    <td>
                                        {{
                                            data.employee.first_name +
                                                " " +
                                                data.employee.last_name
                                        }}
                                    </td>
                                    <td>{{ data.qty }}</td>
                                    <td>{{ cashFormatter(data.total_pay) }}</td>
                                 
                                    <td>{{ data.description }}</td>
                                    <button
                                        v-if="isDeletePermitted"
                                        class="btn btn-danger btn-sm"
                                        @click="deleteRecord(data.id, i)"
                                    >
                                        Delete
                                    </button>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="production_data !== null"
                            v-bind:results="production_data"
                            v-on:get-page="fetchProducts"
                        ></Pagination>
                        Items Count {{ production_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="generateExcel"
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
                                    :fetch="generateExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-primary btn-sm">
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

        <add-production
            v-if="active.add_production"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-production>
        <edit-production
            v-if="active.edit_production"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-production>
    </div>
</template>

<script>
import AddProduction from "./AddProduction";
import EditProduction from "./EditProduction";
import Pagination from "../../utilities/Pagination.vue";
import GenerateProductionPdf from "./GenerateProductionPdf.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import Unauthorized from "../../utilities/Unauthorized.vue";
import ProductionNav from "./ProductionNav.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_production: false,
                edit_production: false
            },
            production_data: [],
            edit_data: null,
            pdf_data: [],

            search: "",
            params: {},
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        AddProduction,
        EditProduction,
        VueHtml2pdf,
        Pagination,
        GenerateProductionPdf,
        Unauthorized,
        ProductionNav
    },
    mounted() {
        this.fetchProducts(1);
    },
    watch: {
        params: {
            handler() {
                this.searchProduction();
            },
            deep: true
        },
        search(val, old) {
            if (val.length >= 2 || old.length >= 2) {
                this.searchProduction();
            }
        }
    },
    methods: {
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/production/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.production_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        beforeDownload(event) {},
        async generateExcel() {
            const res = await this.getAllProduction();

            var data_array = [];
            res.map(array_item => {
                data_array.push({
                    production_date: array_item.produced_on,
                    first_name: array_item.employee.first_name,
                    last_name: array_item.employee.last_name,
                    product:
                        array_item.payroll_product.name,
                    Qty: array_item.qty,
                    total_pay: array_item.total_pay,
                    size: array_item.size,
                    color: array_item.colour,
                    description: array_item.description
                });
            });

            return data_array;
        },
        editProduction(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_production");
        },
       
        async getAllProduction() {
            this.showLoader();
            const res = await this.getApi("data/production/getAllProduction", {
                params: {
                     search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();
            return res.data.results;
        },

        async searchProduction() {
            let page = 1;
            const res = await this.getApi("data/production/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.production_data = res.data.results;
            }
        },
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
            this.fetchProducts(1);
        },

        async fetchProducts(page) {
            this.showLoader();
            const res = await this.getApi("data/production/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.production_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
