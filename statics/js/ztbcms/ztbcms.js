//请务必再Vue-common 前加载
window.Ztbcms = {
    /**
     * 打开内容页
     * @param title
     * @param url
     */
    openNewIframeByUrl: function (title, url){
        if (parent.window !== window) {
            //父窗口
            parent.window.__adminOpenNewFrame({
                title: title,
                url: url
            })
        } else {
            window.location.href = url;
        }
    },
    /**
     * 打开内容页
     * @param title
     * @param router
     * @param url
     */
    openNewIframeByRouter: function(title, router, url){
        if (parent.window !== window) {
            var event = document.createEvent('CustomEvent');
            event.initCustomEvent('adminOpenNewFrame', true, true, {
                title: title,
                router_path: router,
                url: url
            });
            window.parent.dispatchEvent(event)
        } else {
            window.location.href = url;
        }
    },
    /**
     * URL 解析
     * @param url
     * @returns {{protocol: string, hostname: string, search: ({}|{}), host: string, hash: string, pathname: string}}
     */
    parserUrl: function(url){
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
    /**
     * 时间格式化
     * @param time 时间戳
     * @param cFormat 格式，如：{y}-{m}-{d} {h}:{i}:{s}
     * @returns {null|*|string}
     */
    formatTime: function (time, cFormat) {
        if (arguments.length === 0) {
            return null
        }
        var format = cFormat || '{y}-{m}-{d} {h}:{i}:{s}'
        var date
        if (typeof time === 'object') {
            date = time
        } else {
            if ((typeof time === 'string') && (/^[0-9]+$/.test(time))) {
                time = parseInt(time)
            }
            if ((typeof time === 'number') && (time.toString().length === 10)) {
                time = time * 1000
            }
            date = new Date(time)
        }
        var formatObj = {
            y: date.getFullYear(),
            m: date.getMonth() + 1,
            d: date.getDate(),
            h: date.getHours(),
            i: date.getMinutes(),
            s: date.getSeconds(),
            a: date.getDay()
        }
        var time_str = format.replace(/{(y|m|d|h|i|s|a)+}/g, (result, key) => {
            var value = formatObj[key]
            // Note: getDay() returns 0 on Sunday
            if (key === 'a') {
                return ['日', '一', '二', '三', '四', '五', '六'][value]
            }
            if (result.length > 0 && value < 10) {
                value = '0' + value
            }
            return value || 0
        })
        return time_str
    },
    /**
     * 对象合并
     * 例如 合并{"user":"abc"}和{"id":"1"} => {"user":"abc", "id":"1"}
     * @param target
     * @param source
     * @returns {{}|*}
     */
    objectMerge: function (target, source) {
        if (typeof target !== 'object') {
            target = {}
        }
        if (Array.isArray(source)) {
            return source.slice()
        }
        Object.keys(source).forEach(property => {
            const sourceProperty = source[property]
            if (typeof sourceProperty === 'object') {
                target[property] = objectMerge(target[property], sourceProperty)
            } else {
                target[property] = sourceProperty
            }
        })
        return target
    },
    /**
     * 深复制
     * @param source
     * @returns {Array}
     */
    deepClone: function (source) {
        if (!source && typeof source !== 'object') {
            throw new Error('error arguments', 'deepClone')
        }
        const targetObj = source.constructor === Array ? [] : {}
        Object.keys(source).forEach(keys => {
            if (source[keys] && typeof source[keys] === 'object') {
                targetObj[keys] = deepClone(source[keys])
            } else {
                targetObj[keys] = source[keys]
            }
        })
        return targetObj
    }
}
