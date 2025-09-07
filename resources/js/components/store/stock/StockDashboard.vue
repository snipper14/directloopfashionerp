<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2" v-if="isReadPermitted">
                <etim-stock-nav />
            </div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <div class="col-md-3">
                            <h5>Products/Stock Management</h5>
                        </div>
                        <div class="col-md-4 float-md-right">
                            <input
                                type="text"
                                placeholder="Search.."
                                class="form-control small-input"
                                v-model="search"
                            />
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="col-md-2 d-flex flex-row">
                            <v-btn
                                v-if="isWritePermitted"
                                class="ma-2 v-btn-primary"
                                @click="setActiveComponent('add_menu_item')"
                                color="primary"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Add
                            </v-btn>
                            <v-btn
                                v-if="isUpdatePermitted && branch.etims_type == 'oscu'"
                                class="ma-2 v-btn-primary"
                                @click="bulkAddItems(0)"
                                color="warning"
                                title="UPDATE"
                                dark
                            >
                                <v-icon medium>{{ icons.mdiPlusThick }}</v-icon>
                                Bulk Add Items OSCU(Etims)
                            </v-btn>

                            <v-btn
                                v-if="false"
                                color="danger"
                                title="DELETE & ADMIN"
                                @click="deleteAllStock()"
                                >Delete All Records</v-btn
                            >
                            <v-btn
                                @click="syncAllStockToEtims"
                                v-if="branch.etims_type == 'vscu'"
                                >Sync to vscu</v-btn
                            >
                        </div>
                        <div class="table-responsive">
                            <table
                                class="table table-sm table-striped table-bordered custom-table"
                            >
                                <thead>
                                    <tr>
                                        <th>Copy</th>
                                        <th>ID</th>
                                        <th scope="col">
                                            Code
                                            <v-btn
                                                v-if="params.sort_code == 'ASC'"
                                                x-small
                                                @click="codeSort('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="codeSort('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th scope="col">
                                            Item Name
                                            <v-btn
                                                v-if="
                                                    params.sort_product == 'ASC'
                                                "
                                                x-small
                                                @click="productSort('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="productSort('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th scope="col">
                                            Unit
                                            <v-btn
                                                v-if="params.sort_unit == 'ASC'"
                                                x-small
                                                @click="reorder('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="reorder('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>

                                        <th scope="col">
                                            Category
                                            <v-btn
                                                v-if="
                                                    params.sort_category ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sort_category('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sort_category('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>

                                        <th scope="col">
                                            Purchase P
                                            <v-btn
                                                v-if="
                                                    params.sort_purchase ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="sort_purchase('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sort_purchase('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th scope="col">
                                            Selling P
                                            <v-btn
                                                v-if="
                                                    params.sort_selling == 'ASC'
                                                "
                                                x-small
                                                @click="sort_selling('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sort_selling('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            % Settings Margin
                                            <v-btn
                                                v-if="
                                                    params.sort_settings_margin ==
                                                    'ASC'
                                                "
                                                x-small
                                                @click="
                                                    sort_settings_margin('DESC')
                                                "
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="
                                                    sort_settings_margin('ASC')
                                                "
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            %Margin
                                            <v-btn
                                                v-if="
                                                    params.sort_margin == 'ASC'
                                                "
                                                x-small
                                                @click="sort_margin('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sort_margin('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th scope="col">
                                            Tax Dept
                                            <v-btn
                                                v-if="
                                                    params.sort_taxdept == 'ASC'
                                                "
                                                x-small
                                                @click="sort_taxdept('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sort_taxdept('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>
                                        <th>
                                            tax id
                                            <v-btn
                                                v-if="
                                                    params.sort_taxid == 'ASC'
                                                "
                                                x-small
                                                @click="sort_taxid('DESC')"
                                                >DESC</v-btn
                                            ><v-btn
                                                v-else
                                                x-small
                                                @click="sort_taxid('ASC')"
                                                >ASC</v-btn
                                            >
                                        </th>

                                        <th>Img</th>
                                        <th>Item Discount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="small-tr"
                                        v-for="(
                                            data, i
                                        ) in menu_product_data.data"
                                        :key="i"
                                    >
                                        <td>
                                            <div
                                                class="btn-group custom-dropdown"
                                            >
                                                <v-btn
                                                    color="primary"
                                                    type="button"
                                                    x-small
                                                    class="btn dropdown-toggle custom-dropdown-btn"
                                                    data-toggle="dropdown"
                                                    data-display="static"
                                                    aria-expanded="false"
                                                >
                                                    Actions
                                                </v-btn>
                                                <div
                                                    class="dropdown-menu dropdown-menu-lg-right custom-dropdown-menu"
                                                >
                                                    <v-btn
                                                        class="dropdown-item custom-dropdown-item"
                                                        color="primary"
                                                        @click="
                                                            copyData(data, i)
                                                        "
                                                        type="button"
                                                    >
                                                        Copy
                                                    </v-btn>
                                                    <v-btn
                                                        class="dropdown-item custom-dropdown-item"
                                                        color="primary"
                                                        @click="
                                                            editMenuStock(
                                                                data,
                                                                i
                                                            )
                                                        "
                                                        type="button"
                                                    >
                                                        Edit
                                                    </v-btn>
                                                    <v-btn
                                                        v-if="isDeletePermitted"
                                                        title="DELETE"
                                                        class="dropdown-item custom-dropdown-item"
                                                        color="primary"
                                                        x-small
                                                        @click="
                                                            mergeItems(data)
                                                        "
                                                    >
                                                        Merge
                                                    </v-btn>
                                                    <v-btn
                                                        class="dropdown-item custom-dropdown-item"
                                                        color="primary"
                                                        x-small
                                                        @click="
                                                            viewHistory(data)
                                                        "
                                                    >
                                                        History</v-btn
                                                    >
                                                    <v-btn
                                                        class="dropdown-item custom-dropdown-item"
                                                        color="danger"
                                                        v-if="isDeletePermitted"
                                                        @click="
                                                            deleteData(
                                                                data.id,
                                                                i
                                                            )
                                                        "
                                                        title="DELETE"
                                                        type="button"
                                                    >
                                                        Delete
                                                    </v-btn>
                                                    <v-btn
                                                        class="dropdown-item custom-dropdown-item"
                                                        color="danger"
                                                        v-if="isDeletePermitted"
                                                        @click.native="
                                                            deleteImg(data)
                                                        "
                                                        title="DELETE"
                                                        type="button"
                                                    >
                                                        Delete Img
                                                    </v-btn>
                                                    <span
                                                        v-if="
                                                            branch.etims_type ==
                                                            'vscu'
                                                        "
                                                    >
                                                        <span
                                                            v-if="
                                                                !data.etims_code
                                                            "
                                                        >
                                                            <v-btn
                                                                class="dropdown-item custom-dropdown-item"
                                                                color="info"
                                                                x-small
                                                                @click="
                                                                    addVSCUEtims(
                                                                        data,
                                                                        i
                                                                    )
                                                                "
                                                                >Add
                                                                V-Etims</v-btn
                                                            >
                                                        </span>
                                                        <span v-else>
                                                            <v-btn
                                                                class="dropdown-item custom-dropdown-item"
                                                                color="secondary"
                                                                x-small
                                                                @click="
                                                                    updateVSCUEtims(
                                                                        data,
                                                                        i
                                                                    )
                                                                "
                                                                >Update
                                                                V-Etims</v-btn
                                                            >
                                                            <v-btn
                                                                class="dropdown-item custom-dropdown-item"
                                                                color="primary"
                                                                x-small
                                                                @click="
                                                                    updateVSCUEtimsInventory(
                                                                        data,
                                                                        i
                                                                    )
                                                                "
                                                                >V-Etims Stock
                                                                Movt</v-btn
                                                            >
                                                        </span>
                                                    </span>
                                                    <span
                                                        v-if="
                                                            branch.etims_type ==
                                                            'oscu'
                                                        "
                                                    >
                                                        <span
                                                            v-if="!data.item_id"
                                                        >
                                                            <v-btn
                                                                class="dropdown-item custom-dropdown-item"
                                                                color="info"
                                                                x-small
                                                                @click="
                                                                    addEtims(
                                                                        data,
                                                                        i
                                                                    )
                                                                "
                                                                >Add
                                                                O-Etims</v-btn
                                                            >
                                                            <v-btn
                                                                class="dropdown-item custom-dropdown-item"
                                                                color="primary"
                                                                x-small
                                                                @click="
                                                                    addEtimsDirectly(
                                                                        data,
                                                                        i
                                                                    )
                                                                "
                                                                >Add O-Etims
                                                                Directly</v-btn
                                                            >
                                                        </span>

                                                        <v-btn
                                                            class="dropdown-item custom-dropdown-item"
                                                            color="warning"
                                                            x-small
                                                            @click="
                                                                editEtims(
                                                                    data,
                                                                    i
                                                                )
                                                            "
                                                            v-else
                                                            >Update Etims</v-btn
                                                        >
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ data.id }}</td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editMenuStock(data, i)
                                                "
                                            >
                                                {{ data.code }}</router-link
                                            >
                                        </td>
                                        <td>
                                            <router-link
                                                to="#"
                                                @click.native="
                                                    editMenuStock(data, i)
                                                "
                                                >{{
                                                    data.product_name
                                                }}</router-link
                                            >
                                        </td>
                                        <td>
                                            {{
                                                data.unit ? data.unit.name : ""
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                data.product_category
                                                    ? data.product_category.name
                                                    : ""
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.purchase_price
                                                )
                                            }}
                                        </td>

                                        <td>
                                            {{
                                                cashFormatter(
                                                    data.selling_price
                                                )
                                            }}
                                        </td>
                                        <td>{{ data.min_profit_margin }}%</td>
                                        <td>
                                            {{ data.margin ? data.margin : 0 }}%
                                        </td>
                                        <td>
                                            {{ data.tax_dept.tax_code }}-
                                            {{ data.tax_dept.tax_rate }}
                                        </td>
                                        <td>
                                            <span v-if="data.item_id">{{
                                                data.item_id
                                            }}</span
                                            ><span v-else>{{
                                                data.etims_code
                                            }}</span>
                                        </td>

                                        <td>
                                            <span
                                                style="cursor: pointer"
                                                @click="viewImage(data)"
                                                ><img
                                                    width="80px"
                                                    :src="data.img_url"
                                                    alt=""
                                            /></span>
                                        </td>
                                        <td>
                                            {{ data.item_discount }}%
                                            </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination
                            v-if="menu_product_data !== null"
                            v-bind:results="menu_product_data"
                            v-on:get-page="fetchMenuProduct"
                        ></Pagination>
                        Items Count {{ menu_product_data.total }}

                        <div class="row">
                            <div
                                class="col-8 d-flex"
                                v-if="isDownloadPermitted"
                            >
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    type="xls"
                                    worksheet="My Worksheet"
                                    name="filename.xls"
                                >
                                    <v-icon medium>{{
                                        icons.mdiMicrosoftExcel
                                    }}</v-icon>
                                    Export Detailed-Excel
                                </export-excel>

                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchNormalExcel"
                                    type="xls"
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
                                    :fetch="fetchExcel"
                                    worksheet="My Worksheet"
                                    type="csv"
                                    name="erp_stock.csv"
                                >
                                    <v-icon class="v-icon" medium>{{
                                        icons.mdiFileExcel
                                    }}</v-icon>
                                    Export CSV
                                </export-excel>
                            </div>
                        </div>
                        <Modal v-model="stock_take_modal" title="Update Stock">
                            <general-stock-take
                                v-if="active.stock_take_component"
                                :stocktake_form_data="stocktake_form_data"
                                v-on:dismss-stocktake="dismss_stocktake"
                            />
                        </Modal>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>

        <AddStock
            v-if="active.add_menu_item"
            v-on:dashboard-active="setActiveAfterUpdate"
            :stock_category_data="stock_category_data"
            :unit_data="unit_data"
            :shelf_data="shelf_data"
            :stock_type_data="stock_type_data"
            :edit_data="edit_data"
        >
        </AddStock>
        <EditStock
            v-if="active.edit_stock"
            v-bind:edit_data="edit_data"
            :stock_category_data="stock_category_data"
            :unit_data="unit_data"
            :shelf_data="shelf_data"
            v-on:dashboard-active="setActiveRefresh"
            :stock_type_data="stock_type_data"
        >
        </EditStock>
        <create-ingredients
            v-if="active.create_ingredients"
            :product_data="product_data"
            v-on:dashboard-active="setActiveRefresh"
        />
        <Modal v-model="add_vscu_etims_modal" width="1200px">
            <AddStockVSCU
                v-if="add_vscu_etims_modal"
                v-on:dashboard-active="dismissModalNoRefresh"
                :details_data="details_data"
            ></AddStockVSCU
        ></Modal>
        <Modal v-model="update_vscu_etims_modal" width="1200px">
            <UpdateStockVSCU
                v-if="update_vscu_etims_modal"
                v-on:dashboard-active="dismissModalNoRefresh"
                :details_data="details_data"
            />
        </Modal>
        <Modal v-model="add_modal" width="1200px">
            <AddEtimsStock
                v-if="add_modal"
                v-on:dashboard-active="setActiveRefresh"
                :details_data="details_data"
            ></AddEtimsStock
        ></Modal>

        <Modal v-model="update_modal" width="1200px">
            <UpdateEtimsStock
                v-if="update_modal"
                v-bind:details_data="details_data"
                v-on:update-active="setActiveRefresh"
            >
            </UpdateEtimsStock>
        </Modal>
        <Modal v-model="update_etims_modal" width="1200px">
            <UpdateEtimsStock
                v-if="update_etims_modal"
                v-bind:details_data="details_data"
                v-on:update-active="setActiveRefresh"
            >
            </UpdateEtimsStock>
        </Modal>
        <Modal v-model="preview_image_modal" width="800px">
            <PreviewImageModal
                v-if="preview_image_modal"
                v-bind:details_data="details_data"
            >
            </PreviewImageModal>
        </Modal>
        <Modal v-model="etims_stockmovt_modal" width="1200px">
            <VscutimsStockMovtModal
                v-if="etims_stockmovt_modal"
                v-on:dashboard-active="setActiveRefresh"
                :details_data="edit_data"
            />
        </Modal>
        <Modal v-model="merge_modal" width="800px">
            <modal-merge-stock
                v-if="merge_modal"
                v-on:dismiss-modal="dismissModal"
                :merge_details_data="merge_details_data"
            />
        </Modal>
        <stock-movement-history
            v-if="active.history_modal"
            v-on:dashboard-active="setActiveAfterUpdate"
            v-bind:details_data="details_data"
        >
        </stock-movement-history>

        <notifications group="foo" />
    </div>
</template>

<script>
import AddStock from "./AddStock.vue";
import EditStock from "./EditStock.vue";
import Pagination from "../../utilities/Pagination.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline,
} from "@mdi/js";
import VueHtml2pdf from "vue-html2pdf";
import StockNav from "../StockNav.vue";
import GeneralStockTake from "./GeneralStockTake.vue";
import ImportStockComponent from "./ImportStockComponent.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import AddEtimsStock from "./etims_stock/AddEtimsStock.vue";
import UpdateEtimsStock from "./etims_stock/UpdateEtimsStock.vue";
import EtimStockNav from "./etims_stock/EtimStockNav.vue";
import StockMovementHistory from "./StockMovementHistory.vue";
import PreviewImageModal from "./PreviewImageModal.vue";
import AddStockVSCU from "./AddStockVSCU.vue";
import UpdateStockVSCU from "./UpdateStockVSCU.vue";
import VscutimsStockMovtModal from "./VscutimsStockMovtModal.vue";
import ModalMergeStock from "./ModalMergeStock.vue";

export default {
    data() {
        return {
            branch: this.$store.state.branch,
            active: {
                history_modal: false,
                dashboard: true,
                add_menu_item: false,
                edit_stock: false,
                stock_take_component: false,
                create_ingredients: false,
            },

            add_vscu_etims_modal: false,
            preview_image_modal: false,
            update_vscu_etims_modal: false,
            etims_stockmovt_modal: false,
            shelf_data: [],
            stock_take_modal: false,
            stocktake_form_data: null,
            stock_type_data: [{ name: "general" }, { name: "menu" }],
            menu_product_data: [],
            product_data: null,
            edit_data: null,
            pdf_data: [],
            unit_data: [],
            stock_category_data: [],
            editIndex: null,
            details_data: "",
            update_modal: false,
            update_etims_modal: false,
            add_modal: false,
            search: "",
            params: {
                name: "",
                description: "",
                sort_unit: "",
                sort_product: "",
                sort_code: "",
                sort_purchase: "",
                sort_selling: "",
                sort_category: "",
                sort_shelf: "",
                sort_taxid: "",
                sort_taxdept: "",
                sort_margin: "",
                sort_settings_margin: "",
            },
            merge_modal: false,
            merge_details_data: null,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline,
            },
        };
    },
    components: {
        AddStock,
        EditStock,
        VueHtml2pdf,
        Pagination,
        StockNav,
        GeneralStockTake,
        ImportStockComponent,
        Unauthorized,
        AddEtimsStock,
        UpdateEtimsStock,
        EtimStockNav,
        StockMovementHistory,
        PreviewImageModal,
        AddStockVSCU,
        UpdateStockVSCU,
        VscutimsStockMovtModal,
        ModalMergeStock,
    },
    mounted() {
        this.concurrentApiReq();
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
        async bulkAddItems(lastId = 0) {
              if (lastId === 0) {
          this.showLoaderSticky();
            }
           
            const res = await this.callApi(
                "POST",
                "data/digitax_stock/bulkAddItems",
               {
                last_id: lastId,
               }
            );
            this.hideLoader();
            if (res.status === 400 ) {
                this.e(res.data.error);
                return;
            }
            if (res.status === 200) {
                if (res.data && res.data.last_id === null) {
                        // Abort if last_id is explicitly null
                        this.successNotif("Sync aborted: No more data to sync.");

                        this.hideLoader();
                    } else if (res.data && res.data.last_id) {
                        this.bulkAddItems(res.data.last_id);
                    } else {
                        this.s("Sync Completed");
                        this.hideLoader();
                    }
            } else {
                this.form_error(res);
            }
        },
        mergeItems(data) {
            this.merge_details_data = data;
            this.merge_modal = true;
        },
        dismissModal() {
            this.merge_modal = false;
            this.fetchMenuProduct(1);
        },
        copyData(data, i) {
            this.edit_data = data;
            this.editIndex = i;
            this.setActiveComponent("add_menu_item");
        },
        async printRecord() {},
        async syncAllStockToEtims() {
            this.showLoader();
            const res = await this.getApi(
                "data/pgm_stock/syncAllStockToEtims",
                ""
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("syncing completed");
            } else {
                this.form_error(res);
            }
        },
        viewImage(data) {
            this.details_data = data;
            this.preview_image_modal = true;
        },
        sort_taxid(stat) {
            this.params.sort_taxid = stat;
        },
        sort_taxdept(stat) {
            this.params.sort_taxdept = stat;
        },
        sort_shelf(stat) {
            this.params.sort_shelf = stat;
        },
        sort_category(stat) {
            this.params.sort_category = stat;
        },
        sort_purchase(stat) {
            this.params.sort_purchase = stat;
        },
        sort_selling(stat) {
            this.params.sort_selling = stat;
        },
        sort_margin(stat) {
            this.params.sort_margin = stat;
        },
        sort_settings_margin(stat) {
            this.params.sort_settings_margin = stat;
        },
        productSort(stat) {
            this.params.sort_product = stat;
        },
        codeSort(stat) {
            this.params.sort_code = stat;
        },
        reorder(stat) {
            this.params.sort_unit = stat;
        },
        viewHistory(data) {
            this.details_data = data;

            this.setActiveComponent("history_modal");
        },
        // editMenuStock(data, i) {
        //     this.details_data = data;
        //     this.editIndex = i;
        //     this.update_modal = true;
        // },
        addEtims(data, i) {
            this.editIndex = i;
            this.details_data = data;
            this.add_modal = true;
        },
        addVSCUEtims(data, i) {
            this.editIndex = i;
            this.details_data = data;
            this.add_vscu_etims_modal = true;
        },
        async addEtimsDirectly(data, i) {
            this.showLoader();

            const res = await this.callApi(
                "POST",
                "data/digitax_stock/addItems",
                data
            );
            this.hideLoader();
            if (res.status === 200) {
                this.s("Added to etims successfully");

                this.menu_product_data.data[i].item_id = res.data.item_id;
            } else {
                this.form_error(res);
            }
        },
        editEtims(data, i) {
            this.details_data = data;
            this.editIndex = i;
            this.update_etims_modal = true;
        },
        updateVSCUEtims(data, i) {
            this.editIndex = i;
            this.details_data = data;
            this.update_vscu_etims_modal = true;
        },
        updateVSCUEtimsInventory(data, i) {
            this.editIndex = i;
            this.details_data = data;
            this.update_vscu_etims_modal = true;
        },
        async apiTest() {
            const res = await this.callApi(
                "POST",
                "data/digitax_customer/sendApiRequest",
                ""
            );

            if (res.status === 200) {
            } else {
                this.swr("Server error try again later");
            }
        },
        async deleteAllStock() {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/stock/deleteAllStock",
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.concurrentApiReq();
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async bulkImageStatusUpdate(status) {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/stock/bulkImageStatusUpdate",
                {
                    status: status,
                }
            );
            this.hideLoader();
            if (res.status == 200) {
                this.successNotif("Successfully Updated");
            } else {
                this.servo();
            }
        },
        goToOperation(data) {
            var id = data.id;

            localStorage.setItem("storeProductName", data.product_name);
            localStorage.setItem("storeProductId", data.id);

            this.$router.push({ path: "/create_ingredients" });
        },
        productOperations(data) {
            this.product_data = data;

            this.setActiveComponent("create_ingredients");
        },
        dismss_stocktake() {
            this.stock_take_modal = false;
            this.active.stock_take_component = false;
            this.fetchMenuProduct();
        },
        stockTakeDiag(data) {
            this.stock_take_modal = true;
            this.active.stock_take_component = true;
            this.stocktake_form_data = data;
        },
        async concurrentApiReq() {
            const res = await Promise.all([
                this.fetchMenuProduct(1),
                this.fetchCategory(),
                this.fetchUnit(),
                this.fetchShelf(),
            ]).then(function (results) {
                return results;
            });
        },
        async deleteData(id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/stock/destroy/" + id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.menu_product_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async deleteImg(data, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/stock/delete_img/" + data.id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.menu_product_data.data[i].img_url = null;
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        editMenuStock(data, i) {
            this.edit_data = data;
            this.editIndex = i;
            this.setActiveComponent("edit_stock");
        },
        async generateReport() {
            this.showLoader();
            this.pdf_data = await this.fetchAllMenuItems();

            this.hideLoader();
            if (this.pdf_data.length > 0) {
                this.$refs.html2Pdf.generatePdf();
            } else {
                this.e("No data available");
            }
        },
        async fetchNormalExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock/fetchAll", "");
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.id,
                    code: data.code,
                    name: data.product_name,

                    unit: data.unit.name,
                    category: data.product_category.name,

                    reorder_qty: data.reorder_qty,
                    purchase_price: data.purchase_price,
                    selling_price: data.selling_price,
                    wholesale_price: data.wholesale_price,
                    tax_rate: data.tax_dept.tax_rate,
                    tax_code: data.tax_dept.tax_code,
                    location: "Default",
                    qty: 0,
                    inventoryornon_inventory: data.inventory_type,
                    description: data.description,
                    shelf: data.shelf.name,
                    supplier_id: data.supplier.company_name,
                });
            });
            return data_array;
        },
        async fetchExcel() {
            this.showLoader();
            const res = await this.getApi("data/stock/fetchAll", "");
            this.hideLoader();
            const data_array = [];
            res.data.map((data) => {
                data_array.push({
                    id: data.id,
                    code: data.code,
                    name: data.product_name,
                    unit_code: data.unit.code,
                    unit: data.unit.name,
                    category: data.product_category.name,

                    reorder_qty: data.reorder_qty,
                    purchase_price: data.purchase_price,
                    selling_price: data.selling_price,
                    wholesale_price: data.wholesale_price,
                    tax_rate: data.tax_dept.tax_rate,
                    tax_code: data.tax_dept.tax_code,
                    location: "Default",
                    qty: 0,
                    inventoryornon_inventory: data.inventory_type,
                    description: data.description,
                    shelf: data.shelf.name,
                    item_classification_code: data.item_classification.code,
                    item_classification: data.item_classification.name,
                    etims_item_type_code: data.etims_item_type.code,
                    etims_item_type: data.etims_item_type.name,
                    etims_origin_country_code: data.etims_origin_country.code,
                    etims_origin_country: data.etims_origin_country.name,
                    etims_item_packaging_code:
                        data.etims_item_packaging_code.code,
                    etims_item_packaging: data.etims_item_packaging_code.name,
                });
            });
            return data_array;
        },
        async fetchAllMenuItems() {
            const res = await this.getApi("data/stock/fetchAll", "");

            var stock_array = res.data;
            for (var i = 0; i < stock_array.length; i++) {
                stock_array[i].branch = stock_array[i].branch
                    ? stock_array[i].branch.branch
                    : "";
                stock_array[i].menu_category = stock_array[i].menu_category
                    ? stock_array[i].menu_category.name
                    : "";
                stock_array[i].portion = stock_array[i].portion
                    ? stock_array[i].portion.name
                    : "";
            }

            return stock_array;
        },
        async fetchCategory() {
            const res = await this.getApi("data/product_category/fetchAll", "");

            this.stock_category_data = res.data.map((obj) => {
                return {
                    id: obj.id,
                    label: obj.name,
                };
            });
        },
        async fetchUnit() {
            const res = await this.getApi("data/unit/fetchAll", "");

            this.unit_data = res.data;
        },
        async fetchShelf() {
            const res = await this.getApi("data/shelves/fetch", "");

            this.shelf_data = res.data;
        },
        async searchProducts() {
            let page = 1;
            const res = await this.getApi("data/stock/fetch", {
                params: {
                    page,
                    search: this.search.length >= 2 ? this.search : "",
                    ...this.params,
                },
            });
            if (res.status === 200) {
                this.menu_product_data = res.data;
            }
        },
        setActiveComponent: function (component) {
            this.setActive(this.active, component);
        },
        dismissModalNoRefresh(data) {
            this.menu_product_data.data[this.editIndex] = data;
            this.add_vscu_etims_modal = false;
            this.update_vscu_etims_modal = false;
            this.setActive(this.active, "dashboard");
        },
        setActiveAfterUpdate() {
            this.setActive(this.active, "dashboard");
            this.edit_data = null;
            this.fetchMenuProduct();
        },
        setActiveRefresh: function (data = null) {
            this.menu_product_data.data[this.editIndex] = data;
            this.add_modal = false;
            this.update_modal = false;
            this.update_etims_modal = false;

            this.setActive(this.active, "dashboard");
            // this.fetchMenuProduct();
        },

        async fetchMenuProduct(page) {
            this.showLoader();
            const res = await this.getApi("data/stock/fetch", {
                params: {
                    page,
                    search: this.search.length >= 4 ? this.search : "",
                    ...this.params,
                },
            });
            this.hideLoader();

            if (res.status === 200) {
                this.menu_product_data = res.data;
            } else {
                this.hideLoader();
                this.swr("Server error try again later");
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
