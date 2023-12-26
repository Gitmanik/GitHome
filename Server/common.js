function api_post(type, obj)
{
    const formData  = new FormData();
      
    for(const name in obj) {
      formData.append(name, obj[name]);
    }
    fetch(`/api/${type}.php`, {
        method: "POST",
        body: formData
    }).then(() => {location.reload()});
}

function api_get(type, obj)
{
    const formData  = new FormData();
      
    for(const name in obj) {
      formData.append(name, obj[name]);
    }
    fetch(`/api/${type}.php`, {
        method: "GET",
        body: formData
    }).then(() => {location.reload()});
}