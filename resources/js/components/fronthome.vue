<template>
    <div class="body-custom">
        <!-- ======= Top Bar ======= -->
        <OnlineHeaderComponent />

        <!-- ======= Hero Section ======= -->
        <section id="hero" class="d-flex align-items-center">
            <div
                class="container position-relative text-center text-lg-start"
                data-aos="zoom-in"
                data-aos-delay="100"
            >
                <div class="row">
                    <div class="col-lg-8">
                        <h1>
                            Welcome to <span>{{ company_name }}</span>
                        </h1>
                        <h2>Delivering great food for more than 10 years!</h2>

                        <div class="btns">
                            <a
                                href="#menu"
                                class="btn-menu animated fadeInUp scrollto"
                                >Our Menu</a
                            >
                            <router-link
                                to="#"
                                @click.native="goOrders()"
                                class="btn-book animated fadeInUp scrollto"
                                >Order Meals</router-link
                            >
                        </div>
                    </div>
                    <div
                        class="col-lg-4 d-flex align-items-center justify-content-center position-relative"
                        data-aos="zoom-in"
                        data-aos-delay="200"
                    >
                        <a
                            href="https://www.youtube.com/watch?v=u6BOC7CDUTQ"
                            class="glightbox play-btn"
                        ></a>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Hero -->

        <main id="main">
            <!-- ======= About Section ======= -->
            <section id="about" class="about">
                <div class="container" data-aos="fade-up">
                    <div class="row">
                        <div
                            class="col-lg-6 order-1 order-lg-2"
                            data-aos="zoom-in"
                            data-aos-delay="100"
                        >
                            <div class="about-img">
                                <img src="../assets/img/rest_bg.jpeg" alt="" />
                            </div>
                        </div>
                        <div
                            class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 content"
                        >
                            <h3 class="text-white">
                                Are you looking for delicious food, a relaxed
                                environment and good value?
                                {{ company_name }} is the perfect place for your
                                next meal, either dine in or take away
                            </h3>
                            <p class="fst-italic text-white">
                                Since our modest beginnings in 2018 Ramadan with
                                a little banda in 3rd Parklands Nairobi,
                                {{ company_name }}‘s development has been
                                enlivened with the energy to cook and serve
                                solid, Indian-roused takeout food.
                            </p>
                            <ul>
                                <li>
                                    <i class="bi bi-check-circle"></i>
                                    <span class="text-white">
                                        We offer diners with private and ambient
                                        sitting spaces where theycan dine and
                                        enjoy sumptous meals</span
                                    >
                                </li>
                                <li>
                                    <i class="bi bi-check-circle"></i
                                    ><span class="text-white"
                                        >We also offer takeaway food services
                                        just incase you want to share our
                                        delicacies with your loved onces.</span
                                    >
                                </li>
                                <li>
                                    <i class="bi bi-check-circle"></i
                                    ><span class="text-white"
                                        >You can make online orders and have it
                                        delivered at your doorstep within the
                                        shortest time possible.</span
                                    >
                                </li>
                            </ul>
                            <p class="text-white">
                                Not only has the restaurant produced impeccable
                                dishes with stellar service to match, but it has
                                also moulded the careers of many talented chefs
                                as they underwent mentorship from
                                {{ company_name }}. This leaves a legacy as the
                                restaurant was home to inspiration, creativity,
                                and culinary exploration.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End About Section -->

            <!-- ======= Why Us Section ======= -->
            <section id="why-us" class="why-us">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2>Why Us</h2>
                        <p>Why Choose {{ company_name }}</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div
                                class="box"
                                data-aos="zoom-in"
                                data-aos-delay="100"
                            >
                                <span>01</span>
                                <h4>Superior Value</h4>
                                <p>
                                    If you are looking for value for money,
                                    Mixed Grill delivers. You will receive a
                                    fresh 100% home made dish in a portion size
                                    that won’t leave you scrapping your plate.
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <div
                                class="box"
                                data-aos="zoom-in"
                                data-aos-delay="200"
                            >
                                <span>02</span>
                                <h4>Flexible Menu</h4>
                                <p>
                                    Whether you’re looking to dine with two
                                    people or 20 people, we can mix and match
                                    food combinations without a hassle. No one
                                    has to fight over the last piece of chicken!
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <div
                                class="box"
                                data-aos="zoom-in"
                                data-aos-delay="300"
                            >
                                <span>03</span>
                                <h4>Fresh Food</h4>
                                <p>
                                    Our food is made fresh every day with
                                    ingredients. We bring the best taste in our
                                    cooking to make your day happier and
                                    healthier. No matter which season is going
                                    on but giving you extraordinary is a part of
                                    our service.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Why Us Section -->

            <!-- ======= Menu Section ======= -->
            <Menus />
            <!-- End Menu Section -->

            <!-- ======= Book A Table Section ======= -->
            <TableBooking />
            <!-- End Book A Table Section -->

            <!-- ======= Gallery Section ======= -->
            <section id="gallery" class="gallery">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2>Gallery</h2>
                        <p>Some photos from Our Restaurant</p>
                    </div>
                </div>

                <div
                    class="container-fluid"
                    data-aos="fade-up"
                    data-aos-delay="100"
                >
                    <div class="row g-0">
                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-1.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-1.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-2.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-2.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-3.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-3.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-4.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-4.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-5.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-5.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-6.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-6.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-7.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-7.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4">
                            <div class="gallery-item">
                                <a
                                    href="../assets/img/gallery/gallery-8.jpg"
                                    class="gallery-lightbox"
                                    data-gall="gallery-item"
                                >
                                    <img
                                        src="../assets/img/gallery/gallery-8.jpg"
                                        alt=""
                                        class="img-fluid"
                                    />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Gallery Section -->

            <!-- ======= Chefs Section ======= -->
            <section id="chefs" class="chefs">
                <div class="container" data-aos="fade-up">
                    <div class="section-title">
                        <h2>Chefs</h2>
                        <p>Our Proffesional Chefs</p>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div
                                class="member"
                                data-aos="zoom-in"
                                data-aos-delay="100"
                            >
                                <img
                                    src="../assets/img/chefs/chefs-1.jpg"
                                    class="img-fluid"
                                    alt=""
                                />
                                <div class="member-info">
                                    <div class="member-info-content">
                                        <h4>Walter White</h4>
                                        <span>Master Chef</span>
                                    </div>
                                    <div class="social">
                                        <a href=""
                                            ><i class="bi bi-twitter"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-facebook"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-instagram"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-linkedin"></i
                                        ></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div
                                class="member"
                                data-aos="zoom-in"
                                data-aos-delay="200"
                            >
                                <img
                                    src="../assets/img/chefs/chefs-2.jpg"
                                    class="img-fluid"
                                    alt=""
                                />
                                <div class="member-info">
                                    <div class="member-info-content">
                                        <h4>Sarah Jhonson</h4>
                                        <span>Patissier</span>
                                    </div>
                                    <div class="social">
                                        <a href=""
                                            ><i class="bi bi-twitter"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-facebook"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-instagram"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-linkedin"></i
                                        ></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div
                                class="member"
                                data-aos="zoom-in"
                                data-aos-delay="300"
                            >
                                <img
                                    src="../assets/img/chefs/chefs-3.jpg"
                                    class="img-fluid"
                                    alt=""
                                />
                                <div class="member-info">
                                    <div class="member-info-content">
                                        <h4>William Anderson</h4>
                                        <span>Cook</span>
                                    </div>
                                    <div class="social">
                                        <a href=""
                                            ><i class="bi bi-twitter"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-facebook"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-instagram"></i
                                        ></a>
                                        <a href=""
                                            ><i class="bi bi-linkedin"></i
                                        ></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Chefs Section -->

            <!-- ======= Contact Section ======= -->
            <contacts-components />
            <!-- End Contact Section -->
        </main>
        <!-- End #main -->

        <!-- ======= Footer ======= -->
        <!-- End Footer -->

        <div id="preloader"></div>
        <a
            href="#"
            class="back-to-top d-flex align-items-center justify-content-center"
            ><i class="bi bi-arrow-up-short"></i
        ></a>

        <!-- Vendor JS Files -->

        <notifications group="foo" />
    </div>
</template>

<script>
import AutoLogout from "./AutoLogout.vue";
import ContactsComponents from "./restaurant/home_components/ContactsComponents.vue";
import Enqueries from "./restaurant/home_components/Enqueries.vue";
import Menus from "./restaurant/home_components/Menus.vue";
import TableBooking from "./restaurant/home_components/TableBooking.vue";
import OnlineHeaderComponent from "./restaurant/OnlineHeaderComponent.vue";
export default {
    data() {
        return {};
    },
    mounted() {},
    components: {
        AutoLogout,
        Menus,
        TableBooking,
        Enqueries,
        OnlineHeaderComponent,
        ContactsComponents,
    },
    created() {},
    methods: {
        goOrders() {
            window.location.href = "online_ordering";
            //this.$router.push({ name: "online_ordering"})
        },
    },
};
</script>
<style scoped></style>
