<template>
    <div class="container">
        <span ></span>
        <table  width="100%"  class="head">
           
            <tr>
                <td class="printer-header" >
                    
                        <b> {{ branch_name }}</b>
                    
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    
                        {{ branchInfo.name }}
                    
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    
                        {{
                            branchInfo.address
                                ? "Address: " + branchInfo.address
                                : ""
                        }}
                    
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    
                        {{ branchInfo.postal_address }}
                    
                </td>
            </tr>

            <tr>
                <td class="printer-header">
                    
                        {{
                            branchInfo.phone_no
                                ? "TEL: " + branchInfo.phone_no
                                : ""
                        }}
                    
                </td>
            </tr>
             <tr>
                <td class="printer-header">
                    
                        {{
                            branchInfo.kra_pin
                                ? "PIN: " + branchInfo.kra_pin
                                : ""
                        }}
                    
                </td>
            </tr>
            <tr>
                <td class="printer-header">
                    
                        {{ new Date() | moment(" YYYY-MM-DD, h:mm") }}
                    
                </td>
            </tr>
             <tr>
                <td >
                    
                      <b v-if="branchInfo.kra_pin=='P052238128D'">Sales Order</b>
                      
                      <b v-else>Sales Receipt</b>
                    
                </td>
            </tr>
        </table><br>
        <div  :style="{ marginTop: dynamicMarginTop }"></div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            branchInfo: {},
            branch_name: "",
             dynamicMarginTop: this.$store.state.branch.header_height,
        };
    },
    mounted() {
        this.branchInfo = this.$store.state.branch;
        this.branch_name = this.branchInfo.business_name.toUpperCase();
    },
};
</script>
