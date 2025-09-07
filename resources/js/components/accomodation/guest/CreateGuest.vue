<template>
    <div>
        <p slot="header" style="color: #f60; text-align: center">
            <span>New Guest</span>
        </p>
        <div class="row">
            <div class="col-4">
                <SearchGuestCO 
                ref="SearchGuestCO"
                v-on:guestCOSearchSelected="guestCOSearchSelected"/>
              
                <div class="form-group">
                    <label for="">Name*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.name"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Phone*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.phone"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">ID No.*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.id_no"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Email</label>
                    <input
                        type="email"
                        autocomplete="dfdf-dfdfk"
                        v-model="form_data.email"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Postal Address</label>
                    <input
                        type="text"
                        v-model="form_data.postal_address"
                        class="form-control"
                    />
                </div>
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label for="">Gender*</label>
                    <select
                        class="form-control"
                        v-model="form_data.gender"
                        id=""
                    >
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Num Family Members*</label>
                    <input
                        type="number"
                        v-model="form_data.members"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <country-select
                    class="form-control"
                        v-model="form_data.country_origin"
                        :country="form_data.country_origin"
                        :countryName="true"
                        topCountry="KE"
                    />
                </div>
               
            </div>
            <div class="col-4">
                <div class="form-group">
                    <label>BirthDate *</label
                    ><date-picker
                        v-model="form_data.birth_date"
                        valueType="format"
                    ></date-picker>
                </div>

                <div class="form-group">
                    <label for="">Age*</label>
                    <input
                        type="number"
                        v-model="form_data.age"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Details*</label>
                    <input
                        type="text"
                        v-model="form_data.details"
                        class="form-control"
                    />
                </div>
            </div>
        </div>
        <div slot="footer">
            <Button
                type="primary"
                size="large"
                long
                v-if="isWritePermitted"
                @click="saveRecords"
                >Save Guest Details</Button
            >
        </div>
        <notifications group="foo" />
    </div>
</template>
<script>
import SearchGuestCO from '../rooms/room_components/SearchGuestCO.vue';
export default {
  components: { SearchGuestCO },
    data() {
        return {
            birth_date: "",
            country: "",
            region: "",
            form_data: {
                guest_company_id:null,
                company_name: "",
                birth_date: "",
                name: "",
                phone: "",
                id_no: "",
                postal_address: "",
                email: "",
                country_origin: "",
                age: null,
                members: 0,
            },
        };
    },
    watch: {
        form_data: {
            deep: true,
            handler: _.debounce(function (val, old) {
                if (val.birth_date.length > 0) {
                    var age = this.$moment().diff(val.birth_date, "years");
                    this.form_data.age = age;
                }
            }, 500),
        },
    },
    methods: {
        guestCOSearchSelected(val){
            this.form_data.guest_company_id=val
        },
        async saveRecords() {
            this.showLoader();
            const res = await this.callApi(
                "POST",
                "data/guest/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                Object.keys(this.form_data).forEach(
                    (key) => (this.form_data[key] = "")
                );
                this.form_data.members = 0;
               this.$refs.SearchGuestCO.clearID()
                this.successNotif("Record Saved ");
                this.$emit("onSuccess");
            } else {
                this.form_error(res);
            }
        },
    },
};
</script>
