<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="16" :md="8" >
                <!--                插入template 文件-->

            </el-col>
        </el-card>
    </div>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                // 插入export default里面的内容
            });
        });
    </script>

    <style>

    </style>

</block>