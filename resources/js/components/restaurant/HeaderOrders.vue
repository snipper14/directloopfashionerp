<template>
    <div id="topbar" class="d-flex align-items-center fixed-top">
        <div id="topbar" class="d-flex align-items-center fixed-top">
            <div
                class="container d-flex justify-content-center justify-content-md-between"
            >
                <div class="contact-info d-flex align-items-center">
                    <i class="bi bi-phone d-flex align-items-center"
                        ><span>+1 5589 55488 55</span></i
                    >
                    <i class="bi bi-clock d-flex align-items-center ms-4"
                        ><span> Mon-Sat: 11AM - 23PM</span></i
                    >
                </div>

                <div class="languages d-none d-md-flex align-items-center">
                    <ul>
                        <li>En</li>
                        <li><a href="#">De</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- ======= Header ======= -->
        <header id="header" class="fixed-top d-flex align-items-cente">
            <div
                class="container-fluid container-xl d-flex align-items-center justify-content-lg-between"
            >
                <h1 class="logo me-auto me-lg-0">
                    <router-link to="#" @click.native="goHome()">{{
                        company_name
                    }}</router-link>
                </h1>
                <!-- Uncomment below if you prefer to use an image logo -->
                <!-- <a href="index.html" class="logo me-auto me-lg-0"><img src="../assets/img/logo.png" alt="" class="img-fluid"></a>-->

                <nav id="navbar" class="navbar order-last order-lg-0">
                    <ul>
                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >Home</router-link
                            >
                        </li>

                        <li>
                            <router-link
                                class="nav-link active"
                                to="#"
                                @click.native="goOrders()"
                            >
                                Order</router-link
                            >
                        </li>

                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >About</router-link
                            >
                        </li>
                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >Menu</router-link
                            >
                        </li>
                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >Specials</router-link
                            >
                        </li>

                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >Chefs</router-link
                            >
                        </li>
                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >Gallery</router-link
                            >
                        </li>

                        <li>
                            <router-link
                                class="nav-link"
                                to="#"
                                @click.native="goHome()"
                                >Contact</router-link
                            >
                        </li>
                    </ul>
                    <i class="bi bi-list mobile-nav-toggle"></i>
                </nav>
                <!-- .navbar -->

                <router-link
                    class="nav-link book-a-table-btn scrollto d-none d-lg-flex"
                    to="#"
                    @click.native="goHome()"
                    >Book a table</router-link
                >
            </div>
        </header>
        <!-- End Header -->
    </div>
</template>

<script>
export default {
    data() {
        return {};
    },
    mounted() {
        console.log("Component mounted.");
    },
    methods: {
        goOrders() {
            window.location.href = "online_ordering";
            //this.$router.push({ name: "online_ordering"})
        },
        goHome() {
            window.location.href = "/";
        },
    },
};
</script>
