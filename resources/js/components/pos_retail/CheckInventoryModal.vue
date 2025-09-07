<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        Inventory for {{ inventory_data[0].stock.name }}
                    </div>

                    <div class="card-body">
                        <table class="table table-sm table-dark">
                            <tr v-for="(value, i) in inventory_data" :key="i">
                                <td>{{ value.department.department }}</td>
                                <td>
                                    {{ value.qty }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ["inventory_data"],
    data() {
        return {};
    },
    mounted() {
        
    },
};
</script>
