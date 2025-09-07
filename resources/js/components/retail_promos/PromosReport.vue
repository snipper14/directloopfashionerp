<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-2"><promos-nav /></div>
            <div class="col-md-10" v-if="isReadPermitted">
                <div class="card">
                    <div class="card-header">Promos Dashboard</div>

                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User ID</th>
                                    <th>Lower Limit</th>
                                    <th>Upper Limit</th>
                                    <th>Discount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(promo, index) in promos"
                                    :key="promo.id"
                                >
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ promo.user.name }}</td>
                                    <td>
                                        <input
                                        :disabled="!isUpdatePermitted"
                                            type="number"
                                            v-model="promo.lower_limit"
                                            class="form-control"
                                            @change="
                                                updatePromo(
                                                    promo.id,
                                                    'lower_limit',
                                                    promo.lower_limit
                                                )
                                            "
                                        />
                                    </td>
                                    <td>
                                        <input
                                         :disabled="!isUpdatePermitted"
                                            type="number"
                                            v-model="promo.upper_limit"
                                            class="form-control"
                                            @change="
                                                updatePromo(
                                                    promo.id,
                                                    'upper_limit',
                                                    promo.upper_limit
                                                )
                                            "
                                        />
                                    </td>
                                    <td>
                                        <input
                                         :disabled="!isUpdatePermitted"
                                            type="number"
                                            v-model="promo.discount"
                                            class="form-control"
                                            @change="
                                                updatePromo(
                                                    promo.id,
                                                    'discount',
                                                    promo.discount
                                                )
                                            "
                                        />
                                    </td>
                                    <td>
                                        <select
                                         :disabled="!isUpdatePermitted"
                                            v-model="promo.status"
                                            class="form-control"
                                            @change="
                                                updatePromo(
                                                    promo.id,
                                                    'status',
                                                    promo.status
                                                )
                                            "
                                        >
                                            <option value="active">
                                                Active
                                            </option>
                                            <option value="inactive">
                                                Inactive
                                            </option>
                                        </select>
                                    </td>
                                    <td>
                                        <button
                                        v-if="isDeletePermitted"
                                        title="DELETE"
                                            class="btn btn-danger btn-sm"
                                            @click="deletePromo(promo.id)"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div v-if="loading" class="text-center">
                            <p>Loading promos...</p>
                        </div>
                        <div v-if="error" class="alert alert-danger">
                            {{ error }}
                        </div>
                    </div>
                </div>
            </div>
            <div v-else><unauthorized /></div>
        </div>
    </div>
</template>

<script>
import Unauthorized from "../utilities/Unauthorized.vue";
import PromosNav from "./PromosNav.vue";

export default {
    components: { PromosNav, Unauthorized },
    data() {
        return {
            promos: [], // Array to store fetched promos
            loading: false, // Loading state
            error: null, // Error state
        };
    },
    methods: {
        async postApi(endpoint, data) {
            // Simulate an API call to update a promo
            console.log(`Updating promo via ${endpoint}`, data);
            return { status: 200 };
        },
        async fetchPromos() {
            this.loading = true;
            this.error = null;

            try {
                const res = await this.getApi("data/promos/fetch", {});
                this.promos = res.data;
            } catch (err) {
                this.error = "Failed to load promos.";
            } finally {
                this.loading = false;
            }
        },
        async updatePromo(id, field, value) {
            const res = await this.callApi("PUT", `data/promos/update`, {
                [field]: value,
                id: id,
            });
            if (res.status === 200) {
                this.successNotif("Saved");
            } else {
                this.form_error(res);
            }
        },
        async deletePromo(id) {
            if (!confirm("Are you sure you want to delete this promo?")) return;

            try {
                const res = await this.callApi(
                    "DELETE",
                    `data/promos/destroy/${id}`,
                    ""
                );
                if (res.status === 200) {
                    this.promos = this.promos.filter(
                        (promo) => promo.id !== id
                    );
                    alert("Promo deleted successfully.");
                }
            } catch (err) {
                alert("Failed to delete promo. Please try again.");
                console.error(err);
            }
        },
        // Mocking the API methods. Replace these with actual API calls.
    },
    mounted() {
        this.fetchPromos(); // Fetch promos when the component is mounted
    },
};
</script>

<style scoped>
.table {
    margin-top: 20px;
}

.alert {
    margin-top: 20px;
}
</style>
