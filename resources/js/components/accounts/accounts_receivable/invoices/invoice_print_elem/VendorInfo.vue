<template>
    <div style="margin-top: 2rem; border: 1px solid black; padding: 10px">
        <h3><b>Vendor Info</b></h3>
        <span
            ><b>{{ invoice_details[0].branch.business_name }}</b></span
        ><br />
        <span v-if="invoice_details[0].branch.branch">
            <span> {{ invoice_details[0].branch.branch }} </span><br />
        </span>
        <span v-if="invoice_details[0].branch.postal_address">
            <span> {{ invoice_details[0].branch.postal_address }} </span><br />
        </span>
        <span v-if="invoice_details[0].branch.address">
            <span> {{ invoice_details[0].branch.address }} </span><br />
        </span>

        <span v-if="invoice_details[0].branch.kra_pin">
            <span> Document PIN: {{ invoice_details[0].branch.kra_pin }} </span
            ><br />
        </span>

       
        
        <span v-if="invoice_details[0].branch.email">
            <span>
                Email:
                {{ invoice_details[0].branch.email }} </span
            ><br />
        </span>
        <span v-if="invoice_details[0].branch.phone_no">
            <span>
                Tel:
                {{ invoice_details[0].branch.phone_no }} </span
            ><br />
        </span>
         <span v-if="invoice_details[0].branch.till_no">
            <span>
                <b
                    > {{ invoice_details[0].branch.till_no }}</b
                > </span
            ><br
        /></span>
        
    </div>
</template>

<script>
export default {
    props: ["invoice_details"],
    data() {
        return {};
    },
    mounted() {},
};
</script>
