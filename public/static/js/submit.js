        var form = layui.form;
        var layer = layui.layer;
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
        form.on('submit(ajaxsubmit)', function(data){
            return false;
        });

    $(document).ready(function(){ 
         var options = {
              type:'post',           //post提交
              dataType:"json",        //json格式
              data:{},    //如果需要提交附加参数，视情况添加
              clearForm: false,        //成功提交后，清除所有表单元素的值
              resetForm: false,        //成功提交后，重置所有表单元素的值
              cache:false,          
              async:false,          //同步返回 
              success:function(data){
                if(data.code==1){
                    layer.msg(data.msg, {icon:2});
                }else{
                    layer.msg(data.msg,{icon:1,time:500},function(){
                        $("#reset").click();
                        x_admin_close();
                        parent.location.reload();
                    });
                }
              //服务器端返回处理逻辑
                },
                error:function(XmlHttpRequest,textStatus,errorThrown){
                    layer.msg('操作失败:服务器处理失败',{icon:2});
              }
            }; 
        // bind form using 'ajaxForm' 
        $('#myForm').ajaxForm(options).submit(function(data){
            //无逻辑
        }); 

    });
    