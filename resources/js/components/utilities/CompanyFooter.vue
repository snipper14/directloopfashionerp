<template>
    <div
       :style="{
            marginTop: '2rem',
            color: dynamicColor, // Dynamic color
            paddingRight: '1rem',
            paddingLeft: '1rem',
        }"
    >
        <center>
            <span class="footer-item" v-if="company_details.address">
               <v-icon :size="24" color="primary" medium>{{
                                        icons.mdiMapMarker 
                                    }}</v-icon> <b>{{ company_details.address }}</b>
            </span>
            <span class="footer-item" v-if="company_details.postal_address">
                <v-icon :size="24" color="primary" medium>{{
                                        icons.mdiMailbox   
                                    }}</v-icon>  <b>{{ company_details.postal_address }}</b>
            </span>

            <span class="footer-item" v-if="company_details.kra_pin">
                <b>KRA PIN: {{ company_details.kra_pin }}</b> <br />
            </span>

            <span class="footer-item" v-if="company_details.email">
                <b>
                       <v-icon :size="24" color="primary" medium>{{
                                        icons.mdiEmail     
                                    }}</v-icon>   Email:
                    {{ company_details.email }}</b
                >
            </span>
            <span class="footer-item" v-if="company_details.phone_no">
                <b>
                  
                     <v-icon :size="24" color="red" medium>{{
                                        icons.mdiPhone    
                                    }}</v-icon>   
                
                    Tel: {{ company_details.phone_no }}</b
                >
            </span>
        </center>
    </div>
</template>

<script>
import {
    mdiEmail ,
    mdiPhone ,
    mdiMailbox  ,
    mdiMapMarker ,
} from "@mdi/js";
export default {
    props: ["company_details"],
    data() {
        return {
               icons: {
                mdiPhone ,
                mdiEmail ,
                mdiMailbox  ,
                mdiMapMarker ,

            },
            dynamicColor:'#000000'
        };
    },
    mounted() {
        this.dynamicColor=this.company_details.strip_color?this.company_details.strip_color:'#000000'
        console.log(JSON.stringify(this.company_details));
        
    },
};
</script>
