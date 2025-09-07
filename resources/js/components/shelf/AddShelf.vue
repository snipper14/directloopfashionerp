<template>
    <div class="container">
        <v-app>
             <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Add Shelves/Racks</div>

                    <div class="card-body">
                        <div class="row">
                            <div
                                v-for="(value, key) in formData"
                                :key="key"
                                class="col-md-6 form-group"
                            >
                                <label :for="key">{{
                                    key.replace(/_/g, " ").toUpperCase()
                                }}</label>
                                <input
                                    v-if="typeof value === 'boolean'"
                                    v-model="formData[key]"
                                    type="checkbox"
                                    class="form-control"
                                />
                                <input
                                    v-else
                                    v-model="formData[key]"
                                    type="text"
                                    class="form-control"
                                    :id="key"
                                    :required="isRequiredField(key)"
                                />
                            </div>
                        </div>
                        <v-btn
                            type="submit"
                            @click="handleSubmit"
                            :loading="is_loading"
                            color="primary"
                        >
                            Submit
                        </v-btn>
                    </div>
                </div>
            </div>
        </div>
        </v-app>
       
    </div>
</template>

<script>
export default {
    data() {
        return {
            is_loading:false,
            formData: {
                name: "",
                description: "",
            },
        };
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods:{
         async handleSubmit() {
            this.is_loading = true;
            const res = await this.callApi(
                "POST",
                "data/shelves/create",
                this.formData
            );
            this.is_loading = false;
            if (res.status === 200) {
                this.s("Added successfully");
                this.$emit("dashboard-active");
               
            } else {
                this.form_error(res);
            }
        },
          isRequiredField(key) {
            // You can customize this method to determine which fields are required
            // For example, you can have an array of required field names and check against it
            const requiredFields = ["item_class_code", "item_name"];
            return requiredFields.includes(key);
        },
    }
};
</script>
