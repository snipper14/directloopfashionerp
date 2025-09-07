<template>
    <multiselect
        v-model="select_value"
        deselect-label="Can't remove this value"
        track-by="id"
        :custom-label="fullNames"
        placeholder="Select Employee"
        label="first_name"
        :options="select_data.employee_data"
        :multiple="multiple_status"
        :searchable="true"
        :allow-empty="false"
    >
        <template slot="singleLabel" slot-scope="{ option }">
            <strong>{{
                option.first_name + " " + option.last_name
            }}</strong></template
        >
    </multiselect>
</template>

<script>
export default {
    props: ["data"],
    data() {
        return {
            select_value: "",
            select_data: {
                employee_data: [],
                id: ""
            },
            multiple_status: true
        };
    },
    mounted() {
        this.multiple_status = this.select_props;
        this.select_data.employee_data = this.data;
    },
    watch: {
        select_value: {
            handler(val) {
                // this.$emit("employeeIdFromComponent", val.employee_data.id);
                //this.$emit("employeeIdFromComponent", val.id);
                //    this.form_data.total_pay= val.qty*val.pay_rate
            },
            deep: true
        }
    },
    methods: {
        fullNames({ first_name, last_name }) {
            return `${first_name} ${last_name}`;
        }
    }
};
</script>
