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
            var query = window.location.search.substring(1);
            var vars = query.split("&");
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split("=");
                if (pair[0] == variable) {
                    return pair[1];
                }
            }
            return (false);
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