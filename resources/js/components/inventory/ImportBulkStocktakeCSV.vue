<template>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-control-label" for="input-file-import"
                    >Upload StockTake Excel File( csv)</label
                >
                <input
                    type="file"
                    style="width: 50%"
                    class="form-control"
                    :class="{ ' is-invalid': error.message }"
                    id="input-file-import"
                    name="file_import"
                    ref="import_file"
                    @change="onFileChange"
                />
            </div>
            <button
              
                @click="proceedAction()"
                class="btn btn-primary custom-button"
            >
                Upload
            </button>
        </div>

        <notifications group="foo" />
    </div>
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
        proceedAction() {
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
                .post("data/stock_stake/importCSV", formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        status = response.data.status;
                        if (status == "success") {
                            this.s(response.data.message);
                           this.$router.push("/stock_take_variation");
                        } else {
                            this.e(response.data.message);
                        }
                    }
                })
                .catch((error) => {
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
