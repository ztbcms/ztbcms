window.Ztbcms = {
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
