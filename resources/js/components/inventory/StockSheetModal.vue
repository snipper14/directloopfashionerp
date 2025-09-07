<template>
    <v-app class="">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Stock Sheet</div>

                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-md-3 d-flex flex-column">
                                <label for="">Location*</label>
                                <Select
                                    v-model="search_params.department_id"
                                    multiple
                                >
                                    <Option
                                        style="color: black"
                                        v-for="item in dept_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.department }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="form-group col-md-3 d-flex flex-column">
                                <label for="">Category*</label>
                                <Select
                                    v-model="search_params.product_category_id"
                                    multiple
                                >
                                    <Option
                                        v-for="item in product_category_data"
                                        :value="item.id"
                                        :key="item.id"
                                        >{{ item.name }}</Option
                                    >
                                </Select>
                            </div>
                            <div class="col-md-3">
                                <v-btn color="primary" v-if="search_params.department_id.length>0" @click="printReport">Print sheet</v-btn>
                            </div>
                            <div class="col-md-3 d-flex flex-column">
                                <label for="">Shelf</label>
                                <Select
                                    v-model="shelf_id"
                                    multiple
                                >
                                    <Option
                                        :value="0"
                                        disabled
                                        class="font-weight-bold"
                                        >-- Select Shelf --</Option
                                    >
                                    <Option :value="'all'">Select All</Option>
                                    <Option
                                        v-for="item in shelf_select_data"
                                        :value="item.id"
                                        :key="item.id"
                                    >
                                        {{ item.name }}
                                    </Option>
                                </Select>
                                <v-btn
                                    color="primary"
                                    class="mt-2"
                                    @click="applyShelfFilter"
                                    >Apply Shelf Filter</v-btn
                                >
                            </div>
                            <div class="col-md-3" v-if="search_params.department_id.length>0">
                                <export-excel
                                    class="btn btn-default btn-export ml-2"
                                    :fetch="fetchExcel"
                                    type="csv"
                                    worksheet="My Worksheet"
                                    name="stocksheet.csv"
                                >
                                    <v-btn color="primary" x-small>
                                        Export Stock Sheet
                                    </v-btn>
                                </export-excel>
                            </div>
                        </div>
                        <div class="row">
                            <div ref="printSection">
                                <div class="mb-3 text-center">
                                    <h4>Stock Sheet</h4>
                                    <p>Date: {{ stock_sheet_date }}</p>
                                    <p>User: {{ user }}</p>
                                </div>
                                <table class="table">
                                    <thead>
                                     
                                        <th style="cursor: pointer" @click="toggleSort('sort_code')">
                                            Code
                                            <span v-if="sort_params.sort_code === 'asc'">⬆️</span>
                                            <span v-else-if="sort_params.sort_code === 'desc'">⬇️</span>
                                             <span v-else>↕️</span>
                                        </th>
                                        <th style="cursor: pointer" @click="toggleSort('sort_name')">
                                            Item Name 
                                             
                                            <span v-if="sort_params.sort_name === 'asc'">⬆️</span>
                                            <span v-else-if="sort_params.sort_name === 'desc'">⬇️</span>
                                              <span v-else>↕️</span>
                                        </th>
                                        <th style="cursor: pointer" @click="toggleSort('sort_shelf')">
                                            Shelves
                                            <span v-if="sort_params.sort_shelf === 'asc'">⬆️</span>
                                            <span v-else-if="sort_params.sort_shelf === 'desc'">⬇️</span>
                                              <span v-else>↕️</span>
                                        </th>
                                        <th>Counted qty</th>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(value, i) in stock_data" :key="i">
                                         
                                            <td>{{ value.code }}</td>
                                            <td>{{ value.product_name }}</td>
                                            <td>{{ value.shelf ? value.shelf : '-' }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-app>
</template>

<script>
export default {
    data() {
        return {
            filters: {
                shelf_id: [], // array of selected shelf ids
                // ... other filters
            },
            shelf_select_data: [],
            stock_data: [],
            dept_data: null,
            product_category_data: null,
            search_params: {
                department_id: [], // was null
                product_category_id: [],
                shelf_id: [], // Added for shelf filter
                search: "",
            },
            shelf_id: [],
            stock_sheet_date: this.getDateTime(),
            user: this.$store.state.user.name,
            sort_params: {
                sort_category: "",
                sort_code: "",
                sort_name: "",
                sort_shelf: "",
            },
        };
    },
    mounted() {
        this.concurrentApiReq();
    },
    watch: {
        sort_params: {
            deep: true,
            handler() {
                this.fetchStockSheet();
            },
        },
        search_params: {
            deep: true,
            handler(newVal) {
                // Only fetch if department_id or product_category_id changes
                if (newVal.department_id || newVal.product_category_id) {
                    this.fetchStockSheet();
                }
            },
        },
        shelf_id: {
            deep: true,
            handler(newVal) {
                console.log('shelf_id changed, newVal:', newVal);
                if (!Array.isArray(newVal)) {
                    newVal = newVal ? [newVal] : [];
                }
                let newShelfId;
                if (newVal.includes("all")) {
                    newShelfId = this.shelf_select_data.map((d) => d.id);
                } else {
                    newShelfId = newVal.filter((v) => v !== "all" && v !== 0);
                }
                // Only update shelf_id if the new value is different
                if (JSON.stringify(newShelfId) !== JSON.stringify(this.shelf_id)) {
                    this.shelf_id = newShelfId;
                    console.log('shelf_id updated:', this.shelf_id);
                } else {
                    console.log('shelf_id unchanged, no update needed');
                }
            },
        },
    },
    methods: {
        applyShelfFilter() {
            // Trigger fetchStockSheet when the shelf filter button is clicked
            this.fetchStockSheet();
        },
        async fetchShelf() {
            const res = await this.getApi("data/shelves/fetch", {});
            if (res.status == 200) {
                this.shelf_select_data = res.data.map((obj) => {
                    return {
                        id: obj.id,
                        name: obj.name,
                    };
                });
            } else {
                this.swr("Server error occurred");
            }
        },
        fetchExcel() {
            if (this.stock_data.length > 0) {
                const data_array = [];
                this.stock_data.map((data) => {
                    data_array.push({
                        STOCK_ID: data.id,
                        LOCATION_ID: this.search_params.department_id,
                        CODE: data.code,
                        ITEM: data.product_name,
                        UNIT: data.unit,
                        CATEGORY: data.category,
                        "COUNTED QTY": 0,
                        shelf:data.shelf
                    });
                });
                return data_array;
            } else {
                this.errorNotif("select location first");
                return;
            }
        },
        async concurrentApiReq() {
            const res = await Promise.all([
                this.fetchDept(),
                this.fetchCategories(),
                this.fetchShelf(),
            ]);
        },
        async fetchStockSheet() {
            this.showLoader();
            const res = await this.getApi("data/stock/fetchStockSheet", {
                params: {
                    search: this.search_params.search,
                    department_id: this.search_params.department_id.join(","),
                    product_category_id: this.search_params.product_category_id.join(","),
                    shelf_id: this.shelf_id.join(","), // Include shelf_id in request
                    ...this.sort_params,
                },
            });
            this.hideLoader();
            if (res.status == 200) {
                this.stock_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchDept() {
            const res = await this.getApi("data/dept/fetch", "");
            if (res.status == 200) {
                this.dept_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        async fetchCategories() {
            const res = await this.getApi("data/product_category/fetch", {
                params: {},
            });
            if (res.status == 200) {
                this.product_category_data = res.data;
            } else {
                this.swr("Server error occurred");
            }
        },
        toggleSort(key) {
            const current = this.sort_params[key];
            this.sort_params = {
                sort_category: "",
                sort_code: "",
                sort_name: "",
                sort_shelf: "",
            };
            this.sort_params[key] = current === "asc" ? "desc" : "asc";
        },
        printReport() {
            const printContents = this.$refs.printSection.innerHTML;
            const printWindow = window.open("", "", "height=700,width=900");
            printWindow.document.write("<html><head><title>Stock Sheet</title>");
            printWindow.document.write(`
                <style>
                    body { font-family: Arial, sans-serif; padding: 10px; }
                    .mb-3 { margin-bottom: 10px; text-align: center; }
                    h4 { font-size: 16px; margin: 0; }
                    p { font-size: 12px; margin: 5px 0; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { 
                        border: 1px solid #ccc; 
                        padding: 3px 5px; 
                        font-size: 11px; 
                        line-height: 1.2; 
                        height: 18px; 
                    }
                    th { 
                        background-color: #f0f0f0; 
                        font-weight: bold; 
                    }
                    tr { 
                        height: 18px; 
                        overflow: hidden; 
                    }
                </style>
            `);
            printWindow.document.write("</head><body>");
            printWindow.document.write(printContents);
            printWindow.document.write("</body></html>");
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        },
    },
};
</script>
<style scoped></style>