<template>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-control-label" for="input-file-import"
                    >Upload Excel File( csv)</label
                >
                <input
                    type="file"
                    style="width: 20%"
                    class="form-control"
                    :class="{ ' is-invalid': error.message }"
                    id="input-file-import"
                    name="file_import"
                    ref="import_file"
                    @change="onFileChange"
                />
                <div v-if="error.message" class="invalid-feedback"></div>
                <div class="col-md-2">
                    <v-btn
                        title="write"
                        v-if="isWritePermitted"
                        @click="proceedAction()"
                        small
                        color="primary"
                    >
                        Upload
                    </v-btn>
                </div>
            </div>
            <div class="col-md-3">
                <export-excel
                    v-if="isDownloadPermitted"
                   
                    class="btn btn-default btn-export ml-2"
                    :fetch="downLoadSample"
                    worksheet="My Worksheet"
                    type="csv"
                    name="filename.csv"
                >
                    <v-btn small color="success"> Download Template </v-btn>
                </export-excel>
            </div>
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
        async downLoadSample() {
            this.showLoader();
            const res = await this.getApi("data/customers/fetchAll", "");
            this.hideLoader();
            var data_array = [];
            res.data.results.map((array_item) => {
                data_array.push({
                    company_name: array_item.company_name,
                    id: array_item.id,
                    debit_amount: 0,
                    credit_amount: 0,
                });
            });
            return data_array;
        },
        proceedAction() {
            this.showLoader();
            var contx = this;
            let formData = new FormData();
            formData.append("import_file", this.import_file);

            axios
                .post("data/cust_ledger/import", formData, {
                    headers: { "content-type": "multipart/form-data" },
                })
                .then((response) => {
                    contx.hideLoader();
                    if (response.status === 200) {
                        status = response.data.status;
                        if (status == "success") {
                            this.s(response.data.message);
                            window.location.reload();
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
