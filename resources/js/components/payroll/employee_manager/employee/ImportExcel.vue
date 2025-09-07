<template>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label class="form-control-label" for="input-file-import"
                    >Upload Excel File( csv)</label
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
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-center">
           
                <button
                v-if="isWritePermitted"
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
            import_file: ""
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
            let formData = new FormData();
             var contx = this;
            formData.append("import_file", this.import_file);

            axios
                .post("data/employee/import", formData, {
                    headers: { "content-type": "multipart/form-data" }
                })
                .then(response => {
                      contx.hideLoader();
                    if (response.status === 200) {
                        contx.s("successfully uploaded");
                 window.location.reload();
                    }
                })
                .catch(error => {
                    contx.hideLoader();
                    contx.swr("Server error try again later");

                    // code here when an upload is not valid
                    this.uploading = false;
                    this.error = error.response.data;
               
                });
        }
    }
};
</script>
