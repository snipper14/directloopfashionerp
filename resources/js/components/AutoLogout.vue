<template>
    <div v-if="warningZone">Are you still with us?</div>
</template>

<script>
export default {
    name: 'AutoLogout',
    data: function () {
        return {
            events: ['click', 'mousemove', 'mousedown', 'scroll', 'keypress', 'load'],
            warningTimer: null,
            logoutTimer: null,
            warningZone: false,
        }
    },
    mounted() {
        this.events.forEach(function (event) {
            window.addEventListener(event, this.resetTimer);
        }, this);
        this.setTimers();
    },
    destroyed() {
        this.events.forEach(function (event) {
            window.removeEventListener(event, this.resetTimer);
        }, this);
        this.resetTimer();
    },
    methods: {
         
        setTimers: function() {
            this.warningTimer = setTimeout(this.warningMessage,20* 60 * 1000); // 20 minutes - 14 * 60 * 1000
            this.logoutTimer = setTimeout(this.logoutUser, 21*60* 1000); // 21 minutes - 15 * 60 * 1000
            this.warningZone = false;
         
        },
        warningMessage: function() {
            this.warningZone = true;
               this.logoutConfirmation()
        },
        logoutUser: function() {
            // Laravel
          //  document.getElementById('logout-form').submit();
            this.$router.push('logout')
           window.location.reload()
        
        },
        resetTimer: function() {
            clearTimeout(this.warningTimer);
            clearTimeout(this.logoutTimer);
            this.setTimers();
        }
    }
}
</script>
