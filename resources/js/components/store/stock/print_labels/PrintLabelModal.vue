<template>
    <div class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Print Labels</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <span :style="spanStyle">
                                <center>
                                    <p>
                                        20 KG SUGAR 20 KG SUGAR 20 KG SUGAR 20
                                        KG SUGAR
                                    </p>
                                    <p>Ksh. 5,000</p>
                                    <barcode
                                        :width="form_data.bar_width"
                                        :height="form_data.bar_height"
                                        :fontSize="form_data.bar_font_size"
                                        textAlign="center"
                                        v-bind:value="barcodeValue"
                                    >
                                    </barcode>
                                </center>
                            </span>
                        </div>
                        <div class="row mt-6" style="margin-top: 4rem">
                            <div class="col-md-6 form-group">
                                <label for="">Name</label>
                                <input
                                    type="text"
                                    v-model="name"
                                    class="form-control"
                                />
                            </div>
                            <div
                                v-for="(value, key) in form_data"
                                :key="key"
                                class="col-md-6 form-group"
                            >
                                <label :for="key">{{
                                    key.replace(/_/g, " ").toUpperCase()
                                }}</label>
                                <input
                                    v-if="typeof value === 'boolean'"
                                    v-model="form_data[key]"
                                    type="checkbox"
                                    class="form-control"
                                />
                                <input
                                    v-else
                                    v-model="form_data[key]"
                                    type="number"
                                    class="form-control"
                                    :id="key"
                                />
                            </div>

                            <div class="col-md-10">
                                <Button type="primary" @click="saveDimensions"
                                    >Save</Button
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VueBarcode from "vue-barcode";
export default {
    components: { barcode: VueBarcode },
    data() {
        return {
            barcodeValue: "123445",
            name: "",
            form_data: {
                bar_font_size: 8,
                bar_height: 40,
                bar_width: 1.5,
                wrapper_height: 220,
                wrapper_width: 195,

                item_description_fontsize: 8,
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
    },
    computed: {
        spanStyle() {
            return {
                background: "red",
                padding: "6px",
                height: this.form_data.wrapper_height + "px",
                width: this.form_data.wrapper_width + "px",
                fontSize: this.form_data.item_description_fontsize + "px",
            };
        },
    },
    methods: {
        async saveDimensions() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/label_dimension/create",
                {...this.form_data,'name':this.name}
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("saved");
                this.$emit('dismiss-modal')
                // Object.keys(this.form_data).forEach((key) => {
                //     if (key != "name") {
                //         this.form_data[key] = 0;
                //     }
                // });
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
