<template>
    <div>
        <div class="row mb-1">
            <nav aria-label="Page nav">
                <ul class="pagination">
                    <li
                        class="page-item"
                        v-if="results.current_page > 2"
                        @click.prevent="$emit('get-page', 1)"
                    >
                        <a href="#" class="page-link">
                            <v-icon medium>{{
                                icons.mdiChevronDoubleLeft
                            }}</v-icon>
                        </a>
                    </li>

                    <li
                        class="page-item"
                        v-if="results.current_page > 1"
                        @click.prevent="
                            $emit('get-page', results.current_page - 1)
                        "
                    >
                        <a href="#" class="page-link">
                            <v-icon medium>{{ icons.mdiChevronLeft }}</v-icon>
                        </a>
                    </li>
                    <!-- before pagenumbers -->
                    <li class="page-item" v-if="results.current_page > 5">
                        <span class="page-link">...</span>
                    </li>
                    <li
                        class="page-item"
                        v-for="num in results.last_page"
                        v-if="
                            num > results.current_page - 5 &&
                            num < results.current_page + 5
                        "
                        @click.prevent="$emit('get-page', num)"
                        :class="{ active: num === results.current_page }"
                    >
                        <a href="#" class="page-link">{{ num }}</a>
                    </li>
                    <li
                        class="page-item"
                        v-if="results.current_page < results.last_page - 5"
                    >
                        <span class="page-link">...</span>
                    </li>
                    <!-- after pagenumbers -->
                    <li
                        class="page-item"
                        v-if="results.current_page < results.last_page"
                        @click.prevent="
                            $emit('get-page', results.current_page + 1)
                        "
                    >
                        <a href="#" class="page-link"
                            ><v-icon medium>{{
                                icons.mdiChevronRight
                            }}</v-icon></a
                        >
                    </li>
                    <li
                        class="page-item"
                        v-if="results.current_page < results.last_page - 1"
                        @click.prevent="$emit('get-page', results.last_page)"
                    >
                        <a href="#" class="page-link">
                            <v-icon medium>{{
                                icons.mdiChevronDoubleRight
                            }}</v-icon></a
                        >
                    </li>
                </ul>
            </nav>
            <div class="mt-2 d-flex align-items-center">
                <input
                    v-model.number="goToPage"
                    @keyup.enter="jumpToPage"
                    type="number"
                    min="1"
                    :max="results.last_page"
                    placeholder="Page #"
                    class="form-control"
                    style="width: 120px; margin-right: 10px"
                />
                <button class="btn btn-sm btn-primary" @click="jumpToPage">
                    Go
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import {
    mdiChevronDoubleLeft,
    mdiChevronDoubleRight,
    mdiChevronLeft,
    mdiChevronRight,
} from "@mdi/js";
export default {
    data() {
        return {
            goToPage: null,
            icons: {
                mdiChevronDoubleLeft,
                mdiChevronDoubleRight,
                mdiChevronLeft,
                mdiChevronRight,
            },
        };
    },
    props: ["results"],
    mounted() {},
    methods: {
        jumpToPage() {
            if (
                this.goToPage &&
                Number.isInteger(this.goToPage) &&
                this.goToPage > 0 &&
                this.goToPage <= this.results.last_page
            ) {
                this.$emit("get-page", this.goToPage);
            } else {
                alert(
                    `Please enter a valid page number between 1 and ${this.results.last_page}`
                );
            }
        },
    },
};
</script>
