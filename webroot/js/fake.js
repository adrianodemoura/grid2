$(document).ready(function() 
{
	$(".btnFakePopular").click(function()
	{
		$(this).val('Aguarde ...').attr('disabled', 'disabled');
		$("#msg").html('Aguarde ...').removeClass('fakeSucesso').removeClass('fakeErro');

		let model 	= $(this).attr('id').replace('btnFakePopular','');
		let total 	= $("#Total"+model).val();
		let limpar 	= $("#Limpar"+model).is(':checked') 	? 1 : 0;
		let validar = $("#Validar"+model).is(':checked') 	? 1 : 0;
		let params  = {'model':model, 'total':total, 'limpar':limpar, 'validar':validar};

        if (typeof urlFake == 'undefined')
        {
            var urlFake = document.URL.replace('fake','get_fake');
        }

		$.ajax
        ({
            url: urlFake,
            method: "POST",
            data: params,
            dataType: "JSON",
            success: function(resposta)
            {
            	let classe = (resposta.status) ? 'fakeSucesso' : 'fakeErro';
            	$("#btnFakePopular"+model).val('Popular').removeAttr('disabled');
                if (resposta.status)
                {
            		$("#tdTotal"+model).text(resposta.totalAtual.toLocaleString());
            	}
            	showMsgFake(resposta.msg, classe);
            },
            error: function(xhr, status, erro)
            {
                $("#btnFakePopular"+model).val('Popular').removeAttr('disabled');
                showMsgFake(erro,'erro');
            }
        });
	});
});

function showMsgFake(msg, classe)
{
	$("#msg").html(msg).addClass(classe).fadeIn();
}