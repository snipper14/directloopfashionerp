<template>
    <div>
        <p slot="header" style="color: #f60; text-align: center">
            <span>New Company</span>
        </p>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="">Company Name*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.name"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Contacts*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.contacts"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Address*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.address"
                        class="form-control"
                    />
                </div>
                  <div class="form-group">
                    <label for="">Email Address*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.email"
                        class="form-control"
                    />
                </div>
               <Button
                type="info"
                size="large"
                long
             
                @click="saveRecords"
                >Save  Details</Button
            >
            </div>
          
         
        </div>
        
        <notifications group="foo" />
    </div>
</template>
<script>
export default {
    data() {
        return {
            
            form_data: {
                name: "",
                address: "",
                contacts: "",
                email: "",
               
            },
        };
    },
   
    methods: {
        async saveRecords() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/guest_co/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.form_data.members = 0;
                this.successNotif("Record Saved ");
                this.$emit("onAddCompanySuccess");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
