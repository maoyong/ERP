	window.onload=getPrice();
	window.onload=getNum();



	//统计方法
	function getPrice(){
		var a=0;
		for(var i=1;i<$(".table").find("tr").length;i++){
			$(".table tr:eq("+i+") td:eq(0) p").text(i);
			var a=a+Number($(".table tr:eq("+i+") td:eq(8) input").val());
			a = Math.round(a*100) / 100;
		}
		$(".getPrice").val("￥"+a);
		$("input[name='total_money']").val(a);
	}

	function getNum(){
		var a=0;
		for(var i=1;i<$(".table").find("tr").length;i++){
			$(".table tr:eq("+i+") td:eq(0) p").text(i);
			var a=a+Number($(".table tr:eq("+i+") td:eq(5) input").val());
		}
		$(".getNum").val(a);
		$("input[name='total_num']").val(a);
	}
	//计算金额
	function sumPrice(obj){
		var tr = obj.parents('tr');
		var num = Number(tr.find('td:eq(5) input').val()); //数量
		var tax_price = Number(tr.find('td:eq(7) input').val()); //含税单价
		var rate = Number(tr.find('td:eq(9) input').val()); //税率
		rate = rate / 100;
		//单价计算
		var unit_price = Math.round((tax_price / (1+rate)) * 100) / 100;
		tr.find('td:eq(6) input').val(unit_price);
		//计算金额
		var price = Math.round((unit_price * num) * 100) / 100;
		tr.find('td:eq(8) input').val(price);
		//计算税额
		tr.find('td:eq(10) input').val(Math.round((price * rate) * 100) / 100);
		//计算总计
		tr.find('td:eq(11) input').val(Math.round((tax_price * num) * 100) / 100);
		getPrice();
		getNum();
	}

	$('.deleteGoods').on('click', function(){
		var tr = $(this).parents('tr');
		var url = $(this).attr('url');
		layer.confirm('确定删除该商品吗？', {
	        btn: ['确定', '取消'],
	        title: '提示',
	        icon: 3
	    }, function (index) {
	    	layer.close(index);
	        id = [];
	        $.post(url, {id: id}, function (data) {
	            if (data.code == 0) {
	                parent.layer.msg(data.msg, {icon: 1, time: 1000});
	                tr.remove();
	                getPrice();
					getNum();
	            } else {
	                layer.alert(data.msg);
	            }
	        }, 'json')
	    }, function (index) {
	       layer.close(index);
	    });
	})

	$(".tax_price").on('change', function(){
		sumPrice($(this));
	})
	$(".rate").on('change', function(){
		sumPrice($(this));
	})
	

	//采购入库显示订单号
	$("#put_type").on('change', function(){
		if ($(this).val() == '采购入库' || $(this).val() == '销售合同' ){
			$('#order_no').show();
		}else{
			$('#order_no').hide();
		}
	})

	$('.select-goods').on('change', function(){
		var tr = $(this).parents('tr');
		var ptitle = $(this).find("option:selected");	
	 	tr.find('td:eq(1) input').val(ptitle.attr('goods_name'));
	 	tr.find('td:eq(3) input').val(ptitle.attr('model'));
	 	tr.find('td:eq(4) input').val(ptitle.attr('unit_name'));
	});

	$('#order_no_sel').on('change', function(){
		var id = $(this).val();
		var url = $("input[name='getUrl']").val();
		$.ajax({
			type:'get',
			data:{'id':id},
			url:url,
			success:function(data){
				if (data.code == 0) {
					if (data.data.supplier) { //供应商
						$("input[name='order_no']").val(data.data.order_no);
						$("input[name='compact_no']").val(data.data.compact_no);
						$('#supplier_ids').remove();
						$('.active:eq(1)').after("<input type='hidden' name='supplier_id' value='"+data.data.supplier+"'>");
						$('.active:eq(1)').after("<input type='text' style='float: right; margin-right: 4px' disabled='disabled' value='"+data.data.suppliers.client_name+"'>");
						
					}else{ //客户
						$("input[name='order_no']").val(data.data.compact_no);
						$('#supplier_ids').remove();
						$('.active:eq(1)').after("<input type='hidden' name='supplier_id' value='"+data.data.client+"'>");
						$('.active:eq(1)').after("<input type='text' style='float: right; margin-right: 4px' disabled='disabled' value='"+data.data.clients.client_name+"'>");
						
					}
					
					$('#store_table').find('tbody').empty();
					$('.getNum').val(data.data.total_num);
					$('.getPrice').val(data.data.total_money);
					$("input[name='total_money']").val(data.data.total_money);
					$("input[name='total_num']").val(data.data.total_num);
					$("#tmpl_store").tmpl(data.data).appendTo('.table tbody');
				}else{
					layer.msg(data.msg,{'icon':2});
				}
			}
		});
	})

	$(document).ready(function(){
		$(".appends").on('click', function(){
			var html = $("#tpl").html();
			html=html.replace('<tbody>', '');
			html=html.replace('</tbody>', '');
			var select_key = $("input[name='select_key']").val();
			html=html.replace('#no#', select_key);
			$(".table tbody").append(html);
			getPrice();
		 	$(".tax_price").on('change', function(){
				sumPrice($(this));
			})
			$(".rate").on('change', function(){
				sumPrice($(this));
			})
			$('.select-goods-'+select_key).selectMania('init', {
				    size: 'tiny',
				    width:'100%', 
				    themes: ['new'], 
				    placeholder: 'select!',
					removable: false,
					search: true,
			});
			$('.select-goods-'+select_key).on('change', function(){
				var tr = $(this).parents('tr');
				var ptitle = $(this).find("option:selected");	
			 	tr.find('td:eq(1) input').val(ptitle.attr('goods_name'));
			 	tr.find('td:eq(3) input').val(ptitle.attr('model'));
			 	tr.find('td:eq(4) input').val(ptitle.attr('unit_name'));
			});
			$("input[name='select_key']").val(++select_key);
			
		});

    	$('.select-goods').selectMania('init', {
			    size: 'tiny',
			    width:'100%', 
			    themes: ['new'],
			    placeholder: 'select!',
				removable: false,
				search: true,
		});

		$('#supplier_id').selectMania('init', {
			    size: 'tiny',
			    width:'100%', 
			    themes: ['new'],
			    placeholder: 'select!',
				removable: false,
				search: true,
		});

	});
	





