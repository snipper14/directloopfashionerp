<template>
    <div
        style="
            margin-top: 2rem;
            border: 1px solid black;
            padding: 10px;
            
        "
    >
  <h3><b>Client Info</b></h3>
        <span
            ><b>{{ invoice_details[0].customer.company_name }}</b></span
        ><br />
        <span v-if="invoice_details[0].customer.postal_address">
            <span> {{ invoice_details[0].customer.postal_address }} </span
            ><br />
        </span>

        <span v-if="invoice_details[0].customer.country">
            <span>
                {{ invoice_details[0].customer.country }} /
                {{ invoice_details[0].customer.city }} </span
            ><br />
        </span>

        <span v-if="invoice_details[0].customer.pin">
            <span>
                Client PIN:
                {{ invoice_details[0].customer.pin }} </span
            ><br
        /></span>
        <span v-if="invoice_details[0].customer.company_email">
            <span>
                Email:
                {{ invoice_details[0].customer.company_email }} </span
            ><br />
        </span>
        <span v-if="invoice_details[0].customer.company_phone">
            <span>
                Tel:
                {{ invoice_details[0].customer.company_phone }} </span
            ><br />
        </span>
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
