<template>
    <div class="container">
        <router-view> </router-view>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 style="color:#000">Generate Payslips</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-5 ">
                                <fieldset class="border">
                                    <legend class="text-center">
                                        Monthly Salary
                                    </legend>
                                    <div class="form-group">
                                        <label>PAYROLL MONTH</label
                                        ><date-picker
                                            v-model="form_data.payroll_month"
                                            valueType="format"
                                        ></date-picker
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label>Department*</label>
                                        <select
                                            type="text"
                                            @change="onMonthlyDeptMethodChange($event)"
                                            class="form-control"
                                            v-model="form_data.department_id"
                                            placeholder="---Select Dept"
                                        >
                                            <option selected v-bind:value="0"
                                                >All Departments</option
                                            >
                                            <option
                                                v-for="(data,
                                                i) in select_data.dept_data"
                                                :key="i"
                                                v-bind:value="data.id"
                                                >{{ data.department }}</option
                                            >
                                        </select>
                                    </div>

                                    <div class="form-group">
                                      <span class="d-flex ">   <label> Employee *</label>
                                        <span class="ml-2 d-flex"><label>Check All</label><input
                                            type="checkbox"
                                            class="ml-2 mt-1"
                                            v-model="checkedEmployees"
                                            @change="checkMonthlyEmployee($event)"
                                        /></span>
                                        </span>
                                        <multiselect
                                            v-model="form_data.employee_id"
                                            deselect-label="Can't remove this value"
                                            track-by="id"
                                            :custom-label="fullNames"
                                            placeholder="Select Employee"
                                            label="first_name"
                                            :options="select_data.employee_data"
                                            :multiple="true"
                                            :searchable="true"
                                            :allow-empty="false"
                                        >
                                            <template
                                                slot="singleLabel"
                                                slot-scope="{ option }"
                                            >
                                                <strong>{{
                                                    option.first_name +
                                                        " " +
                                                        option.last_name
                                                }}</strong></template
                                            >
                                        </multiselect>
                                        
                                    </div>
                                    <button
                                    v-if="isWritePermitted"
                                        type="button"
                                        class="btn btn-primary"
                                        @click="generateMonthlyPayslips()"
                                    >
                                        Generate Payslips
                                    </button>
                                </fieldset>
                            </div>

                            <div class="col-5">
                                <fieldset class="border">
                                    <legend class="text-center">
                                        Piece Workers
                                    </legend>
                                    <div class="form-group">
                                        <label>FROM DATE </label
                                        ><date-picker
                                            v-model="
                                                piece_form_data.start_payroll_date
                                            "
                                            valueType="format"
                                        ></date-picker>
                                    </div>
                                    <div class="form-group">
                                        <label>TO DATE </label
                                        ><date-picker
                                            v-model="
                                                piece_form_data.end_payroll_date
                                            "
                                            valueType="format"
                                        ></date-picker
                                        >
                                    </div>
                                    <div class="form-group">
                                       <span class="d-flex ">   <label> Employee *</label>
                                        <span class="ml-2 d-flex"><label>Check All</label><input
                                            type="checkbox"
                                            class="ml-2 mt-1"
                                            v-model="checkedPieceEmployees"
                                            @change="checkPieceEmployee($event)"
                                        /></span>
                                        </span>
                                         <multiselect
                                            v-model="piece_form_data.employee_id"
                                            deselect-label="Can't remove this value"
                                            track-by="id"
                                            :custom-label="fullNames"
                                            placeholder="Select Employee"
                                            label="first_name"
                                            :options="select_data.piecework_employee_data"
                                            :multiple="true"
                                            :searchable="true"
                                            :allow-empty="false"
                                        >
                                            <template
                                                slot="singleLabel"
                                                slot-scope="{ option }"
                                            >
                                                <strong>{{
                                                    option.first_name +
                                                        " " +
                                                        option.last_name
                                                }}</strong></template
                                            >
                                        </multiselect>
                                        
                                    
                                    </div>
                                    <button
                                    v-if="isWritePermitted"
                                        type="button"
                                        class="btn btn-primary"
                                        @click="generatePiecePayslips('piece_worker')"
                                    >
                                        Generate Payslips
                                    </button>
                                </fieldset>
                            </div>


                            <!-- casuals -->
                            <div class="col-5">
                                <fieldset class="border">
                                    <legend class="text-center">
                                        Casual Workers
                                    </legend>
                                    
                                    <div class="form-group d-flex flex-column">
                                        <label>PAYROLL DATE/MONTH </label
                                        ><date-picker
                                            v-model="
                                                casual_form_data.payroll_month
                                            "
                                            valueType="format"
                                        ></date-picker
                                        >
                                    </div>
                                
                                    <div class="form-group">
                                       <span class="d-flex ">   <label> Employee *</label>
                                        <span class="ml-2 d-flex"><label>Check All</label><input
                                            type="checkbox"
                                            class="ml-2 mt-1"
                                            v-model="checkedCasualEmployees"
                                            @change="checkCasualEmployee($event)"
                                        /></span>
                                        </span>
                                         <multiselect
                                            v-model="casual_form_data.employee_id"
                                            deselect-label="Can't remove this value"
                                            track-by="id"
                                            :custom-label="fullNames"
                                            placeholder="Select Employee"
                                            label="first_name"
                                            :options="select_data.casual_employee_data"
                                            :multiple="true"
                                            :searchable="true"
                                            :allow-empty="false"
                                        >
                                            <template
                                                slot="singleLabel"
                                                slot-scope="{ option }"
                                            >
                                                <strong>{{
                                                    option.first_name +
                                                        " " +
                                                        option.last_name
                                                }}</strong></template
                                            >
                                        </multiselect>
                                        
                                    
                                    </div>
                                    <button
                                    v-if="isWritePermitted"
                                        type="button"
                                        class="btn btn-primary"
                                        @click="generateCasualPayslips('casuals')"
                                    >
                                        Generate casuals Payslips
                                    </button>
                                </fieldset>
                            </div>
                            <!-- end casuals -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
           <notifications group="foo" />
    </div>
</template>

<script>

import CustomSelectData from "../../utilities/CustomSelectData.vue";

import CustomSelect from "../../utilities/CustomSelect.vue";
export default {
    data() {
        return {
            select_data: {
                dept_data: "",
              
                employee_data: [],
                piecework_employee_data: [],
                  casual_employee_data: []
            },
        active:{ mutiple_select:false},
            
            checkedEmployees:false,
            checkedPieceEmployees:false,
              checkedCasualEmployees:false,
            form_data: {
                employee_id: [ ],
                payroll_month: "",
                payment_type: "monthly",
                department_id: ""
            },
                piece_form_data: {
                employee_id: [],
                end_payroll_date: "",
                start_payroll_date: "",
                 payment_type: "piece_work",
            },
            casual_form_data: {
               
                employee_id:[],
                payroll_month: "",
                
                 payment_type: "casual",
            },
                  
            default_date: ""
        };
    },
    components: {
        CustomSelect,
        CustomSelectData,
    },
    mounted() {
      
        
        this.form_data.payroll_month = this.getCurrentDate();
        this.piece_form_data.end_payroll_date = this.getCurrentDate();
        this.casual_form_data.payroll_month = this.getCurrentDate();
        
        
    },
    created(){
  this.concurrentApiReq();
    },
    methods: {
      async generateCasualPayslips(){
        
            if(!this.casual_form_data.payroll_month){
               this.e('Select  payroll month')
               return;
           }
           
           if(this.casual_form_data.employee_id.length<1){
               this.e('Select  Employee/s')
               return
           }
         
           this.showLoader()
               const res = await this.callApi(
                "post",
                "data/payroll/createCasualsPayslips",
                this.casual_form_data
            );
            this.hideLoader();
             if (res.status === 200) {
                this.s("  Payroll has been generated successfully!");
              this.casual_form_data.no_hrs=0
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);

                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
      }, 
      async  generateMonthlyPayslips() {
          
           if(this.form_data.payroll_month==''){
               this.e('Select  payroll month')
               return;
           }
           
           if(this.form_data.employee_id.length<1){
               this.e('Select  Employees')
               return
           }
           this.showLoader()
               const res = await this.callApi(
                "post",
                "data/payroll/create",
                this.form_data
            );
            this.hideLoader();
             if (res.status === 200) {
                this.s("  Payroll has been generated successfully!");
               this.$router.push('generic_payslips')
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);

                    }
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async  generatePiecePayslips(){
          
            if(this.piece_form_data.start_payroll_date=='' || this.piece_form_data.end_payroll_date==''){
               this.e('Select  payroll month')
               return;
           }
           
           if(this.piece_form_data.employee_id.length<1){
               this.e('Select  Employee/s')
               return
           }
           this.showLoader()
               const res = await this.callApi(
                "post",
                "data/payroll/createPieceWorkPayslips",
                this.piece_form_data
            );
            this.hideLoader();
             if (res.status === 200) {
                this.s("  Payroll has been generated successfully!");
               this.$router.push('generic_payslips')
            } else {
                if (res.status == 422) {
                    for (let i in res.data.errors) {
                        this.e(res.data.errors[i][0]);

                    }
                } else {
                    this.swr("Server error try again later");
                }
            }       
        },
        fullNames({ first_name, last_name }) {
            return `${first_name} ${last_name}`;
        },
        checkMonthlyEmployee(event){
        
        if(this.checkedEmployees==true){
               this.form_data.employee_id=this.select_data.employee_data
           }else{
               this.form_data.employee_id=[]
           }
        },
          checkPieceEmployee(event){
         
         if(this.checkedPieceEmployees==true){
               this.piece_form_data.employee_id=this.select_data.piecework_employee_data
           }else{
               this.piece_form_data.employee_id=[]
           }
        },
          checkCasualEmployee(event){
         
         if(this.checkedCasualEmployees==true){
               this.casual_form_data.employee_id=this.select_data.casual_employee_data
           }else{
               this.casual_form_data.employee_id=[]
           }
        },
        async onMonthlyDeptMethodChange(event) {
            this.showLoader();
            const type = event.target.value;
            const res = await this.callApi(
                "post",
                "data/payroll/getMonthlyEmplByDept",
                {
                    type: type
                }
            );
            this.hideLoader();
           
            if (res.status === 200) {
                this.select_data.employee_data = res.data.results;
            } else {
                this.swr("Server error try again later");
            }
        },

       
        async concurrentApiReq() {
            this.showLoader();
            const res = await Promise.all([
                this.getDept(),

                this.getAllEmployees("monthly"),
                this.getAllEmployees("piece_work"),
                 this.getAllEmployees("casual")
            ]).then(function(results) {
                return results;
            });
            this.hideLoader();

            this.select_data.dept_data = res[0].data.results;
            this.select_data.employee_data = res[1].data.results;
            this.select_data.piecework_employee_data = res[2].data.results;
             this.select_data.casual_employee_data = res[3].data.results;
            this.active.mutiple_select=true
        },

        async getDept() {
            return await this.getApi("data/dept/fetch", "");
        },

        async getEmplType() {
            return await this.getApi("data/employee_type/fetch", "");
        },
        async getAllEmployees(type) {
            const res = await this.callApi(
                "post",
                "data/payroll/getEmployeesByPayMethod",
                {
                    type: type
                }
            );
            return res;
        },
          async getAllEmployees(type) {
            const res = await this.callApi(
                "post",
                "data/payroll/getEmployeesByPayMethod",
                {
                    type: type
                }
            );
            return res;
        },
      
    }
};
</script>
<style scoped>
legend {
    width: 200px;
    padding: 10px 20px, 2rem, 2rem;
}
.border {
    padding: 2rem !important;
}
</style>
