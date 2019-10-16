
<!-- // --------------------vue--------------------- -->
<script>
$(document).ready(function() {
Vue.component('window-dashboard', {
    data: function () {
        return {
            count: 0,
            showSearchBox: false,
            fullscrn: false

        }
    },
    props: [
        'title'
    ],

    template: ` <div v-bind:class="['component',(fullscrn?'full-scrn':'')]" draggable="true" id="dragtarget1">
    <div class="head-component">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6">
                    <div class="compo-head">
                        <span class="spl-mouse"><img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/drag.svg" alt=""></span>
                        <span v-on:click="fullscrn = !fullscrn"> <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/min.svg" alt=""></span>
                        <span v-on:click="showSearchBox = !showSearchBox"> <img src="<?php echo $GLOBALS['assets_static_relative']; ?>/img/search-white.svg" alt=""></span>
                        <input type="text" v-bind:class="['component-search',(showSearchBox?'w-100':'')]">
                    </div>
                </div>
                <div class="col-6">
                    <p class="text-white head-p" v-text="title"></p>
                </div>
            </div>
        </div>
    </div>
    <div class="body-compo">
     
    <slot></slot>

    </div>
</div>`
})
new Vue({
    el: '#app',

    data: {
        isActive: false,
        width: false,
        width2: false,
        width3: false,
        fullscrn: false,
        showMailBox: true,
        addData: true,
        accordianShowCaseCounter: [2, 2, 0, 0, 0, 0, 0]
    },

    methods: {

    },
    mounted() {

    }

})
});
</script>
