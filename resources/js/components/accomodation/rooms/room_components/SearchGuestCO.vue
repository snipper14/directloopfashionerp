<template>
    <div>
        <div class="form-group">
            <label for="">Visitor's Company</label>
            <Select
                v-model="guest_company_id"
                filterable
                :remote-method="searchGuestCO"
                :loading="loading1"
            >
                <Option
                    v-for="(option, index) in search_guest_co_list"
                    :value="option.value"
                    :key="index"
                    >{{ option.label }}</Option
                >
            </Select>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            guest_company_id: "",
            search_guest_co_list: [],
            loading1: false,
        };
    },
    mounted() {
    },
     watch: {
        guest_company_id: {
            handler(val, old) {
               this.$emit('guestCOSearchSelected',val)
            },
        },
     },
    methods: {
        clearID(){
            this.guest_company_id=null
        },
        searchGuestCO: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;

                const res = await this.getApi("data/guest_co/fetch", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_guest_co_list = this.modifyGuestKey2(res.data).filter(
                    (item) =>
                        item.label.toLowerCase().indexOf(query.toLowerCase()) >
                        -1
                );
            }
        }, 500),
        modifyGuestKey2(data) {
            let modif = data.map((obj) => {
                return {
                    value: obj.id,
                    label: obj.name,
                };
            });
            return modif;
        },
    },
};
</script>
