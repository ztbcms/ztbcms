<div>
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>客户端直传示例</h3>
            <p style="color: #666; margin-bottom: 20px;">
                统一直传模式：前端通过统一接口获取上传凭证，然后根据驱动类型直接上传到目标存储（本地服务器/阿里云OSS/七牛云）。
            </p>

            <!-- 驱动切换 -->
            <div style="margin-bottom: 20px;">
                <el-row :gutter="20">
                    <el-col :span="8">
                        <span style="margin-right: 10px;">上传驱动：</span>
                        <el-select v-model="selectedDriver" placeholder="选择驱动" @change="onDriverChange" style="width: 200px;">
                            <el-option label="系统默认" value=""></el-option>
                            <el-option label="本地存储 (Local)" value="Local"></el-option>
                            <el-option label="阿里云 OSS (Aliyun)" value="Aliyun"></el-option>
                            <el-option label="七牛云 (Qiniu)" value="Qiniu"></el-option>
                        </el-select>
                    </el-col>
                </el-row>
            </div>

            <!-- 驱动状态 -->
            <el-alert
                v-if="driverChecked && supportsDirectUpload"
                :title="'当前驱动: ' + (credential ? credential.driver : '未知') + ' - 支持直传'"
                type="success"
                :closable="false"
                show-icon
                style="margin-bottom: 20px;">
            </el-alert>
            <el-alert
                v-if="driverChecked && !supportsDirectUpload"
                title="当前驱动不支持直传"
                type="warning"
                :closable="false"
                show-icon
                style="margin-bottom: 20px;">
            </el-alert>

            <!-- 上传配置 -->
            <el-card v-if="uploadConfig" shadow="never" style="margin-bottom: 20px;">
                <div slot="header">上传配置</div>
                <p>允许后缀: {{ uploadConfig.allow_ext_str || '-' }}</p>
                <p>大小限制: {{ formatFileSize(uploadConfig.max_size_bytes || 0) }}</p>
            </el-card>



            <!-- 上传区域 -->
            <div style="margin-bottom: 20px;">
                <el-upload
                    ref="directUpload"
                    drag
                    action="#"
                    :auto-upload="false"
                    :on-change="handleFileChange"
                    :show-file-list="false"
                    :disabled="!flowReady"
                    :accept="acceptValue">
                    <i class="el-icon-upload"></i>
                    <div class="el-upload__text">将文件拖到此处，或<em>点击选择</em></div>
                    <div class="el-upload__tip" slot="tip">支持 {{ uploadConfig ? (uploadConfig.allow_ext_str || '-') : '-' }}，大小不超过 {{ formatFileSize(uploadConfig ? (uploadConfig.max_size_bytes || 0) : 0) }}</div>
                </el-upload>
            </div>

            <!-- 上传按钮 -->
            <div style="margin-bottom: 20px;">
                <el-button
                    type="primary"
                    @click="startDirectUpload"
                    :loading="uploading"
                    :disabled="!selectedFile || !credential || !flowReady">
                    {{ uploading ? '上传中...' : '开始直传' }}
                </el-button>
                <el-button @click="refreshCredential">刷新凭证</el-button>
            </div>

            <!-- 进度条 -->
            <el-progress
                v-if="uploading || uploadProgress > 0"
                :percentage="uploadProgress"
                :status="uploadProgress === 100 ? 'success' : undefined"
                style="margin-bottom: 20px;">
            </el-progress>

            <!-- 已选文件信息 -->
            <el-card v-if="selectedFile" shadow="never" style="margin-bottom: 20px;">
                <div slot="header">已选文件</div>
                <p>文件名: {{ selectedFile.name }}</p>
                <p>大小: {{ formatFileSize(selectedFile.size) }}</p>
                <p>类型: {{ selectedFile.type }}</p>
            </el-card>

            <!-- 上传结果 -->
            <el-card v-if="uploadResult" shadow="never">
                <div slot="header">上传结果</div>
                <el-image
                    v-if="uploadResult.fileurl"
                    :src="uploadResult.fileurl"
                    style="max-width: 300px; max-height: 200px;"
                    fit="contain">
                </el-image>
                <pre style="background: #f5f7fa; padding: 10px; margin-top: 10px; overflow: auto;">{{ JSON.stringify(uploadResult, null, 2) }}</pre>
            </el-card>
        </el-card>

        <!-- 直传凭证信息 -->
        <el-card style="margin-top: 20px;">
            <h3>直传凭证信息</h3>
            <p style="color: #666; margin-bottom: 10px;">以下是从服务端获取的直传凭证，用于客户端直接上传到云存储。</p>
            <pre v-if="credential" style="background: #f5f7fa; padding: 10px; overflow: auto;">{{ JSON.stringify(credential, null, 2) }}</pre>
            <el-empty v-else description="暂无凭证信息"></el-empty>
        </el-card>

        <!-- 代码示例 -->
        <el-card style="margin-top: 20px;">
            <h3>二次开发指南</h3>

            <el-collapse>
                <el-collapse-item title="1. 获取直传凭证" name="1">
                    <pre style="background: #f5f7fa; padding: 10px; overflow: auto;">
// 获取直传凭证接口
GET /common/upload.api/getDirectUploadCredential?filename=test.jpg

// 响应示例 (本地存储)
{
    "status": true,
    "data": {
        "driver": "Local",
        "module": "image",
        "fileext": "jpg",
        "file_dir": "image/20240101/",
        "upload_url": "/common/upload.api/uploadLocalDirect",
        "domain": "http://localhost"
    }
}

// 响应示例 (阿里云 OSS)
{
    "status": true,
    "data": {
        "driver": "Aliyun",
        "module": "image",
        "fileext": "jpg",
        "file_dir": "image/20240101/",
        "object_key": "image/20240101/120001_abcd1234ef.jpg",
        "region": "oss-cn-hangzhou",
        "endpoint": "https://oss-cn-hangzhou.aliyuncs.com",
        "bucket": "your-bucket",
        "accessKeyId": "临时AccessKeyId",
        "accessKeySecret": "临时AccessKeySecret",
        "stsToken": "SecurityToken",
        "expiration": "2024-01-01T12:00:00Z"
    }
}

// 响应示例 (七牛云)
{
    "status": true,
    "data": {
        "driver": "Qiniu",
        "module": "image",
        "fileext": "jpg",
        "file_dir": "image/20240101/",
        "object_key": "image/20240101/120001_abcd1234ef.jpg",
        "upload_url": "https://up-z2.qiniup.com",
        "domain": "https://cdn.example.com",
        "bucket": "your-bucket",
        "token": "上传Token",
        "expireTime": 1704096000
    }
}
                    </pre>
                </el-collapse-item>

                <el-collapse-item title="2. 本地存储直传示例" name="2">
                    <pre style="background: #f5f7fa; padding: 10px; overflow: auto;">
// 本地存储"直传"是指直接上传到服务器 API

async function uploadToLocal(file, credential, jwtToken) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('module', credential.module);

    const response = await fetch(credential.upload_url, {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + jwtToken
        },
        body: formData
    });

    const result = await response.json();

    if (result.status) {
        return result.data;  // 仅返回上传结果，不包含附件记录
    } else {
        throw new Error(result.msg || '上传失败');
    }
}
                    </pre>
                </el-collapse-item>

                <el-collapse-item title="3. 阿里云 OSS 直传示例" name="3">
                    <pre style="background: #f5f7fa; padding: 10px; overflow: auto;">
// 安装阿里云 OSS SDK
// npm install ali-oss

import OSS from 'ali-oss';

async function uploadToAliyun(file, credential) {
    const client = new OSS({
        region: credential.region,
        accessKeyId: credential.accessKeyId,
        accessKeySecret: credential.accessKeySecret,
        stsToken: credential.stsToken,
        bucket: credential.bucket,
    });

    // 生成文件名
    const key = credential.object_key;

    // 上传文件
    const result = await client.put(key, file);

    return {
        filepath: key,
        fileurl: result.url
    };
}
                    </pre>
                </el-collapse-item>

                <el-collapse-item title="4. 七牛云直传示例" name="4">
                    <pre style="background: #f5f7fa; padding: 10px; overflow: auto;">
// 使用七牛云 JS SDK 或 FormData 直传

async function uploadToQiniu(file, credential) {
    const formData = new FormData();
    const key = credential.object_key;

    formData.append('file', file);
    formData.append('token', credential.token);
    formData.append('key', key);

    // upload_url 由后端根据 bucket 区域自动匹配
    const response = await fetch(credential.upload_url, {
        method: 'POST',
        body: formData
    });

    const result = await response.json();

    return {
        filepath: result.key,
        fileurl: credential.domain + '/' + result.key
    };
}
                    </pre>
                </el-collapse-item>

                <el-collapse-item title="5. 上传完成后保存记录（所有驱动）" name="5">
                    <pre style="background: #f5f7fa; padding: 10px; overflow: auto;">
// 直传完成后，调用接口保存附件记录到数据库
POST /common/upload.api/saveDirectUploadRecord

// 请求参数
{
    "filepath": "image/20240101/xxx.jpg",  // 存储中的文件路径
    "filename": "原始文件名.jpg",
    "filesize": 102400,
    "fileext": "jpg",
    "module": "image",  // image/video/file
    "is_private": 0     // 0=公开, 1=私有
}

// 响应
{
    "status": true,
    "data": {
        "aid": 123,
        "filename": "原始文件名.jpg",
        "fileurl": "https://xxx.com/image/20240101/xxx.jpg",
        "filethumb": "image/20240101/xxx.jpg"
    }
}
                    </pre>
                </el-collapse-item>

                <el-collapse-item title="6. 完整流程封装" name="6">
                    <pre style="background: #f5f7fa; padding: 10px; overflow: auto;">
/**
 * 通用直传上传类
 */
class DirectUploader {
    constructor(options = {}) {
        this.apiBase = options.apiBase || '';
        this.token = options.token || '';  // JWT Token
    }

    // 设置请求头
    getHeaders() {
        return {
            'Authorization': 'Bearer ' + this.token
        };
    }

    // 获取直传凭证
    async getCredential(module = 'image') {
        const res = await fetch(this.apiBase + '/common/upload.api/getDirectUploadCredential?module=' + module, {
            headers: this.getHeaders()
        });
        return res.json();
    }

    // 上传文件
    async upload(file, module = 'image', onProgress) {
        // 1. 获取凭证
        const credRes = await this.getCredential(module);
        if (!credRes.status) {
            throw new Error(credRes.msg || '获取凭证失败');
        }
        const credential = credRes.data;

        // 2. 根据驱动类型执行直传
        let result;
        if (credential.driver === 'Local') {
            result = await this.uploadToLocal(file, credential, onProgress);
        } else if (credential.driver === 'Aliyun') {
            result = await this.uploadToAliyun(file, credential, onProgress);
        } else if (credential.driver === 'Qiniu') {
            result = await this.uploadToQiniu(file, credential, onProgress);
        } else {
            throw new Error('不支持的驱动类型: ' + credential.driver);
        }

        // 3. 所有驱动统一保存记录
        const saveRes = await fetch(this.apiBase + '/common/upload.api/saveDirectUploadRecord', {
            method: 'POST',
            headers: {
                ...this.getHeaders(),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                filepath: result.filepath,
                filename: file.name,
                filesize: file.size,
                fileext: credential.fileext,
                module: credential.module,
                is_private: 0
            })
        });

        return saveRes.json();
    }

    // 本地服务器上传实现
    async uploadToLocal(file, credential, onProgress) {
        const formData = new FormData();
        formData.append('file', file);
        
        // 使用 XHR 支持进度回调
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', this.apiBase + credential.upload_url, true);
            xhr.setRequestHeader('Authorization', 'Bearer ' + this.token);
            
            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable && onProgress) {
                    onProgress(e.loaded / e.total);
                }
            };
            
            xhr.onload = () => {
                if (xhr.status === 200) {
                    const res = JSON.parse(xhr.responseText);
                    if (res.status) {
                        resolve(res);
                    } else {
                        reject(new Error(res.msg || '上传失败'));
                    }
                } else {
                    reject(new Error('上传失败: ' + xhr.statusText));
                }
            };
            
            xhr.onerror = () => reject(new Error('网络错误'));
            xhr.send(formData);
        });
    }

    // 阿里云上传实现
    async uploadToAliyun(file, credential, onProgress) {
        // 需要引入 ali-oss SDK
        // ...
    }

    // 七牛云上传实现
    async uploadToQiniu(file, credential, onProgress) {
        // ...
    }
}

// 使用示例
const uploader = new DirectUploader({
    apiBase: 'http://your-domain.com',
    token: 'your-jwt-token'
});

uploader.upload(file, 'image', (percent) => {
    console.log('上传进度:', percent);
}).then(res => {
    console.log('上传成功:', res);
});
                    </pre>
                </el-collapse-item>
            </el-collapse>
        </el-card>
    </div>

    <!-- 阿里云 OSS SDK -->
    <script src="https://gosspublic.alicdn.com/aliyun-oss-sdk-6.17.1.min.js"></script>

    <script>
        $(document).ready(function() {
            new Vue({
                el: '#app',
                data: {
                    credential: null,
                    uploadConfig: null,
                    acceptValue: '',
                    supportsDirectUpload: false,
                    driverChecked: false,
                    flowReady: false,
                    selectedFile: null,
                    uploading: false,
                    uploadProgress: 0,
                    uploadResult: null,
                    jwtToken: '',
                    selectedDriver: '' // 空字符串表示使用系统默认
                },
                methods: {
                    // 初始化四步流程：配置 -> 凭证 -> 上传 -> 保存
                    initUploadFlow: async function() {
                        this.flowReady = false;
                        this.uploadResult = null;
                        this.uploadProgress = 0;
                        try {
                            await this.fetchJwtToken();
                            await this.fetchUploadConfig();
                            this.flowReady = true;
                        } catch (e) {
                            this.flowReady = false;
                            this.$message.error(e.message || '初始化失败');
                        }
                    },

                    // 获取 JWT Token
                    fetchJwtToken: function() {
                        var that = this;
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.upload/getUploadToken')}",
                                type: 'GET',
                                success: function(res) {
                                    if (res.status && res.data.token) {
                                        that.jwtToken = res.data.token;
                                        resolve(res.data.token);
                                    } else {
                                        reject(new Error(res.msg || '获取 Token 失败'));
                                    }
                                },
                                error: function() {
                                    reject(new Error('获取 Token 请求失败'));
                                }
                            });
                        });
                    },

                    // 获取上传配置（允许后缀、大小）
                    fetchUploadConfig: function() {
                        var that = this;
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.api/getUploadConfig')}",
                                type: 'GET',
                                headers: {
                                    'Authorization': 'Bearer ' + that.jwtToken
                                },
                                success: function(res) {
                                    if (res.status && res.data) {
                                        var allowExt = [];
                                        if (Array.isArray(res.data.allow_ext)) {
                                            allowExt = res.data.allow_ext;
                                        } else if (res.data.allow_ext_str) {
                                            allowExt = String(res.data.allow_ext_str).split('|');
                                        }
                                        allowExt = allowExt.map(function(item) {
                                            return String(item || '').trim().toLowerCase();
                                        }).filter(function(item) {
                                            return !!item;
                                        });
                                        var uploadConfig = Object.assign({}, res.data, {
                                            allow_ext: allowExt,
                                            allow_ext_str: allowExt.join('|')
                                        });
                                        that.uploadConfig = uploadConfig;
                                        that.acceptValue = that.buildAcceptValue(allowExt);
                                        resolve(uploadConfig);
                                    } else {
                                        reject(new Error(res.msg || '获取上传配置失败'));
                                    }
                                },
                                error: function() {
                                    reject(new Error('获取上传配置请求失败'));
                                }
                            });
                        });
                    },

                    // 获取直传凭证
                    fetchCredential: function() {
                        var that = this;
                        if (!this.selectedFile) {
                            return Promise.reject(new Error('请先选择文件'));
                        }

                        this.driverChecked = false;
                        var requestData = {
                            filename: this.selectedFile.name
                        };
                        // 如果选择了特定驱动，传入 driver 参数
                        if (this.selectedDriver) {
                            requestData.driver = this.selectedDriver;
                        }
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.api/getDirectUploadCredential')}",
                                type: 'GET',
                                data: requestData,
                                headers: {
                                    'Authorization': 'Bearer ' + that.jwtToken
                                },
                                success: function(res) {
                                    that.driverChecked = true;
                                    if (res.status) {
                                        that.credential = res.data;
                                        that.supportsDirectUpload = true;
                                        that.flowReady = !!that.uploadConfig;
                                        resolve(res.data);
                                    } else {
                                        that.supportsDirectUpload = false;
                                        that.credential = null;
                                        that.flowReady = !!that.uploadConfig;
                                        reject(new Error(res.msg || '获取凭证失败'));
                                    }
                                },
                                error: function() {
                                    that.driverChecked = true;
                                    that.supportsDirectUpload = false;
                                    that.credential = null;
                                    that.flowReady = !!that.uploadConfig;
                                    reject(new Error('获取凭证请求失败'));
                                }
                            });
                        });
                    },

                    // 校验文件格式与大小
                    validateSelectedFileByConfig: function(file, showMessage) {
                        if (!file) {
                            return false;
                        }
                        if (!this.uploadConfig) {
                            if (showMessage) {
                                this.$message.error('上传配置未就绪，请先刷新页面');
                            }
                            return false;
                        }

                        var ext = this.getFileExt(file.name);
                        var allowExt = this.uploadConfig.allow_ext || [];
                        var maxSizeBytes = this.uploadConfig.max_size_bytes || 0;

                        if (allowExt.length > 0 && allowExt.indexOf(ext) === -1) {
                            if (showMessage) {
                                this.$message.error('不支持该文件格式，仅允许: ' + (this.uploadConfig.allow_ext_str || ''));
                            }
                            return false;
                        }

                        if (maxSizeBytes > 0 && file.size > maxSizeBytes) {
                            if (showMessage) {
                                this.$message.error('文件大小超限，最大支持 ' + this.formatFileSize(maxSizeBytes));
                            }
                            return false;
                        }

                        return true;
                    },

                    getFileExt: function(fileName) {
                        if (!fileName || fileName.indexOf('.') === -1) {
                            return '';
                        }
                        return fileName.split('.').pop().toLowerCase();
                    },

                    buildAcceptValue: function(allowExt) {
                        if (!allowExt || allowExt.length === 0) {
                            return '';
                        }
                        return allowExt.map(function(ext) {
                            return '.' + ext;
                        }).join(',');
                    },

                    // 切换驱动时重新获取凭证
                    onDriverChange: function() {
                        this.credential = null;
                        this.driverChecked = false;
                        this.flowReady = !!this.uploadConfig;
                        this.uploadResult = null;
                        this.selectedFile = null;
                        this.uploadProgress = 0;
                    },

                    // 刷新凭证
                    refreshCredential: function() {
                        if (!this.selectedFile) {
                            this.$message.error('请先选择文件');
                            return;
                        }
                        var that = this;
                        this.fetchCredential().then(function() {
                            that.$message.success('凭证已刷新');
                        }).catch(function(e) {
                            that.$message.error(e.message || '获取凭证失败');
                        });
                    },

                    // 选择文件
                    handleFileChange: function(file) {
                        if (!file || !file.raw) {
                            this.selectedFile = null;
                            return;
                        }

                        if (!this.validateSelectedFileByConfig(file.raw, true)) {
                            this.selectedFile = null;
                            if (this.$refs.directUpload) {
                                this.$refs.directUpload.clearFiles();
                            }
                            return;
                        }

                        this.selectedFile = file.raw;
                        this.uploadProgress = 0;
                        this.uploadResult = null;
                        var that = this;
                        this.fetchCredential().catch(function(e) {
                            that.$message.error(e.message || '获取凭证失败');
                        });
                    },

                    // 开始直传
                    startDirectUpload: async function() {
                        if (!this.selectedFile) {
                            this.$message.error('请先选择文件');
                            return;
                        }

                        if (!this.validateSelectedFileByConfig(this.selectedFile, true)) {
                            return;
                        }

                        if (!this.credential) {
                            await this.fetchCredential();
                        }
                        if (!this.flowReady || !this.credential) {
                            this.$message.error('上传流程未就绪，请先刷新凭证');
                            return;
                        }

                        this.uploading = true;
                        this.uploadProgress = 0;

                        try {
                            var result = await this.uploadByDriver();
                            result = await this.saveUploadRecord(result);

                            this.uploadResult = result;
                            this.$message.success('上传成功');
                        } catch (e) {
                            this.$message.error('上传失败: ' + e.message);
                            console.error(e);
                        } finally {
                            this.uploading = false;
                        }
                    },

                    // 根据驱动执行上传
                    uploadByDriver: async function() {
                        if (this.credential.driver === 'Local') {
                            return await this.uploadToLocal();
                        }
                        if (this.credential.driver === 'Aliyun') {
                            return await this.uploadToAliyun();
                        }
                        if (this.credential.driver === 'Qiniu') {
                            return await this.uploadToQiniu();
                        }
                        throw new Error('不支持的驱动: ' + this.credential.driver);
                    },

                    // 本地服务器直传
                    uploadToLocal: function() {
                        var that = this;
                        var cred = this.credential;

                        return new Promise(function(resolve, reject) {
                            var formData = new FormData();
                            formData.append('file', that.selectedFile);
                            formData.append('module', cred.module || 'file');

                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', cred.upload_url, true);
                            xhr.setRequestHeader('Authorization', 'Bearer ' + that.jwtToken);

                            xhr.upload.onprogress = function(e) {
                                if (e.lengthComputable) {
                                    that.uploadProgress = Math.round((e.loaded / e.total) * 100);
                                }
                            };

                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    var res = JSON.parse(xhr.responseText);
                                    if (res.status) {
                                        resolve(res.data);
                                    } else {
                                        reject(new Error(res.msg || '上传失败'));
                                    }
                                } else {
                                    reject(new Error('上传失败: ' + xhr.statusText));
                                }
                            };

                            xhr.onerror = function() {
                                reject(new Error('网络错误'));
                            };

                            xhr.send(formData);
                        });
                    },

                    // 阿里云 OSS 直传
                    uploadToAliyun: function() {
                        var that = this;
                        var cred = this.credential;

                        return new Promise(function(resolve, reject) {
                            var client = new OSS({
                                region: cred.region,
                                accessKeyId: cred.accessKeyId,
                                accessKeySecret: cred.accessKeySecret,
                                stsToken: cred.stsToken,
                                bucket: cred.bucket,
                            });

                            var key = cred.object_key;
                            if (!key) {
                                reject(new Error('缺少 object_key'));
                                return;
                            }

                            client.multipartUpload(key, that.selectedFile, {
                                progress: function(p) {
                                    that.uploadProgress = Math.round(p * 100);
                                }
                            }).then(function(result) {
                                resolve({
                                    filepath: key,
                                    fileurl: 'https://' + cred.bucket + '.' + cred.endpoint.replace('https://', '') + '/' + key
                                });
                            }).catch(function(err) {
                                reject(err);
                            });
                        });
                    },

                    // 七牛云直传
                    uploadToQiniu: function() {
                        var that = this;
                        var cred = this.credential;

                        return new Promise(function(resolve, reject) {
                            var formData = new FormData();
                            var key = cred.object_key;
                            if (!key) {
                                reject(new Error('缺少 object_key'));
                                return;
                            }

                            formData.append('file', that.selectedFile);
                            formData.append('token', cred.token);
                            formData.append('key', key);

                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', cred.upload_url, true);

                            xhr.upload.onprogress = function(e) {
                                if (e.lengthComputable) {
                                    that.uploadProgress = Math.round((e.loaded / e.total) * 100);
                                }
                            };

                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    var result = JSON.parse(xhr.responseText);
                                    resolve({
                                        filepath: result.key,
                                        fileurl: cred.domain + '/' + result.key
                                    });
                                } else {
                                    reject(new Error('上传失败: ' + xhr.statusText));
                                }
                            };

                            xhr.onerror = function() {
                                reject(new Error('网络错误'));
                            };

                            xhr.send(formData);
                        });
                    },

                    // 保存上传记录
                    saveUploadRecord: function(result) {
                        var that = this;
                        var ext = (this.credential && this.credential.fileext) ? this.credential.fileext : this.getFileExt(this.selectedFile.name);
                        var module = (this.credential && this.credential.module) ? this.credential.module : 'file';

                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.api/saveDirectUploadRecord')}",
                                type: 'POST',
                                headers: {
                                    'Authorization': 'Bearer ' + that.jwtToken
                                },
                                data: {
                                    filepath: result.filepath,
                                    filename: that.selectedFile.name,
                                    filesize: that.selectedFile.size,
                                    fileext: ext,
                                    module: module,
                                    is_private: 0,
                                    driver: (that.credential && that.credential.driver) ? that.credential.driver : (that.selectedDriver || '')
                                },
                                success: function(res) {
                                    if (res.status) {
                                        resolve(res.data);
                                    } else {
                                        reject(new Error(res.msg || '保存记录失败'));
                                    }
                                },
                                error: function() {
                                    reject(new Error('保存记录请求失败'));
                                }
                            });
                        });
                    },

                    // 格式化文件大小
                    formatFileSize: function(bytes) {
                        if (bytes === 0) return '0 B';
                        var k = 1024;
                        var sizes = ['B', 'KB', 'MB', 'GB'];
                        var i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                    }
                },
                mounted: function() {
                    this.initUploadFlow();
                }
            });
        });
    </script>
</div>
