<template>
    <div class="row">
        <div class="col-md-4">
            <label for="">Search Customer</label>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <Select
                    v-model="customer_id"
                    filterable
                    :remote-method="searchCustomer"
                    :loading="loading1"
                >
                    <Option
                        v-for="(option, index) in search_customer_list"
                        :value="option.value"
                        :key="index"
                        >{{ option.label }}</Option
                    >
                </Select>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            customer_id: "",

            search_customer_list: [],
            loading1: false,
        };
    },
    mounted() {},
    watch: {
        customer_id: {
            handler(val, old) {
               var data = this.findId(this.search_customer_list, val);
                
                
                this.$emit("searchCustomerResults", data);
            },
        },
    },
    methods: {
        clearID() {
            this.customer_id = null;
        },
        findId(data, idToLookFor) {
            for (var i = 0; i < data.length; i++) {
                if (data[i].value == idToLookFor) {
                    return data[i];
                }
            }
        },
        searchCustomer: _.debounce(async function (query) {
            if (query.length !== "") {
                this.loading1 = true;

                const res = await this.getApi("data/customers/searchCustomer", {
                    params: { search: query },
                });
                this.loading1 = false;

                this.search_customer_list = this.modifyGuestKey2(
                    res.data
                ).filter(
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
                    label: obj.company_name+"-"+obj.company_phone,
                    pin:obj.pin,
                    is_discount_qualified:obj.is_discount_qualified,
                    loyalty_program:obj.loyalty_program,
                    created_at:this.formatDate(obj.created_at),
                    price_group_id:obj.price_group_id
                };
            });
            return modif;
        },
    },
};
</script>
