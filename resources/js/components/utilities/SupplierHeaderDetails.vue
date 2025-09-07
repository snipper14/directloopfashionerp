<template>
    <div
        style="
            margin-left: 4rem;

            padding: 10px;
        "
    >
        <h3><b style="text-decoration: underline"><span v-if="header">{{header}}</span><span v-else>To</span></b></h3>
        <span
            ><b>{{ supplier_details.company_name }}</b></span
        ><br />
        <span v-if="supplier_details.postal_address">
            <span> {{ supplier_details.postal_address }} </span
            ><br />
        </span>

        <span v-if="supplier_details.country">
            <span>
                {{ supplier_details.country }} /
                {{ supplier_details.city }} </span
            ><br />
        </span>

        <span v-if="supplier_details.pin">
            <span>
                Client PIN:
                {{ supplier_details.pin }} </span
            ><br
        /></span>
        <span v-if="supplier_details.company_email">
            <span>
                Email:
                {{ supplier_details.company_email }} </span
            ><br />
        </span>
        <span v-if="supplier_details.company_phone">
            <span>
                Tel:
                {{ supplier_details.company_phone }} </span
            ><br />
        </span>
    </div>
</template>

<script>
export default {
    props: ["supplier_details",'header'],
    data() {
        return {};
    },
    mounted() {},
};
</script>
