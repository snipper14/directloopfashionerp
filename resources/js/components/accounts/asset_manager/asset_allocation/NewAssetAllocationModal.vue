<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">New Assets Allocation</div>

                    <div class="card-body">
                        <div class="row">
                               <div class="col-md-3 form-group">
                                <label for="">Allocation Code*</label>

                                <input
                                    type="text"
                                    placeholder="Allocation Code"
                                    v-model="form_data.allocation_code"
                                    class="form-control"
                                />
                            </div>
                            <div class="form-group col-md-3 nopadding">
                                    <label for=""
                                        >Search Employee</label
                                    >
                                    <treeselect
                                        :load-options="fetchEmployee"
                                        :options="employee_select_data"
                                        :auto-load-root-options="false"
                                        v-model="form_data.employee_id"
                                        :multiple="false"
                                        :show-count="true"
                                        placeholder="Select  Employee "
                                    />
                                </div>
                                 <div class="col-md-3 form-group d-flex flex-column">
                                <label for="">Asset *</label>

                                <Select v-model="form_data.asset_id">
                                    <Option
                                        v-for="item in asset_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.asset_name }}</Option
                                    >
                                </Select>
                            </div>
                           
                            <div class="col-md-3 form-group">
                                <label for="">Qty</label>

                                <input
                                    type="number"
                                    placeholder="Qty"
                                    v-model="form_data.qty"
                                    class="form-control"
                                />
                            </div>
                           
                          
                           
                           
                            <div
                                class="col-md-3 form-group date-picker-wrapper"
                            >
                                <label for="">Allocated From </label>
                                <date-picker
                                    v-model="form_data.allocated_from"
                                     type="datetime"
                                ></date-picker>
                            </div>
                              <div
                                class="col-md-3 form-group date-picker-wrapper"
                            >
                                <label for="">Allocated To</label>
                                <date-picker
                                    v-model="form_data.allocated_to"
                                     type="datetime"
                                ></date-picker>
                            </div>
                            <div class="col-md-10 form-group">
                                <label for="">Reason</label>

                                <input
                                    type="text"
                                    placeholder="Reason"
                                    v-model="form_data.reason"
                                    class="form-control"
                                />
                            </div>
                           
                           
                        
                            <div class="col-md-10 form-group">
                                <v-btn color="primary" :loading="btn_loading" @click="saveAsset()" small>Save</v-btn>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </v-app>
        
    </div>
</template>

<script>
import Treeselect from "@riophae/vue-treeselect";
import "@riophae/vue-treeselect/dist/vue-treeselect.css";

export default {
    data() {
        return {
            employee_select_data:null,
            showLoading: false,
            dept_data: null,
            asset_data:null,
           
              purchase_type_select: [
                { name: "Owned" },
                { name: "Rented" },
                { name: "Leased" },
              
            ],
            btn_loading:false,
            form_data: {
                employee_id:null,
                allocation_code: null,
                asset_id:null,
               
                qty: null,
               
                allocated_from: null,
                allocated_to:null,
                reason:''
               
             
            },
        };
    },
    components:{
        Treeselect
    },
    mounted() {
        this.concurrentApiReq();
    },
    methods: {
        async saveAsset(){
             const res = await this.callApi('POST',"data/asset_allocation/create", this.form_data);

            if (res.status == 200) {
                this.s('saved')
                this.$emit('dashboard-active')
            } else {
               this.form_error(res)
            }
        },
        async fetchEmployee() {
            const res = await this.getApi("data/employee/fetch", "");

            if (res.status == 200) {
                 this.employee_select_data = res.data.results.map(
                    (obj) => {
                        return {
                            id: obj.id,
                            label: obj.first_name+" "+obj.last_name,
                        };
                    }
                );
            } else {
                this.swr("Server error occurred");
            }
        },
         async fetchAsset() {
            const res = await this.getApi("data/assets/fetch", "");

            if (res.status == 200) {
                this.asset_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
       
        async concurrentApiReq() {
            this.showLoading ? this.showLoader() : "";
            const res = await Promise.all([
                this.fetchAsset(),
               
            ]);
            this.showLoading ? this.hideLoader() : "";
        },
    },
};
</script>
