<template>
    <div class="container">
        <v-app>
            <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Edit Assets</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group d-flex flex-column">
                                <label for="">Location*</label>

                                <Select v-model="form_data.department_id">
                                    <Option
                                        v-for="item in dept_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.department }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Asset Code*</label>

                                <input
                                    type="text"
                                    placeholder="Asset Code"
                                    v-model="form_data.asset_code"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Asset Name*</label>

                                <input
                                    type="text"
                                    placeholder="Asset Name"
                                    v-model="form_data.asset_name"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Qty*</label>

                                <input
                                    type="number"
                                    placeholder="Asset Qty"
                                    v-model="form_data.qty"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Model Name/Number</label>

                                <input
                                    type="text"
                                    placeholder="Model Name/Number"
                                    v-model="form_data.model_name"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Serial Number:</label>

                                <input
                                    type="text"
                                    placeholder="Serial Number:"
                                    v-model="form_data.serial_no"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-3 form-group d-flex flex-column">
                                <label for="">Asset Category*</label>

                                <Select v-model="form_data.asset_category_id">
                                    <Option
                                        v-for="item in asset_cate_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.category }}</Option
                                    >
                                </Select>
                            </div>
                            <div
                                class="col-md-3 form-group date-picker-wrapper"
                            >
                                <label for="">Purchase Date</label>
                                <date-picker
                                    v-model="form_data.purchase_date"
                                    valueType="format"
                                ></date-picker>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="">Purchase Price:</label>

                                <input
                                    type="number"
                                    placeholder="Purchase Price"
                                    v-model="form_data.purchase_price"
                                    class="form-control"
                                />
                            </div>
                             <div class="form-group col-md-3 ">
                                <label>Purchase Type*</label>
                                <Select v-model="form_data.purchase_type">
                                    <Option
                                        v-bind:value="data.name"
                                        v-for="(
                                            data, i
                                        ) of purchase_type_select"
                                        :key="i"
                                    >
                                        {{ data.name }}
                                    </Option>
                                </Select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for=""
                                    >Depreciation Rate(%Yearly):</label
                                >

                                <input
                                    type="number"
                                    placeholder="Depreciation Rate(%Yearly)"
                                    v-model="form_data.monthly_depreciation"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-10 form-group">
                                <label for="">Description:</label>

                                <input
                                    type="text"
                                    placeholder="Description"
                                    v-model="form_data.description"
                                    class="form-control"
                                />
                            </div>
                            <div class="col-md-10 form-group">
                                <v-btn color="primary" :loading="btn_loading" @click="updateAsset()" small>UPDATE</v-btn>
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
export default {
    props:['edit_data'],
    data() {
        return {
            
            showLoading: false,
            dept_data: null,
            asset_cate_data: null,
              purchase_type_select: [
                { name: "Owned" },
                { name: "Rented" },
                { name: "Leased" },
              
            ],
            btn_loading:false,
            form_data: {
                id:null,
                department_id: null,
                asset_code: null,
                asset_name: null,
                qty: null,
                model_name: null,
                serial_no: null,
                asset_category_id: null,
                purchase_date: null,
                purchase_price: null,
                purchase_type:null,
               
                monthly_depreciation: null,
                description: null,
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
        
        this.form_data=this.edit_data
        console.log(JSON.stringify(this.form_data));
    },
    methods: {
        async updateAsset(){
             const res = await this.callApi('PUT',"data/assets/update", this.form_data);

            if (res.status == 200) {
                this.s('saved')
                this.$emit('dashboard-active')
            } else {
               this.form_error(res)
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");

            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchAssetCategory() {
            const res = await this.getApi("data/assets_category/fetch", "");

            if (res.status == 200) {
                this.asset_cate_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async concurrentApiReq() {
            this.showLoading ? this.showLoader() : "";
            const res = await Promise.all([
                this.fetchDept(),
                this.fetchAssetCategory(),
            ]);
            this.showLoading ? this.hideLoader() : "";
        },
    },
};
</script>
