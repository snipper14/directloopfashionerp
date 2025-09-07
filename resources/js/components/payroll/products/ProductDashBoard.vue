<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <production-nav />
            </div>
            <div class="col-md-8">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_product')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                        </div>
                        <div class="col-md-5">
                            <h5>Products Records</h5>
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
                                    <th scope="col">Code</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Size</th>
                                    <th scope="col">pay_rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="small-tr"
                                    v-for="(data, i) in product_data.data"
                                    :key="i"
                                >
                                    <td>
                                        <router-link
                                            to="#"
                                            @click.native="editProduct(data)"
                                        >
                                            {{ data.product_code }}</router-link
                                        >
                                    </td>
                                    <td>
                                        {{ data.name }}
                                    </td>
                                    <td>{{ data.description }}</td>
                                    <td>{{ data.size }}</td>
                                    <td>{{ cashFormatter(data.pay_rate) }}</td>
                                    <td>
                                        <button
                                            v-if="isDeletePermitted"
                                            class="btn btn-danger btn-sm"
                                            @click="deleteRecord(data.id, i)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Pagination
                            v-if="product_data !== null"
                            v-bind:results="product_data"
                            v-on:get-page="fetchProducts"
                        ></Pagination>
                        Items Count {{ product_data.total }}

                        <div class="row">
                            <div class="col-4 d-flex">
                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="getAllProducts"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-success btn-sm">
                                        Export Excel
                                    </button>
                                </export-excel>

                                <export-excel
                                    v-if="isDownloadPermitted"
                                    class="btn btn-default btn-export ml-2 "
                                    :fetch="getAllProducts"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="filename.xls"
                                >
                                    <button class="btn btn-secondary btn-sm">
                                        Export CSV
                                    </button>
                                </export-excel>
                                <button
                                    v-if="isDownloadPermitted"
                                    class="btn btn-secondary btn-sm"
                                    @click="generateReport"
                                >
                                    Export pdf
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>

        <div>
            <vue-html2pdf
                :show-layout="false"
                :float-layout="true"
                :enable-download="true"
                :preview-modal="true"
                :paginate-elements-by-height="1400"
                filename="products_details"
                :pdf-quality="2"
                :manual-pagination="false"
                pdf-format="a4"
                pdf-orientation="landscape"
                pdf-content-width="95%"
                ref="html2Pdf"
            >
                <section slot="pdf-content">
                    <generate-product-pdf v-bind:pdf_data="pdf_data" />
                </section>
            </vue-html2pdf>
        </div>
        <add-product
            v-if="active.add_product"
            v-on:dashboard-active="setActiveRefresh"
        >
        </add-product>
        <edit-product
            v-if="active.edit_product"
            v-bind:data="edit_data"
            v-on:dashboard-active="setActiveRefresh"
        >
        </edit-product>
    </div>
</template>

<script>
import AddProduct from "./AddProduct";
import EditProduct from "./EditProduct";
import Pagination from "../../utilities/Pagination.vue";
import GenerateProductPdf from "./GenerateProductPdf.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import Unauthorized from "../../utilities/Unauthorized.vue";
import ProductionNav from "../employee_production/ProductionNav.vue";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_product: false,
                edit_product: false
            },
            product_data: [],
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
        AddProduct,
        EditProduct,
        VueHtml2pdf,
        Pagination,
        GenerateProductPdf,
        Unauthorized,
        ProductionNav
    },
    mounted() {
        this.fetchProducts(1);
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
        async deleteRecord(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/products/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.product_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editProduct(data) {
            this.edit_data = data;

            this.setActiveComponent("edit_product");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.getAllProducts();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async getAllProducts() {
            const res = await this.getApi("data/products/getAllProducts", {
                params: {
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });

            return res.data.results;
        },

        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/products/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params
                }
            });
            if (res.status === 200) {
                this.product_data = res.data.results;
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
            const res = await this.getApi("data/products/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params
                }
            });
            this.hideLoader();

            if (res.status === 200) {
                this.product_data = res.data.results;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
            }
        }
    }
};
</script>
<style scoped></style>
