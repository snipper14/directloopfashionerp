<template>
       <div  class="php-email-form">
              <div class="row">
                <div class="col-md-6 form-group">
                  <input type="text"  v-model="form_data.name" class="form-control" id="name" placeholder="Your Name" required>
                </div>
                <div class="col-md-6 form-group mt-3 mt-md-0">
                  <input type="email" class="form-control" v-model="form_data.email" placeholder="Your Email" required>
                </div>
              </div>
              <div class="form-group mt-3">
                <input type="text" class="form-control" v-model="form_data.subject" placeholder="Subject" required>
              </div>
              <div class="form-group mt-3">
                <textarea class="form-control" name="message" v-model="form_data.message"  rows="8" placeholder="Message" required></textarea>
              </div>
              <div class="my-3">
                <div class="loading">Loading</div>
                <div class="error-message"></div>
                <div class="sent-message">Your message has been sent. Thank you!</div>
              </div>
              <div class="text-center">
                   <v-btn
                        rounded
                        color="#cda45e"
                        :loading="loading"
                        :disabled="loading"
                        dark
                        @click="completeEnquiries()"
                    >
                       Enquire
                    </v-btn>
              </div>
            </div>
</template>

<script>
export default {
    data() {
        return {
            loading:false,
            form_data:{
                name:'',
                email:'',
                subject:'',
                message:''
            }
        };
    },
    mounted() {
        
    },
     methods: {
        async completeEnquiries() {
            this.loading=true
           
            const res=await this.callApi("POST","enquiries/create",this.form_data)
            this.loading=false
            if(res.status==200){
                this.successNotif("Successfully sent")
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
            }else{
                this.form_error(res)
            }
        },
    },
};
</script>
