<template>
    <section id="menu" class="menu section-bg">
        <div class="container" data-aos="fade-up">
            <div class="section-title">
                <h2>Menu</h2>
                <p>Check Our Tasty Menu</p>
            </div>

            <div class="row" data-aos="fade-up" data-aos-delay="100">
                <div class="col-lg-12 d-flex justify-content-center">
                    <ul id="menu-flters">
                        <li data-filter="*" class="filter-active">All</li>
                        <li
                            :data-filter="'.' + value.category"
                            v-for="(value, i) in menus_data"
                            :key="i"
                        >
                            {{ value.category.toUpperCase() }}
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="row menu-container"
                data-aos="fade-up"
                data-aos-delay="200"
            >
                <div
                    :class="'col-lg-6 menu-item ' + value.product_category.name"
                    v-for="(value, i) in stock_data"
                    :key="i"
                >
                    <img
                        v-if="value.image"
                        :src="value.img_url"
                        class="menu-img"
                        alt=""
                    />
                    <img
                        v-else
                        src="../../../assets/img/menu/empty_food_placeholder.jpg"
                        class="menu-img"
                        alt=""
                    />
                    <div class="menu-content">
                        <a href="#">{{ value.product_name }}</a
                        ><span
                            >Ksh {{ cashFormatter(value.selling_price) }}</span
                        >
                    </div>
                    <div class="menu-ingredients">
                        {{ value.menu_description }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import Pagination from "../../utilities/Pagination.vue";
export default {
    components: { Pagination },
    data() {
        return {
            menus_data: [],
            stock_data: [],
        };
    },
    mounted() {

      
//  setTimeout(() => {
//                     this.concurrentApiReq();
//                     }, 5000);
        this.concurrentApiReq();
    },
    methods: {
        async concurrentApiReq() {
            //   this.showLoader();
            const res = await Promise.all([
                this.getProductCategories(),
                this.getMenuProduct(),
            ]).then(function (results) {
                return results;
            });
            // this.hideLoader();
        },
        async getProductCategories() {
            const res = await this.getApi("online_menu/fetch", {
                params: {},
            });

            if (res.status == 200) {
                this.menus_data = res.data;
               
            } else {
                this.swr("Server error occurred");
            }
        },
        async getMenuProduct() {
            const res = await this.getApi("online_menu/fetchStock", {
                params: {},
            });

            if (res.status == 200) {
                this.stock_data = res.data;
             
            } else {
                this.swr("Server error occurred");
            }
        },
    },
};
</script>
