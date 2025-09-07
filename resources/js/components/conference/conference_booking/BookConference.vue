<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3><b>Book Conference</b></h3>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Receipt No</label>
                                    <input
                                        type="text"
                                        disabled
                                        class="form-control"
                                        v-model="form_data.receipt_no"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Conference Room(name)</label>
                                    <input
                                        type="text"
                                        autocomplete="off"
                                        class="form-control"
                                        v-model="form_data.conference_room"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Customer Name</label>
                                    <input
                                        type="text"
                                        autocomplete="off"
                                        class="form-control"
                                        v-model="form_data.company_name"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Customer Contacts</label>
                                    <input
                                        type="text"
                                        autocomplete="off"
                                        class="form-control"
                                        v-model="form_data.phone"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">No of Attendees</label>
                                    <input
                                        type="number"
                                        autocomplete="off"
                                        class="form-control"
                                        v-model="form_data.no_guest"
                                    />
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="">Customer Email</label>
                                    <input
                                        type="text"
                                        autocomplete="off"
                                        class="form-control"
                                        v-model="form_data.email"
                                    />
                                </div>
                                <div>
                                    <label for="">Event Start</label>
                                    <date-picker
                                        type="datetime"
                                        v-model="form_data.from"
                                    />
                                </div>
                                <div>
                                    <label for="">Event End</label>
                                    <date-picker
                                        type="datetime"
                                        v-model="form_data.to"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="">Event Charges</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.total_paid"
                                    />
                                </div>
                                  <div class="form-group">
                                    <label for="">Payment Methods</label>
                                  <Select
                                    v-model="form_data.pay_method"
                                   
                                >
                                    <Option
                                        v-for="(value, i) in pay_method_obj"
                                        :key="i"
                                        :value="value.value"
                                    >
                                        {{ value.name }}
                                    </Option>
                                </Select>
                                </div>
                                <div class="form-group">
                                    <label for="">Details</label>
                                    <input
                                        type="number"
                                        class="form-control"
                                        v-model="form_data.details"
                                    />
                                </div>
                                <div class="form-group">
                                    <button
                                        @click="saveConferenceBooking()"
                                        class="btn btn-primary"
                                    >
                                        Save Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


         <div id="printReceipt">
              <div class="row">
                   <div class="col-4">
                       <receipt-header/>
                        <table width="100%" class="head">
                            <tr>
                            <td>
                                <center>
                                    <h3><b>CONFERENCE BOOKING RECEIPT</b></h3>
                                </center>
                            </td>
                        </tr>
                        </table>
                        -------------------------------------------------------------------------
                        <table  width="100%">
                            <tr>
                                <td><b>Served By: {{current_user}}</b></td>
                            </tr>
                            <tr>
                                <td><b>Receipt No</b></td>
                                <td><b>{{form_data.receipt_no}}</b></td>
                            </tr>
                        </table>
                          <table width="100%" class="mt-3">
                        <tr>
                            <td class="room-details">Client Name</td>
                            <td class="room-details">{{ form_data.company_name }}</td>
                        </tr>

                        <tr>
                            <td class="room-details">Conference Room</td>
                            <td class="room-details">
                                {{form_data.conference_room }}
                            </td>
                        </tr>
                        <tr>
                         <td>   Event Start: {{form_data.from}}</td>
                        </tr>
                         <tr>
                         <td>   Event End: {{form_data.to}}</td>
                        </tr>

                    </table>
                    <table  width="100%">
                        <tr>
                            <td><b> Total Amount</b> </td>
                            <td><b>{{cashFormatter(this.form_data.total_paid)}}</b></td>
                        </tr>

                    </table>
                   </div>
              </div>
         </div>
    </div>
</template>

<script>
import ReceiptHeader from '../../pos/menu_components/dinerscomponents/ReceiptHeader.vue';
export default {
  components: { ReceiptHeader },
    data() {
        return {
            pay_method_obj:[
                {
                    "value":"mobile_money",
                    "name":"Mobile Money"
                },
                {
                    "value":"cash",
                    "name":"Cash"
                },
                   {
                    "value":"card",
                    "name":"Card"
                }],
            form_data: {
                pay_method:"",
                receipt_no: null,
                conference_room: "",
                no_guest: null,
                company_name: "",
                email: "",
                phone: "",
                total_paid: 0,
                from: null,
                to: null,
                details: "",
            },
            current_user:null,
        };
    },
    mounted() {

       this.current_user= this.$store.state.user.name
      
        this.getReceiptNo();
    },
    methods: {
        async saveConferenceBooking() {
            this.form_data.from=this.formatDateTime(this.form_data.from)
            this.form_data.to=this.formatDateTime(this.form_data.to)
            this.showLoader();
            const res = await this.callApi(
                "post",
                "data/conference/create",
                this.form_data
            );
            this.hideLoader();
            if (res.status == 200) {
                this.s("Successfully saved")
                 await printJS("printReceipt", "html");

                console.log(JSON.stringify(res.data));
                  Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
                 this.form_data.receipt_no = res.data.receipt_no;
              //  this.$router.go()
            } else {
                this.form_error(res);
            }
        },
        async getReceiptNo() {
            this.showLoader();
            const res = await this.getApi("data/conference/getReceiptNo", "");
            this.hideLoader();
            if (res.status == 200) {
                this.form_data.receipt_no = res.data.receipt_no;
            } else {
                this.servo();
            }
        },
    },
};
</script>
<style scoped>
#printReceipt {
    visibility: hidden;
}
</style>
