<template>
    <div class="container">
        <div class="card">
            <div class="card-header">
                Reservation Details
            </div>
            <div class="card-body">
                <ul class="list-group">

                    <li class="list-group-item"><b>Allocated Waiter : </b>{{package_data.waiter.name}}</li>
                    <li class="list-group-item"><b>Room : </b>{{package_data.room.door_name}}</li>
                    <li class="list-group-item"><b>Package Details: </b>{{package_data.room_package.details}}</li>
                    <li class="list-group-item"><b>Package Name: </b>{{package_data.room_package.name}}</li>
                   
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ["package_data"],
    data() {
        return {};
    },
    methods: {

    }
};
</script>
