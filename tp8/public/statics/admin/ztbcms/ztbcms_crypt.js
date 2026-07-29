/**
 * 请必须在ztbcms.js 后加载
 *
 * ztbcms 内置AES加密采用128位，CBC模式，填充方式：ZeroPadding
 */
if (window.Ztbcms) {
    window.Ztbcms.AES = {
        //aes加密 128位-CBC
        encrypt: function (word, secret) {
            var encrypted = "";
            var key = CryptoJS.MD5(secret).toString().substr(0, 16)
            var iv = CryptoJS.MD5(secret).toString().substr(0, 16)
            var key_words = CryptoJS.enc.Utf8.parse(key)
            var iv_words = CryptoJS.enc.Utf8.parse(iv)
            encrypted = CryptoJS.AES.encrypt(word, key_words, {
                iv: iv_words,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.ZeroPadding
            });
            return encrypted.ciphertext.toString();
        },
        // aes解密
        decrypt: function (word, secret) {
            var key = CryptoJS.MD5(secret).toString().substr(0, 16)
            var iv = CryptoJS.MD5(secret).toString().substr(0, 16)
            var key_words = CryptoJS.enc.Utf8.parse(key)
            var iv_words = CryptoJS.enc.Utf8.parse(iv)

            var encryptedHexStr = CryptoJS.enc.Hex.parse(word);
            var srcs = CryptoJS.enc.Base64.stringify(encryptedHexStr);
            var decrypt = CryptoJS.AES.decrypt(srcs, key_words, {
                iv: iv_words,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.ZeroPadding
            });

            return CryptoJS.enc.Utf8.stringify(decrypt)
        }
    }
}
