String.prototype.capitalize = function() 
{
    return this.charAt(0).toUpperCase() + this.slice(1);
}

function showModal(janela)
{
	var maskHeight 	= $(document).height();
	var maskWidth 	= $(window).width();
	$('#tampaTudo').css({'width':maskWidth,'height':maskHeight});
	$('#tampaTudo').fadeTo("slow",0.8);	
	$("#"+janela).fadeIn(2000); 
}
