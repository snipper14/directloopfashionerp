<template>
    <div class="row">
        <div class="col-2">
            <RequisitioNav />
        </div>
        <div class="col-10">
            <div class="container" v-if="isReadPermitted">
                <div class="row justify-content-center" v-if="active.dashboard">
                    <div class="col-md-12">
                        <!-- <ImportExcel/> -->
                        <div class="card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div class="col-md-3">
                                    <v-btn
                                        v-if="isWritePermitted"
                                        class="ma-2 v-btn-primary"
                                        @click="$router.push('requisition')"
                                        color="primary"
                                        dark
                                    >
                                        <v-icon medium>{{
                                            icons.mdiPlusThick
                                        }}</v-icon>
                                        Add
                                    </v-btn>
                                </div>
                                <div class="col-md-5">
                                    <b> <h3>Assembly Requisition</h3></b>
                                </div>
                                <div class="col-md-4 float-md-right ">
                                    <input
                                        type="text"
                                        placeholder="Search.."
                                        class="form-control small-input"
                                        v-model="search"
                                    />
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="table-responsive">
                                        <table
                                            class="table table-sm table-striped table-bordered mt-3"
                                        >
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        Req No
                                                    </th>
                                                  
                                                    <th>Due Date</th>
                                                    
                                                     <th>Status</th>
                                                     <th>Approval</th>
                                                    <th>User</th>

                                                    <th scope="col">Raw Materials Qty</th>
                                                  
                                                      <th>Total  Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    class="small-tr"
                                                    v-for="(data,
                                                    i) in requisition_data.data"
                                                    :key="i"
                                                      :class="{'approved-selected':data.approved=='0'}"
                                                >
                                                    <td>
                                                        <router-link
                                                            to="#"
                                                            @click.native="
                                                                viewDetails(
                                                                    data
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                data.req_id
                                                            }}</router-link
                                                        >
                                                    </td>
                                                  
                                                    <td>
                                                        {{
                                                            data.date_due
                                                        }}
                                                    </td>
                                                   
                                                    <td>
                                                        {{data.status }}
                                                    </td>
                                                    <td>
                                                        {{data.approved==1 ? "Approved":'Not Approved'}}
                                                    </td>
                                                    <td>
                                                        {{
                                                            data.user
                                                                ? data.user.name
                                                                : ""
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                           
                                                               cashFormatter( data.sum_ingredient_qty)
                                                          
                                                        }}
                                                    </td>

                                                    <td>
                                                        {{
                                                            cashFormatter(
                                                                data.sum_sub_total
                                                            )
                                                        }}
                                                    </td>

                                                         <td>
                                                        <router-link
                                                            v-if="
                                                                isDeletePermitted
                                                            "
                                                            class="danger"
                                                            to="#"
                                                            @click.native="
                                                                deleteData(
                                                                    data.req_id
                                                                )
                                                            "
                                                            >Delete</router-link
                                                        >
                                                    </td>
                                                    <td>
                                                        <router-link
                                                            v-if="
                                                                isApprovePermitted &&   data.approved=='0'
                                                            "
                                                            to="#"
                                                            @click.native="
                                                                approveRequest(
                                                                    data
                                                                )
                                                            "
                                                        >
                                                            Approve</router-link
                                                        >
                                                    </td>
                                                
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <Pagination
                                        v-if="requisition_data !== null"
                                        v-bind:results="requisition_data"
                                        v-on:get-page="fetchRequistion"
                                    ></Pagination>
                                    Items Count {{ requisition_data.total }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              
                <AssemblyRequisitionItemsDetails
                    v-if="active.details"
                    v-on:dashboard-active="setActiveRefresh"
                  :req_data="req_data"/>
            </div>
            <center v-else>
                <b style="color:red;font-size:1.2rem"
                    >YOU ARE UNAUTHORIZED TO VIEW THIS PAGE</b
                >
            </center>
        </div>
    </div>
</template>

<script>
import AssemblyRequisitionItemsDetails from "./AssemblyRequisitionItemsDetails.vue";
import Pagination from "../../utilities/Pagination.vue";
import RequisitioNav from "./RequisitioNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_requisition: false,
                detail:false,
            },
            search: "",
            req_data:null,
            requisition_data:[],
           
          displayLoader:false,
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        Pagination,
        RequisitioNav,
        AssemblyRequisitionItemsDetails,
    },
    mounted(){
        this.concurrentApiReq()
    },
     watch: {
        search: {
          
            handler: _.debounce(function(val, old) {
                this.fetchRequistion(1);
            }, 500)
        },
     },
    methods: {
          async approveRequest(data) {
            const shouldDelete = await this.confirmDialogue(
                "Are sure this can't be undone!!!"
            );
            if (shouldDelete) {
                const res = await this.callApi(
                    "post",
                    "data/assembly_requisition/approve",
                    {
                        req_id: data.req_id
                    }
                );

                if (res.status === 200) {
                    this.s("Approved ");
                    data.approved = "1";
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
        async deleteData(req_id, i) {
            const shouldDelete = await this.deleteDialogue();
            if (shouldDelete) {
                const res = await this.callApi(
                    "delete",
                    "data/assembly_requisition/delete/" + req_id,
                    ""
                );

                if (res.status === 200) {
                    this.s("deleted ");
                    this.requisition_data.data.splice(i, 1);
                } else {
                    this.swr("Server error try again later");
                }
            }
        },
         viewDetails(data){
       this.req_data=data
       this.setActiveComponent('details')
         },
        async concurrentApiReq() {
            this.showLoader();
            await Promise.all([this.fetchRequistion(1), ]).then(
                function(results) {
                    return results;
                }
            );
            this.hideLoader();
        },
        async fetchRequistion(page) {
             const currentRoute = this.$route.name;
             if(this.displayLoader){
                 this.showLoader()
             }
            const res = await this.getApi("data/assembly_requisition/fetch", {
                params: {
                    currentRoute:currentRoute,
                    page,
                    search: this.search.length >= 2 ? this.search : ""
                }
            });
             if(this.displayLoader){
                 this.hideLoader()
             }
            if (res.status === 200) {
                this.requisition_data = res.data;
            } else {
                this.swr("Server error try again later");
            }
        },
       
        
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },
        setActiveRefresh: function() {
            this.setActive(this.active, "dashboard");
          this.displayLoader=true
            this.fetchRequistion(1);
        
        }
    }
};
</script>
<style scoped>
.approved-selected{
    background: #9fa8da !important;
}
</style>