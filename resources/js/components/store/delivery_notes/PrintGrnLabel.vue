<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Print GRN Label for {{ stockObject.label }}</div>

                    <div class="card-body">
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
            <PrintLabelTemplate
                v-if="show_print && dimensionObj"
                ref="PrintLabelTemplate"
                :dimensionObj="dimensionObj"
                :stockObject="stockObject"
                :batch_no="batch_no"
                :qty="qty"
            />
        </div>
    </v-app>
</template>

<script>
import PrintLabelTemplate from "../stock/print_labels/PrintLabelTemplate.vue";
export default {
    components: { PrintLabelTemplate },
    props: ["item_details"],
    data() {
        return {
            dimension_id:null,
            show_print:false,
            qty: 1,
            dimensionObj: null,
            dimension_data: "",
            stockObject: {
                label: this.item_details.stock.name,
                code: this.item_details.stock.code,
                selling_price: this.item_details.stock.selling_price,
            },
            batch_no: this.item_details.batch_no,
        };
    },
    mounted() {
          this.fetchDimension();
        console.log("Component mounted." + JSON.stringify(this.item_details));
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
    },
    methods: {
         async fetchDimension() {
            const res = await this.getApi("data/label_dimension/fetch", {});
            this.dimension_data = res.data;
              this.dimensionObj = this.dimension_data.find(
                    (item) => item.id === val
                );
        },
        async printLabel() {
            this.show_print = true;
            setTimeout(() => {
                this.$refs.PrintLabelTemplate.printBill();
                this.show_print = false;
            }, 1000);
        },
    },
};
</script>
