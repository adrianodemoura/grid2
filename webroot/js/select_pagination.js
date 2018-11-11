var PaginacaoSP = {
	params: {},

	init: function(params)
	{
		this.params = params;
	},

	setNavegacao: function()
	{
		let domId	= this.params.id.substr(0,1).toUpperCase()+this.params.id.substr(1,this.params.id.length);

		$("#page"+domId).text(this.params.page+'/'+this.params.last_page);
		$("#total"+domId).text('exibindo: '+this.params.limit+' de '+this.params.total);

		$("#btnPri"+domId).removeAttr('disabled');
		$("#btnAnt"+domId).removeAttr('disabled');
		$("#btnPro"+domId).removeAttr('disabled');
		$("#btnUlt"+domId).removeAttr('disabled');
		switch(this.params.page)
		{
			case 1:
				$("#btnPri"+domId).attr('disabled', 'disabled');
				$("#btnAnt"+domId).attr('disabled', 'disabled');
				break;
			case this.params.last_page:
				$("#btnPro"+domId).attr('disabled', 'disabled');
				$("#btnUlt"+domId).attr('disabled', 'disabled');
				break;
		}
	},

 	getPagina: function (p)
	{
		// atualizando a página do elemento.
		let page = this.params.page;
		switch(p)
		{
			case 1:
				page = 1;
				break;
			case -1:
				page--;
				break;
			case 2:
				page++;
				break;
			case 0:
				page = this.params.last_page;
				break;
		}
		if (page<1)
		{
			page = 1;
		}
		if (page>this.params.last_page)
		{
			page = this.params.last_page;
		}
		this.params.page = page;

		let url 	= this.params.url;
		let domId	= this.params.id.substr(0,1).toUpperCase()+this.params.id.substr(1,this.params.id.length);
		let params 	= {};
		params.name  	= this.params.name;
		params.pagina 	= this.params.page;
		params.limite 	= this.params.limit;
		params.campo 	= this.params.field_search;
		params.filtro 	= $("#"+this.params.id).val();
		params.token 	= this.params.token;

		$("#div"+domId+"Message").removeClass('erro').addClass('atencao').html('Aguarde ...').fadeIn();

		$.ajax({
	        url: url,
	        method: "POST",
	        dataType: "JSON",
	        data: params,
	        success: function(resposta)
	        {
	            try
	            {
	            	if (resposta.status == false)
	            	{
	            		throw resposta.msg;
	            	}
	            	if (typeof resposta.total === 'undefined')
	            	{
	            		throw "O campo total é de retorno obrigatório !";
	            	}
	            	if (typeof resposta.ultima_pagina === 'undefined')
	            	{
	            		throw "O campo last_page é de retorno obrigatório !";
	            	}
	            	if (typeof resposta.novo_token === 'undefined')
	            	{
	            		throw "O campo novo_token é de retorno obrigatório !";
	            	}

	            	window["Paginacao"+domId].params.total 		= resposta.total;
	            	window["Paginacao"+domId].params.last_page 	= resposta.ultima_pagina;
	            	window["Paginacao"+domId].params.token 		= resposta.novo_token;
	            	window["Paginacao"+domId].setNavegacao();

	            	let htmlTr = "";
	            	$.each(resposta.lista, function(l, arrMods)
	            	{
	            		htmlTr += "<tr id='"+(l+1)+"'>";
	            		$.each(arrMods, function(model, arrFields)
	            		{
	            			htmlTr += "<td>"+arrFields.rowCount+"</td>";
	            			$.each(arrFields, function(field, vlr)
	            			{
	            				if (field != 'rowCount')
	            				{
	            					htmlTr += "<td>"+vlr+"</td>";
	            				}
	            			})
	            		})
	            		htmlTr += "</tr>";
	            	});

	            	$("#tbody"+domId).html(htmlTr).fadeIn();
	            	$("#div"+domId+"Message").html(resposta.msg).fadeIn();
	            } catch (erro)
	            {
	            	$("#div"+domId+"Message").html(erro).addClass('erro').fadeIn();
	            }
	        },
	        error: function(xhr, status, erro)
	        {
	            $("#div"+domId+'Error').html(erro).addClass('erro').fadeIn();
	        }
	    });
	}
}
