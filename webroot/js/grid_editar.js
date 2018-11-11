/**
 * Script para grid
 */
var actionCurrent 	= 0;
var podeSalvar 		= false;


window.onload = function()
{
	if (window['flashMessage'] !== undefined)
	{
		setTimeout(function() 
		{
			$('#flashMessage').fadeOut(100);
		},3000);
	}
};

window.onclick = function(event)
{
    if (event.target.id == 'btSalvar') 
    {
        window.document.formEditar.submit();
        return false;
    }
}


$(document).ready(function() 
{
	$(".imgField").click(function() 
	{
		console.log('aqui vou exibir o modal da foto');
	});

	$(".divField label").click(function()
	{
		let id = $(this).attr('id').replace('label','').toLowerCase();
		if ($("#prop"+id).length > 0)
		{
			let prop = $("#prop"+id).html().replace('<pre>','<pre style="text-align: left;">');
			showModalGrid(prop,{'titulo':'Propriedades do campo '+id, 'altura':'auto'});
		}
	});
});