<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-2"><POSReportNav /></div>
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Merged Bills Report</div>

                    <div class="card-body">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link active"
                                    id="home-tab"
                                    data-toggle="tab"
                                    href="#home"
                                    role="tab"
                                    aria-controls="home"
                                    aria-selected="true"
                                    >Merged POS Report</a
                                >
                            </li>
                         
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div
                                class="tab-pane fade show active"
                                id="home"
                                role="tabpanel"
                                aria-labelledby="home-tab"
                            >
                                <MergeBillPOS />
                            </div>
                            <div
                                class="tab-pane fade"
                                id="single"
                                role="tabpanel"
                                aria-labelledby="single-tab"
                            >
                                <merge-bill-room-service />
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>

        <notifications group="foo" />
    </div>
</template>

<script>
import POSReportNav from "../POSReportNav.vue";
import Unauthorized from "../../../utilities/Unauthorized.vue";
import Pagination from "../../../utilities/Pagination.vue";
import MergeBillPOS from "./MergeBillPOS.vue";
import MergeBillRoomService from "./MergeBillRoomService.vue";

export default {
    data() {
        return {};
    },
    components: {
        POSReportNav,
        Unauthorized,
        Pagination,
        MergeBillPOS,
        MergeBillRoomService,
    },
};
</script>
