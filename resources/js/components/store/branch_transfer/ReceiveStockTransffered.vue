<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2">
                <branch-transfer-nav />
            </div>
            <div class="col-md-10" v-if="isDisplayPermitted">
                <div class="card">
                    <div class="card-header"> <h3><b>Receive Stock</b></h3></div>

                    <div class="card-body">
                        <div class="row">
                                      <table class="table table-dark custom-table">
                                <thead>
                                    <tr>
                                      
                                        <th scope="col">Transfer Date</th>
                                       
                                        <th scope="col">Transfer Code</th>
                                        <th>Reference Code</th>
                                        <th>Origin Branch</th>
                                        <th>Dest. Branch</th>
                                        <th scope="col">Issuer</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(data, i) in grouped_transfer_data"
                                        :key="i"
                                    >
                                      

                                        <td>
                                            {{ data.transfer_date }}
                                        </td>
                                      
                                        <td>{{ data.transfer_code }}</td>
                                        <td>{{ data.reference_code }}</td>
                                        <td>{{ data.branch.branch }}</td>
                                        <td>{{ data.branch_to.branch }}</td>
                                        <td>{{ data.user.name }}</td>
                                        
                                        <td>
                                            <v-btn
                                                @click="
                                                    receiveTransfer(data)
                                                "
                                                color="info"
                                                x-small
                                                >Receive</v-btn
                                            >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                  

                       
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
        <Modal  width="1200px" v-model="show_receive_modal">
            <receive-stock-modal
           v-on:dismiss-modal="dismissModal"
            :details_data="details_data"
            v-if="show_receive_modal"/>
        </Modal>
    </div>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
import Unauthorized from "../../utilities/Unauthorized.vue";
import BranchTransferNav from "./BranchTransferNav.vue";
import PrintBranchStockTransferTemplate from "./PrintBranchStockTransferTemplate.vue";
import ReceiveStockModal from './ReceiveStockModal.vue';
export default {
    components: {
        BranchTransferNav,
        PrintBranchStockTransferTemplate,
        Unauthorized,
        Pagination,
        ReceiveStockModal,
    },
    data() {
        return {
            details_data:null,
            show_receive_modal:false,
            dept_data: null,
            grouped_transfer_data:[],
            transfer_data: [],
            search: "",
            search_results: [],
            params: { search: "" },
            show_print: false,
            form_data: {
                received_department_id: null,

                received_date: null,
            },
        };
    },
    mounted() {
      this.concurrentApiReq(1);
    },
    watch: {
        search: {
            handler: _.debounce(function (val, old) {
                this.concurrentApiReq(1);
            }, 500),
        },
    },
    methods: {
        dismissModal(){
           this.show_receive_modal=false 
            this.concurrentApiReq(1);  
        },
        async receiveTransfer(value) {
            this.details_data=value
          this.show_receive_modal=true
        },
      
 
async fetchUnreceivedGroupedTransfer(){
  
            const res = await this.getApi(
                "data/branch_stock_transfer/fetchUnreceivedGroupedTransfer",
                {
                    params: {
                        ...this.params,
                    },
                }
            );

            if (res.status == 200) {
                this.grouped_transfer_data =res.data
            } else {
                this.swr("Server error occurred");
            }
},
      
        async concurrentApiReq(page) {
            this.showLoader();
            await Promise.all([
                
                this.fetchUnreceivedGroupedTransfer(page),
            ]);
            this.hideLoader();
        },
    },
};
</script>
