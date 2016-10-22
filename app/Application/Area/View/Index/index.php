<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">
    <div class="h_a">说明</div>
    <div class="prompt_text">
        <ol>
            <li>主要包含了中国省份、县区,乡镇，学校信息,常用于填写用户的所属的省市,如物流等</li>
            <li>详细文档: <a href="http://ztbcms.com/module/area.html"> http://ztbcms.com/module/area.html</a></li>
            <li>使用操作,参考 Area/Controller/ApiController </li>
        </ol>
    </div>

    <section>
        <h3>样例1： 省市区联动 <small>请参考 Application/Area/View/Index/index.php</small></h3>
        <div>
            <select name="province" id="">
                <get sql="SELECT * FROM cms_area_province " page="$page" num="100">
                    <volist name="data" id="vo">
                        <option value="{$vo.id}">{$vo.areaname}</option>
                    </volist>
                </get>
            </select>

            <select name="city" id=""></select>

            <select name="district" id=""></select>

            <template id="tpl_option">
                <option value="{id}">{name}</option>
            </template>
        </div>


        <script>
            (function($){
                var $province = $('select[name=province]');
                var $city = $('select[name=city]');
                var $district = $('select[name=district]');
                var tpl_option = $('#tpl_option').html();

                //省份切换
                $province.on('change', function(){
                    $.ajax({
                        'url': "{:U('Area/Api/getCitiesByProvinceId')}" + '&id=' + $province.val(),
                        'type': 'GET',
                        'dataType': 'json',
                        'success': function(res){
                            console.log(res.data);

                            var html = '';
                            res.data.forEach(function(item){
                                html += tpl_option.replace('{id}', item.id).replace('{name}', item.areaname);
                            });
                            $city.html(html);
                            $city.trigger('change');
                        }
                    });
                });

                //城市切换
                $city.on('change', function(){
                    $.ajax({
                        'url': "{:U('Area/Api/getDistrictsByCityId')}" + '&id=' + $city.val(),
                        'type': 'GET',
                        'dataType': 'json',
                        'success': function(res){
                            console.log(res.data);

                            var html = '';
                            res.data.forEach(function(item){
                                html += tpl_option.replace('{id}', item.id).replace('{name}', item.areaname);
                            });
                            $district.html(html);
                        }
                    });
                });

                //触发初始化
                $province.trigger('change');

            })(jQuery);
        </script>
    </section>

    <section>
        <h3>样例2： 根据省份获取学校 <small>请参考 Application/Area/View/Index/index.php</small></h3>
        <div>
            <select name="province2" id="">
                <get sql="SELECT * FROM cms_area_province " page="$page" num="100">
                    <volist name="data" id="vo">
                        <option value="{$vo.id}">{$vo.areaname}</option>
                    </volist>
                </get>
            </select>

            <select name="school" id=""></select>


            <template id="tpl_option_school">
                <option value="{id}">{name}</option>
            </template>
        </div>

        <script>
            (function($){
                var $province = $('select[name=province2]');
                var $school = $('select[name=school]');
                var tpl_option = $('#tpl_option_school').html();

                //省份切换
                $province.on('change', function(){
                    console.log($province.val())
                    $.ajax({
                        'url': "{:U('Area/Api/getSchoolListByProvinceId')}" + '&province_id=' + $province.val(),
                        'type': 'GET',
                        'dataType': 'json',
                        'success': function(res){
                            console.log(res.data);
                            var html = '';
                            res.data.forEach(function(item){
                                html += tpl_option.replace('{id}', item.id).replace('{name}', item.school_name);
                            });
                            $school.html(html);
                        }
                    });
                });

                //触发初始化
                $province.trigger('change');

            })(jQuery);
        </script>
    </section>

</div>



</body>
</html>
