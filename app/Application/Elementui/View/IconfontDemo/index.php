<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <h1 style="text-align: center;">内置iconfont</h1>
        <iframe src="/statics/css/iconfont/demo_index.html" frameborder="0" style="height:100%;width: 100%"></iframe>
    </div>

    <style>
        html,body, #app{
            height: 100%;
            width: 100%;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {},
                watch: {},
                filters: {},
                methods: {},
                mounted: function () {

                },

            })
        })
    </script>
</block>
