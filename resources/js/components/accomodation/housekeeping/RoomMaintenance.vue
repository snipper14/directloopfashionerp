<template>
    <div class="container">
        <div class="row justify-content-center" v-if="active.dashboard">
            <div class="col-md-2">
                <HouseKeepingNav />
            </div>
            <div class="col-md-10">
                <div class="card" v-if="isReadPermitted">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                    <div class="col-3">
                         <span class="badge badge-pill maintenance-progress">Maintenance In Progress</span>
                    </div>
                        <div class="col-md-5">
                            <h3>General Maintenance
                                .</h3>
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
                            <div
                                class="col-2"
                                v-for="(data, i) in rooms_data"
                                :key="i"
                           
                                :class="[
                                    'room-badge',
                                    {
                                        'maintenance-progress':
                                            data.is_under_maintenance == '1'
                                    }
                                ]"
                               
                            >
                                <b> DOOR:{{ data.door_name }}</b
                                ><br />
                                <hr />
                                <b> FLOOR:{{ data.floor_no }}</b
                                ><br />
                                <button @click="showUpdateModal(data)" class="btn btn-primary btn-sm">Update Maintenance</button>
                                 <button @click="maintenanceDetails(data)" class="btn btn-secondary btn-sm mt-2"> Maintenance Details</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <unauthorized />
                </div>
            </div>
        </div>
       <Modal v-model="set_maintenance_model" width="400">
            <div class="d-flex flex-column" v-if="model_data">
                <h4 class="mt-3">Room No:  {{model_data.door_name}}</h4>
                <div class="form-group mt-3">
                    <label for="">Maintenance Status</label>
                    <Select v-model="status_form.is_under_maintenance" >
                        <Option value="1">Under Maintenance</Option>
                        
                        <Option value="0"> Maintenance Completed</Option>
                    </Select>
                </div>
                <div class="form-group mt-2">
                    <button class="btn btn-primary btn-sm" v-if="isUpdatePermitted" @click="updateMaintenance()">Update</button>
                </div>
            </div>
        </Modal>
        <Modal  v-model="add_maintenance_model" width="1260">
              <div class="row">
                  <add-maintenance-records 
                  v-if="model_data && add_maintenance_model"
                  :model_data="model_data"
                  />
              </div>
        </Modal>
        <notifications group="foo" />
    </div>
</template>

<script>
import HouseKeepingNav from "./HouseKeepingNav.vue";
import {
    mdiMicrosoftExcel,
    mdiPlusThick,
    mdiFileExcel,
    mdiFilePdfBoxOutline
} from "@mdi/js";
import AddMaintenanceRecords from './AddMaintenanceRecords.vue';
export default {
    data() {
        return {
            active: {
                dashboard: true,
                add_details: false
            },
            model_data:null,
            set_maintenance_model:false,
            add_maintenance_model:false,
            isLoading: true,
            search: "",
            room_details: null,
            rooms_data: [],
            status_form:{
                is_under_maintenance:null,
                room_id:null,
            },
            icons: {
                mdiPlusThick,
                mdiMicrosoftExcel,
                mdiFileExcel,
                mdiFilePdfBoxOutline
            }
        };
    },
    components: {
        HouseKeepingNav,
        AddMaintenanceRecords
    },
    mounted() {
        this.fetchRooms();
    },
    watch: {
        search: {
            handler: _.debounce(function(val, old) {
                this.fetchRooms();
            }, 500)
        }
    },
    methods: {
        maintenanceDetails(data){
              this.model_data=data
            this.add_maintenance_model=true
        },
       async updateMaintenance(){
             this.showLoader()
             const res=await this.callApi('POST','data/rooms/updateMaintenanceStatus',this.status_form)
              this.hideLoader()
              if(res.status==200){
                  this.successNotif('Updated')
                   this.set_maintenance_model=false 
                   this.fetchRooms();
              }else{
                  this.form_error(res)
              }
       },
        showUpdateModal(data){
           
            this.model_data=data
            this.status_form.room_id=data.id
          this.set_maintenance_model=true 
        },
      
        setActiveComponent: function(component) {
            this.setActive(this.active, component);
        },

        setActiveRefresh: function() {
            this.setActiveComponent("dashboard");
            this.fetchRooms();
        },
        async fetchRooms() {
            this.isLoading ? this.showLoader() : "";
            const res = await this.getApi("data/rooms/fetch", {
                params: {
                    search: this.search
                }
            });
            this.isLoading ? this.hideLoader() : "";
            if (res.status == 200) {
                this.rooms_data = res.data;
            } else {
                this.servo();
            }
        }
    }
};
</script>
<style scoped>
.maintenance-progress {
    background: #827717 !important;
    margin: 2px;
    color: #fff;
}
.room-badge {
    border: 1px solid #827717;
    border-radius: 2px;
    background: #e0e0e0;
}
.room-badge:hover {
    cursor: pointer;
}
</style>
