<template>
    <div>
        <p slot="header" style="color:#f60;text-align:center">
            <span>Update Company</span>
        </p>
      <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="">Company Name*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.name"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Contacts*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.contacts"
                        class="form-control"
                    />
                </div>
                <div class="form-group">
                    <label for="">Address*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.address"
                        class="form-control"
                    />
                </div>
                 <div class="form-group">
                    <label for="">Email*</label>
                    <input
                        type="text"
                        autocomplete="dfdf-dfdfk"
                        v-model="edit_form_data.email"
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
    props:['edit_guest_co_data'],
    data() {
        return {
            edit_form_data: {
                id:null,
               name: "",
                address: "",
                contacts: "",
                email: "",
            }
        };
    },
    created(){
     console.log(">>>>"+JSON.stringify(this.edit_guest_co_data));
   this.edit_form_data=     this.edit_guest_co_data
  
    },
    methods:{
   async     saveRecords(){
       this.showLoader()
        const res=await this.callApi('PUT','data/guest_co/update',this.edit_form_data)
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
