window.onload=getPrice();
	window.onload=getNum();
	//统计方法
	function getPrice(){
		var a=0;
		for(var i=1;i<$(".table").find("tr").length;i++){
			$(".table tr:eq("+i+") td:eq(0) p").text(i);
			var a=a+Number($(".table tr:eq("+i+") td:eq(8) input").val());
			
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

	$(".tax_price").on('change', function(){
		sumPrice($(this));
	})
	$(".rate").on('change', function(){
		sumPrice($(this));
	})
	$(".appends").click(function(){
		var html = $("#tpl").html();
		html=html.replace('<tbody>', '');
		html=html.replace('</tbody>', '');

		$(".table tbody").append(html);
		getPrice();
	 	$('.price').on('change', function(){
	 		sumPrice($(this));
		})
		$(".rate").on('change', function(){
			sumPrice($(this));
		})
	});