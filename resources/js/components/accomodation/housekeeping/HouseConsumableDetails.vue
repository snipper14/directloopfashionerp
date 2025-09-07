<template>
    <div>
        <div class="card-body">
            <table class="table table-sm table-striped table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Stock Code</th>
                        <th scope="col">Item Name</th>

                        <th scope="col">Unit</th>
                        <th scope="col">Qty</th>
                        <th scope="col">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        class="small-tr"
                        v-for="(data, i) in usage_data"
                        :key="i"
                    >
                        <td>
                            {{ data.stock.code }}
                        </td>
                        <td>
                            {{ data.stock.product_name }}
                        </td>
                        <td>{{ data.unit.name }}</td>
                        <td>{{ data.qty }}</td>
                        <td>{{ cashFormatter(data.price) }}</td>

                       
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
<script>
export default {
    props: ["data"],
    data() {
        return {
            usage_data: []
        };
    },
    mounted() {
      
        this.fetchDetails(this.data.room_reservation_id)
    },
    methods: {
        async fetchDetails(room_reservation_id) {
            this.showLoader();
            const res = await this.getApi(
                "data/house_keeping/fetchConsumableDetails",{
                    params:{
                        room_reservation_id:room_reservation_id
                    }
                }
            );
            this.hideLoader();
            res.status == 200 ? (this.usage_data = res.data) : this.servo();
        }
    }
};
</script>
