<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2"><etim-stock-nav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Print Labels</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <v-btn
                                color="primary"
                                x-small
                                @click="add_dimension_modal = true"
                                >Add Dimension</v-btn
                            >

                            <v-btn
                                color="secondary"
                                x-small
                                @click="setActiveComponent('view_label')"
                                >View Dimension</v-btn
                            >
                        </div>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label for="">Dimension</label>
                                <select
                                    class="form-control"
                                    v-model="dimension_id"
                                >
                                    <option
                                        :value="value.id"
                                        v-for="(value, i) in dimension_data"
                                        :key="i"
                                    >
                                        {{ value.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Search Products</label>
                                    <Select
                                        @change="onChange($event)"
                                        v-model="stock_id"
                                        filterable
                                        :remote-method="searchProducts"
                                        :loading="loading1"
                                    >
                                        <Option
                                            v-for="(
                                                option, index
                                            ) in search_stock_list"
                                            :value="option.value"
                                            :key="index"
                                            >{{ option.label }}</Option
                                        >
                                    </Select>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="">Barcode Qty </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="qty"
                                />
                            </div>
                             <div class="col-md-4 form-group">
                                <label for="">Batch No </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="batch_no"
                                />
                            </div>
                        </div>
                        <div class="row">
                            <v-btn color="primary" @click="printLabel()"
                                >Print Label</v-btn
                            >
                        
                        
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Modal v-model="add_dimension_modal"
            ><PrintLabelModal
                v-if="add_dimension_modal"
                v-on:dismiss-modal="dismissModal"
        /></Modal>
        <PrintLabelTemplate
            v-if="show_print"
            ref="PrintLabelTemplate"
            :dimensionObj="dimensionObj"
            :stockObject="stockObject"
            :batch_no="batch_no"
            :qty="qty"
        />
      
        <LabelReport
            v-if="active.view_label"
            v-on:dashboard-active="setActiveRefresh"
        />
    </div>
</template>

<script>
import EtimStockNav from "../etims_stock/EtimStockNav.vue";
import LabelReport from "./LabelReport.vue";

import PrintLabelModal from "./PrintLabelModal.vue";
import PrintLabelTemplate from "./PrintLabelTemplate.vue";

export default {
    components: {
        PrintLabelModal,
        PrintLabelTemplate,
        EtimStockNav,
        
        LabelReport,
    },
    data() {
        return {
            active: {
                dashboard: true,
                view_label: false,
            },
            show_print_cutter: false,
            edit_dimension_modal: false,
            loading1: false,
            show_print: false,
            add_dimension_modal: false,
            dimension_data: "",
            dimension_id: null,
            dimensionObj: null,
            stockObject: null,
            stock_id: null,
            search_stock_list: [],
            qty: 1,
            batch_no: "",
        };
    },
    mounted() {
        console.log("Component mounted.");
        this.fetchDimension();
    },
    watch: {
        dimension_id: {
            handler: _.debounce(function (val, old) {
                this.dimensionObj = this.dimension_data.find(
                    (item) => item.id === val
                );
                console.log(JSON.stringify(this.dimensionObj));
            }, 500),
        },
        stock_id: {
            handler: _.debounce(function (val, old) {
                this.stockObject = this.search_stock_list.find(
                    (item) => item.value === val
                );
                console.log(JSON.stringify(this.stockObject));
            }, 500),
        },
    },
    methods: {
        setActiveRefresh: function () {
            this.dimension_data=[]
            this.setActive(this.active, "dashboard");
            this.dimension_id=null
             this.fetchDimension();
        },
        setActiveComponent: function (component) {
              this.dimension_data=[]
            this.setActive(this.active, component);
            
        },
        dismissModal() {
            this.add_dimension_modal = false;
            this.fetchDimension();
        },
        async printServer() {
            const payload = {
                branch: {
                    invoice_printer: "POS-80",
                },
                labels: [
                    {
                        barcode: "123456789012",
                        text: "Product A",
                    },
                    {
                        barcode: "987654321098",
                        text: "Product B",
                    },
                ],
            };
            const res = await this.callApi(
                "POST",
                "data/label_printer/printLabels",
                payload
            );
        },
       
        async printLabel() {
            this.show_print = true;
            setTimeout(() => {
                this.$refs.PrintLabelTemplate.printBill();
                this.show_print = false;
            }, 1000);
        },
        async fetchDimension() {
            const res = await this.getApi("data/label_dimension/fetch", {});
            this.dimension_data = res.data;
        },
        searchProducts: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;
                const res = await this.getApi("data/stock/search_items", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_stock_list = this.modifyKey(res.data).filter(
                    (item) =>
                        item.label.toLowerCase().indexOf(query.toLowerCase()) >
                        -1
                );
            }
        }, 500),

        modifyKey(data) {
            let modif = data.map((obj) => {
                return {
                    value: obj.id,
                    label: obj.product_name,
                    selling_price: obj.selling_price,
                    code: obj.code,
                };
            });
            return modif;
        },
    },
};
</script>
