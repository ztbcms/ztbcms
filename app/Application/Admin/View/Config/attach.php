<extend name="../../Admin/View/Common/element_layout"/>


<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="16" :md="12" >
                <!--                插入template 文件-->
                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px"
                                 label-position="top">
                            <el-form-item label="网站存储方案" prop="attachment_driver">
                                <el-select v-model="formData.attachment_driver" placeholder="请选择网站存储方案" clearable
                                           :style="{width: '100%'}">
                                    <volist name="dirverList" id="vo" key="key">
                                        <el-option label="{$vo}" value="{$key}"></el-option>
                                    </volist>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="允许上传附件大小" prop="uploadmaxsize">
                                <el-input v-model="formData.uploadmaxsize" placeholder="请输入允许上传附件大小" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">KB</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="允许上传附件类型" prop="uploadallowext">
                                <el-input v-model="formData.uploadallowext" placeholder="请输入允许上传附件类型" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">多个用"|"隔开</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="前台允许上传附件大小" prop="qtuploadmaxsize">
                                <el-input v-model="formData.qtuploadmaxsize" placeholder="请输入前台允许上传附件大小" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">KB</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="前台允许上传附件类型" prop="qtuploadallowext">
                                <el-input v-model="formData.qtuploadallowext" placeholder="请输入前台允许上传附件类型" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">多个用"|"隔开</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="保存远程图片过滤域名" prop="fileexclude">
                                <el-input v-model="formData.fileexclude" placeholder="请输入保存远程图片过滤域名" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">多个用"|"隔开，域名以"/"结尾，例如：http://www.ztbcms.com/</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="FTP服务器地址" prop="ftphost">
                                <el-input v-model="formData.ftphost" placeholder="请输入FTP服务器地址" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="FTP服务器端口" prop="ftpport">
                                <el-input v-model="formData.ftpport" placeholder="请输入FTP服务器端口" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="FTP上传目录" prop="ftpuppat">
                                <el-input v-model="formData.ftpuppat" placeholder="请输入FTP上传目录" clearable :style="{width: '100%'}">
                                    <template slot="append">"/"表示上传到FTP根目录</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="FTP用户名" prop="ftpuser">
                                <el-input v-model="formData.ftpuser" placeholder="请输入FTP用户名" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="FTP密码" prop="ftppassword">
                                <el-input v-model="formData.ftppassword" placeholder="请输入FTP密码" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="FTP是否开启被动模式" prop="ftppasv">
                                <el-radio-group v-model="formData.ftppasv" size="medium">
                                    <el-radio v-for="(item, index) in ftppasvOptions" :key="index" :label="item.value"
                                              :disabled="item.disabled">{{item.label}}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="FTP是否使用SSL连接" prop="ftpssl">
                                <el-radio-group v-model="formData.ftpssl" size="medium">
                                    <el-radio v-for="(item, index) in ftpsslOptions" :key="index" :label="item.value"
                                              :disabled="item.disabled">{{item.label}}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="FTP超时时间" prop="ftptimeout">
                                <el-input v-model="formData.ftptimeout" placeholder="请输入FTP超时时间" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="是否开启图片水印" prop="watermarkenable">
                                <el-radio-group v-model="formData.watermarkenable" size="medium">
                                    <el-radio v-for="(item, index) in watermarkenableOptions" :key="index" :label="item.value"
                                              :disabled="item.disabled">{{item.label}}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="水印添加条件（宽）" prop="watermarkminwidth">
                                <el-input v-model="formData.watermarkminwidth" placeholder="请输入水印添加条件（宽）" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">PX</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="水印添加条件（高）" prop="watermarkminheight">
                                <el-input v-model="formData.watermarkminheight" placeholder="请输入水印添加条件（高）" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">PX</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="水印图片" prop="watermarkimg">
                                <el-input v-model="formData.watermarkimg" placeholder="请输入水印图片" clearable :style="{width: '100%'}">
                                    <template slot="append">水印存放路径从网站根目录起</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="水印透明度" prop="watermarkpct">
                                <el-input v-model="formData.watermarkpct" placeholder="请输入水印透明度" clearable :style="{width: '100%'}">
                                    <template slot="append">请设置为0-100之间的数字，0代表完全透明，100代表不透明</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="JPEG 水印质量" prop="watermarkquality">
                                <el-input v-model="formData.watermarkquality" placeholder="请输入JPEG 水印质量" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">水印质量请设置为0-100之间的数字,决定 jpg 格式图片的质量</template>
                                </el-input>
                            </el-form-item>

                            <el-form-item label="JPEG 水印质量" prop="watermarkquality">
                                <div class="locate">
                                    <ul class="cc" id="J_locate_list">
                                        <li class="<if condition="$Site['watermarkpos'] eq '1' "> current</if>"><a href="" data-value="1">左上</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '2' "> current</if>"><a href="" data-value="2">中上</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '3' "> current</if>"><a href="" data-value="3">右上</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '4' "> current</if>"><a href="" data-value="4">左中</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '5' "> current</if>"><a href="" data-value="5">中心</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '6' "> current</if>"><a href="" data-value="6">右中</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '7' "> current</if>"><a href="" data-value="7">左下</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '8' "> current</if>"><a href="" data-value="8">中下</a></li>
                                        <li class="<if condition="$Site['watermarkpos'] eq '9' "> current</if>"><a href="" data-value="9">右下</a></li>
                                    </ul>
                                    <input v-model="formData.watermarkpos" id="J_locate_input" name="watermarkpos" type="hidden" value="{$Site.watermarkpos}">
                                </div>
                            </el-form-item>


                            <el-form-item size="large">
                                <el-button type="primary" @click="submitForm">保存</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </template>
            </el-col>
        </el-card>
    </div>

    <script>
        $(document).ready(function () {
            window.app = new Vue({
                el: '#app',
                // 插入export default里面的内容
                components: {},
                props: [],
                data() {
                    return {
                        formData: {
                            attachment_driver: "{$Site.attachment_driver}",
                            uploadmaxsize: "{$Site.uploadmaxsize}",
                            uploadallowext: "{$Site.uploadallowext}",
                            qtuploadmaxsize: "{$Site.qtuploadmaxsize}",
                            qtuploadallowext: "{$Site.qtuploadallowext}",
                            fileexclude: "{$Site.fileexclude}",
                            ftphost: "{$Site.ftphost}",
                            ftpport: "{$Site.ftpport}",
                            ftpuppat: "{$Site.ftpuppat}",
                            ftpuser: "{$Site.ftpuser}",
                            ftppassword: "{$Site.ftppassword}",
                            ftppasv: "{$Site.ftppasv}",
                            ftpssl: "{$Site.ftpssl}",
                            ftptimeout: "{$Site.ftptimeout}",
                            watermarkenable: "{$Site.watermarkenable}",
                            watermarkminwidth: "{$Site.watermarkminwidth}",
                            watermarkminheight: "{$Site.watermarkminheight}",
                            watermarkimg: "{$Site.watermarkimg}",
                            watermarkpct: "{$Site.watermarkpct}",
                            watermarkquality: "{$Site.watermarkquality}",
                            watermarkpos: "{$Site.watermarkpos}",
                        },
                        rules: {
                            attachment_driver: [{
                                required: true,
                                message: '请选择网站存储方案',
                                trigger: 'change'
                            }],
                            uploadmaxsize: [{
                                required: true,
                                message: '请输入允许上传附件大小',
                                trigger: 'blur'
                            }],
                            uploadallowext: [{
                                required: true,
                                message: '请输入允许上传附件类型',
                                trigger: 'blur'
                            }],
                            qtuploadmaxsize: [{
                                required: true,
                                message: '请输入前台允许上传附件大小',
                                trigger: 'blur'
                            }],
                            qtuploadallowext: [{
                                required: true,
                                message: '请输入前台允许上传附件类型',
                                trigger: 'blur'
                            }],
                            watermarkenable: [{
                                required: true,
                                message: '是否开启图片水印不能为空',
                                trigger: 'change'
                            }],
                            watermarkminwidth: [{
                                required: true,
                                message: '请输入水印添加条件（宽）',
                                trigger: 'blur'
                            }],
                            watermarkminheight: [{
                                required: true,
                                message: '请输入水印添加条件（高）',
                                trigger: 'blur'
                            }],
                            watermarkimg: [{
                                required: true,
                                message: '请输入水印图片',
                                trigger: 'blur'
                            }],
                            watermarkpct: [{
                                required: true,
                                message: '请输入水印透明度',
                                trigger: 'blur'
                            }],
                            watermarkquality: [{
                                required: true,
                                message: '请输入JPEG 水印质量',
                                trigger: 'blur'
                            }],
                        },
                        attachment_driverOptions: [{
                            "label": "选项一",
                            "value": '1'
                        }, {
                            "label": "选项二",
                            "value": '2'
                        }],
                        ftppasvOptions: [{
                            "label": "开启",
                            "value": '1'
                        }, {
                            "label": "关闭",
                            "value": '0'
                        }],
                        ftpsslOptions: [{
                            "label": "开启",
                            "value": '1'
                        }, {
                            "label": "关闭",
                            "value": '0'
                        }],
                        watermarkenableOptions: [{
                            "label": "开启",
                            "value": '1'
                        }, {
                            "label": "关闭",
                            "value": '0'
                        }],
                    }
                },
                computed: {},
                watch: {},
                created() {},
                mounted() {},
                methods: {
                    submitForm() {
                        this.$refs['elForm'].validate(valid => {
                            if (!valid) return;
                            // TODO 提交表单

                            $.ajax({
                                url: "{:U('Config/attach')}",
                                method: 'post',
                                dataType: 'json',
                                data: this.formData,
                                success: function (res) {
                                    if (!res.status) {
                                        layer.msg(res.info)
                                    } else {
                                        layer.msg(res.info)
                                    }
                                }
                            });
                        })
                    }
                }
            });
        });


        $(function(){
            //水印位置
            $('#J_locate_list > li > a').click(function(e){
                e.preventDefault();
                var $this = $(this);
                $this.parents('li').addClass('current').siblings('.current').removeClass('current');
                $('#J_locate_input').val($this.data('value'));

                window.app.formData.watermarkpos = $this.data('value');
            });
        });

    </script>

    <style>
        /*
===================
@explain: ç³»ç»ŸåŽå°å†…å®¹åŒºåŸŸ
===================
*/
        html,body,
        div, dl, dt, dd, ul, p, th, td,
        h1, h2, h3, h4, h5, h6,
        pre, code, form,
        fieldset, legend{
            margin: 0;
            padding: 0;
        }
        cite,
        em,
        th {
            font-style: inherit;
            font-weight: inherit;
        }
        strong{
            font-weight:700;
        }
        td,
        th,
        div {
            word-break:break-all;
            word-wrap:break-word;
        }
        table {
            border-collapse: collapse;
            border-spacing:0;
        }
        th {
            text-align: left;
            font-weight:100;
        }
        ol li {
            list-style: decimal outside;
        }
        ol{
            padding:0 0 0 18px;
            margin:0;
        }
        li {
            list-style:none;
        }
        img {
            border: 0;
        }
        html {
            -webkit-text-size-adjust:none;
        }
        /*
        ===================
        html5ç›¸å…³æ ‡ç­¾
        ===================
        */
        article,
        aside,
        details,
        figcaption,
        figure,
        footer,
        header,
        hgroup,
        nav,
        section {
            display: block;
        }

        /*
        ===================
        æ¸…é™¤æµ®åŠ¨
        * cc ä½œç”¨äºŽçˆ¶æ¨¡åž‹
        * c ä½œç”¨äºŽæµ®åŠ¨æ¨¡åž‹åŽï¼Œçˆ¶æ¨¡åž‹åŒºåŸŸé‡Œ
        ===================
        */
        .cc{
            zoom:1;
        }
        .cc:after{
            content:'\20';
            display:block;
            height:0;
            clear:both;
            visibility: hidden;
        }

        /*
        ===================
        ç°è‰²æ¸å˜èƒŒæ™¯
        ===================
        */
        .user_group dt,
        #error_tips h2,
        .task_item_list .hd,
        .medal_term .ct h6,
        .app_info .hd,
        .app_present .hd,
        .app_updata .hd,
        .sql_list dt{
            background:#f9f9f9;
            background-repeat: no-repeat;
            background-image: -webkit-gradient(linear, 0 0, 0 100%, from(#ffffff), color-stop(25%, #ffffff), to(#f4f4f4));
            background-image: -webkit-linear-gradient(#ffffff, #ffffff 25%, #f4f4f4);
            background-image: -moz-linear-gradient(top, #ffffff, #ffffff 25%, #f4f4f4);
            background-image: -ms-linear-gradient(#ffffff, #ffffff 25%, #f4f4f4);
            background-image: -o-linear-gradient(#ffffff, #ffffff 25%, #f4f4f4);
            background-image: linear-gradient(#ffffff, #ffffff 25%, #f4f4f4);
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f4f4f4', GradientType=0);
            border-bottom:1px solid #dfdfdf;
        }

        /*
        ===================
        æŒ‰é’®ã€å¯åœ¨inputã€buttonã€aä½¿ç”¨ï¼Œå¯è‡ªç”±ç»„åˆã€‘
        >>	btn									é»˜è®¤æŒ‰é’®
        >>	btn btn_big					å¤§æŒ‰é’®
        >>	btn btn_error				é”™è¯¯æŒ‰é’®
        >>	btn btn_success			ç¡®è®¤æŒ‰é’®
        >>	btn btn_submit			æäº¤æŒ‰é’®
        ===================
        */
        button::-moz-focus-inner,
        input::-moz-focus-inner {
            border: 0;
            padding: 0;
        }

        .btn em{
            font-size:10px;
            font-style:normal;
            padding-left:2px;
            font-family:Arial;
            vertical-align:1px;
        }
        .btn .add{
            width:9px;
            height:9px;
            background:url(../images/admin/content/btn_add.png) center center no-repeat;
            display:inline-block;
            vertical-align:middle;
            margin:-3px 5px 0 -3px;
            line-height:9px;
        }
        textarea,
        select {
            display: inline-block;
            /*width: 100%;*/
            height: 34px;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
        textarea{
            height:72px;
            overflow: auto;
            vertical-align: top;
            resize: vertical;
        }
        select,
        input[type="file"] {
            line-height: 28px;
        }
        select[size]{
            height:auto;
        }

        /*
        ===================
        åˆ†é¡µ
        >>	pages_pre		ä¸Šä¸€é¡µ
        >>	pages_next	ä¸‹ä¸€é¡µ
        >>	strong			æ¿€æ´»çŠ¶æ€
        >>	a:hover			é¼ æ ‡æ‚¬åœçŠ¶æ€
        ===================
        */
        .pages a,
        .pages strong{
            display:inline-block;
            line-height:28px;
            padding:0 10px;
            border:1px solid #d9d9d9;
            background-color:#ffffff;
            text-decoration:none;
            color:#666;
            font-family:Simsun;
            -webkit-transition: all 0.2s ease-out;transition: all 0.2s ease-out;
            margin-right:3px;
        }
        .pages  .current{
            display:inline-block;
            line-height:28px;
            padding:0 10px;
            border:1px solid #d9d9d9;
            background-color:#ffffff;
            text-decoration:none;
            font-family:Simsun;
            -webkit-transition: all 0.2s ease-out;transition: all 0.2s ease-out;
            margin-right:3px;

            color:#fff;
            background-color:#24557d;
            border-color:#fff;
        }
        .pages a:hover{
            color:#fff;
            background-color:#24557d;
            border-color:#fff;
            text-decoration:none;
        }
        .pages strong{
            color:#fff;
            background-color:#24557d;
            border-color:#fff;
        }
        .pages span{
            padding:0 10px;
            line-height:23px;
        }

        .tips_block .tips_error{
            background:#cc3333;
            color:#fff;
            display:block;
            padding:0;
            margin:0;
        }
        .tips_block .tips_success{
            background:#008800;
            color:#fff;
            display:block;
            padding:0;
            margin:0;
        }

        /*ä¸‹ç®­å¤´*/
        .tips_bubble .core_arrow_bottom{
            position:absolute;
            bottom:9px;
            right:10px;
            padding:0 20px;
        }
        .tips_bubble .core_arrow_bottom span,
        .tips_bubble .core_arrow_bottom em{
            position:absolute;
            width:0;
            height:0;
            padding:0;
            margin:9px 0 0 2px;
            border-bottom:8px transparent dashed;
            border-right:8px transparent dashed;
            border-left:8px transparent dashed;
            overflow:hidden;
        }
        .tips_bubble .core_arrow_bottom span{
            border-top:8px #fffbe7 solid;
        }
        .tips_bubble .core_arrow_bottom em{
            border-top:8px #f4d1a5 solid;
            margin-top:10px;
        }


        .core_pop_wrap a{
            color:#336699;
        }
        .pop_top strong{
            text-indent:15px;
            font-size:14px;
            color:#333;
            font-weight:700;
            white-space:nowrap;
            margin-right:10px;
            float:left;
        }
        .pop_top select{
            float:left;
            padding:1px;
            line-height:22px;
            height:22px;
        }
        .pop_top ul{
            border-bottom:1px solid #ccc;
            height:25px;
        }
        .pop_top li{
            float:left;
            display:block;
            line-height:25px;
            height:25px;
            padding:0 15px;
            cursor:pointer;
        }
        .pop_top li.current{
            float:left;
            border:1px solid #ccc;
            background:#fff;
            border-bottom:0 none;
        }
        .pop_top .pop_close{
            margin-right:15px;
        }

        .pop_bottom label{
            display:inline-block;
            padding-top:3px;
        }
        .pop_bottom .btn{
            padding-left:20px;
            padding-right:20px;
        }
        .pop_bottom .tips_error,
        .pop_bottom .tips_success{
            max-width:200px;
            float:left;
        }
        .founder_pop .pop_bottom .tips_error{
            width:150px;
        }
        .pop_table th,
        .pop_table td{
            padding:6px 0 6px;
        }
        .pop_table th{
            height:26px;
            line-height:26px;
            vertical-align:top;
            white-space:nowrap;
            padding-right:20px;
        }
        .pop_table tr:hover th{
            color:#000;
        }

        /*
        ===================
        å†…å®¹æ ·å¼å¼€å§‹
        ===================
        */
        body{
            font-family:Arial;
            color:#333;
            font-size:12px;
            background:#f3f3f3;
            line-height:1.5;
            _width:98%;
            overflow-x:hidden;
            overflow-y:auto;
        }
        body.body_none{
            background:#fff;
        }
        a{
            color:#266aae;
            text-decoration:none;
        }
        a:hover{
            text-decoration:underline;
        }
        .nav ul{
            height:35px;
            float:left;
        }
        .nav li,
        .nav .return{
            float:left;
        }
        .nav li a,
        .nav .return a{
            float:left;
            line-height:32px;
            color:#666;
            padding:0 15px;
            text-align:center;
        }
        .nav li a:hover{
            border-bottom:2px solid #aaa;
            color:#333;
            text-decoration:none;
        }

        .nav li.current a{
            border-bottom:2px solid #266aae;
            font-weight:700;
            color:#266aae;
        }
        .nav .return a{
            background:url(../images/admin/content/return.png) 2px center no-repeat;
            color:#990000;
            padding:0 20px;
        }
        .nav_minor li{
            float:left;
        }
        .nav_minor li a{
            float:left;
            line-height:24px;
            color:#666;
            padding:0 15px;
            text-align:center;
        }
        .nav_minor li a:hover{
            text-decoration:none;
            background:#e4e4e4;
        }
        .nav_minor li.current a{
            background:#ddd;
            color:#333;
            font-weight:700;
        }
        /*
        ===================
        æ ‡é¢˜æ ç»„
        ===================
        */
        tr.h_a th,
        tr.h_a td
        {
            padding-top:5px;
            padding-bottom:5px;
            line-height:18px;
            background:#e6e6e6 !important;
            border-top:1px solid #ddd;
            border-bottom:1px solid #ffffff;
        }
        .table_list .h_a{
            border-top:0 none;
        }
        .table_list td{
            padding:7px 10px 9px;
        }
        .table_list tr:hover td{
            color:#000;
            background-color:#f9f9f9;
        }
        .table_list thead tr td,
        .table_list thead tr:hover td{
            background:#e6e6e6;
            border-top:1px solid #ddd;
            border-bottom:1px solid #ffffff;
            color:#666 !important;
        }
        .table_list thead tr td select{
            padding:1px;
            height:23px;
            line-height:23px;
        }
        .table_list thead label{
            white-space:nowrap;
        }
        .table_list .thumb{
            line-height:1;
            vertical-align:top;
            margin-right:10px;
            float:left;
        }
        .table_list tr.bgA td{
            background-color:#f0f8fc;
        }
        /*
        ===================
        åŠŸèƒ½è¡¨æ ¼åˆ—è¡¨
        ===================
        */
        .table_full{
            margin-bottom:10px;
        }
        .table_full table{
            table-layout:fixed;
        }
        .table_full tr,
        .table_full tr:hover th,
        .table_full tr:hover td,
        .table_full th,
        .table_purview tr,
        .table_purview th,
        .table_purview td,
        .table_list td,
        .nav,
        .prompt_text,
        .search_type,
        .search_photo_list,
        .design_ct dl,
        .pop_design_tablelist td,
        .pop_design_tablelist th,
        .table_full tr.tr_checkbox td,
        .search_list dl{
            background:url(../images/admin/content/hr.png) 0 bottom repeat-x;
        }
        .table_full th{
            padding:7px 10px 9px;
            background-color:#f8f8f8;
            border-right:1px solid #e5e3e3;
            font-weight:200;
            line-height:24px;
            vertical-align: middle;
        }
        .table_full .th{
            width:160px;
        }
        .table_full th .s1{
            padding-left:5px;
        }
        .table_full td{
            padding:7px 10px 9px 15px;
            color:#666;
            vertical-align:top;
        }
        .table_full tr:hover th{
            background-color:#eef3f6;
        }
        .table_full tr:hover td{
            color:#000;
            background-color:#eef3f6;
        }
        .table_full tr .fun_tips{
            color:#999;
            line-height:24px;
        }
        .table_full tr:hover .fun_tips{
            color:#000;
        }
        .table_full tr.tr_checkbox th,
        .table_full tr.tr_checkbox td{
            background-color:#fffaeb;
        }
        /*
        ===================
        åŠŸèƒ½è¡¨æ ¼åˆ—è¡¨/äº¤å‰åž‹
        TODO:
        ===================
        */
        .table_purview{
        }
        .table_purview th{
            padding:7px 10px 9px;
            font-weight:100;
            background-color:#f9f9f9;
        }
        .table_purview td{
            padding:5px 10px 7px 15px;
            border-left:1px solid #e5e3e3;
        }
        .table_purview tr:hover th,
        .table_purview tr:hover td{
            background-color:#f9f9f9;
        }
        .table_purview  tr.hd_bar td{
            border:0 none;
            background-color:#f9f9f9;
        }
        .table_purview tr.hd_bar th{
            color:#000;
            font-weight:700;
            background-color:#f9f9f9;
        }
        .table_purview tr.hd td,.table_purview tr.hd th{
            background-color:#fffeee;
        }
        /*
        ===================
        è¡¨æ ¼é€‰ä¸­æ ·å¼åž‹
        ===================
        */
        tr.high_light td,tr.high_light th{
            border-bottom:1px solid #ebdf99 !important;
            background-color:#fff8cf !important;
            background-image:url(../images/admin/content/high_light.png) !important;
            background-repeat:repeat-x !important;
        }
        /*
        ===================
        ä¸‰åˆ—åˆ—è¡¨
        ===================
        */
        .three_list{}
        .three_list li{
            float:left;
            width:33%;
            height:25px;
            line-height:25px;
            overflow:hidden;
        }
        .three_list li.all_check{
            width:100%;
            padding-left:2px;
        }
        /*
        ===================
        åŒåˆ—åˆ—è¡¨
        ===================
        */
        .double_list{}
        .double_list li{
            float:left;
            width:49%;
            line-height:25px;
            height:25px;
            overflow:hidden;
        }
        .double_list li.all_check{
            width:100%;
        }
        .double_list li span{
            padding-left:20px;
            color:#666;
        }
        .double_list li label span{
            padding:inherit;
            color:#666;
        }
        /*
        ===================
        å•é€‰ç»„åˆ—è¡¨
        ===================
        */
        .single_list li{
            line-height:25px;
            height:25px;
        }
        /*
        ===================
        å¼€å…³é€‰é¡¹
        ===================
        */
        .switch_list{
            height:25px;
            line-height:25px;
            overflow:hidden;
        }
        .switch_list li{
            float:left;
            width:120px;
        }
        /*
        ===================
        å…¨å±€é€‰ä¸­é«˜äº®
        ===================
        */
        input:checked+span{
            color:#008800 !important;
        }
        input:radio+span{
            color:#008800 !important;
        }
        /*
        ===================
        ç”¨æˆ·ç»„åˆ—è¡¨
        ===================
        */
        .user_group{
            border:1px solid #ccc;
            background:#fff;
            width:358px;
            position:relative;
        }
        .user_group dl{
            border-top:1px solid #ccc;
            position:relative;
            margin-top:-1px;
        }
        .user_group dt{
            line-height:25px;
            height:25px;
            padding:0 5px;
            font-weight:700;
            color:#000;
        }
        .user_group dt input{
            _margin:3px 0 0 0;
            _padding:0;
        }
        .user_group dd{
            zoom:1;
            padding:5px 0;
            position:relative;
        }
        .user_group dd:after{
            content:'\20';
            display:block;
            height:0;
            clear:both;
            visibility: hidden;
        }
        .user_group dd label{
            display:inline;
            margin-bottom:1px;
            float:left;
            width:113px;
            margin-left:5px;
        }
        .user_group dd input{
            float:left;
            margin-top:4px;
            *margin-top:0;
        }
        .user_group dd span{
            display:block;
            padding:2px 0 2px 5px;
            color:#666;
        }

        /*
        ===================
        æ•°æ®ç»„åˆ—è¡¨
        ===================
        */
        .sql_list{
            border:1px solid #ccc;
            background:#fff;
            width:558px;
            position:relative;
        }
        .sql_list dl{
            border-top:1px solid #ccc;
            position:relative;
            margin-top:-1px;
        }
        .sql_list dt{
            line-height:25px;
            height:25px;
            padding:0 5px;
            font-weight:700;
            color:#000;
            color:#666;
        }
        .sql_list dd{
            height:300px;
            overflow:hidden;
            overflow-y:auto;
        }
        .sql_list dd p{
            border-bottom:1px solid #e4e4e4;
            padding:3px 5px;
            line-height:25px;
            height:25px;
            overflow:hidden;
        }
        .sql_list dd p:hover{
            background:#f7f7f7;
        }
        .sql_list span{
            float:left;
        }
        .sql_list .span_1{
            width:50px;
        }
        .sql_list .span_2{
            width:300px;
        }
        .sql_list .span_3{
            width:170px;
        }


        /*===================4æœˆ16æ—¥å½’ç±»åˆ°è¿™é‡Œ,todo===================*/

        /*
        ===================
        ä»»åŠ¡æ¡ä»¶ç»„
        ===================
        */
        .task_item_list{
            border:1px solid #ccc;
            background:#fff;
            width:358px;
            overflow:hidden;
        }
        .task_item_list .hd{
            height:25px;
        }
        .task_item_list .hd li{
            margin-left:-1px;
            border-left:1px solid #dfdfdf;
            float:left;
            width:50%;
            text-align:center;
        }
        .task_item_list .hd a{
            line-height:25px;
            font-weight:700;
            color:#000;
            display:block;
        }
        .task_item_list .hd a:hover{
            text-decoration:none
        }
        .task_item_list .hd .current a{
            background:#ffffff;
            height:26px;
        }
        .task_item_list .ct ul{
            padding:5px 0 10px 10px;
        }
        .task_item_list .ct li{
            float:left;
            width:33%;
            line-height:24px;
            height:24px;
            overflow:hidden;
        }
        /*
        ===================
        åˆ—è¡¨æ¨ªæŽ’æ“ä½œç»„åˆ
        ===================
        */
        .cross{
            width:390px;
            overflow:hidden;
        }
        .cross ul{
            margin-left:-10px;
        }
        .cross li{
            line-height:30px;
            height:40px;
            padding:0 0 10px 0;
            overflow:hidden;
        }
        .cross img{
            vertical-align:middle;
        }
        .cross li span{
            margin-left:10px;
            float:left;
            height:30px;
        }
        .cross li .span_1{
            width:30px;
        }
        .cross li .span_2{
            width:80px;
        }
        .cross li .span_3{
            width:120px;
        }
        .cross li .span_4{
            width:180px;
        }
        /*
        ===================
        å›¾æ ‡ å¯¼èˆªã€ç‰ˆå—ç®¡ç†
        å±•å¼€æ”¶èµ·ã€æ‰€å±ž
        ===================
        */
        .start_icon,
        .away_icon,
        .zero_icon,
        .plus_icon,
        .plus_end_icon{
            background:url(../images/admin/content/icon_list.png) no-repeat;
            display:inline-block;
            vertical-align:middle;
        }
        .start_icon,
        .away_icon,
        .zero_icon,
        .plus_none_icon{
            width:20px;
            height:20px;
            overflow:hidden;
            cursor:pointer;
        }
        .away_icon{
            background-position:0 -20px;
        }
        .zero_icon{
            background-position:-20px 0;
            cursor:default;
        }
        .plus_icon,
        .plus_end_icon{
            width:40px;
            height:24px;
            background-position:0 -43px;
            margin-right:5px;
        }
        .plus_end_icon{
            background-position:0 -70px;
        }
        .plus_on_icon{
            background-position:0 -100px;
            cursor:default;
        }
        .plus_none_icon{
            background-position:999px 999px;
            cursor:default;
        }
        /*
        ===================
        ç‹¬ç«‹é¡µé¢æäº¤æŒ‰é’®æ¡†
        ===================
        */
        .btn_wrap{
            position:fixed;
            bottom:0;
            left:0;
            width:100%;
            z-index:5;
            _position: absolute;
            _top: expression(documentElement . scrollTop +   documentElement . clientHeight-this . offsetHeight);
        }
        .btn_wrap_pd{
            padding:0 10px 10px;
        }
        .btn_wrap .btn_wrap_pd{
            padding:10px 20px 10px;
            background:#eaeaea url(../images/admin/content/btn_wrap.png) repeat-x;
            border-top:1px solid #d8d8d8;
        }
        .btn_wrap_pd .btn{
            min-width:80px;
            margin-right:10px;
            _width:100px;
        }
        .select_pages{
            float:right;
            line-height:26px;
            color:#999;
        }
        .select_pages a{
            font-size:14px;
        }
        .select_pages span{
            font-family:Simsun;
            color:#999;
            padding:0 10px 0;
        }
        /*
        ===================
        å±žæ€§è½¬ç§» å¦‚ï¼šè§’è‰²æƒé™æ·»åŠ åˆ é™¤
        ===================
        */
        .shift{}
        .shift select{
            width:160px;
            padding:5px;
        }
        .shift_operate{
            padding:70px 10px 0;
        }
        .shift h4{
            margin-bottom:5px;
            font-size:12px;
        }
        .shift .btn{
            font-family:Simsun;
        }
        .shift .select_div{
            border:1px solid #ccc;
            background:#fff;
            height:180px;
            overflow-y:auto;
            width:158px;
            float:left;
            line-height:25px;
            text-indent:10px;
            padding-bottom:5px;
        }
        .shift .select_div dl{
            padding:5px 5px 0;
        }
        .shift .select_div dl dd{
            display:none;
        }
        .shift .select_div dl.current dd{
            display:block;
        }
        .shift .select_div dl dt{
            font-weight:700;
            background:#e9e9e9 url(../images/admin/content/down.png) 115px center no-repeat;
        }
        .shift .select_div dl.current dt{
            background:#e9e9e9 url(../images/admin/content/up.png) 115px center no-repeat;
        }
        .shift .select_div a{
            display:block;
            color:#666;
        }
        .shift .select_div a:hover,
        .shift .select_div a.current{
            display:block;
            background:#e6e6e6;
            text-decoration:none;
        }

        /*
        ===================
        å¤§åŠŸèƒ½æç¤ºè¯´æ˜Ž
        ===================
        */
        .prompt_text{
            padding:10px;
            color:#666;
            margin-bottom:10px;
        }
        .prompt_text:hover{
            color:#000;
        }
        .prompt_text ol,
        .prompt_text ul{
            padding:0 0 5px 2em;
            margin:0;
            line-height:1.5;
        }
        .prompt_text ul li{
            list-style: disc outside;
        }
        /*
        ===================
        é¡µé¢çº§æç¤º
        ===================
        */
        #error_tips{
            border:1px solid #d4d4d4;
            background:#fff;
            -webkit-box-shadow: #ccc 0 1px 5px;
            -moz-box-shadow: #ccc 0 1px 5px;
            -o-box-shadow:#ccc 0 1px 5px;
            box-shadow: #ccc 0 1px 5px;
            filter: progid: DXImageTransform.Microsoft.Shadow(Strength=3, Direction=180, Color='#ccc');
            max-width:500px;
            margin:50px auto;
        }
        #error_tips h2{
            font:bold 14px/40px Arial;
            height:40px;
            padding:0 20px;
            color:#666;
        }
        .error_cont{
            padding:20px 20px 30px 80px;
            background:url(../images/admin/tips/light.png) 20px 20px no-repeat;
            line-height:1.8;
        }
        .error_return{
            padding:10px 0 0 0;
        }
        /*
        ===================
        æœç´¢ç»“æž„
        ===================
        */
        .search_type{
            padding:10px;
        }
        .search_type li{
            _height:30px;
        }
        .search_type li label{
            display:inline-block;
            width:70px;
            line-height:24px;
        }
        .search_type li select{
            vertical-align:top;
        }
        .search_type .ul_wrap{
            width:100%;
            min-height:1px;
            zoom:1;
        }
        .search_type .ul_wrap:after{
            content:'\20';
            display:block;
            height:0;
            clear:both;
            visibility: hidden;
        }
        .search_type li{
            float:left;
            width:350px;
            margin-right:20px;
            min-height:30px;
            padding-bottom:5px;
        }
        .search_type li.two{
            width:700px;
        }
        .search_type .btn_side{
            text-align:center;
            padding-top:10px;
            padding-right:140px;
        }
        .search_type .btn{
        }

        /*
        ===================
        é“¾æŽ¥åž‹æ·»åŠ 
        ===================
        */
        .link_add{
            background:url(../images/admin/content/link_add.png) 3px 3px no-repeat;
            display:inline-block;
            padding-left:20px;
            color:#ff5500;
        }
        /*
        ===================
        æ–¹ä½é€‰æ‹© ä¾‹å­ï¼šæ°´å°
        ===================
        */
        .locate{
            border:1px solid #ccc;
            background:#fff;
            width:209px;
        }
        .locate ul{
            margin-top:-1px;
            margin-left:-1px;
        }
        .locate li{
            border-left:1px solid #ccc;
            border-top:1px solid #ccc;
            float:left;
            width:69px;
            height:40px;
            text-align:center;
        }
        .locate li a{
            color:#333;
            display:block;
            border:1px solid #fff;
            line-height:38px;
        }
        .locate li a:hover{
            text-decoration:none;
            background:#e4e4e4;
        }
        .locate li.current a{
            background:#266aae;
            color:#fff;
        }
        /*
        ===================
        ç‰ˆå—æœç´¢ä¸‹æ‹‰
        ===================
        */
        .forum_search_pop{
            position:absolute;
            border:1px solid #ccc;
            border-bottom-width:2px;
            background:#fff;
            width:178px;
        }
        .forum_search_pop ul{
            border:1px solid #fff;
        }
        .forum_search_pop li{
            line-height:25px;
            height:25px;
            overflow:hidden;
            text-indent:15px;
        }
        .forum_search_pop li a{
            text-decoration:none;
            display:block;
            color:#333;
        }
        .forum_search_pop li.current a{
            background:#f0f0f0;
            color:#266aae;
        }

        /*
        ===================
        åœ°åŒºåº“--è·¯å¾„
        ===================
        */
        .yarnball {
            border-left:1px solid #d9d9d9;
            overflow:hidden;
            padding-right:10px;
            vertical-align:middle;
            display:inline-block;
            position:relative;
        }
        .yarnball ul {
            float:left;
            margin-left:-5px;
        }
        .yarnball ul li{
            border-top:1px solid #d9d9d9;
            border-bottom:1px solid #d9d9d9;
            float:left;
            width:90px;
        }
        .yarnball ul li a,
        .yarnball ul li em{
            background:url(../images/admin/content/yarnball.png) 0 0 no-repeat;
        }
        .yarnball ul li a{
            color: #666;
            display:block;
            line-height:30px;
            height:30px;
            overflow:hidden;
            text-decoration: none;
            text-align:center;
            padding:0 0 0 10px;
        }
        .yarnball ul li a:hover{
            text-decoration:none;
        }
        .yarnball ul li em{
            width:9px;
            height:30px;
            position:absolute;
            background-position:right 0;
            margin-top:-30px;
            z-index:2;
            margin-left:90px;
        }
        .yarnball ul li.hover a{
            background-position:0 -30px;
            color:#333;
        }
        .yarnball ul li.hover em{
            background-position:right -30px;
        }
        .yarnball ul li.li_disabled a{
            background-position:0 -60px;
            color:#333;
            cursor:default;
        }
        .yarnball ul li.li_disabled em{
            background-position:right -60px;
        }



        /*
        ===================
        å‹æƒ…é“¾æŽ¥åˆ†ç±»
        ===================
        */
        .cate_link span{
            display:inline-block;
            padding:0 5px;
            background:#ddd;
            margin:0 5px 5px 0;
        }

        .core_menu_list li{
            float:left;
            width:100%;
            margin:0;
            padding:2px 5px;
            line-height:18px;
            height:18px;
        }
        .core_menu_list li a{
            display:block;
            text-indent:10px;
            color:#333333;
            border:0 none;
            float:left;
            width:100%;
            margin:0;
            border-radius: 0;
        }
        .core_menu_list li a:hover{
            border:0 none;
            background:#3366cc;
            color:#fff;
            text-decoration:none;
        }

        /*
        ===================
        å‹‹ç« é¢å‘æ¡ä»¶
        ===================
        */
        .medal_term{
        }
        .medal_term .hd{
            margin-bottom:10px;
        }
        .medal_term .hd span{
            font-family:Simsun;
            margin:0 10px;
        }
        .medal_term .ct{
            border:1px solid #ccc;
            width:358px;
            border-bottom:0 none;
        }
        .medal_term .ct h6{
            height:24px;
            line-height:24px;
            text-align:center;
            font-size:12px;
        }
        .medal_term .ct dl{
            height:24px;
            line-height:24px;
            border-bottom:1px solid #ccc;
            overflow:hidden;
        }
        .medal_term .ct dt{
            float:left;
            width:80px;
            text-align:center;
        }
        .medal_term .ct dd{
            float:left;
            border-left:1px solid #ccc;
        }
        .medal_term .ct dd.num{
            width:100px;
            font-family:Simsun;
            text-align:center;
        }
        .medal_term .ct dd.title{
            width:160px;
            text-indent:10px;
        }


        /*
        ===================
        å•å›¾ä¸Šä¼ 
        ===================
        */
        .single_image_up{
        }
        .single_image_up a{
            width:0;
            height:0;
            display:block;
            overflow:hidden;
            text-indent:-2000em;
        }

        /*
        ===================
        å•é™„ä»¶ä¸Šä¼ 
        ===================
        */
        .single_file_up{
            width:80px;
            height:20px;
            position:relative;
            margin-bottom:5px;
            overflow:hidden;
        }
        .single_file_up a{
            height:20px;
            width:80px;
            display:block;
            line-height:20px;
            text-indent:25px;
            background:url(../images/admin/content/file_up.png) no-repeat;
            filter:alpha(opacity=80);
            -moz-opacity:0.8;
            opacity:0.8;
            text-decoration:none;
            color:#333;
        }
        .single_file_up:hover a{
            text-decoration:none;
            filter:alpha(opacity=100);
            -moz-opacity:1;
            opacity:1;
        }
        .single_file_up input,
        .single_file_up object{
            width:80px;
            height:22px;
            position:absolute;
            top:0;
            right:0;
            background:none;
            filter:alpha(opacity=0);
            -moz-opacity:0;
            opacity:0;
            cursor:pointer;
            outline:none;
        }

        /*
        ===================
        è¿›åº¦æ¡
        ===================
        */
        .progress_bar{
            border:1px solid #c6c6c6;
            background:#fff;
            height:9px;
            display:inline-block;
            width:100%;
            vertical-align:middle;
            position:relative;
        }
        .progress_bar span{
            background-color: #669201;
            border:1px solid #577d00;
            display:inline-block;
            height:9px;
            position:absolute;
            margin-top:-1px;
            left:-1px;
            font:0/0 Arial;
        }

        /*
        ===================
        é¢œè‰²ç­›é€‰
        ===================
        */
        .color_pick{
            border:1px solid #ccc;
            padding:2px 13px 2px 3px;
            background:#fff url(../images/admin/content/down.png) 40px center no-repeat;
            display:inline-block;
            cursor:pointer;
            height:20px;
            overflow:hidden;
            vertical-align:middle;
            position:relative;
            line-height:normal;
        }
        .color_pick:hover{
            background-color:#fffbde;
        }
        .color_pick.color_current{
            border-color:#aaa #aaa #555;
            background-color:#fffbde;
        }
        .color_pick em{
            height:20px;
            width:34px;
            display:inline-block;
            background:url(../images/admin/content/transparent.png);
        }
        .color_pick.color_big{
            background-image:none;
            width:80px;
            padding:3px;
            height:60px;
            text-align:center;
            color:#333;
        }
        .color_pick.color_big em{
            width:80px;
            height:38px;
            *margin-bottom:3px;
            text-align:center;
        }
        /*å­—ä½“é¢œè‰²é€‰æ‹©ç»„åˆ*/
        .color_pick_dom{
            width:70px;
            width:300px;
        }
        .color_pick_dom ul{
            height:29px;
            width:170px;
            padding:3px 0 0 0;
        }
        .color_pick_dom li{
            float:left;
            margin-right:10px;
            _margin-right:8px;
            white-space:nowrap;
        }
        .color_pick_dom li input{
            _margin:0 0 -1px -3px;
            _padding:0;
        }
        .color_pick_dom li.none{
            margin-right:0;
        }
        .color_pick_dom .color_pick{
            background-position:151px center;
        }
        .color_pick_dom .color_pick em{
            width:145px;
        }
        .color_pick_dom .case{
            float:right;
            width:100px;
            border:1px solid #ccc;
            background:#fff;
            padding:10px 10px;
        }

        .pop_region_list ul{
            padding-left:2px;
        }
        .pop_region_list ul li{
            float:left;
            line-height:20px;
        }
        .pop_region_list ul li a,
        .pop_region_list ul li span{
            display:block;
            padding:0 5px;
            color:#333;
            white-space:nowrap;
            border-radius:2px;
        }
        .pop_region_list ul li a:hover{
            background:#e0e0e0;
            text-decoration:none;
        }
        .pop_region_list ul li.current a,
        .pop_region_list ul li.current span{
            background:#266aae;
            color:#ffffff;
        }
        .pop_region_list .hr{
            background:#e4e4e4;
            height:1px;
            overflow:hidden;
            font:0/0 Arial;
            clear:both;
            margin:10px 0;
        }
        .pop_region_list .filter{
            padding:10px 0;
        }
        .pop_region_list .filter a{
            margin-right:12px;
        }
        .pop_region_list .filter a.current{
            color:#333;
            font-weight:700;
        }
        .pop_region_list .list{
            border:1px solid #ccc;
            height:108px;
            overflow-x:hidden;
            overflow-y:auto;
        }
        .pop_region_list .list ul{
            padding:5px;
        }
        .pop_region_list .list li{
            float:left;
            width:33%;
            cursor:pointer;
            text-indent:5px;
        }
        .pop_region_list .list li:hover{
            background:#f7f7f7;
        }

        .pop_seo .close{
            float:right;
            width:9px;
            height:8px;
            overflow:hidden;
            text-indent:-2000em;
            background:url(../images/admin/content/close.png) no-repeat;
            -webkit-transition: all 0.2s ease-out;
            margin:3px 3px 0 10px;
        }
        .pop_seo .close:hover{
            background-position:0 -8px;
        }
        .pop_seo .hd{
            margin-bottom:5px;
            color:#000;
        }
        .pop_seo .ct a{
            display:inline-block;
            line-height:25px;
            margin-right:20px;
        }

        .agreements pre{
            height:150px;
            overflow-x:hidden;
            overflow-y:scroll;
            border:1px solid #e4e4e4;
            background:#fff;
            text-align:left;
        }



        .app_info .hd{
            padding:8px 15px;
        }
        .app_info .ct{
            padding:8px 15px 20px;
        }
        .app_info h1{
            font-size:24px;
            font-family:"Microsoft Yahei";
            font-weight:400;
            margin-bottom:5px;
        }
        .app_info ul{
            padding-bottom:10px;
        }
        .app_info li{
            line-height:25px;
            float:left;
            width:45%;
        }
        .app_info li em{
            display:inline-block;
            width:80px;
            color:#666;
        }
        .app_info li.li{
            width:100%;
        }

        .app_present .hd{
            padding:8px 15px;
        }
        .app_present .ct{
            padding:8px 15px 20px;
        }
        /*
        ===================
        åº”ç”¨æˆªå›¾
        ===================
        */
        .app_thumb li{
            float:left;
            margin-right:20px;
            margin-top:20px;
        }
        .app_thumb li img{
            vertical-align:top;
            height:266px;
            width:200px;
        }

        .app_updata .hd{
            padding:8px 15px;
        }
        .app_updata .ct{
            padding:8px 15px 20px;
        }
        .app_updata .time{
            font-weight:700;
            margin-bottom:5px;
        }
        .app_updata .version{
            margin-bottom:5px;
        }


        .pop_stats table td,
        .pop_stats table th{
            padding:5px 10px;
            border:1px solid #ccc;
        }
        .pop_stats thead td,
        .pop_stats thead th{
            background:#f7f7f7;
        }
        .pop_stats thead th{
            width:120px;
        }


        .widget_upload_photos li{
            position:relative;
            float:left;
            width:50px;
            height:50px;
            border:1px solid #ccc;
            background:#f7f7f7;
            overflow:hidden;
            margin-right:5px;
            line-height:50px;
            text-align:center;
        }
        .widget_upload_photos li input{
            height:50px;
            border:0;
            padding:0;
            margin:0;
            width:50px;
            position:absolute;
            left:0;
            top:0;
            filter:alpha(opacity=00);
            -moz-opacity:0;
            opacity:0;
        }


        .widget_history_file dt{
            float:left;
            width:70px;
            padding:5px 10px;
        }
        .widget_history_file dd{
            float:left;
            padding:5px 10px;
            border-left:1px solid #ccc;
        }
        .widget_history_file dd.num{
            width:60px;
        }
        .widget_history_file dd.time{
            width:80px;
        }
        .widget_history_file dd.name a{
            padding-left:20px;
            background:url(../images/admin/content/zip.png) 0 center no-repeat;
            display:inline-block;
        }

        /*
        ===================
        æ›´æ–°æ—¥å¿—
        ===================
        */
        .widget_update_log h3{
            font-size:12px;
            margin-bottom:5px;
        }
        .widget_update_log table{
            border:1px solid #ccc;
        }
        .widget_update_log td,
        .widget_update_log th{
            border:1px solid #ccc;
            padding:5px 10px;
        }
        .widget_update_log thead th,
        .widget_update_log thead td{
            background:#f7f7f7;
        }


        .pop_advanced_search .pop_cont{
            overflow:hidden;
        }
        .pop_advanced_search ul{
            margin-left:-4%;
        }
        .pop_advanced_search li{
            float:left;
            width:46%;
            margin-left:4%;
            height:60px;
        }
        .pop_advanced_search li.all{
            width:100%;
        }
        .pop_advanced_search p{
            padding-bottom:3px;
        }
        .pop_advanced_search .gap{
            display:inline-block;
            width:22px;
            text-align:center;
        }


        .about_list dl{
            padding-top:10px;
        }
        .about_list dt{
            float:left;
            width:80px;
            line-height:26px;
        }
        .about_list dd{
            overflow:hidden;
            line-height:26px;
        }
        .about_list dd p{
            color:#999;
        }
        .about_list label{
            display:inline-block;
            width:100px;
            color:#666;
        }
        .about_list:hover label{
            color:#333;
        }

        /*
        ===================
        é‚®ä»¶ç¤ºä¾‹æ•ˆæžœ
        ===================
        */
        .email_example{
            border:1px solid #ccc;
            background:#fff;
            padding:10px 15px;
            color:#333;
            width:268px;
            line-height:1.8;
        }

        .variable_sample li{
            border-bottom:1px solid #e4e4e4;
            line-height:25px;
            height:25px;
        }
        .variable_sample li span{
            float:left;
            width:200px;
            font-family:"Courier New", Courier, monospace;
            border-left:1px solid #e4e4e4;
            padding-left:10px;
            color:#333;
        }
        .variable_sample li em{
            padding-left:10px;
            float:left;
            width:200px;
            background:#f7fbff;
        }

        .search_photo_list li{
            float:left;
            width:90px;
            height:90px;
            padding:4px;
            border:1px solid #ccc;
            background:#fff;
            position:relative;
            display:inline;
            margin:0 20px 20px 0;
        }
        .search_photo_list li label{
            display:block;
        }
        .search_photo_list li input{
            position:absolute;
            right:0;
            bottom:0;
        }

        /*
        ===================
        é—¨æˆ·è°ƒç”¨ç¼–è¾‘
        ===================
        */
        .design_ct dl{
            padding:7px 0;
        }
        .design_ct dt{
            float:left;
            width:120px;
            line-height:26px;
            padding:0 10px;
        }
        .design_ct dd{
            margin-left:150px;
        }
        .design_ct dd .three_list{
            width:360px;
        }
        .design_ct .pop_design_code{
            padding:10px;
        }
        .design_ct .pop_design_tablelist{
        }


        .pop_design_code textarea{
            width:430px;
            height:280px;
            font-family:"Courier New", Courier, monospace;
            margin-bottom:5px;
        }
        /*å³ä¾§*/
        .pop_design_case{
            float:left;
            width:180px;
            height:350px;
            overflow-x:hidden;
            overflow-y:auto;
            padding:10px 0;
        }
        .pop_design_case .thbg{
            background:#fff;
        }

        .pop_design_tablelist thead td,
        .pop_design_tablelist thead th{
            background-color:#f7f7f7;
        }
        .pop_design_tablelist thead td select{
            padding:1px;
            line-height:22px;
            height:22px;
        }
        .pop_design_tablelist td,
        .pop_design_tablelist th{
            padding:7px 0;
        }
        .pop_design_tablelist th{
            padding-left:10px;
        }
        .pop_design_tablelist th input{
            margin:0;
            padding:0;
        }
        .pop_design_tablelist .subject{
            white-space:nowrap;
            text-overflow:ellipsis;
            line-height:18px;
            height:18px;
            overflow:hidden;
        }


        .pop_showmsg span{
            padding:10px 10px 10px 68px;
            display:inline-block;
            line-height:36px;
            height:35px;
            text-shadow: 0 1px 1px #eee;
            color:#333;
        }
        .pop_showmsg span.success{
            background:url(../images/admin/tips/success.gif) 20px center no-repeat;
        }
        .pop_showmsg span.warning{
            background:url(../images/admin/tips/warning.gif) 20px center no-repeat;
        }


        /*
        ===================
        æ¨¡æ¿åˆ—è¡¨
        ===================
        */
        .design_page{
            padding-bottom:10px;
            width:800px;
        }
        .design_page li{
            float:left;
            margin-right:23px;
            display:inline;
            box-shadow:0 0 1px rgba(0,0,0,0.1);
            background:#fff;
            margin-bottom:20px;
            border:1px solid;
            border-color:#ecebeb #e1e0e0 #d5d5d5 #e1e0e0;
            width:230px;
            height:310px;
            position:relative;
        }
        .design_page .img{
            display:block;
            padding:10px;
        }
        .design_page li img{
            display:block;
        }
        .design_page li .ft{
            position:absolute;
            left:0;
            right:0;
            bottom:0;
            width:100%;
            background:#f8f8f8;
            border-top:1px solid #eeeeee;
            padding:5px 0;
            border-bottom:1px solid #fff;
        }
        .design_page li .ft .org{
            padding:0 0 0 10px;
        }
        .design_page li .ft a{
            color:#666;
            margin:0 0 0 10px;
        }
        .design_page li .title{
            padding:0 10px 0;
            font-size:14px;
            line-height:18px;
            height:18px;
            overflow:hidden;
            margin-bottom:3px;
            white-space:nowrap;
            text-overflow:ellipsis;
            -ms-text-overflow:ellipsis;
            word-wrap:normal;
        }
        .design_page li .descrip{
            padding:0 10px 3px;
            color:#999;
            line-height:18px;
            height:18px;
            overflow:hidden;
            white-space:nowrap;
            text-overflow:ellipsis;
            -ms-text-overflow:ellipsis;
            word-wrap:normal;
        }
        .design_page li .type{
            padding:0 10px 8px;
            color:#999;
        }
        .design_page li .type span{
            margin-right:10px;
        }
        .home_tips h4{
            font-size:12px;
            font-weight:700;
            color:#af8133;
            margin-bottom:10px;
        }


        .home_info li{
            line-height:25px;
            zoom:1;
        }
        .home_info li:after{
            content:'\20';
            display:block;
            height:0;
            clear:both;
            visibility: hidden;
        }
        .home_info li em{
            float:left;
            width:100px;
            font-style:normal;
        }
        .home_info li span{
            display:block;
            overflow:hidden;
        }
        .install_schedule span{
            background:url(../images/admin/content/install_schedule.png) no-repeat;
        }
        .install_schedule span{
            background-position:0 -17px;
            display:block;
            width:5px;
            height:15px;
        }


        .search_list h2{
            font-size:14px;
            padding:10px;
        }
        .search_list dl{
            padding:0 2em 10px;
            margin:0 0 10px;
        }
        .search_list dt{
            font-size:14px;
        }


        .pop_expand .core_arrow_bottom{
            position:absolute;
            bottom:9px;
            left:25px;
            padding:0 25px;
        }
        .pop_expand .core_arrow_bottom span,
        .pop_expand .core_arrow_bottom em{
            position:absolute;
            width:0;
            height:0;
            padding:0;
            margin:9px 0 0 2px;
            border-bottom:8px transparent dashed;
            border-right:8px transparent dashed;
            border-left:8px transparent dashed;
            overflow:hidden;
        }
        .pop_expand .core_arrow_bottom span{
            border-top:8px #ffffff solid;
        }
        .pop_expand .core_arrow_bottom em{
            border-top:8px #c1c1c1 solid;
            margin-top:10px;
        }



        .system_update a{
            color:#266AAE;
            margin:0 0 0 20px;
        }
        .system_update a:hover{
            text-decoration:underline;
        }


        .app_icon b{
            position:absolute;
            left:0;
            top:0;
            width:80px;
            height:80px;
            background:url(../images/app_bg.png) no-repeat;
            _background:none;
        }


        .pop_nav ul{
            border-bottom:1px solid #e3e3e3;
            padding:0 5px;
            height:25px;
            clear:both;
        }
        .pop_nav ul li{
            float:left;
            margin-right:10px;
        }
        .pop_nav ul li a{
            float:left;
            display:block;
            padding:0 10px;
            height:25px;
            line-height:23px;
        }
        .pop_nav ul li.current a{
            border:1px solid #e3e3e3;
            border-bottom:0 none;
            color:#333;
            font-weight:700;
            background:#fff;
            position:relative;
            border-radius:2px;
            margin-bottom:-1px;
        }

        .table_list  table{
            border-right: 1px solid #ddd;
            border-spacing: 0;
            border-collapse: collapse;
            background-color: transparent;
        }
        .table_list  table tr{
        }

        .table_list  table tr td{
            border-left: 1px solid #ddd;
            padding: 10px;
        }
    </style>

</block>