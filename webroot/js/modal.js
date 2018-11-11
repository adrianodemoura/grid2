// variáveis globais
var htmlAguarde = '<center><img src="'+base+'/img/ajax.gif" />&nbsp;&nbsp;&nbsp;<span class="spAguarde">A G U A R D E</span></center>';
var acaoGridOk  = '';
var atualizarPagina = false;

$(document).on('click','#btnModalGridFechar', function() 
{
    $("#divModalGrid").dialog('close');
});
$(document).on('click','#btnModalGridOk', function() 
{
    $("#divModalGridBotoes").html(htmlAguarde);
    if (acaoGridOk!='')
    {
        let acao    = acaoGridOk.substr(acaoGridOk,acaoGridOk.indexOf('('));
        let arrP    = acaoGridOk.split('(');
        let params  = '';
        if (typeof arrP[1] !== 'undefined')
        {
            params  = "'"+arrP[1].replace(')','')+"'";
        }
        if (acao.length>0)
        {
            window[acao](params);
        } else
        {
            document.location.href = acaoGridOk;
        }
    }
});

/**
 * Exibe o modal de mensagens de erro
 *
 * @param 	string 	msg 	Mensagem de erro
 * @param 	json    config 	Configurações do modal
 * @return 	void
 */
function showModalGrid(msg,config)
{
	config 				= (typeof config              !== 'undefined') ? config             : {};
	config.titulo		= (typeof config.titulo       !== 'undefined') ? config.titulo      : 'Ok';
	config.posicao		= (typeof config.posicao      !== 'undefined') ? config.posicao     : {my: 'top', at: 'top+225'};
    config.largura      = (typeof config.largura      !== 'undefined') ? config.largura     : '800';
	config.altura 		= (typeof config.altura       !== 'undefined') ? config.altura      : '160';
	config.tempoSombra 	= (typeof config.tempoSombra  !== 'undefined') ? config.tempoSombra : 10;
    config.redirecionar = (typeof config.redirecionar !== 'undefined') ? config.redirecionar: '';
    config.atualizar    = (typeof config.atualizar    !== 'undefined') ? config.atualizar   : false;
    config.txtOk        = (typeof config.txtOk        !== 'undefined') ? config.txtOk       : 'Ok';
    config.txtFechar    = (typeof config.txtFechar    !== 'undefined') ? config.txtFechar   : 'Fechar';
    if (typeof config.acao !== 'undefined')
    {
        acaoGridOk = config.acao;
    }

    if (typeof config.classe === 'undefined')
    {
        config.classe = '';
    }

    if (!config.altura)
    {
        altura = {};
    }
    if (typeof config.altura === 'undefined')
    {
        config.altura = '30';
    }
    if (config.altura==='0')
    {
        config.altura = '150';
    }

	let htmlModal= "<div id='divModalGridMsg' style='padding: 10px 0px; min-height: 40px;' class='"+config.classe+"'>"+msg+"</div>";
	htmlModal += "<div id='divModalGridBotoes' style='margin-top: 0px; padding-top: 10px; border-top: 1px solid #ccc;'>";
    if (typeof config.botaoOkOff === 'undefined')
    {
        htmlModal += "<input type='button' name='btnModalGridOk'    id='btnModalGridOk'         value='"+config.txtOk+"'  class='btn-link-botao' />";
    }
    if (typeof config.botaoFecharOff === 'undefined')
    {
        htmlModal += "<input type='button' name='btnModalGridFechar' id='btnModalGridFechar' autofocus='autofocus' value='"+config.txtFechar+"'  class='btn-link-botao' />";
    }
    htmlModal += "</div>";

	$("#divModalGrid").remove();
	$("#divModalFundo").remove();

	$("#footer").after("<div id='divModalFundo' class='divModalFundo'></div>");
	$("#divModalFundo").after("<div id='divModalGrid' style='text-align: center; font-weight: bold; letter-spacing: 2px;'>"+htmlModal+"</div>").fadeIn(config.tempoSombra, function()
	{
		$('#divModalGrid').fadeIn();
	});
	$("#divModalGrid").dialog
    ({
        position: config.posicao,
        title: config.titulo,
        width: config.largura.replace('px',''),
        height: config.altura.replace('px',''),
        close: function(event, ui)
        {
        	$("#divModalFundo").fadeOut();
            if (config.atualizar === true)
            {
                $("#conteudo").html(htmlAguarde);
                location.reload();
            }
            if (config.redirecionar.length>0)
            {
                $("#conteudo").html(htmlAguarde);
                document.location.href = config.redirecionar;               
            }
            if (atualizarPagina === true)
            {
                document.location.reload();
            }
        }
    }).dialog("open");

    return false;
}