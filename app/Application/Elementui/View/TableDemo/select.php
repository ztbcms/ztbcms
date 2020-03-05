<extend name="../../Admin/View/Common/element_layout"/>
<block name="content">
<div id="app">
    <el-select v-model="pname" @change="choseProvince" placeholder="省级地区">
        <el-option v-for="(item,$index) in province" :key="$index" :label="item.value" :value="item.id">
        </el-option>
    </el-select>
    <el-select v-model="cname" @change="choseCity" placeholder="市级地区">
        <el-option v-for="(item,$index) in city" :key="$index" :label="item.value" :value="item.id">
        </el-option>
    </el-select>
    <el-select v-model="bname" @change="choseBlock" placeholder="区级地区">
        <el-option v-for="(item,$index) in block" :key="$index" :label="item.value" :value="item.value">
        </el-option>
    </el-select>
    <el-input
            placeholder="获取地址"
            v-model="input"
            :disabled="false" :rows="2" style="width: 200px;">
    </el-input>
</div>
    <script src="app/Application/Elementui/View/TableDemo/city.js"></script>
    <script>
        new Vue({
            el: '#app',
            data:{
                ChineseDistricts:arrAll,
                province:[],
                shi1: [],
                qu1: [],
                city:[],
                block:[],
                pname:'',//省的名字
                cname:'',//市的名字
                bname:'',  //区的名字,
                input:''
            },
            methods:{
                // 加载china地点数据，三级
                getCityData:function(){
                    let that = this;
                    that.ChineseDistricts.forEach(function(item,index){
                        //省级数据
                        that.province.push({id: item.name, value: item.name, children: item.sub})
                    })
                },
                // 选省
                choseProvince:function(e) {
                    // console.log(e)
                    let that = this;
                    that.city = [];
                    that.block = [];
                    that.cname = '';
                    that.bname = '';
                    for (var index2 in that.province) {
                        if (e === that.province[index2].id) {
                            that.shi1 = that.province[index2].children
                            that.pname = that.province[index2].value
                            that.shi1.forEach(function(citem,cindex){
                                that.city.push({id:citem.name,value: citem.name, children: citem.sub})
                            })
                        }
                    }
                    console.log(that.pname)
                    that.input=that.pname+that.cname+that.bname
                },
                // 选市
                choseCity:function(e) {
                    let that = this;
                    that.block = [];
                    for (var index3 in that.city) {
                        if (e === that.city[index3].id) {
                            that.qu1 = that.city[index3].children
                            that.cname = that.city[index3].value
                            that.E = that.qu1[0].id
                            that.qu1.forEach(function(bitem,bindex){
                                that.block.push({id:bitem.name,value: bitem.name, children: []})
                            })
                        }
                    }
                    console.log(that.cname)
                    that.input=that.pname+that.cname+that.bname
                },

                // 选区
                choseBlock:function(e) {
                    this.bname=e;
                    console.log(this.bname)
                    this.input=this.pname+this.cname+this.bname
                },
            },
            created:function(){
                this.getCityData()
            }

        })
    </script>
</block>