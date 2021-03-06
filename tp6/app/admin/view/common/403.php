<style>
    body {
        height: 100%;
        background-color: #F8F8F8
    }

    .http404-container {
        transform: translate(-50%, -50%);
        position: absolute;
        top: 40%;
        left: 50%;
    }

    .http404 {
        position: relative;
        width: 1200px;
        padding: 0 50px;
        overflow: hidden;
    }

    .pic-404 {
        position: relative;
        float: left;
        width: 600px;
        overflow: hidden;
    }

    .pic-404 .pic-404__parent {
        width: 100%;
    }

    .bullshit {
        position: relative;
        float: left;
        width: 300px;
        padding: 30px 0;
        overflow: hidden;
    }

    .bullshit .bullshit__oops {
        font-size: 32px;
        font-weight: bold;
        line-height: 40px;
        color: #1482f0;
        margin-bottom: 20px;

    }

    .bullshit .bullshit__headline {
        font-size: 20px;
        line-height: 24px;
        color: #222;
        font-weight: bold;
        margin-bottom: 10px;

    }

    .bullshit .bullshit__info {
        font-size: 13px;
        line-height: 21px;
        color: grey;
        margin-bottom: 30px;

    }

    .bullshit .bullshit__return-home {
        display: block;
        float: left;
        width: 110px;
        height: 36px;
        background: #1482f0;
        border-radius: 100px;
        text-align: center;
        color: #ffffff;
        font-size: 14px;
        line-height: 36px;
        cursor: pointer;
        text-decoration: none;
    }

</style>

<div class="http404-container">
    <div class="http404">
        <div class="pic-404">
            <img class="pic-404__parent" src="/statics/admin/pages/guidePage/404/404.png" alt="404">
        </div>
        <div class="bullshit">
            <div class="bullshit__oops">OOPS!</div>
            <div class="bullshit__info"/>
            <div class="bullshit__headline">{$title|default='你没有权限进入该页面'}</div>
            <div class="bullshit__info">{$msg|default='请检查您输入的网址是否正确或向网管反馈'}</div>
            <!--            <a href="/" class="bullshit__return-home">返回首页</a>-->
        </div>
    </div>
</div>


