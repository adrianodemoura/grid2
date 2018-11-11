$(document).ready(function() 
{
	if (typeof $("#flashMessage") !== 'undefined')
	{
		$("#flashMessage").delay(1000).fadeOut(4000);
	}

    $(".txtData").datepicker();

	/*$(".btn-excluir").click(function() 
	{
		linkExcluir = $(this).attr('href');
		tokenIndex 	= $("#TokenIndex").val();

		// recuperando a solicitação para montar o modal
	    try
	    {
	    	var botoes = {};
	    	botoes['ExcluirModal'] = {'id':'btnExcluirModal','text':'Excluir', 'click':function() 
	    	{
	    		$.ajax
		        ({
		            url: linkExcluir,
		            method: "POST",
		            dataType: "JSON",
		            data: {'TokenIndex': tokenIndex},
		            success: function(resposta)
		            {
		            	if (resposta.status == true)
		            	{
	    					document.location.href = resposta.redirect;
	    				} else
	    				{
	    					$("#btnExcluirModal").before("<span class='erroModal'>"+resposta.msg+"</span>&nbsp;&nbsp;");
	    				}
		            }
		        });
	    	}};
    		botoes['FecharModal'] = {'id':'btnFecharModal','text':'Cancelar', 'click':function() { $("#modalCenter").dialog('close'); }};

	    	// exibindo o modal
            $("#modalFull").html("<div class='modalContent' id='modalCenter'><br /><center>Você tem certeza em excluir este registro ?</center></div>").fadeIn(1000);
	        $("#modalCenter").dialog
            ({
                position: {my: 'top', at: 'top+230'},
                title: 'Exclusão Registro',
                width: 500,
                buttons: botoes,
                close: function(event, ui) 
                {
                    $("#modalFull").fadeOut(function() 
                    {
                        $("#modalCenter").remove();
                    });
                },
            }).dialog("open");
	    } catch(e)
	    {
	        $("#divRespostaAjax").addClass('erro').delay(50).html(e).fadeIn();
	    }
		return false;
	});*/
});

/**
 * Configura a linha do último registro acessado pelo usuário.
 */
function setLastId(lastId, antigoLastid)
{
	if (typeof antigoLastid !== 'undefined' && typeof lastId !== 'undefined' && antigoLastid !== null && lastId !== null)
	{
		$("#tr"+lastId['id']).addClass('trLastId').attr('title', 'Último acesso em '+lastId['hora']);
		$("#tr"+antigoLastid['id']).removeClass('trLastId');
	}
}
