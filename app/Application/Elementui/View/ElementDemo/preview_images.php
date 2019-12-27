<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>图片预览-view.js</h3>
            <el-row>
                <el-col :span="8">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="1.单图展示">
                                <div id="galley">
                                    <ul class="pictures">
                                        <template v-for="img_url in preview_images">
                                            <li @click="previewImage(img_url)">
                                                <img data-original="../images/tibet-1.jpg" :src="img_url">
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-col>
                <el-col :span="16"><div class="grid-content "></div></el-col>
            </el-row>

            <h3>图片预览-Element Image图片组件</h3>
            <el-row>
                <el-col :span="8">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="相册">
                                <el-image v-for="url in preview_images" :key="url" :src="url" lazy style="width: 80px;margin-right:4px" :preview-src-list="[url]"></el-image>
                            </el-form-item>

                        </el-form>
                    </div>
                </el-col>
                <el-col :span="16"><div class="grid-content "></div></el-col>
            </el-row>



        </el-card>
    </div>

    <style>
        .pictures{
            padding-left: 0;
        }
        .pictures li{
            list-style: none;
            width: 80px;
            display: inline-block;
            margin-right: 4px;
        }
        .pictures li img{
            height: 100%;
            width: 100%;
        }
    </style>


</block>

<block name="footer">
    <!--  引入viewer.js  -->
    <script src="{$config_siteurl}statics/admin/viewerjs/viewer.min.js"></script>
    <link rel="stylesheet" href="{$config_siteurl}statics/admin/viewerjs/viewer.min.css">
    <!--  引入viewer.js  END -->

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    // 图片列表
                    preview_images: [
                        'https://picsum.photos/id/10/1250/833',
                        'https://picsum.photos/id/1002/1078/717',
                        'https://picsum.photos/id/1015/1000/666',
                        'https://picsum.photos/id/1016/961/641',
                    ]

                },
                watch: {},
                filters: {},
                methods: {
                    /**
                     * 预览图片
                     * @param url 图片URL
                     */
                    previewImage: function(url){
                        console.log(url)
                        var image = new Image()
                        image.src = url
                        var viewer = new Viewer(image, {
                            hidden: function () {
                                viewer.destroy();
                            },
                        });
                        viewer.show();
                    }
                },
                mounted: function () {

                },

            })
        })
    </script>
</block>
