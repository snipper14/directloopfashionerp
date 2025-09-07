<template>
    <v-app style="margin-top: 0px; height: 100px">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-control-label" for="input-file-import"
                        >Upload Excel File ( csv) Stock</label
                    >
                    <input
                        type="file"
                        class="form-control"
                        :class="{ ' is-invalid': error.message }"
                        id="input-file-import"
                        name="file_import"
                        ref="import_file"
                        @change="onFileChange"
                    />
                    <div v-if="error.message" class="invalid-feedback"></div>
                    <div class="d-flex flex-row">
                        <v-btn
                       
                            @click="uploadPriceGroup()"
                            color="primary"
                            small
                        >
                            Upload Price Group
                        </v-btn>

                        <export-excel
                            class="btn btn-default btn-export ml-2"
                            :fetch="downloadSheet"
                            worksheet="My Worksheet"
                            name="pricegroupsheet.xls"
                        >
                            <v-btn
                                @click="downloadSheet()"
                                color="warning"
                                small
                            >
                                Download sheet
                            </v-btn>
                        </export-excel>
                    </div>
                </div>
            </div>

            <notifications group="foo" />
        </div>
    </v-app>
</template>
<script>
export default {
    props: ["edit_data"],
    data() {
        return {
            error: {},
            import_file: "",
        };
    },
    methods: {
        onFileChange(e) {
            this.import_file = e.target.files[0];
        },
        isCSVFile(file) {
            // Check if the file type is CSV
            return file.type === "text/csv" || file.name.endsWith(".csv");
        },
        async downloadSheet() {
            this.showLoader();
            const res = await this.getApi(
                "data/price_group_amount/fetchExcelSample",
                ""
            );
            this.hideLoader();
            if (res.status === 200) {
                const data_array = [];
                res.data.map((data) => {
                    data_array.push({
                        id: data.id,
                        selling_price: data.selling_price,
                        code: data.code,
                        item_name: data.product_name,
                    });
                });
                return data_array;
            } else {
                this.swr("Server Error occurred");
            }
        },

        async uploadPriceGroup() {
            if (this.import_file) {
                if (!this.isCSVFile(this.import_file)) {
                    this.errorNotif("select csv file");
                    return;
                }
            } else {
                this.errorNotif("select csv file");
                return;
            }
            this.showLoader();
            var contx = this;
            let formData = new FormData();
            formData.append("import_file", this.import_file);
            formData.append("price_group_id", this.edit_data.id);

            axios
                .post("data/price_group_amount/uploadPriceGroup", formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        if (response.data.status == "error") {
                            this.errorNotif(response.data.message);
                        } else {
                            this.s("successfully uploaded");
                        }
                    } else if (response.status === 500) {
                        this.errorNotif(response.data.message);
                    }
                })
                .catch((error) => {
                    console.log(JSON.stringify(error));

                    contx.swr("Server error try again later");
                    contx.hideLoader();
                    // code here when an upload is not valid
                    this.uploading = false;
                    this.error = error.response.data;
                });
        },
    },
};
</script>
