/**
 * variáveis locais	
 */
var positionShowFieldTop = 0;
var positionShowFieldLeft= 0;

function Valor(vlr)
{
    if (typeof vlr != 'undefined')
    {
        return vlr;
    } else
    {
        return '';
    }
}

$(document).ready(function() 
{

	$(".spanShowField").mouseover(function() 
	{
		var position 	= $(this).position();
		var id 			= $(this).attr('id').replace('spanField','');
		var propField 	= 'propField'+id;

		$("#"+propField).attr('left',position.left);
		$("#"+propField).fadeIn();
	});

	$(".spanShowField").mouseout(function() 
	{
		var id = $(this).attr('id').replace('spanField','');
		var propField = 'propField'+id;
		$("#"+propField).fadeOut();
	});

});