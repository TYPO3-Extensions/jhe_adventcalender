/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function openModal(id, windowwidth, windowheight, pageid) {

	$(document).ready(function() {  

		$('<div id="boxes"><div id="dialog" class="window"><b>Testing of Modal Window</b> | <button id="windowclose">Close it</button></div><div id="mask"></div></div>').appendTo('body');

		//select all the a tag with name equal to modal
		$('area').click(function(e) {

			//Cancel the link behavior
			e.preventDefault();
			//Get the A tag
			//var id = $(this).attr('href');

			//Get the screen height and width
			var maskHeight = $(document).height();
			var maskWidth = $(window).width();

			//Set height and width to mask to fill up the whole screen
			$('#mask').css({'width':maskWidth,'height':maskHeight});

			//transition effect     
			$('#mask').fadeIn(1000);    
			$('#mask').fadeTo("slow",0.8);  

			//Get the window height and width
			var winH = $(window).height();
			var winW = $(window).width();

			//Set the popup window to center
			$(id).css('top',  winH/2-$(id).height()/2);
			$(id).css('left', winW/2-$(id).width()/2);
			$(id).css('width', windowwidth);
			$(id).css('height', windowheight);
			
			$.ajax({
				url: '?eID=adventcalender',
				type: 'GET',
				data: 'pageID=' + pageid,
				dataType: 'json',
				success: function(result) {
					alert(result);
					$(id).html('<h3>' + result.pageTitle + '</h3><strong><br /><br/>AJAX-Result: ' + result.contentTitle + '<br /><br /><br />' + result.url + '<br /><br />' + result.code + '</strong><button id="windowclose">Close it</button>');
				}
                });

			//transition effect
			$(id).fadeIn(2000); 

		});

		//if close button is clicked
		$('#windowclose').click(function (e) {
			//Cancel the link behavior
			e.preventDefault();
			//$('#mask, .window').hide();
			$('#mask, .window').fadeOut(500);
		});     

		//if mask is clicked
		$('#mask').click(function () {
			$(this).fadeOut(500);
			$('.window').fadeOut(500);
		});         

	});

}