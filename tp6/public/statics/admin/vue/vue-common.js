window.__vueCommon = {
    data() {
        return {
            dataList: [],
            per_page: 20,
            last_page: 0,
            current_page: 1,
            isInitLoading: false
        }
    },
    mounted() {
        if (this.isInitLoading) {
            this.GetList()
        }
    },
    methods: {
        currentChangeEvent(e) {
            console.log('currentChangeEvent', e)
            this.current_page = e
            this.GetList()
        },
        handRes({data, current_page, last_page, per_page}) {
            this.dataList = data
            this.per_page = per_page;
            this.last_page = last_page
            this.current_page = current_page
        },
        /**
         * post 请求
         * @param url
         * @param data
         * @param success
         */
        httpPost: function (url, data, success) {
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: success
            })
        },
        /**
         * get 请求
         * @param url
         * @param data
         * @param success
         */
        httpGet: function (url, data, success) {
            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                dataType: 'json',
                success: success
            })
        },
        /**
         * 获取url参数
         * @param variable
         * @returns {*}
         */
        getUrlQuery: function (variable) {
            var urlObj = this.__parserUrl(window.location.href);
            return urlObj && urlObj.search && urlObj.search[variable] ? urlObj.search[variable] : '';
        },
        //解析URL
        __parserUrl: function (url) {
            return window.Ztbcms.parserUrl(url)
        },
        openNewIframeByUrl: function (title, url) {
            window.Ztbcms.openNewIframeByUrl(title, url)
        },
        openNewIframeByRouter: function (title, router, url) {
            window.Ztbcms.openNewIframeByRouter(title, router, url)

        }
    },
    filters: {
        /**
         * 格式化时间
         * @param value
         * @returns {string}
         */
        getFormatDatetime: function (value) {
            var today = new Date(value * 1000);
            var y = today.getFullYear();
            var m = today.getMonth() + 1;
            var d = today.getDate();
            var H = today.getHours();
            var i = today.getMinutes();
            var s = today.getSeconds();
            return y + '-' + (m > 9 ? m : '0' + m) + '-' + (d > 9 ? d : '0' + d) + "  " + (H > 9 ? H : '0' + H) + ":" + (i > 9 ? i : '0' + i) + ":" + (s > 9 ? s : '0' + s);
        }
    }
};