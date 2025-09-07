<template>
    <div
        style="
            margin-left: 4rem;

            padding: 10px;
        "
    >
       <span
            ><b>{{ customer_info.company_name }}</b></span
        ><br />
        <span v-if="customer_info.postal_address">
            <span> {{ customer_info.postal_address }} </span
            ><br />
        </span>

        <span v-if="customer_info.country">
            <span>
                {{ customer_info.country }} /
                {{ customer_info.city }} </span
            ><br />
        </span>

        <span v-if="customer_info.pin">
            <span>
                Client PIN:
                {{ customer_info.pin }} </span
            ><br
        /></span>
        <span v-if="customer_info.company_email">
            <span>
                Email:
                {{ customer_info.company_email }} </span
            ><br />
        </span>
        <span v-if="customer_info.company_phone">
            <span>
                Tel:
                {{ customer_info.company_phone }} </span
            ><br />
        </span>
    </div>
</template>

<script>
export default {
    props: ["customer_info",'header'],
    data() {
        return {};
    },
    mounted() {},
};
</script>
