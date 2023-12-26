function config_api_post(obj)
{
    const formData  = new FormData();
      
    for(const name in obj) {
      formData.append(name, obj[name]);
    }
    fetch(`save.php`, {
        method: "POST",
        body: formData
    }).then(() => {location.reload()});
}

function update(element) {
  let code = element.value;
  let result_element = element.parentElement.querySelector("#highlighting").querySelector("#highlighting-content");
  // Handle final newlines (see article)
  if(code[code.length-1] == "\n") {
    code += " ";
  }
  // Update code
  result_element.innerHTML = code.replace(new RegExp("&", "g"), "&amp;").replace(new RegExp("<", "g"), "&lt;"); /* Global RegExp */
  // Syntax Highlight
  Prism.highlightElement(result_element);
}

function sync_scroll(element) {
  /* Scroll result to scroll coords of event - sync with textarea */
  let result_element = element.parentElement.querySelector("#highlighting");
  // Get and set x and y
  result_element.scrollTop = element.scrollTop;
  result_element.scrollLeft = element.scrollLeft;
}

function check_tab(element, event) {
  let code = element.value;
  if(event.key == "Tab") {
    /* Tab key pressed */
    event.preventDefault(); // stop normal
    let before_tab = code.slice(0, element.selectionStart); // text before tab
    let after_tab = code.slice(element.selectionEnd, element.value.length); // text after tab
    let cursor_pos = element.selectionStart + 1; // where cursor moves after tab - moving forward by 1 char to after tab
    element.value = before_tab + "\t" + after_tab; // add tab char
    // move cursor
    element.selectionStart = cursor_pos;
    element.selectionEnd = cursor_pos;
    update(element); // Update text to include indent
  }
}
document.addEventListener("DOMContentLoaded", () => {

  var codeInputs = document.querySelectorAll("#code");
  for (var i = 0; i < codeInputs.length; i++) {
    update(codeInputs[i]);
  }

  document.getElementById("cliclick").onclick=async ()=>{await cli()};

  var coll = document.getElementsByClassName("collapsible");
  var i;
    
  for (i = 0; i < coll.length; i++) {
    coll[i].addEventListener("click", function() {
      this.classList.toggle("active");
      var content = this.nextElementSibling;
      if (content.style.display === "block") {
        content.style.display = "none";
      } else {
        content.style.display = "block";
      }
    });
  }
});

function save_device(dev) {
  a = {
        id: dev.getAttribute('device-id'),
        type: dev.parentElement.querySelector("#type").value,
        data: Base64.encode(dev.parentElement.querySelector("#data")?.value ?? ""),
        auto: Base64.encode(dev.parentElement.querySelector("#auto")?.value ?? ""),
        datatosend: Base64.encode(dev.parentElement.querySelector("#datatosend")?.value ?? ""),
        firmware: dev.parentElement.querySelector("#firmware").value,
        name: dev.parentElement.querySelector("#name").value,
        code: Base64.encode(dev.parentElement.querySelector("#code")?.value ?? ""),
        visible: dev.parentElement.querySelector("#visible").checked,
        isDevice:true
  };
  console.log(a);
  config_api_post(a);
}

function save_box(box) {
  a = {
        id: box.getAttribute('box-id'),
        name: box.parentElement.querySelector("#name").value,
        code: Base64.encode(box.parentElement.querySelector("#code").value ?? ""),
        visible: box.parentElement.querySelector("#visible").checked,
        isBox:true
  };
  console.log(a);
	config_api_post(a);
}

async function cli() {

  const formData = new FormData();     
  formData.append("content", document.getElementById("cliinput").value);

  let response = await fetch(`/api/sms.php`, {
      method: "POST",
      body: formData
  });

  let data = await response.text();
  document.getElementsByClassName('clioutput')[0].innerHTML = data.replace(/\n/g, "<br>");
}

function addBox() {
	config_api_post({addBox: true});
}