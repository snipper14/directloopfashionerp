<template>
    <v-app style="margin-top: 0px; height: 100px">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-control-label" for="input-file-import"
                        >Upload Excel File ( csv) Stock</label
                    >
                    <div class="d-flex flex-row">
                        <input
                            type="file"
                            class="form-control"
                            :class="{ ' is-invalid': error.message }"
                            id="input-file-import"
                            name="file_import"
                            ref="import_file"
                            @change="onFileChange"
                        />
                        <div
                            v-if="error.message"
                            class="invalid-feedback"
                        ></div>

                        <div>
                            <v-btn
                                v-if="isWritePermitted"
                                @click="uploadEtims()"
                                color="warning"
                                small
                            >
                                Upload Edit Etims
                            </v-btn>
                            <!-- <v-btn
                                v-if="isWritePermitted"
                                @click="importInventory()"
                                color="primary"
                                small
                            >
                                Import Inventory
                            </v-btn>
                             <v-btn
                                v-if="isWritePermitted"
                                @click="importStockCode()"
                                color="primary"
                                small
                            >
                                Import Scancode
                            </v-btn> -->
                        </div>
                    </div>
                </div>
            </div>

            <notifications group="foo" />
        </div>
    </v-app>
</template>
<script>
export default {
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
        async importInventory() {
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

            axios
                .post("data/stock/importInventory", formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        if (response.data.status == "error") {
                            this.errorNotif(response.data.message);
                        } else {
                            this.s("successfully uploaded");
                            this.$emit("refresh-data");
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
          async importStockCode() {
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

            axios
                .post("data/stock/importStockCode", formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        if (response.data.status == "error") {
                            this.errorNotif(response.data.message);
                        } else {
                            this.s("successfully uploaded");
                            this.$emit("refresh-data");
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
        uploadEtims() {
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

            axios
                .post("data/stock/importEditEtims", formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        if (response.data.status == "error") {
                            this.errorNotif(response.data.message);
                        } else {
                            this.s("successfully uploaded");
                            this.$emit("refresh-data");
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
