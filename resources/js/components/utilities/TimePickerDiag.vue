<template>
    <v-row>
        <v-menu
            ref="menu"
            v-model="menu2"
            :close-on-content-click="false"
            :nudge-right="40"
            :return-value.sync="time"
            transition="scale-transition"
            offset-y
            max-width="290px"
            min-width="290px"
        >
            <template v-slot:activator="{ on, attrs }">
                <v-text-field
                    @change="setDeliveryTime()"
                    v-model="time"
                    label="Delivery Time"
                    prepend-icon="mdi-clock-time-four-outline"
                    readonly
                    v-bind="attrs"
                    v-on="on"
                ></v-text-field>
            </template>
            <v-time-picker
                v-if="menu2"
                v-model="time"
                full-width
                @click:minute="$refs.menu.save(time)"
            ></v-time-picker>
        </v-menu>
    </v-row>
</template>
<script>
export default {
    data() {
        return {
            time: null,
            menu2: false,
            delivery_time: null,
        };
    },
    mounted() {
        this.time = this.getCurrentTime();
    },
    watch: {
        time: {
            handler: function (val) {
                var futuretime = this.$moment()
                    .add(30, "minutes")
                    .format("hh:mm");

                var mins = this.$moment.utc(
                    this.$moment(futuretime, "HH:mm").diff(
                        this.$moment(val, "HH:mm"),
                        "hours"
                    )
                );

               // console.log(">>>" + futuretime + "  >>" + val + " >>" + mins);
                this.delivery_time = val;

                // if (mins < 30) {
                //     console.log(mins + "  is less than " + "30");
                //     this.delivery_time = "NOW";
                // }

                this.$emit("delivery-time", this.delivery_time);
            },
        },
    },
    methods: {
        setDeliveryTime() {
            console.log(">>>");
        },
    },
};
</script>
