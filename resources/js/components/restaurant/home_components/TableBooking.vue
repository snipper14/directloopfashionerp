<template>
    <section id="book-a-table" class="book-a-table">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Reservation</h2>
                <p>Book a Table</p>
            </div>

            <form
                action="forms/book-a-table.php"
                method="post"
                role="form"
                class="php-email-form"
                data-aos="fade-up"
                data-aos-delay="100"
            >
                <div class="row">
                    <div class="col-lg-4 col-md-6 form-group">
                        <input
                            type="text"
                            v-model="form_data.name"
                            class="form-control"
                            placeholder="Your Name"
                            data-rule="minlen:4"
                            data-msg="Please enter at least 4 chars"
                        />
                        <div class="validate"></div>
                    </div>
                    <div class="col-lg-4 col-md-6 form-group mt-3 mt-md-0">
                        <input
                            type="email"
                            class="form-control"
                            v-model="form_data.email"
                            placeholder="Your Email"
                            data-rule="email"
                            data-msg="Please enter a valid email"
                        />
                        <div class="validate"></div>
                    </div>
                    <div class="col-lg-4 col-md-6 form-group mt-3 mt-md-0">
                        <input
                            type="text"
                            class="form-control"
                            v-model="form_data.phone"
                            placeholder="Your Phone"
                            data-rule="minlen:4"
                            data-msg="Please enter at least 4 chars"
                        />
                        <div class="validate"></div>
                    </div>
                    <div class="col-lg-6 col-md-6 form-group mt-3">
                        <date-picker
                            type="datetime"
                            placeholder="Booking Date"
                            format="YYYY-MM-DD HH:mm:ss"
                            v-model="form_data.event_date"
                        ></date-picker>
                    </div>

                    <div class="col-lg-4 col-md-6 form-group mt-3">
                        <input
                            type="number"
                            class="form-control"
                            v-model="form_data.no_people"
                            placeholder="# of people"
                            data-rule="minlen:1"
                            data-msg="Please enter at least 1 chars"
                        />
                        <div class="validate"></div>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <textarea
                        class="form-control"
                        name="message"
                        rows="5"
                        v-model="form_data.details"
                        placeholder="Message"
                    ></textarea>
                    <div class="validate"></div>
                </div>
                <div class="mb-3">
                    <div class="loading">Loading</div>
                    <div class="error-message"></div>
                    <div class="sent-message">
                        Your booking request was sent. We will call back or send
                        an Email to confirm your reservation. Thank you!
                    </div>
                </div>
                <div class="text-center">
                    <v-btn
                        rounded
                        color="#cda45e"
                        :loading="loading"
                        :disabled="loading"
                        dark
                        @click="completeBooking()"
                    >
                        Book a Table
                    </v-btn>
                </div>
            </form>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            loading:false,
            form_data: {
                event_date: null,
                name: "",
                email: "",
                phone: "",
                event_date: "",
                no_people: "",
                details: "",
            },
        };
    },
    mounted() {
       
    },
    methods: {
        async completeBooking() {
            this.loading=true
            this.form_data.event_date=this.formatDateTime(this.form_data.event_date)
            const res=await this.callApi("POST","table_booking/create",this.form_data)
            this.loading=false
            if(res.status==200){
                this.successNotif("Successfully booked")
                Object.keys(this.form_data).forEach(
                    key => (this.form_data[key] = "")
                );
            }else{
                this.form_error(res)
            }
        },
    },
};
</script>
