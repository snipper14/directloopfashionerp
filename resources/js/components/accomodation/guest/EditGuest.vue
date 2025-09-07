<template>
    <div>
        <p slot="header" style="color:#f60;text-align:center">
            <span>Update Guest</span>
        </p>
        <div class="row">
            <div class="col-4">
               
                <div class=" form-group">
                    <label for="">Name*</label>
                    <input
                        type="text"
                         autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.name"
                        class="form-control"
                    />
                </div>
                <div class=" form-group">
                    <label for="">Phone*</label>
                    <input
                        type="text"
                         autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.phone"
                        class="form-control"
                    />
                </div>
                <div class=" form-group">
                    <label for="">ID No.*</label>
                    <input
                        type="text"
                         autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.id_no"
                        class="form-control"
                    />
                </div>
                 <div class=" form-group">
                    <label for="">Email</label>
                    <input
                        type="email"
                        autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.email"
                        class="form-control"
                    />
                </div>
                <div class=" form-group">
                    <label for="">Postal Address</label>
                    <input
                        type="text"
                        v-model="edit_form_data.postal_address"
                        class="form-control"
                    />
                </div>
            </div>
            <div class="col-4">
               
                <div class=" form-group">
                    <label for="">Gender*</label>
                    <select
                        class="form-control"
                        v-model="edit_form_data.gender"
                        id=""
                    >
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class=" form-group">
                    <label for="">Num Family Members*</label>
                    <input
                        type="number"
                        v-model="edit_form_data.members"
                        class="form-control"
                    />
                </div>
                 <div class=" form-group">
                    <label for="">Country*</label>
                    <input
                        type="text"
                        v-model="edit_form_data.country_origin"
                        class="form-control"
                    />
                </div>
            </div>
            <div class="col-4">
                   <div class="form-group">
                    <label>BirthDate  *</label
                    ><date-picker
                        v-model="edit_form_data.birth_date"
                        valueType="format"
                    ></date-picker>
                </div>
               
                  <div class=" form-group">
                    <label for="">Age*</label>
                    <input
                        type="number"
                        v-model="edit_form_data.age"
                        class="form-control"
                    />
                </div>
                  <div class=" form-group">
                    <label for="">Details*</label>
                    <input
                        type="text"
                        v-model="edit_form_data.details"
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
export default {
    props:['edit_guest_data'],
    data() {
        return {
            edit_form_data: {
                company_name:"",
                birth_date:null,
                name: "",
                phone: "",
                id_no: "",
                postal_address: "",
                email: "",
                country_origin:'',
                age:null,
                members:0,
            }
        };
    },
    created(){
    
   this.edit_form_data=     this.edit_guest_data
    },
    methods:{
   async     saveRecords(){
       this.showLoader()
        const res=await this.callApi('PUT','data/guest/update',this.edit_form_data)
       this.hideLoader()
       if(res.status==200){
           this.successNotif('Record Saved ')
           this.$emit('onSuccess')
       }else{
           this.form_error(res)
       }
   }
    },
};
</script>
