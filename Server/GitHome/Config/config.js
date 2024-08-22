document.addEventListener("DOMContentLoaded", () => {

	var coll = document.getElementsByClassName("collapsible");
	var i;
	
	for (i = 0; i < coll.length; i++)
	{
		coll[i].addEventListener("click", function() {
			this.classList.toggle("active");
			var content = this.nextElementSibling;

			if (content.style.display === "block")
				content.style.display = "none";
			else
				content.style.display = "block";
		});
	}
});

function deleteDevice(id)
{
	if (confirm(`Remove device ${id}?`))
	{
		window.location = '/config/deleteDevice/' + id;
	}
}