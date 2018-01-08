$().ready(function() {
//我们强烈推荐你在代码最外层把需要用到的模块先加载
    layui.use(['form', 'layedit', 'laydate'], function(){
        var form = layui.form()
            ,layer = layui.layer
            ,layedit = layui.layedit
            ,laydate = layui.laydate;
        //创建一个编辑器
        var editIndex = layedit.build('LAY_demo_editor');
        //自定义验证规则
        form.verify({
            title: function(value){
                if(value.length < 5){
                    return '标题至少得5个字符啊';
                }
            }
            ,pass: [/(.+){6,12}$/, '密码必须6到12位']
            ,content: function(value){
                layedit.sync(editIndex);
            }
            ,phone: [/^1[3|4|5|7|8]\d{9}$/, '手机必须11位，只能是数字！']
            ,email: [/^[a-z0-9._%-]+@([a-z0-9-]+\.)+[a-z]{2,4}$|^1[3|4|5|7|8]\d{9}$/, '邮箱格式不对']
        });
        //监听提交
        form.on('submit(ajaxSubmit)', function(data){
            var url = $('form').attr('toUrl');
            $(form).ajaxSubmit({
                url:$('form').attr('action'),
                type: 'POST',
                data: $('form').serializeArray(),
                dataType: 'JSON',
                xhrFields:{
                    widthCredentials:true
                },
                success: function(data){
                    if (data.error == 1){
                        layer.msg(data.message, {icon:2});
                    }else{
                        layer.msg(data.message, {icon:1});
                        if (url != ''){
                            window.location.href = url;
                        }
                    }
                }
            });
            return false;
        });
    });
});