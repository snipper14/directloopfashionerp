<template>
    <div>
        <div class="row ">
            <div class="col-md-10">
                <header-component  v-if="branch" :business_name="branch.business_name" />
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-4" v-if="branch">
                <h3>Customer</h3>
                <p >{{ branch.business_name }}</p>

                <p v-if="branch.branch">Branch: {{ branch.branch }}</p>

                <p v-if="branch.postal_address">
                    Address: {{ branch.postal_address }}
                </p>

                <p v-if="branch.address">{{ branch.address }}</p>
            </div>
            <div class="col-4"></div>
            <div class="col-4 " >
               <div style="float:right;width:300px"> 
            <p>
                <h3> GRN</h3>
            </p>
            <p> <b>GRN No: </b>{{data[0].delivery_no}}</p>
            <p> <b>Order No: </b>{{data[0].order_no}}</p>
            <p> <b>Delivery Date: </b>{{data[0].received_date}}</p>
        </div>

            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-4 ">
                <h4>Vendor</h4><br>
                 <h4>{{ data[0].supplier.company_name}}</h4>
                 
            <p v-if="data[0].supplier.company_phone">Phone: {{data[0].supplier.company_phone}} </p>
          
            <p v-if="data[0].supplier.company_email">Email: {{data[0].supplier.company_email}} </p>
           
            <p v-if="data[0].supplier.country">Country: {{data[0].supplier.country}}/{{data[0].supplier.city}} </p>
        
            <p v-if="data[0].supplier.postal_address">{{data[0].supplier.postal_address}} / {{data[0].supplier.address}} </p>
          
            </div>
            <div class="col-4">

            </div>
            <div class="col-4">
              
                  <div style="float:right; width:300px;"> 
                       <h4> Shipping Address</h4>
            <p v-if="data[0].purchase_order.shipping_address">Branch: {{data[0].purchase_order.shipping_address}}</p>
            <p v-else>Branch: {{branch.address}}</p>
           <p v-if="branch.phone_no">Phone: {{ branch.phone_no}}</p>
        <p v-if="branch.email">Email: {{ branch.email}}</p>
         </div>

            </div>
        </div>
        <hr>
          <div class="row">
        <div class="col-12">
            <center><h3>GOODS RECEIVED NOTE (GRN)</h3></center>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-12">
  <table style="margin-top: 2rem;margin-bottom:2rem;width:100%">
        <thead class="bg-dark">
            <tr class="title_2">
                <td><b>Received  By: </b></td>
                <td><b>Department</b></td>
                <td><b>Order Date</b></td>
                <td><b>Deadline Date</b></td>
                
            </tr>
        </thead>
        <tr style="background:#eceff1;">
            <td>
                <p > {{data[0].user.name}}</p>
            </td>
            
            <td>
                <p> {{data[0].user.department.department}}</p>
            </td>
            <td>
                <p>{{data[0].purchase_order.receipt_date}}</p>
            </td>
            <td>
                <p>{{data[0].purchase_order.order_deadline}}</p>
            </td>
        </tr>
    </table>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
           
              <table class="table " style="width:100%" v-if="true">
        <thead class="bg-dark">
            <tr>
                <td><b>Code</b></td>
                <td><b>Item Name</b></td>
              
                <td><b>Qty Ordered</b></td>
                <td><b>Qty Shipped</b></td>
          
            </tr>
        </thead>
        <tbody>
         
            <tr  v-for="(item,i) in data" :key="i">
                <td>
                    <p>{{ item.stock.code}}</p>
                </td>
                <td>
                    <p>{{ item.stock.product_name}}</p>
                </td>
               

                <td>
                    <p>{{ item.qty_ordered}}</p>
                </td>
                <td>
                    <p>{{ item.qty_delivered}}</p>
                </td>
             
                
            </tr>
           
            
          
        </tbody>
    </table>
        </div>
    </div>
    <div class="row">
        
        <div class="col-4">
            <b>Approved By</b><br><br><br>
            -----------------------------------------
        </div>

        <div class="col-4">
            <b>Signature </b><br><br><br>
            ------------------------------------------
        </div>

        <div class="col-4">
            <b>Date </b><br><br><br>
            -------------------------------------------
        
    </div>
    </div>
    </div>
</template>

<script>
import HeaderComponent from "./download_component/HeaderComponent.vue";
export default {
    props: ["grn_download_data"],
    components: {
        HeaderComponent
    },
    data() {
        return {
            branch: null,
            data:null,
        };
    },
    created() {
        this.branch = this.grn_download_data.branch;
        this.data=this.grn_download_data.data
        console.log(JSON.stringify(this.data));
        console.log(
            "Component mounted." + JSON.stringify(this.branch)
        );
    },

    methods: {}
};
</script>
<style scoped>
/* #printMe {
    visibility: hidden;
} */
</style>
