<template>
    <div style="margin-top: 2rem;margin-left:12rem">
       
        <span
            ><h3><b>{{ company_details.business_name }}</b></h3></span
        ><br />
        <span v-if="company_details.branch">
            <span> {{ company_details.branch }} </span><br />
        </span>
        <span v-if="company_details.postal_address">
            <span> {{ company_details.postal_address }} </span><br />
        </span>
        <span v-if="company_details.address">
            <span> {{ company_details.address }} </span><br />
        </span>

        <span v-if="company_details.kra_pin">
            <span> KRA PIN: {{ company_details.kra_pin }} </span
            ><br />
        </span>

       
        
        <span v-if="company_details.email">
            <span>
                Email:
                {{ company_details.email }} </span
            ><br />
        </span>
        <span v-if="company_details.phone_no">
            <span>
                Tel:
                {{ company_details.phone_no }} </span
            ><br />
        </span>
         <!-- <span v-if="company_details.till_no">
            <span>
                <b
                    > {{ company_details.till_no }}</b
                > </span
            ><br
        /></span> -->
        
    </div>
</template>

<script>
export default {
    props: ["company_details"],
    data() {
        return {};
    },
    mounted() {},
};
</script>
