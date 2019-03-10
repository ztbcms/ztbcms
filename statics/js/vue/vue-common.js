window.__vueCommon = {
    methods: {
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
            return urlObj && urlObj.search && urlObj.search[variable] ? urlObj.search[variable] : '' ;
        },
        //解析URL
        __parserUrl: function(url){
            var a = document.createElement('a');
            a.href = url;

            var search = function(search) {
                if(!search) return {};

                var ret = {};
                search = search.slice(1).split('&');
                for(var i = 0, arr; i < search.length; i++) {
                    arr = search[i].split('=');
                    var key = arr[0], value = arr[1];
                    if(/\[\]$/.test(key)) {
                        ret[key] = ret[key] || [];
                        ret[key].push(value);
                    } else {
                        ret[key] = value;
                    }
                }
                return ret;
            };

            return {
                protocol: a.protocol,
                host: a.host,
                hostname: a.hostname,
                pathname: a.pathname,
                search: search(a.search),
                hash: a.hash
            }
        },
        openNewIframeByUrl: function(title, url){
            if (parent.window != window) {
                parent.window.__adminOpenNewFrame({
                    title: title,
                    url: url
                })
            } else {
                window.location.href = url;
            }
        },
        openNewIframeByRouter: function(title, router, url){
            if (parent.window != window) {
                var event = new CustomEvent('adminOpenNewFrame', {
                    detail: {
                        title: title,
                        router_path: router,
                        url: url
                    }
                })
                window.parent.dispatchEvent(event)
            } else {
                window.location.href = url;
            }

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