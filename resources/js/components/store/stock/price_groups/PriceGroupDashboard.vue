<template>
    <v-app>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-2"><etim-stock-nav/></div>
                <div class="col-md-10">
                  
                    <div class="card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <div class="col-md-2">
                                <v-btn
                                    class="ma-2 v-btn-primary"
                                    @click="add_price_group_modal = true"
                                    color="primary"
                                    dark
                                >
                                    <v-icon medium>{{
                                        icons.mdiPlusThick
                                    }}</v-icon>
                                    Add
                                </v-btn>
                            </div>

                            <div class="col-md-3">
                                <h5>Price Groups</h5>
                            </div>
                            <div class="col-md-4 float-md-right">
                                <input
                                    type="text"
                                    placeholder="Search.."
                                    class="form-control small-input"
                                    v-model="params.search"
                                />
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table
                                    class="table table-sm table-striped table-bordered custom-table"
                                >
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th scope="col">Price Group</th>

                                            <th scope="col">Add Prices</th>
                                            <th scope="col">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            class="small-tr"
                                            v-for="(
                                                data, i
                                            ) in price_group_data.data"
                                            :key="i"
                                        >
                                            <td>{{ data.id }}</td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        editPriceGroup(data)
                                                    "
                                                >
                                                    {{
                                                        data.price_group
                                                    }}</router-link
                                                >
                                            </td>
                                            <td>
                                                <v-btn color="primary" small @click="addPrices(data)"
                                                    >Add Prices</v-btn
                                                >
                                            </td>
                                            <td>
                                                <router-link
                                                    to="#"
                                                    @click.native="
                                                        deleteData(data.id, i)
                                                    "
                                                    class="danger"
                                                >
                                                    Delete
                                                </router-link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <Pagination
                                v-if="price_group_data !== null"
                                v-bind:results="price_group_data"
                                v-on:get-page="fetch"
                            ></Pagination>
                            Items Count {{ price_group_data.total }}
                        </div>
                    </div>
                </div>
            </div>

            <Modal v-model="add_price_group_modal">
                <div class="form-group">
                    <label for="">Price Group</label>
                    <input
                        type="text"
                        v-model="form_data.price_group"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <button
                        class="btn btn-secondary btn-small"
                        small
                        @click="savePrice()"
                    >
                        Save
                    </button>
                </div>
            </Modal>
            <Modal v-model="edit_price_group_modal">
                <div class="form-group">
                    <label for="">Price Group</label>
                    <input
                        type="text"
                        v-model="edit_form_data.price_group"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <button
                        class="btn btn-secondary btn-small"
                        small
                        @click="updatePriceGroup()"
                    >
                        Save
                    </button>
                </div>
            </Modal>
            <Modal v-model="add_prices_modal" width="1000px">
                <add-prices-modal  
                v-if="add_prices_modal"
                :edit_data="edit_data"
                />
            </Modal>
            <notifications group="foo" />
        </div>
    </v-app>
</template>

<script>
import Pagination from "../../../utilities/Pagination.vue";

import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import StockNav from "../../StockNav.vue";
import AddPricesModal from "./AddPricesModal.vue";
import EtimStockNav from '../etims_stock/EtimStockNav.vue';

export default {
    data() {
        return {
            btn_loading: false,
            add_price_group_modal: false,
            edit_price_group_modal: false,
            add_prices_modal: false,
            price_group_data: [],
            edit_data: null,

            params: {
                search: "",
            },
            form_data: {
                price_group: "",
            },
            edit_form_data: {
                id: null,
                price_group: "",
            },
            show_loader: true,
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
        StockNav,
        AddPricesModal,
        EtimStockNav,
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        params: {
            handler() {
                this.show_loader = false;
                this.concurrentApiReq();
            },
            deep: true,
        },
    },
    methods: {
        async addPrices(data){
            this.edit_data=data
            this.add_prices_modal=true
        },
        async editPriceGroup(data) {
            this.edit_form_data = data;
            this.edit_price_group_modal = true;
        },
        async savePrice() {
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/price_groups/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Saved ");
                this.add_price_group_modal = false;
                this.concurrentApiReq();
            } else {
                this.form_error(res);
            }
        },
        async updatePriceGroup() {
            this.showLoader();
            const res = await this.callApi(
                "put",
                "data/price_groups/update",
                this.edit_form_data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Updated ");
                this.edit_price_group_modal = false;
                this.concurrentApiReq();
            } else {
                this.form_error(res);
            }
        },
        async concurrentApiReq() {
            this.show_loader ? this.showLoader() : null;
            await Promise.all([this.fetch(1)]);
            this.show_loader ? this.hideLoader() : null;
        },
        async fetch(page) {
            const res = await this.getApi("data/price_groups/fetch", {
                params: {
                    page: page,
                    ...this.form_data,
                },
            });

            if (res.status === 200) {
                this.price_group_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/price_groups/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.price_group_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
    },
};
</script>
<style scoped>
.show-img {
    border-color: #ff6f00;
    background: #ff6f00;
}
.small-tr {
    padding: 0px !important;
    margin: 0px !important;
}
</style>
