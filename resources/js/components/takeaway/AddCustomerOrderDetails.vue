<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6 form-group mr-2">
                        <v-card-text>
                            <v-autocomplete
                                v-model="model"
                                :items="items"
                                :loading="isLoading"
                                :search-input.sync="search"
                                color="white"
                                hide-no-data
                                hide-selected
                                item-text="Description"
                                item-value="API"
                                label="Type Phone / Name"
                                placeholder="Start typing to Search"
                                prepend-icon="mdi-database-search"
                                return-object
                            ></v-autocomplete>
                        </v-card-text>
                     
                    </div>
                </div>

                <div class="row">
                   
                    <div class="col-md-4 form-group mr-2">
                        <label for="">Customer</label>
                        <input
                            type="text"
                            v-model="form_data.company_name"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3 form-group ">
                        <label for="">Phone</label>
                        <input
                            type="text"
                            v-model="form_data.company_phone"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-4 form-group mr-2">
                        <label for="">Address</label>
                        <input
                            type="text"
                            v-model="form_data.address"
                            class="form-control"
                        />
                    </div>
                    <div class="col-md-3">
                        <time-picker-diag v-on:delivery-time="deliveryTime" />
                    </div>
                    <div class="col-md-2">
                        <v-btn
                            :loading="btn_loading_cust"
                            class="ma-2 v-btn-primary"
                            @click="createSetCustomer()"
                            color="primary"
                            dark
                            >+ SAVE
                        </v-btn>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</template>

<script>
import _ from "lodash";
import TimePickerDiag from "../utilities/TimePickerDiag.vue";
export default {
    components: { TimePickerDiag },
    data() {
        return {
            descriptionLimit: 60,
            entries: [],
            isLoading: false,
            model: null,
            search: null, //end
            searchi: "",
            search_results: [],

            btn_loading_cust: false,
            form_data: {
                customer_id: null,
                company_name: "",
                company_phone: "",
                address: "",
                delivery_time: null,
            },
        };
    },
    mounted() {
        console.log();
        Object.keys(this.form_data).forEach(
            (key) => (this.form_data[key] = "")
        );
        this.form_data.customer_id = null;
    },
    watch: {
        model(val) {
            if (val != null) this.tab = 0;
            else this.tab = null;
            this.form_data.company_phone=val.company_phone
            this.form_data.company_name=val.company_name
            this.form_data.customer_id=val.id
            this.form_data.address=val.address
           
        },
        search: {
            // Items have already been loaded
            //  if (this.items.length > 0) return;
            handler: _.debounce(function (val, old) {
                this.isLoading = true;

                this.searchCust();
            }, 500),

            // Lazily load input items
        },

        searchi: {
            handler: _.debounce(function (val, old) {
                this.searchCustomer();
            }, 500),
        },
    },
     computed: {
    
      items () {
        return this.entries.map(entry => {
          const Description = entry.company_name+'-'+entry.company_phone 
        
          return Object.assign({}, entry, { Description })
        })
      },
    },

    methods: {
        async searchCust() {
            const res = await this.getApi("data/customers/searchCustomer", {
                params: {
                    search: this.search,
                },
            });
            this.isLoading = false;
            if (res.status == 200) {
                
                 const { count, entries } = res.data
            this.count = 5
            this.entries =  res.data;
                //console.log(">>> " + JSON.stringify(res.data));
            }
        },
        async createSetCustomer() {
            if (this.form_data.customer_id) {
                this.$emit("customerSearchResult", this.form_data);
            } else {
                this.btn_loading_cust = true;
                const res = await this.callApi(
                    "post",
                    "data/customers/create",
                    this.form_data
                );
                this.btn_loading_cust = false;
                if (res.status === 200) {
                    this.s("Added successfully");
                    this.form_data.customer_id = res.data.id;
                    this.$emit("customerSearchResult", this.form_data);
                } else {
                    this.form_error(res);
                }
            }
        },
        deliveryTime(val) {
            this.form_data.delivery_time = val;
        },
        setCustomerRecords(data) {
            this.form_data.customer_id = data.id;
            this.form_data.company_name = data.company_name;
            this.form_data.company_phone = data.company_phone;
            this.form_data.address = data.address;
            this.search_results = [];
        },
        submitRecords(data) {
            this.$emit("customerSearchResult", data);
        },
        async searchCustomer() {
            if (this.search.length > 0) {
                const res = await this.getApi("data/customers/searchCustomer", {
                    params: {
                        search: this.search,
                    },
                });

                this.search_results = res.data;
            } else {
                this.search_results = [];
            }
        },
    },
};
</script>
